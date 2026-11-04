<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Giro;
use App\Models\Reto;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JuegoTest extends TestCase
{
    use RefreshDatabase;

    private function crearReto(string $titulo = 'Reto A'): Reto
    {
        return Reto::create([
            'titulo' => $titulo,
            'microleccion' => 'Texto de la microlección',
            'tipo_actividad' => 'escenario',
            'actividad' => [
                'escena' => '📁',
                'situacion' => 'Situación de prueba',
                'opciones' => [['texto' => 'Opción', 'correcta' => true, 'feedback' => 'Bien']],
            ],
            'consejo' => 'Consejo de prueba',
            'activo' => true,
        ]);
    }

    private function darGiro(User $user, Reto $reto): Giro
    {
        return Giro::create([
            'user_id' => $user->id,
            'reto_id' => $reto->id,
            'fecha' => today()->toDateString(),
        ]);
    }

    /* ---------- Acceso ---------- */

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get(route('ruleta'))->assertRedirect(route('login'));
    }

    public function test_jugador_no_puede_ver_los_indicadores_del_administrador(): void
    {
        $jugador = User::factory()->create();

        $this->actingAs($jugador)->get(route('admin.indicadores'))->assertForbidden();
    }

    public function test_administrador_puede_ver_los_indicadores(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $this->actingAs($admin)->get(route('admin.indicadores'))->assertOk();
    }

    /* ---------- Registro ---------- */

    public function test_registro_crea_un_jugador_y_lo_lleva_a_la_ruleta(): void
    {
        $respuesta = $this->post(route('registro'), [
            'seudonimo' => 'docente_uno',
            'password' => 'clave123',
            'perfil' => 'cumplidor',
            'consentimiento' => '1',
        ]);

        $respuesta->assertRedirect(route('ruleta'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['name' => 'docente_uno', 'rol' => 'jugador', 'perfil' => 'cumplidor']);
    }

    public function test_registro_exige_aceptar_el_consentimiento(): void
    {
        $respuesta = $this->post(route('registro'), [
            'seudonimo' => 'docente_dos',
            'password' => 'clave123',
            'perfil' => 'competitivo',
        ]);

        $respuesta->assertSessionHasErrors('consentimiento');
        $this->assertDatabaseMissing('users', ['name' => 'docente_dos']);
    }

    /* ---------- Ruleta (HU6) ---------- */

    public function test_girar_asigna_un_solo_reto_por_dia(): void
    {
        $jugador = User::factory()->create();
        $this->crearReto('Reto A');
        $this->crearReto('Reto B');
        $this->crearReto('Reto C');

        $primero = $this->actingAs($jugador)->postJson(route('ruleta.girar'));
        $primero->assertOk()->assertJsonStructure(['reto_id', 'titulo', 'tipo', 'url']);

        $segundo = $this->actingAs($jugador)->postJson(route('ruleta.girar'));

        $this->assertSame($primero->json('reto_id'), $segundo->json('reto_id'));
        $this->assertSame(1, Giro::count());
    }

    public function test_un_usuario_no_puede_tener_dos_giros_el_mismo_dia(): void
    {
        $jugador = User::factory()->create();
        $retoA = $this->crearReto('Reto A');
        $retoB = $this->crearReto('Reto B');

        $this->darGiro($jugador, $retoA);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->darGiro($jugador, $retoB);
    }

    public function test_no_se_puede_abrir_un_reto_que_no_salio_en_la_ruleta(): void
    {
        $jugador = User::factory()->create();
        $reto = $this->crearReto();

        $this->actingAs($jugador)
            ->get(route('retos.show', $reto))
            ->assertRedirect(route('ruleta'));
    }

    public function test_el_reto_del_dia_si_se_puede_abrir(): void
    {
        $jugador = User::factory()->create();
        $reto = $this->crearReto();
        $this->darGiro($jugador, $reto);

        $this->actingAs($jugador)
            ->get(route('retos.show', $reto))
            ->assertOk()
            ->assertSee('Reto A');
    }

    /* ---------- Práctica y aplicación (HU1) ---------- */

    public function test_practica_con_errores_guarda_5_puntos(): void
    {
        $jugador = User::factory()->create();
        $reto = $this->crearReto();
        $this->darGiro($jugador, $reto);

        $this->actingAs($jugador)->post(route('retos.practica', $reto), ['errores' => 2]);

        $this->assertDatabaseHas('eventos', [
            'user_id' => $jugador->id,
            'reto_id' => $reto->id,
            'tipo' => 'practica',
            'puntos' => 5,
            'errores' => 2,
        ]);
    }

    public function test_practica_de_un_reto_no_sorteado_no_se_guarda(): void
    {
        $jugador = User::factory()->create();
        $reto = $this->crearReto();

        $this->actingAs($jugador)
            ->post(route('retos.practica', $reto), ['errores' => 0])
            ->assertRedirect(route('ruleta'));

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_aplicar_sin_haber_practicado_no_suma_puntos(): void
    {
        $jugador = User::factory()->create();
        $reto = $this->crearReto();
        $this->darGiro($jugador, $reto);

        $this->actingAs($jugador)->post(route('retos.aplicar', $reto));

        $this->assertDatabaseMissing('eventos', ['tipo' => 'aplicacion']);
    }

    /* ---------- Ranking (HU3) ---------- */

    public function test_ranking_oculta_a_quien_lo_pidio(): void
    {
        $observador = User::factory()->create(['name' => 'observador']);
        $visible = User::factory()->create(['name' => 'docente_visible', 'visible_ranking' => true]);
        $oculto = User::factory()->create(['name' => 'docente_oculto', 'visible_ranking' => false]);

        foreach ([$visible, $oculto] as $u) {
            Evento::create(['user_id' => $u->id, 'tipo' => 'practica', 'puntos' => 10]);
        }

        $this->actingAs($observador)
            ->get(route('ranking'))
            ->assertOk()
            ->assertSee('docente_visible')
            ->assertDontSee('docente_oculto');
    }
}
<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Reto;
use App\Models\User;
use App\Services\Reglas\ReglaPuntos;
use App\Services\ServicioPuntos;
use Database\Seeders\InsigniaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServicioPuntosTest extends TestCase
{
    use RefreshDatabase;

    private ServicioPuntos $servicio;
    private User $user;
    private int $contadorRetos = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Fecha fija para que las pruebas no dependan de la hora real.
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00'));

        $this->servicio = new ServicioPuntos();
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /* ---------- Ayudas ---------- */

    private function crearReto(): Reto
    {
        $this->contadorRetos++;

        return Reto::create([
            'titulo' => 'Reto de prueba ' . $this->contadorRetos,
            'microleccion' => 'Texto de prueba',
            'tipo_actividad' => 'escenario',
            'actividad' => [],
            'activo' => true,
        ]);
    }

    private function evento(?Reto $reto, string $tipo, int $puntos, ?Carbon $fecha = null, int $errores = 0): Evento
    {
        $fecha = $fecha ?? Carbon::now();

        $evento = new Evento([
            'user_id' => $this->user->id,
            'reto_id' => $reto?->id,
            'tipo' => $tipo,
            'puntos' => $puntos,
            'errores' => $errores,
        ]);
        $evento->created_at = $fecha;
        $evento->updated_at = $fecha;
        $evento->save();

        return $evento;
    }

    private function aplicadoHace(int $dias, ?Reto $reto = null): Evento
    {
        return $this->evento($reto ?? $this->crearReto(), 'aplicacion', 20, Carbon::now()->subDays($dias));
    }

    /* ---------- Práctica (HU1) ---------- */

    public function test_practica_sin_errores_da_10_puntos(): void
    {
        $evento = $this->servicio->registrarPractica($this->user, $this->crearReto(), 0);

        $this->assertSame(10, $evento->puntos);
        $this->assertSame(0, $evento->errores);
        $this->assertSame('practica', $evento->tipo);
    }

    public function test_practica_con_errores_da_5_puntos_y_guarda_los_errores(): void
    {
        $evento = $this->servicio->registrarPractica($this->user, $this->crearReto(), 3);

        $this->assertSame(5, $evento->puntos);
        $this->assertSame(3, $evento->errores);
    }

    public function test_practica_repetida_no_suma_puntos(): void
    {
        $reto = $this->crearReto();

        $this->servicio->registrarPractica($this->user, $reto, 0);
        $segunda = $this->servicio->registrarPractica($this->user, $reto, 0);

        $this->assertNull($segunda);
        $this->assertSame(10, $this->user->puntosTotales());
    }

    public function test_errores_se_limitan_a_50(): void
    {
        $evento = $this->servicio->registrarPractica($this->user, $this->crearReto(), 500);

        $this->assertSame(50, $evento->errores);
    }

    /* ---------- Aplicación en clase (HU1) ---------- */

    public function test_aplicacion_el_mismo_dia_da_20_mas_bonus_rapido(): void
    {
        $reto = $this->crearReto();
        $this->servicio->registrarPractica($this->user, $reto, 0);

        $eventos = $this->servicio->registrarAplicacion($this->user, $reto);

        $this->assertSame(30, collect($eventos)->sum('puntos'));
        $this->assertEqualsCanonicalizing(['aplicacion', 'bonus_rapido'], collect($eventos)->pluck('tipo')->all());
    }

    public function test_aplicacion_despues_de_3_dias_no_da_bonus_rapido(): void
    {
        $reto = $this->crearReto();
        $this->evento($reto, 'practica', 10, Carbon::now()->subDays(5));

        $eventos = $this->servicio->registrarAplicacion($this->user, $reto);

        $this->assertSame(20, collect($eventos)->sum('puntos'));
        $this->assertSame(['aplicacion'], collect($eventos)->pluck('tipo')->all());
    }

    public function test_aplicacion_repetida_no_suma_puntos(): void
    {
        $reto = $this->crearReto();
        $this->servicio->registrarPractica($this->user, $reto, 0);

        $this->servicio->registrarAplicacion($this->user, $reto);
        $segunda = $this->servicio->registrarAplicacion($this->user, $reto);

        $this->assertSame([], $segunda);
    }

    public function test_aplicar_dos_dias_seguidos_da_bonus_de_racha(): void
    {
        $retoA = $this->crearReto();
        $retoB = $this->crearReto();

        $this->aplicadoHace(1, $retoA);
        $this->servicio->registrarPractica($this->user, $retoB, 0);

        $eventos = $this->servicio->registrarAplicacion($this->user, $retoB);

        // 20 aplicación + 10 bonus rápido + 15 racha
        $this->assertSame(45, collect($eventos)->sum('puntos'));
        $this->assertContains('bonus_racha', collect($eventos)->pluck('tipo')->all());
    }

    /* ---------- Racha (HU2) ---------- */

    public function test_racha_vacia_es_cero(): void
    {
        $this->assertSame(0, $this->servicio->racha($this->user));
    }

    public function test_racha_cuenta_dias_consecutivos_hasta_hoy(): void
    {
        $this->aplicadoHace(0);
        $this->aplicadoHace(1);
        $this->aplicadoHace(2);

        $this->assertSame(3, $this->servicio->racha($this->user));
    }

    public function test_racha_se_mantiene_si_hoy_aun_no_ha_aplicado(): void
    {
        $this->aplicadoHace(1);
        $this->aplicadoHace(2);

        $this->assertSame(2, $this->servicio->racha($this->user));
    }

    public function test_racha_se_rompe_con_un_dia_sin_actividad(): void
    {
        $this->aplicadoHace(0);
        $this->aplicadoHace(2);

        $this->assertSame(1, $this->servicio->racha($this->user));
    }

    public function test_racha_antigua_ya_no_cuenta(): void
    {
        $this->aplicadoHace(3);

        $this->assertSame(0, $this->servicio->racha($this->user));
    }

    /* ---------- Insignias (HU2) ---------- */

    public function test_primera_aplicacion_otorga_primera_estrategia(): void
    {
        $this->seed(InsigniaSeeder::class);
        $reto = $this->crearReto();
        $this->servicio->registrarPractica($this->user, $reto, 0);

        $this->servicio->registrarAplicacion($this->user, $reto);

        $this->assertContains('primera_estrategia', $this->user->insignias()->pluck('clave')->all());
    }

    public function test_cinco_retos_distintos_otorgan_cinco_estrategias(): void
    {
        $this->seed(InsigniaSeeder::class);

        for ($i = 0; $i < 5; $i++) {
            $this->aplicadoHace($i * 2);
        }
        $this->servicio->evaluarInsignias($this->user);

        $this->assertContains('cinco_estrategias', $this->user->insignias()->pluck('clave')->all());
    }

    public function test_tres_dias_seguidos_otorgan_racha_3(): void
    {
        $this->seed(InsigniaSeeder::class);

        $this->aplicadoHace(0);
        $this->aplicadoHace(1);
        $this->aplicadoHace(2);
        $this->servicio->evaluarInsignias($this->user);

        $this->assertContains('racha_3', $this->user->insignias()->pluck('clave')->all());
    }

    public function test_sin_logros_no_hay_insignias(): void
    {
        $this->seed(InsigniaSeeder::class);

        $this->servicio->evaluarInsignias($this->user);

        $this->assertCount(0, $this->user->insignias);
    }

    /* ---------- Niveles (HU2) ---------- */

    public static function niveles(): array
    {
        return [
            'cero puntos' => [0, 'Explorador'],
            'limite inferior de explorador' => [49, 'Explorador'],
            'inicio de practicante' => [50, 'Practicante'],
            'limite de practicante' => [149, 'Practicante'],
            'docente inclusivo' => [150, 'Docente inclusivo'],
        ];
    }

    #[DataProvider('niveles')]
    public function test_nivel_segun_puntos(int $puntos, string $nivel): void
    {
        if ($puntos > 0) {
            $this->evento(null, 'practica', $puntos);
        }

        $this->assertSame($puntos, $this->user->puntosTotales());
        $this->assertSame($nivel, $this->user->nivel());
    }

    /* ---------- Patrón Strategy (HU4) ---------- */

    public function test_se_puede_cambiar_una_regla_sin_tocar_el_servicio(): void
    {
        $reglaFija = new class implements ReglaPuntos {
            public function tipo(): string
            {
                return 'practica';
            }

            public function calcular(array $contexto): int
            {
                return 99;
            }
        };

        $servicio = new ServicioPuntos([$reglaFija]);
        $evento = $servicio->registrarPractica($this->user, $this->crearReto(), 0);

        $this->assertSame(99, $evento->puntos);
    }

    public function test_pedir_una_regla_inexistente_lanza_excepcion(): void
    {
        $servicio = new ServicioPuntos([]);

        $this->expectException(\InvalidArgumentException::class);

        $servicio->registrarPractica($this->user, $this->crearReto(), 0);
    }
}
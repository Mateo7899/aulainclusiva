<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Reto;
use App\Models\User;
use App\Services\ServicioIndicadores;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServicioIndicadoresTest extends TestCase
{
    use RefreshDatabase;

    private ServicioIndicadores $indicadores;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indicadores = new ServicioIndicadores();
    }

    private function evento(User $user, string $tipo, string $fecha = '2026-11-08 09:00:00', int $errores = 0): Evento
    {
        $reto = Reto::first() ?? Reto::create([
            'titulo' => 'Reto indicadores',
            'microleccion' => 'Texto',
            'tipo_actividad' => 'escenario',
            'actividad' => [],
            'activo' => true,
        ]);

        $evento = new Evento([
            'user_id' => $user->id,
            'reto_id' => $reto->id,
            'tipo' => $tipo,
            'puntos' => 10,
            'errores' => $errores,
        ]);
        $evento->created_at = Carbon::parse($fecha);
        $evento->updated_at = Carbon::parse($fecha);
        $evento->save();

        return $evento;
    }

    /* ---------- Sin datos (HU5) ---------- */

    public function test_sin_datos_los_indicadores_son_cero(): void
    {
        $this->assertSame(0.0, $this->indicadores->porcentajeRetosAplicados());
        $this->assertSame(0.0, $this->indicadores->porcentajeAciertoPrimerIntento());
        $this->assertSame([], $this->indicadores->usuariosActivosPorDia());
    }

    /* ---------- Indicador 1 (HU5) ---------- */

    public function test_porcentaje_de_retos_aplicados(): void
    {
        $u = User::factory()->create();

        foreach (range(1, 4) as $i) {
            $this->evento($u, 'practica');
        }
        $this->evento($u, 'aplicacion');
        $this->evento($u, 'aplicacion');

        // 2 aplicaciones / 4 prácticas = 50 %
        $this->assertSame(50.0, $this->indicadores->porcentajeRetosAplicados());
    }

    /* ---------- Indicador complementario (HU5) ---------- */

    public function test_porcentaje_de_practicas_sin_errores(): void
    {
        $u = User::factory()->create();

        $this->evento($u, 'practica', '2026-11-08 09:00:00', 0);
        $this->evento($u, 'practica', '2026-11-08 10:00:00', 0);
        $this->evento($u, 'practica', '2026-11-08 11:00:00', 1);
        $this->evento($u, 'practica', '2026-11-08 12:00:00', 4);

        $this->assertSame(50.0, $this->indicadores->porcentajeAciertoPrimerIntento());
    }

    /* ---------- Indicador 2 (HU5) ---------- */

    public function test_usuarios_activos_por_dia_cuenta_usuarios_distintos(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // Día 8: dos usuarios (uno con dos eventos). Día 9: uno.
        $this->evento($a, 'practica', '2026-11-08 09:00:00');
        $this->evento($a, 'aplicacion', '2026-11-08 15:00:00');
        $this->evento($b, 'practica', '2026-11-08 10:00:00');
        $this->evento($a, 'practica', '2026-11-09 09:00:00');

        $this->assertSame([
            ['dia' => '2026-11-08', 'usuarios' => 2],
            ['dia' => '2026-11-09', 'usuarios' => 1],
        ], $this->indicadores->usuariosActivosPorDia());
    }

    /* ---------- Totales ---------- */

    public function test_totales_de_jugadores_y_eventos(): void
    {
        User::factory()->create(['rol' => 'administrador']);
        $a = User::factory()->create();
        User::factory()->create();

        $this->evento($a, 'practica');
        $this->evento($a, 'aplicacion');

        $this->assertSame(2, $this->indicadores->totalJugadores());   // el administrador no cuenta
        $this->assertSame(2, $this->indicadores->totalEventos());
    }
}
<?php

namespace Tests\Unit;

use App\Services\Reglas\ReglaAplicacion;
use App\Services\Reglas\ReglaBonusRapido;
use App\Services\Reglas\ReglaPractica;
use App\Services\Reglas\ReglaRacha;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReglasPuntosTest extends TestCase
{
    /* ---------- ReglaPractica ---------- */

    public static function casosPractica(): array
    {
        return [
            'sin errores' => [0, 10],
            'un error' => [1, 5],
            'muchos errores' => [7, 5],
        ];
    }

    #[DataProvider('casosPractica')]
    public function test_practica_otorga_puntos_segun_errores(int $errores, int $esperado): void
    {
        $this->assertSame($esperado, (new ReglaPractica())->calcular(['errores' => $errores]));
    }

    public function test_practica_sin_dato_de_errores_cuenta_como_sin_errores(): void
    {
        $this->assertSame(10, (new ReglaPractica())->calcular([]));
    }

    /* ---------- ReglaAplicacion ---------- */

    public function test_aplicacion_siempre_otorga_20_puntos(): void
    {
        $regla = new ReglaAplicacion();

        $this->assertSame(20, $regla->calcular([]));
        $this->assertSame(20, $regla->calcular(['racha' => 5, 'dias_desde_practica' => 9]));
    }

    /* ---------- ReglaBonusRapido ---------- */

    public static function casosBonusRapido(): array
    {
        return [
            'mismo dia' => [0, 10],
            'un dia' => [1, 10],
            'limite de 3 dias' => [3, 10],
            'pasado el limite' => [4, 0],
            'mucho despues' => [30, 0],
        ];
    }

    #[DataProvider('casosBonusRapido')]
    public function test_bonus_rapido_depende_de_los_dias(int $dias, int $esperado): void
    {
        $this->assertSame($esperado, (new ReglaBonusRapido())->calcular(['dias_desde_practica' => $dias]));
    }

    public function test_bonus_rapido_sin_practica_previa_no_da_puntos(): void
    {
        $this->assertSame(0, (new ReglaBonusRapido())->calcular(['dias_desde_practica' => null]));
        $this->assertSame(0, (new ReglaBonusRapido())->calcular([]));
    }

    /* ---------- ReglaRacha ---------- */

    public static function casosRacha(): array
    {
        return [
            'sin racha' => [0, 0],
            'un dia' => [1, 0],
            'dos dias' => [2, 15],
            'cinco dias' => [5, 15],
        ];
    }

    #[DataProvider('casosRacha')]
    public function test_bonus_de_racha_desde_dos_dias(int $racha, int $esperado): void
    {
        $this->assertSame($esperado, (new ReglaRacha())->calcular(['racha' => $racha]));
    }

    public function test_racha_sin_dato_no_da_puntos(): void
    {
        $this->assertSame(0, (new ReglaRacha())->calcular([]));
    }

    /* ---------- Tipos de evento ---------- */

    public function test_cada_regla_declara_su_tipo_de_evento(): void
    {
        $this->assertSame('practica', (new ReglaPractica())->tipo());
        $this->assertSame('aplicacion', (new ReglaAplicacion())->tipo());
        $this->assertSame('bonus_rapido', (new ReglaBonusRapido())->tipo());
        $this->assertSame('bonus_racha', (new ReglaRacha())->tipo());
    }
}
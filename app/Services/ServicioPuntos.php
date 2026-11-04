<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Insignia;
use App\Models\Reto;
use App\Models\User;
use App\Services\Reglas\ReglaAplicacion;
use App\Services\Reglas\ReglaBonusRapido;
use App\Services\Reglas\ReglaPractica;
use App\Services\Reglas\ReglaPuntos;
use App\Services\Reglas\ReglaRacha;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServicioPuntos
{
    /** @var ReglaPuntos[] */
    private array $reglas;

    /** @param ReglaPuntos[]|null $reglas */
    public function __construct(?array $reglas = null)
    {
        $this->reglas = $reglas ?? [
            new ReglaPractica(),
            new ReglaAplicacion(),
            new ReglaBonusRapido(),
            new ReglaRacha(),
        ];
    }

    /** Registra la práctica completada. Devuelve null si ya la había completado. */
    public function registrarPractica(User $user, Reto $reto, int $errores = 0): ?Evento
    {
        $yaPractico = Evento::where('user_id', $user->id)
            ->where('reto_id', $reto->id)
            ->where('tipo', 'practica')
            ->exists();

        if ($yaPractico) {
            return null;
        }

        $errores = max(0, min($errores, 50));
        $regla = $this->reglaPorTipo('practica');

        return Evento::create([
            'user_id' => $user->id,
            'reto_id' => $reto->id,
            'tipo' => 'practica',
            'puntos' => $regla->calcular(['errores' => $errores]),
            'errores' => $errores,
        ]);
    }

    /**
     * Registra que el docente aplicó el reto en clase.
     * Devuelve la lista de eventos creados, o [] si ya lo había aplicado.
     *
     * @return Evento[]
     */
    public function registrarAplicacion(User $user, Reto $reto, ?Carbon $ahora = null): array
    {
        $ahora = $ahora ?? Carbon::now();

        $yaAplico = Evento::where('user_id', $user->id)
            ->where('reto_id', $reto->id)
            ->where('tipo', 'aplicacion')
            ->exists();

        if ($yaAplico) {
            return [];
        }

        $practica = Evento::where('user_id', $user->id)
            ->where('reto_id', $reto->id)
            ->where('tipo', 'practica')
            ->first();

        $contexto = [
            'dias_desde_practica' => $practica ? $practica->created_at->diffInDays($ahora, true) : null,
            'racha' => $this->racha($user, $ahora, true),
        ];

        $creados = [];

        DB::transaction(function () use ($user, $reto, $contexto, &$creados) {
            foreach ($this->reglas as $regla) {
                if ($regla->tipo() === 'practica') {
                    continue;
                }

                $puntos = $regla->calcular($contexto);

                if ($puntos > 0) {
                    $creados[] = Evento::create([
                        'user_id' => $user->id,
                        'reto_id' => $reto->id,
                        'tipo' => $regla->tipo(),
                        'puntos' => $puntos,
                    ]);
                }
            }
        });

        $this->evaluarInsignias($user, $ahora);

        return $creados;
    }

    /** Días consecutivos con al menos una aplicación de reto. */
    public function racha(User $user, ?Carbon $hoy = null, bool $incluirHoy = false): int
    {
        $hoy = ($hoy ?? Carbon::now())->copy();

        $dias = $user->eventos()
            ->where('tipo', 'aplicacion')
            ->get()
            ->map(fn ($e) => $e->created_at->toDateString())
            ->unique()
            ->values()
            ->all();

        $dias = array_flip($dias);

        if ($incluirHoy) {
            $dias[$hoy->toDateString()] = true;
        }

        $dia = $hoy->copy();

        // Si hoy aún no aplicó nada, la racha se cuenta desde ayer.
        if (!isset($dias[$dia->toDateString()])) {
            $dia->subDay();
        }

        $racha = 0;
        while (isset($dias[$dia->toDateString()])) {
            $racha++;
            $dia->subDay();
        }

        return $racha;
    }

    /** Otorga las insignias que el usuario ya merece. */
    public function evaluarInsignias(User $user, ?Carbon $ahora = null): void
    {
        $retosAplicados = $user->eventos()
            ->where('tipo', 'aplicacion')
            ->distinct('reto_id')
            ->count('reto_id');

        $merecidas = [];

        if ($retosAplicados >= 1) {
            $merecidas[] = 'primera_estrategia';
        }
        if ($retosAplicados >= 5) {
            $merecidas[] = 'cinco_estrategias';
        }
        if ($this->racha($user, $ahora) >= 3) {
            $merecidas[] = 'racha_3';
        }

        $ids = Insignia::whereIn('clave', $merecidas)->pluck('id');
        $user->insignias()->syncWithoutDetaching($ids);
    }

    private function reglaPorTipo(string $tipo): ReglaPuntos
    {
        foreach ($this->reglas as $regla) {
            if ($regla->tipo() === $tipo) {
                return $regla;
            }
        }

        throw new \InvalidArgumentException("No existe la regla: {$tipo}");
    }
}
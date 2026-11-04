<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Giro;
use App\Models\Insignia;
use App\Models\Reto;
use App\Models\User;
use App\Services\Reglas\ReglaPractica;
use App\Services\ServicioPuntos;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class JuegoController extends Controller
{
    public function __construct(private ServicioPuntos $puntos)
    {
    }

    /** Giro que el usuario hizo hoy (o null). */
    private function giroDeHoy(User $user): ?Giro
    {
        return Giro::where('user_id', $user->id)
            ->where('fecha', today()->toDateString())
            ->first();
    }

    /** Ids de los retos cuya práctica ya completó el usuario. */
    private function retosPracticados(User $user): array
    {
        return Evento::where('user_id', $user->id)
            ->where('tipo', 'practica')
            ->pluck('reto_id')
            ->all();
    }

    /** Solo se puede jugar el reto de hoy o uno ya practicado. */
    private function puedeJugar(User $user, Reto $reto): bool
    {
        if (in_array($reto->id, $this->retosPracticados($user))) {
            return true;
        }

        $giro = $this->giroDeHoy($user);

        return $giro !== null && $giro->reto_id === $reto->id;
    }

    /* ---------- Ruleta ---------- */

    public function ruleta()
    {
        $user = auth()->user();
        $giro = $this->giroDeHoy($user);
        $practicados = $this->retosPracticados($user);

        $todos = Reto::where('activo', true)->orderBy('id')->pluck('id')->values();

        $segmentos = $todos
            ->reject(fn ($id) => in_array($id, $practicados))
            ->map(fn ($id) => ['id' => $id, 'n' => $todos->search($id) + 1])
            ->values();

        $aplicadoHoy = $giro
            ? Evento::where('user_id', $user->id)->where('reto_id', $giro->reto_id)->where('tipo', 'aplicacion')->exists()
            : false;

        return view('ruleta', [
            'retoHoy' => $giro?->reto,
            'practicadoHoy' => $giro ? in_array($giro->reto_id, $practicados) : false,
            'aplicadoHoy' => $aplicadoHoy,
            'segmentos' => $segmentos,
        ]);
    }

    public function girar()
    {
        $user = auth()->user();
        $giro = $this->giroDeHoy($user);

        if (!$giro) {
            $pendientes = Reto::where('activo', true)
                ->whereNotIn('id', $this->retosPracticados($user))
                ->pluck('id');

            if ($pendientes->isEmpty()) {
                return response()->json(['error' => 'Ya completaste todos los retos.'], 422);
            }

            try {
                $giro = Giro::create([
                    'user_id' => $user->id,
                    'reto_id' => $pendientes->random(),
                    'fecha' => today()->toDateString(),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                // Doble clic: ya existe el giro de hoy.
                $giro = $this->giroDeHoy($user);
            }
        }

        $reto = $giro->reto;

        return response()->json([
            'reto_id' => $reto->id,
            'titulo' => $reto->titulo,
            'tipo' => $reto->nombre_actividad,
            'url' => route('retos.show', $reto),
        ]);
    }

    /* ---------- Retos ---------- */

    public function index()
    {
        $user = auth()->user();
        $retos = Reto::where('activo', true)->orderBy('id')->get();
        $giro = $this->giroDeHoy($user);

        $aplicados = Evento::where('user_id', $user->id)->where('tipo', 'aplicacion')->pluck('reto_id')->all();

        return view('retos.index', [
            'retos' => $retos,
            'practicados' => $this->retosPracticados($user),
            'aplicados' => $aplicados,
            'hoyId' => $giro?->reto_id,
            'puntos' => $user->puntosTotales(),
            'nivel' => $user->nivel(),
            'racha' => $this->puntos->racha($user),
        ]);
    }

    public function show(Reto $reto)
    {
        $user = auth()->user();

        if (!$this->puedeJugar($user, $reto)) {
            return redirect()->route('ruleta')
                ->with('error', 'Gira la ruleta para descubrir tu reto de hoy.');
        }

        $practica = Evento::where('user_id', $user->id)
            ->where('reto_id', $reto->id)
            ->where('tipo', 'practica')
            ->first();

        $regla = new ReglaPractica();

        return view('retos.show', [
            'reto' => $reto,
            'practicado' => $practica !== null,
            'errores' => $practica?->errores,
            'aplicado' => Evento::where('user_id', $user->id)->where('reto_id', $reto->id)->where('tipo', 'aplicacion')->exists(),
            'ptsMax' => $regla->calcular(['errores' => 0]),
            'ptsMin' => $regla->calcular(['errores' => 1]),
        ]);
    }

    public function practica(Request $request, Reto $reto)
    {
        $user = auth()->user();

        if (!$this->puedeJugar($user, $reto)) {
            return redirect()->route('ruleta')
                ->with('error', 'Gira la ruleta para descubrir tu reto de hoy.');
        }

        $datos = $request->validate([
            'errores' => 'required|integer|min:0|max:50',
        ]);

        $evento = $this->puntos->registrarPractica($user, $reto, (int) $datos['errores']);

        if (!$evento) {
            return back()->with('error', 'Ya habías completado esta práctica.');
        }

        $mensaje = $evento->errores === 0
            ? "¡Práctica perfecta! Ganaste {$evento->puntos} puntos. Ahora aplícala en clase."
            : "¡Práctica completada! Ganaste {$evento->puntos} puntos. Repasa el consejo y aplícalo en clase.";

        return back()->with('ok', $mensaje);
    }

    public function aplicar(Reto $reto)
    {
        $user = auth()->user();

        $practico = Evento::where('user_id', $user->id)
            ->where('reto_id', $reto->id)
            ->where('tipo', 'practica')
            ->exists();

        if (!$practico) {
            return back()->with('error', 'Primero completa la práctica de este reto.');
        }

        $eventos = $this->puntos->registrarAplicacion($user, $reto);

        if (empty($eventos)) {
            return back()->with('error', 'Ya registraste este reto. No suma puntos otra vez.');
        }

        $total = collect($eventos)->sum('puntos');

        return back()->with('ok', "¡Estrategia aplicada! Ganaste {$total} puntos.");
    }

    /* ---------- Progreso y ranking ---------- */

    public function progreso()
    {
        $user = auth()->user();

        $obtenidas = $user->insignias()->pluck('insignias.id')->all();

        return view('progreso', [
            'user' => $user,
            'puntos' => $user->puntosTotales(),
            'nivel' => $user->nivel(),
            'racha' => $this->puntos->racha($user),
            'insignias' => Insignia::orderBy('id')->get(),
            'obtenidas' => $obtenidas,
            'ultimos' => $user->eventos()->with('reto')->latest()->take(10)->get(),
        ]);
    }

    public function ranking()
    {
        $top = User::where('rol', 'jugador')
            ->where('visible_ranking', true)
            ->withSum('eventos', 'puntos')
            ->orderByDesc('eventos_sum_puntos')
            ->take(10)
            ->get();

        return view('ranking', ['top' => $top, 'user' => auth()->user()]);
    }

    public function visibilidad(Request $request)
    {
        $user = auth()->user();
        $user->visible_ranking = !$user->visible_ranking;
        $user->save();

        return back()->with('ok', $user->visible_ranking
            ? 'Ahora apareces en el ranking.'
            : 'Ya no apareces en el ranking.');
    }
}
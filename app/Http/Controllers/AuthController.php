<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'seudonimo' => 'required|string',
            'password' => 'required|string',
        ]);

        $credenciales = [
            'email' => $this->correoInterno($datos['seudonimo']),
            'password' => $datos['password'],
        ];

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            return Auth::user()->esAdministrador()
                ? redirect()->route('admin.indicadores')
                : redirect()->route('ruleta');
        }

        return back()
            ->withErrors(['seudonimo' => 'Seudónimo o contraseña incorrectos.'])
            ->onlyInput('seudonimo');
    }

    public function mostrarRegistro()
    {
        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'seudonimo' => 'required|alpha_dash|min:3|max:30|unique:users,name',
            'password' => 'required|string|min:6',
            'perfil' => 'required|in:cumplidor,competitivo',
            'consentimiento' => 'accepted',
        ], [
            'seudonimo.unique' => 'Ese seudónimo ya está en uso. Elige otro.',
            'seudonimo.alpha_dash' => 'Usa solo letras, números, guion o guion bajo (sin espacios).',
            'consentimiento.accepted' => 'Debes aceptar el consentimiento para participar.',
        ]);

        $user = User::create([
            'name' => $datos['seudonimo'],
            'email' => $this->correoInterno($datos['seudonimo']),
            'password' => Hash::make($datos['password']),
            'rol' => 'jugador',
            'perfil' => $datos['perfil'],
            'visible_ranking' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('ruleta')
            ->with('ok', '¡Bienvenido a AulaInclusiva! Gira la ruleta para descubrir tu primer reto.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** No se piden correos reales: se genera uno interno a partir del seudónimo. */
    private function correoInterno(string $seudonimo): string
    {
        return Str::lower($seudonimo) . '@aulainclusiva.local';
    }
}

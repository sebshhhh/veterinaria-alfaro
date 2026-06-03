<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar formulario de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesar login con DNI.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'dni' => 'required|digits:8',
            'password' => 'required|min:4',
        ], [
            'dni.required' => 'El DNI es obligatorio',
            'dni.digits' => 'El DNI debe tener 8 dígitos',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'Minimo 4 caracteres',
        ]);

        if (Auth::attempt($request->only('dni', 'password'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard')->with('toast', [
                'type' => 'success',
                'message' => 'Bienvenida, ' . auth()->user()->name,
            ]);
        }

        throw ValidationException::withMessages([
            'dni' => 'DNI o contraseña incorrectos',
        ]);
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('toast', [
            'type' => 'info',
            'message' => 'Sesion cerrada correctamente',
        ]);
    }
}


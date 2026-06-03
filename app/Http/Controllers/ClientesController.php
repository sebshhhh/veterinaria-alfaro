<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Mascotas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Clientes::with(['mascotas' => function ($relationQuery) {
            $relationQuery->orderBy('nombre');
        }])->withCount('mascotas');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('dni', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('id')->paginate(12)->withQueryString();

        $stats = [
            'total' => Clientes::count(),
            'nuevos' => Clientes::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'con_mascotas' => Clientes::has('mascotas')->count(),
            'mascotas' => Mascotas::count(),
        ];

        return view('clientes.index', compact('clientes', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateCliente($request);
        $redirectTo = $this->resolveRedirectTo($request->input('redirect_to'));

        Clientes::create($validated);

        return redirect()->route($redirectTo)->with('toast', [
            'type' => 'success',
            'message' => 'Cliente registrado correctamente.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Clientes::findOrFail($id);

        $validated = $this->validateCliente($request, $cliente);
        $redirectTo = $this->resolveRedirectTo($request->input('redirect_to'));

        $cliente->update($validated);

        return redirect()->route($redirectTo)->with('toast', [
            'type' => 'success',
            'message' => 'Cliente actualizado correctamente.',
        ]);
    }

    public function destroy(Clientes $cliente)
    {
        if ($cliente->mascotas()->exists()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar este cliente porque tiene mascotas registradas.',
            ]);
        }

        $cliente->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cliente eliminado.',
        ]);
    }

    private function validateCliente(Request $request, ?Clientes $cliente = null): array
    {
        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'digits:8', Rule::unique('clientes', 'dni')->ignore($cliente?->id)],
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|digits:9',
            'direccion' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clientes', 'email')->ignore($cliente?->id)],
        ]);

        $validator->after(function ($validator) use ($request, $cliente) {
            $nombre = trim((string) $request->input('nombre'));
            $telefono = trim((string) $request->input('telefono'));
            $direccion = trim((string) $request->input('direccion'));
            $email = trim((string) $request->input('email'));
            $telefonoDigitos = preg_replace('/\D+/', '', $telefono);

            if ($nombre === '') {
                $validator->errors()->add('nombre', 'Escribe el nombre del cliente.');
            }

            if ($telefono === '') {
                $validator->errors()->add('telefono', 'Escribe un telefono de contacto.');
            } elseif (strlen($telefonoDigitos) !== 9) {
                $validator->errors()->add('telefono', 'El celular debe tener exactamente 9 dígitos.');
            }

            if ($direccion === '') {
                $validator->errors()->add('direccion', 'Escribe una direccion de referencia.');
            }

            if ($email !== '') {
                $duplicateEmail = Clientes::query()
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)]);

                if ($cliente) {
                    $duplicateEmail->whereKeyNot($cliente->id);
                }

                if ($duplicateEmail->exists()) {
                    $validator->errors()->add('email', 'Ya existe un cliente con ese correo registrado.');
                }
            }
        });

        $validated = $validator->validate();
        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['telefono'] = trim((string) $validated['telefono']);
        $validated['direccion'] = trim((string) $validated['direccion']);
        $validated['email'] = trim((string) ($validated['email'] ?? '')) ?: null;

        return $validated;
    }

    private function resolveRedirectTo(?string $redirectTo): string
    {
        return in_array($redirectTo, ['clientes.index', 'mascotas.index'], true)
            ? $redirectTo
            : 'clientes.index';
    }
}


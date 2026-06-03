<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistoriasClinicasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $fecha = $request->input('fecha');
        $origen = $request->input('origen');
        $tipo = $request->input('tipo');
        $requestedMascotaId = $request->input('mascota_id');

        $query = HistoriaClinica::with(['mascota.cliente', 'tratamientos', 'recetas', 'vacunas', 'seguimientos', 'ventas', 'servicioProducto', 'cita']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('diagnostico', 'like', '%' . $search . '%')
                    ->orWhere('observaciones', 'like', '%' . $search . '%')
                    ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                        $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhere('dni', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha', $fecha);
        }

        if (!empty($origen)) {
            $query->where('origen_atencion', $origen);
        }

        if (!empty($tipo)) {
            $query->where('tipo_atencion', $tipo);
        }

        if (!empty($requestedMascotaId)) {
            $query->where('mascota_id', $requestedMascotaId);
        }

        $historias = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $stats = [
            'total' => HistoriaClinica::count(),
            'hoy' => HistoriaClinica::whereDate('fecha', now()->toDateString())->count(),
            'mes' => HistoriaClinica::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->count(),
            'mascotas' => HistoriaClinica::distinct()->count('mascota_id'),
        ];

        $historiaMascotas = Mascotas::with('cliente:id,nombre')
            ->orderBy('nombre')
            ->get(['id', 'cliente_id', 'nombre', 'tipo_animal', 'foto']);

        $prefillMascotaId = $historiaMascotas->contains('id', (int) $requestedMascotaId)
            ? (int) $requestedMascotaId
            : null;

        $shouldOpenCreate = $request->boolean('open_create') && $prefillMascotaId;
        $selectedMascota = $prefillMascotaId
            ? $historiaMascotas->firstWhere('id', $prefillMascotaId)
            : null;

        return view('historias-clinicas.index', compact(
            'historias',
            'stats',
            'historiaMascotas',
            'prefillMascotaId',
            'shouldOpenCreate',
            'selectedMascota'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateHistoria($request);
        $validated['origen_atencion'] = 'manual';
        $validated['tipo_atencion'] = 'consulta';

        HistoriaClinica::create($validated);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Evento clínico agregado al historial del paciente.',
        ]);
    }

    public function update(Request $request, HistoriaClinica $historias_clinica)
    {
        $validated = $this->validateHistoria($request);

        $historias_clinica->update($validated);

        return redirect()->route('historias-clinicas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Evento clínico actualizado correctamente.',
        ]);
    }

    public function destroy(HistoriaClinica $historias_clinica)
    {
        $historias_clinica->loadCount(['tratamientos', 'recetas', 'vacunas', 'seguimientos', 'ventas']);

        $hasClinicalLinks = (
            $historias_clinica->tratamientos_count +
            $historias_clinica->recetas_count +
            $historias_clinica->vacunas_count +
            $historias_clinica->seguimientos_count +
            $historias_clinica->ventas_count
        ) > 0;

        if ($hasClinicalLinks) {
            return redirect()->route('historias-clinicas.index')->with('toast', [
                'type' => 'error',
                'message' => 'No se puede eliminar este evento porque tiene vacunas, tratamientos, recetas, ventas o seguimientos vinculados.',
            ]);
        }

        $historias_clinica->delete();

        return redirect()->route('historias-clinicas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Evento clínico eliminado correctamente.',
        ]);
    }

    private function validateHistoria(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'mascota_id' => 'required|exists:mascotas,id',
            'diagnostico' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'fecha' => 'required|date',
            'peso' => 'nullable|numeric|min:0|max:999.99',
            'temperatura' => 'nullable|numeric|min:30|max:45',
        ]);

        $validator->after(function ($validator) use ($request) {
            $diagnostico = trim((string) $request->input('diagnostico'));
            $observaciones = trim((string) $request->input('observaciones'));

            if ($diagnostico === '' && $observaciones === '') {
                $validator->errors()->add('diagnostico', 'Ingresa un diagnóstico u observaciones para guardar el evento clínico.');
            }
        });

        return $validator->validateWithBag('historiaStore');
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecetasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $periodo = $request->input('periodo');
        $fecha = $request->input('fecha');
        $requestedMascotaId = (int) $request->input('mascota_id');
        $requestedHistoriaId = (int) $request->input('historia_clinica_id');
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $query = Receta::with(['historiaClinica.mascota.cliente', 'historiaClinica.servicioProducto']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('medicamentos', 'like', '%' . $search . '%')
                    ->orWhere('indicaciones', 'like', '%' . $search . '%')
                    ->orWhereHas('historiaClinica', function ($historiaQuery) use ($search) {
                        $historiaQuery->where('diagnostico', 'like', '%' . $search . '%')
                            ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                                $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                        $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                            ->orWhere('dni', 'like', '%' . $search . '%');
                                    });
                            });
                    });
            });
        }

        if ($periodo === 'hoy') {
            $query->whereDate('created_at', $today);
        }

        if ($periodo === 'semana') {
            $query->whereBetween('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59']);
        }

        if ($periodo === 'mes') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        if (!empty($fecha)) {
            $query->whereDate('created_at', $fecha);
        }

        if ($requestedHistoriaId) {
            $query->where('historia_clinica_id', $requestedHistoriaId);
        }

        if ($requestedMascotaId) {
            $query->whereHas('historiaClinica', function ($historiaQuery) use ($requestedMascotaId) {
                $historiaQuery->where('mascota_id', $requestedMascotaId);
            });
        }

        $recetas = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $stats = [
            'total' => Receta::count(),
            'hoy' => Receta::whereDate('created_at', $today)->count(),
            'semana' => Receta::whereBetween('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59'])->count(),
            'mes' => Receta::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'mascotas' => DB::table('recetas')
                ->join('historias_clinicas', 'recetas.historia_clinica_id', '=', 'historias_clinicas.id')
                ->distinct()
                ->count('historias_clinicas.mascota_id'),
        ];

        $historiaCatalogo = HistoriaClinica::with(['mascota.cliente:id,nombre,dni', 'servicioProducto:id,nombre'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get([
                'id',
                'mascota_id',
                'servicio_producto_id',
                'fecha',
                'diagnostico',
                'observaciones',
                'peso',
                'temperatura',
                'tipo_atencion',
            ]);

        $prefillHistoriaId = $historiaCatalogo->contains('id', $requestedHistoriaId)
            ? $requestedHistoriaId
            : null;

        $selectedHistoria = $prefillHistoriaId
            ? $historiaCatalogo->firstWhere('id', $prefillHistoriaId)
            : null;

        $selectedMascota = $selectedHistoria?->mascota;

        if (!$selectedMascota && $requestedMascotaId) {
            $selectedMascota = Mascotas::with('cliente:id,nombre')
                ->find($requestedMascotaId, ['id', 'cliente_id', 'nombre', 'tipo_animal', 'foto']);
        }

        $shouldOpenCreate = $request->boolean('open_create') && $prefillHistoriaId;

        return view('recetas.index', compact(
            'recetas',
            'stats',
            'historiaCatalogo',
            'prefillHistoriaId',
            'selectedHistoria',
            'selectedMascota',
            'shouldOpenCreate'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateReceta($request);

        Receta::create($validated);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Receta registrada correctamente.',
        ]);
    }

    public function update(Request $request, Receta $receta)
    {
        $validated = $this->validateReceta($request, $receta);

        $receta->update($validated);

        return redirect()->route('recetas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Receta actualizada correctamente.',
        ]);
    }

    public function destroy(Receta $receta)
    {
        $receta->delete();

        return redirect()->route('recetas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Receta eliminada correctamente.',
        ]);
    }

    private function validateReceta(Request $request, ?Receta $receta = null): array
    {
        $validator = Validator::make($request->all(), [
            'historia_clinica_id' => 'required|exists:historias_clinicas,id',
            'medicamentos' => 'required|string',
            'indicaciones' => 'required|string',
        ]);

        $validator->after(function ($validator) use ($request, $receta) {
            $medicamentos = trim((string) $request->input('medicamentos'));
            $indicaciones = trim((string) $request->input('indicaciones'));

            if ($medicamentos === '' || $indicaciones === '') {
                $validator->errors()->add('medicamentos', 'Completa medicamentos e indicaciones para guardar la receta.');
            }

            if ($medicamentos !== '' && $indicaciones !== '' && $request->filled('historia_clinica_id')) {
                $duplicateQuery = Receta::query()
                    ->where('historia_clinica_id', $request->input('historia_clinica_id'))
                    ->where('medicamentos', $medicamentos)
                    ->where('indicaciones', $indicaciones);

                if ($receta) {
                    $duplicateQuery->whereKeyNot($receta->id);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add('indicaciones', 'Ya existe una receta igual registrada para esta atención clínica.');
                }
            }
        });

        $validated = $validator->validateWithBag('recetaStore');
        $validated['medicamentos'] = trim((string) $validated['medicamentos']);
        $validated['indicaciones'] = trim((string) $validated['indicaciones']);

        return $validated;
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConfiguracionController extends Controller
{
    public function index()
    {
        ConfiguracionSistema::ensureDefaults();

        $settings = ConfiguracionSistema::query()
            ->orderByRaw("FIELD(grupo, 'general', 'operacion', 'documentos')")
            ->orderBy('id')
            ->get()
            ->groupBy('grupo');

        $systemInfo = [
            ['label' => 'Framework', 'value' => 'Laravel ' . app()->version()],
            ['label' => 'PHP', 'value' => PHP_VERSION],
            ['label' => 'Base de datos', 'value' => strtoupper((string) config('database.default'))],
            ['label' => 'Entorno', 'value' => strtoupper((string) config('app.env'))],
        ];

        return view('configuracion.index', compact('settings', 'systemInfo'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clinic_name' => ['required', 'string', 'max:100'],
            'clinic_subtitle' => ['nullable', 'string', 'max:140'],
            'clinic_phone' => ['nullable', 'digits:9'],
            'clinic_email' => ['nullable', 'email', 'max:120'],
            'clinic_address' => ['nullable', 'string', 'max:255'],
            'appointment_interval' => ['required', 'integer', Rule::in([15, 20, 30, 45, 60])],
            'default_control_time' => ['required', 'date_format:H:i'],
            'vaccine_alert_days' => ['required', 'integer', 'min:1', 'max:30'],
            'control_alert_days' => ['required', 'integer', 'min:1', 'max:30'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:999'],
            'currency_symbol' => ['required', 'string', 'max:6'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ], [
            'clinic_name.required' => 'El nombre de la clínica es obligatorio.',
            'clinic_phone.digits' => 'El teléfono debe tener 9 dígitos.',
            'clinic_email.email' => 'Ingresa un correo válido.',
            'appointment_interval.in' => 'Selecciona una duración válida para las citas.',
            'default_control_time.date_format' => 'La hora de control debe tener formato HH:MM.',
        ]);

        $validated = $validator->validateWithBag('configuracionUpdate');

        foreach ($validated as $clave => $valor) {
            ConfiguracionSistema::query()
                ->where('clave', $clave)
                ->update(['valor' => is_null($valor) ? '' : trim((string) $valor)]);
        }

        return redirect()
            ->route('configuracion.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Configuración guardada correctamente.',
            ]);
    }
}
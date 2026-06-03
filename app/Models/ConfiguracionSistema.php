<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuraciones_sistema';

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'tipo',
        'etiqueta',
        'descripcion',
    ];

    public static function defaults(): array
    {
        return [
            'clinic_name' => ['DRA. ALFARO', 'general', 'text', 'Nombre de la clínica', 'Nombre visible en el sistema.'],
            'clinic_subtitle' => ['Sistema de Gestión Veterinaria', 'general', 'text', 'Subtítulo institucional', 'Texto corto bajo el nombre de la clínica.'],
            'clinic_phone' => ['', 'general', 'text', 'Teléfono', 'Número de contacto principal.'],
            'clinic_email' => ['', 'general', 'email', 'Correo institucional', 'Correo de contacto o administración.'],
            'clinic_address' => ['', 'general', 'textarea', 'Dirección', 'Dirección física de la clínica.'],
            'appointment_interval' => ['30', 'operacion', 'number', 'Duración estándar de cita', 'Minutos sugeridos para organizar agenda.'],
            'default_control_time' => ['09:00', 'operacion', 'time', 'Hora por defecto para controles', 'Hora usada cuando el sistema crea citas de retorno automáticas.'],
            'vaccine_alert_days' => ['3', 'operacion', 'number', 'Alerta previa de vacunas', 'Días de anticipación para mostrar vacunas próximas.'],
            'control_alert_days' => ['7', 'operacion', 'number', 'Alerta previa de controles', 'Días de anticipación para mostrar controles próximos.'],
            'low_stock_threshold' => ['5', 'operacion', 'number', 'Stock mínimo', 'Cantidad desde la cual un producto aparece como stock bajo.'],
            'currency_symbol' => ['S/', 'documentos', 'text', 'Moneda', 'Símbolo usado para importes del sistema.'],
            'receipt_footer' => ['Gracias por confiar en DRA. ALFARO.', 'documentos', 'textarea', 'Pie para documentos', 'Texto institucional para recetas, comprobantes o documentos internos.'],
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::defaults() as $clave => [$valor, $grupo, $tipo, $etiqueta, $descripcion]) {
            self::firstOrCreate(
                ['clave' => $clave],
                compact('valor', 'grupo', 'tipo', 'etiqueta', 'descripcion')
            );
        }
    }

    public static function valores(): array
    {
        try {
            self::ensureDefaults();

            return self::query()
                ->pluck('valor', 'clave')
                ->all();
        } catch (\Throwable) {
            return collect(self::defaults())
                ->mapWithKeys(fn ($setting, $clave) => [$clave => $setting[0]])
                ->all();
        }
    }

    public static function valor(string $clave, mixed $default = null): mixed
    {
        $defaults = self::defaults();
        $fallback = $default ?? ($defaults[$clave][0] ?? null);

        try {
            return self::query()->where('clave', $clave)->value('valor') ?? $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public static function numero(string $clave, int|float $default): int|float
    {
        $value = self::valor($clave, $default);

        return is_numeric($value) ? $value + 0 : $default;
    }
}
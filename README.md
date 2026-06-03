# Sistema de Gestión Veterinaria Dra. Alfaro

Sistema web desarrollado en Laravel para centralizar la gestión operativa, clinica y administrativa de una veterinaria.

El sistema permite registrar clientes, mascotas, citas, atenciones, vacunas, controles de retorno, tratamientos, recetas, productos, ventas, reportes y configuración desde un flujo integrado.

## Objetivo

Reemplazar procesos manuales de la clínica por un sistema automatizado que permita:

- Atender pacientes con cita o sin cita.
- Mantener una ficha única por mascota.
- Conservar el historial clínico cronologico.
- Programar vacunas y controles de retorno.
- Conectar tratamientos, recetas y ventas.
- Consultar alertas, dashboard y reportes para tomar decisiones.

## Tecnologias

- Laravel 12
- PHP 8.2
- MySQL
- Blade
- Tailwind CSS
- JavaScript modular por vista
- Vite

## Estructura Principal

```text
app/
  Http/Controllers/      Controladores por módulo.
  Models/                Modelos Eloquent y relaciones.
  Services/              Logica automatizada y reglas de negocio.
  Traits/                Reutilizacion de lógica comun.

resources/
  views/                 Vistas Blade separadas por módulo.
  css/                   Estilos separados por layout, componentes y modulos.
  js/                    Entrada Vite y scripts globales reutilizables.

public/
  js/modules/            JavaScript especifico por módulo.
  img/                   Imagenes y recursos visuales.
  build/                 Archivos compilados por Vite.

routes/
  web.php                Rutas principales del sistema.
  auth.php               Rutas de autenticacion.

database/
  migrations/            Estructura de base de datos.
  seeders/               Datos iniciales.

docs/
  ESTRUCTURA_PROYECTO.md Guia de carpetas y archivos.
  FLUJO_FUNCIONAL.md     Flujo profesional del sistema.
  GUIA_EXPLICACION.md    Apoyo para explicar el proyecto.
```

## Modulos

- Dashboard: resumen operativo del dia.
- Citas: agenda de pacientes programados.
- Nueva atención: atención directa para pacientes sin cita.
- Mascotas: ficha única del paciente.
- Historial clínico: línea de tiempo de atenciones.
- Vacunas: aplicadas, programadas y próximas dosis.
- Controles: retornos automáticos generados por atenciones, vacunas o tratamientos.
- Tratamientos: planes indicados y próximo control.
- Recetas: indicaciones profesionales para el propietario.
- Clientes: datos de propietarios.
- Productos: inventario y servicios.
- Ventas: cobros conectados al flujo operativo.
- Reportes: lectura gerencial de datos.
- Configuración: reglas y resumen de automatizacion.

## Flujo General

```text
Cliente / Mascota
        |
        v
Cita programada o Nueva atención
        |
        v
Atención clínica
        |
        +-- Vacuna aplicada o programada
        +-- Tratamiento opcional
        +-- Receta opcional
        +-- Control de retorno opcional
        +-- Venta / cobro si corresponde
        |
        v
Historial clínico + Reportes + Alertas
```

## Comandos Utiles

Instalar dependencias:

```bash
composer install
npm install
```

Ejecutar migraciones:

```bash
php artisan migrate
```

Levantar servidor local:

```bash
php artisan serve
```

Compilar assets:

```bash
npm run build
```

Validar vistas:

```bash
php artisan view:cache
```

## Documentacion Interna

Revisar la carpeta `docs/` para explicar el proyecto de forma ordenada:

- `docs/ESTRUCTURA_PROYECTO.md`
- `docs/FLUJO_FUNCIONAL.md`
- `docs/GUIA_EXPLICACION.md`


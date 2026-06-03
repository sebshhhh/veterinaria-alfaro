# Estructura del Proyecto

Este documento ordena el proyecto para que sea facil de mantener y explicar.

## Criterio de Orden

El sistema respeta la estructura natural de Laravel. No se deben mover carpetas criticas como `app`, `resources`, `routes`, `database`, `public`, `storage`, `vendor` o `node_modules`, porque Laravel y Composer dependen de esas rutas.

La organizacion correcta se maneja asi:

- Controladores separados por módulo.
- Modelos separados por entidad.
- Servicios para reglas de negocio y automatizacion.
- Vistas Blade agrupadas por módulo.
- JavaScript agrupado por módulo.
- Migraciones ordenadas cronológicamente.
- Documentacion tecnica dentro de `docs`.

## Carpetas Principales

| Carpeta | Funcion |
| --- | --- |
| `app/Http/Controllers` | Recibe solicitudes, valida datos y coordina cada módulo. |
| `app/Models` | Representa tablas de la base de datos y relaciones. |
| `app/Services` | Contiene reglas de negocio, automatizacion y operaciones reutilizables. |
| `app/Traits` | Reutiliza lógica comun entre controladores. |
| `resources/views` | Contiene las pantallas Blade, agrupadas por módulo. |
| `public/js/modules` | Contiene interaccion JavaScript separada por módulo. |
| `resources/css` | Estilos del sistema organizados por layout, componentes y modulos. |
| `routes/web.php` | Define rutas web protegidas por autenticacion. |
| `database/migrations` | Define la estructura de tablas y cambios de base de datos. |
| `storage` | Archivos generados por Laravel: cache, logs, sesiones, backups. |
| `docs` | Documentacion del proyecto para mantenimiento y exposicion. |

## Controladores por Modulo

| Controlador | Modulo |
| --- | --- |
| `DashboardController` | Panel principal. |
| `CitasController` | Agenda y atencion de citas. |
| `AtencionRapidaController` | Atención directa sin cita. |
| `MascotasController` | Ficha única del paciente. |
| `HistoriasClinicasController` | Historial clínico cronologico. |
| `VacunasController` | Vacunas aplicadas y programadas. |
| `SeguimientosController` | Controles de retorno. |
| `TratamientosController` | Tratamientos médicos. |
| `RecetasController` | Recetas e indicaciones. |
| `ClientesController` | Propietarios. |
| `ProductosController` | Inventario y servicios. |
| `VentasController` | Ventas y cobros. |
| `ReportesController` | Reportes gerenciales. |
| `ConfiguraciónController` | Configuración y reglas del sistema. |

## Servicios Importantes

| Servicio | Responsabilidad |
| --- | --- |
| `AttentionFlowService` | Valida el flujo de atencion y decide que bloques son obligatorios u opcionales. |
| `ClinicalAttentionService` | Guarda atencion, vacuna, tratamiento, receta y control de retorno de forma conectada. |
| `SalesService` | Gestiona reglas de ventas y detalle de cobros. |
| `WorkspaceNotificationService` | Calcula alertas y pendientes del sistema. |

## Vistas

Las vistas están separadas por módulo:

```text
resources/views/
  atencion-rapida/
  citas/
  clientes/
  configuracion/
  historias-clinicas/
  mascotas/
  productos/
  recetas/
  reportes/
  seguimientos/
  tratamientos/
  vacunas/
  ventas/
```

Cada carpeta contiene su `index.blade.php` y, cuando corresponde, una carpeta `modals/` para formularios emergentes.

## JavaScript

Cada modulo importante tiene su archivo JS:

```text
public/js/modules/
  atencion-rapida.js
  citas.js
  clientes.js
  dashboard.js
  historias-clinicas.js
  mascotas.js
  productos.js
  recetas.js
  seguimientos.js
  tratamientos.js
  vacunas.js
  ventas.js
```

Esto permite explicar que la interfaz esta separada por responsabilidad y no mezclada en un solo archivo.

El JavaScript global de Vite tambien esta separado:

```text
resources/js/
  app.js                  Entrada principal.
  bootstrap.js            Configuración base.
  core/
    alert-center.js       Centro de alertas.
    modal-portal.js       Manejo global de modales.
```

## CSS

Los estilos globales ya no están acumulados en un solo archivo grande. `resources/css/app.css` funciona como entrada principal e importa estilos por responsabilidad:

```text
resources/css/
  app.css
  layout/
    workspace.css         Estructura general del sistema.
  components/
    action-buttons.css   Botones reutilizables de acciones.
    alpine.css           Utilidades de Alpine, como x-cloak.
    alerts.css            Centro de alertas.
    attention-flow.css    Accesos visuales del flujo de atencion.
    modals.css            Capa global de modales.
    pagination.css        Paginacion reutilizable.
  modules/
    reportes.css          Estilos especificos de impresion de reportes.
    shared.css            Componentes visuales reutilizables por módulo.
```

Esta separación permite mantener el diseno sin mezclar reglas de layout, componentes y modulos.

Las vistas Blade no deben tener bloques `<style>`. Solo se permiten estilos inline cuando son valores dinamicos calculados por PHP, por ejemplo colores de graficos, porcentajes o barras de avance.

## Archivos que No Deben Tocarse Manualmente

| Carpeta / archivo | Motivo |
| --- | --- |
| `vendor/` | Dependencias PHP administradas por Composer. |
| `node_modules/` | Dependencias JavaScript administradas por npm. |
| `public/build/` | Archivos generados por Vite. |
| `storage/framework/` | Cache, sesiones y vistas compiladas de Laravel. |
| `.env` | Configuración local y credenciales. |

## Buena Practica Aplicada

El proyecto queda ordenado no por mover archivos sin criterio, sino por separar responsabilidades:

- Laravel conserva su estructura estándar.
- Cada modulo tiene controlador, vista y JavaScript propio.
- La automatizacion compleja esta en servicios.
- Los estilos están separados por responsabilidad para evitar un CSS gigante y dificil de explicar.
- La documentacion vive en `docs`.
- Los temporales y caches quedan ignorados por `.gitignore`.


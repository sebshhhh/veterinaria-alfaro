# Guia para Explicar el Proyecto

Esta guia sirve para exponer el sistema de forma clara.

## Explicacion Corta

El proyecto es un sistema web de gestión veterinaria desarrollado en Laravel. Su objetivo es digitalizar y automatizar los procesos de la clínica: agenda, atencion de pacientes, vacunas, tratamientos, recetas, controles de retorno, ventas y reportes.

## Arquitectura

El sistema usa el patron MVC:

- Modelo: representa tablas y relaciones de la base de datos.
- Vista: muestra las pantallas del sistema.
- Controlador: recibe solicitudes, valida datos y coordina la respuesta.

Ademas, usa servicios para centralizar reglas importantes:

- `AttentionFlowService`: valida el flujo de atencion.
- `ClinicalAttentionService`: guarda la atención y conecta módulos automáticamente.
- `WorkspaceNotificationService`: genera alertas operativas.
- `SalesService`: gestiona reglas de ventas.

## Como Explicar la Organizacion

Puedes decir:

> El proyecto mantiene la estructura estándar de Laravel para no romper buenas prácticas. Los controladores están separados por módulo, las vistas están agrupadas por carpeta, los archivos JavaScript están separados por pantalla y la lógica automatizada se centraliza en servicios.

## Como Explicar la Automatizacion

Puedes decir:

> El sistema evita duplicidad de registros. Cuando se atiende una mascota, desde un solo formulario se puede registrar la atención y, si corresponde, guardar vacuna, tratamiento, receta o control de retorno. Cada dato se almacena en su módulo correspondiente sin que el usuario tenga que registrarlo dos veces.

## Ejemplo de Flujo para Exponer

Caso: mascota llega por vacuna.

1. Se abre `Citas` o `Nueva atención`.
2. Se busca la mascota.
3. Se elige tipo `Vacuna`.
4. Se registra la vacuna aplicada.
5. Si corresponde, se programa próxima dosis.
6. El sistema crea el control de retorno y la cita futura.
7. La atención queda en el historial clínico.

## Diferencia entre Modulos Clave

| Modulo | Explicacion |
| --- | --- |
| `Mascotas` | Ficha única del paciente. |
| `Historial clínico` | Línea de tiempo de atenciones y eventos. |
| `Citas` | Agenda de pacientes programados. |
| `Nueva atención` | Atención directa para pacientes sin cita. |
| `Vacunas` | Control preventivo aplicado o programado. |
| `Controles` | Retornos pendientes o revisiónes posteriores. |
| `Reportes` | Informacion para decisiones administrativas. |

## Buenas Practicas que Puedes Mencionar

- Separación por módulos.
- Uso de MVC.
- Servicios para reglas de negocio.
- Validaciones antes de guardar.
- Relaciones entre tablas.
- Migraciones para controlar la base de datos.
- Uso de dashboard y reportes para toma de decisiones.
- `.gitignore` para evitar subir temporales, dependencias y cache.

## Frase para Sustentar el Orden del Proyecto

> El orden del proyecto no consiste en mover archivos sin criterio, sino en mantener una estructura mantenible. Laravel ya define carpetas especializadas y el sistema respeta esa arquitectura: controladores para coordinar, modelos para datos, servicios para automatizacion, vistas para interfaz y documentacion para explicar el mantenimiento del sistema.


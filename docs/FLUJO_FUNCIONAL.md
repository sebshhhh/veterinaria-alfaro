# Flujo Funcional del Sistema

Este documento explica como funciona el sistema desde el punto de vista de la clínica veterinaria.

## Idea Principal

El sistema esta pensado para que la veterinaria no repita registros manuales. Una atencion puede conectar automáticamente historial clínico, vacunas, tratamientos, recetas, controles de retorno, citas y ventas.

## Flujo General

```text
Cliente
  |
  v
Mascota
  |
  v
Cita programada o Nueva atención
  |
  v
Atención clínica
  |
  +-- Vacuna
  +-- Tratamiento
  +-- Receta
  +-- Control de retorno
  +-- Venta
  |
  v
Historial clínico, alertas, dashboard y reportes
```

## Citas

Se usa cuando el paciente ya tiene una visita programada.

Flujo:

1. Se registra o busca cliente y mascota.
2. Se agenda fecha y hora.
3. Cuando llega el paciente, se usa `Atender cita`.
4. Al guardar, la cita pasa a completada.
5. La atención se agrega al historial clínico.

## Nueva Atencion

Se usa cuando el paciente llega sin cita.

Flujo:

1. Se busca rapidamente la mascota.
2. Si no existe, se puede crear cliente y mascota desde el mismo flujo.
3. Se elige tipo de atención: consulta, vacuna, control, servicio u otro.
4. Se completan solo los bloques necesarios.
5. El sistema conecta automáticamente la información con sus modulos.

## Vacunas

La vacuna puede ser:

- Aplicada: cuando se coloca durante la atención.
- Programada: cuando queda pendiente para una fecha futura.

Automatizacion:

- Si se programa una próxima vacuna, el sistema crea un control de retorno.
- Si corresponde, tambien se sincroniza una cita pendiente.
- Cuando se atiende esa cita y se aplica la vacuna, el control queda atendido.

## Tratamientos

Se usa cuando la mascota recibe un manejo médico por varios dias.

Regla:

- El tratamiento es opcional dentro de una atencion.
- Solo se guarda si realmente se completa descripcion y fecha de inicio.
- Si tiene próximo control, se genera un control de retorno.

## Recetas

Se usa para medicamentos o indicaciones formales para el propietario.

Regla:

- Es opcional.
- Solo se guarda cuando se completan medicamentos e indicaciones.

## Controles de Retorno

Antes el concepto podia sentirse como "seguimiento". Para el usuario es mas claro entenderlo como `Controles`.

Sirve para saber que mascota debe volver y por que motivo.

Puede nacer desde:

- Una atencion que requiere revisión posterior.
- Una vacuna programada.
- Un tratamiento con próximo control.
- Un registro manual excepcional.

Ejemplo:

```text
Mascota con diarrea
  -> Nueva atención
  -> Se marca próximo control en 3 dias
  -> El sistema crea control de retorno
  -> El sistema genera cita de retorno
  -> Al atender la cita, se actualiza la evolucion
```

## Ficha vs Historial Clinico

Para explicar el sistema:

- `Mascotas` es la ficha única del paciente. Resume datos, alertas y estado general.
- `Historial clínico` es la línea de tiempo. Muestra eventos: atenciones, vacunas, tratamientos, recetas y controles.

No son dos cosas repetidas. Se complementan:

- Ficha = resumen del paciente.
- Historial = detalle cronologico.

## Ventas

Ventas conecta la parte administrativa con la atención.

Permite registrar cobros por:

- Productos.
- Servicios.
- Tratamientos.
- Atenciones relacionadas.

## Reportes

El modulo de reportes permite ver datos gerenciales:

- Atenciones realizadas.
- Ingresos.
- Mascotas atendidas.
- Vacunas aplicadas.
- Servicios mas usados.
- Riesgos operativos.

## Dashboard

El dashboard resume la operacion del dia:

- Clientes y mascotas.
- Citas de hoy.
- Atenciones.
- Vacunas.
- Controles.
- Ventas.
- Alertas criticas.

## Flujo Profesional Recomendado para la Clinica

1. Si el paciente tiene cita, entrar por `Citas`.
2. Si llega sin cita, entrar por `Nueva atención`.
3. Si solo se quiere consultar datos del paciente, entrar por `Mascotas`.
4. Si se quiere ver todo lo realizado, entrar por `Historial clínico`.
5. Si hay retornos pendientes, entrar por `Controles`.
6. Para decisiones, revisar `Dashboard` y `Reportes`.


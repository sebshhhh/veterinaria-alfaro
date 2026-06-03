@include('mascotas.partials.form', [
    'cliente' => $cliente,
    'mascota' => $mascota,
    'isEdit' => true,
    'pageTag' => 'Edicion de mascota',
    'pageTitle' => 'Editar mascota',
    'pageDescription' => 'Actualiza la información de ' . $mascota->nombre . ' perteneciente a ' . $cliente->nombre . '.',
    'backUrl' => route('mascotas.index'),
    'backLabel' => 'Volver a mascotas',
    'formAction' => route('mascotas.update', $mascota),
    'submitLabel' => 'Actualizar mascota',
])


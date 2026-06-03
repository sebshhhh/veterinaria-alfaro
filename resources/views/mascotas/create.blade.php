@include('mascotas.partials.form', [
    'cliente' => $cliente,
    'isEdit' => false,
    'pageTag' => 'Registro de mascota',
    'pageTitle' => 'Nueva mascota',
    'pageDescription' => 'Estas registrando una mascota para ' . $cliente->nombre . '.',
    'backUrl' => route('clientes.index'),
    'backLabel' => 'Volver a clientes',
    'formAction' => route('mascotas.store'),
    'submitLabel' => 'Guardar mascota',
])

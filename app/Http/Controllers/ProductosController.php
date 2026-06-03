<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductosController extends Controller
{
    private const PRODUCT_CATEGORIES = [
        'medicamento' => 'Medicamento',
        'vacuna' => 'Vacuna',
        'insumo' => 'Insumo clínico',
        'higiene' => 'Higiene y cuidado',
        'alimento' => 'Alimento',
        'accesorio' => 'Accesorio',
        'otro_producto' => 'Otro producto',
    ];

    private const SERVICE_CATEGORIES = [
        'consulta' => 'Consulta clínica',
        'estetica' => 'Estética',
        'procedimiento' => 'Procedimiento',
        'vacunacion' => 'Servicio de vacunación',
        'laboratorio' => 'Laboratorio',
        'hospedaje' => 'Hospedaje',
        'otro_servicio' => 'Otro servicio',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $tipo = $request->input('tipo');
        $stock = $request->input('stock');
        $categoria = $request->input('categoria');

        $query = Producto::query()->withCount(['tratamientos', 'detalleVentas']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('descripcion', 'like', '%' . $search . '%');
            });
        }

        if ($tipo === 'producto') {
            $query->where('es_servicio', false);
        }

        if ($tipo === 'servicio') {
            $query->where('es_servicio', true);
        }

        if ($stock === 'bajo') {
            $query->where('es_servicio', false)
                ->where('stock', '>', 0)
                ->where('stock', '<=', 5);
        }

        if ($stock === 'agotado') {
            $query->where('es_servicio', false)
                ->where('stock', '<=', 0);
        }

        if (!empty($categoria) && array_key_exists($categoria, $this->categoryLabels())) {
            $query->where('categoria', $categoria);
        }

        $productos = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Producto::count(),
            'servicios' => Producto::where('es_servicio', true)->count(),
            'fisicos' => Producto::where('es_servicio', false)->count(),
            'bajo_stock' => Producto::where('es_servicio', false)->where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'agotados' => Producto::where('es_servicio', false)->where('stock', '<=', 0)->count(),
        ];

        $categoryOptions = [
            'Productos físicos' => self::PRODUCT_CATEGORIES,
            'Servicios' => self::SERVICE_CATEGORIES,
        ];
        $categoryLabels = $this->categoryLabels();

        return view('productos.index', compact('productos', 'stats', 'categoryOptions', 'categoryLabels'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProducto($request);
        Producto::create($validated);

        return redirect()->route('productos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Registro guardado en Servicios e Inventario.',
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $this->validateProducto($request, $producto);
        $producto->update($validated);

        return redirect()->route('productos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Registro actualizado correctamente.',
        ]);
    }

    public function destroy(Producto $producto)
    {
        if ($producto->tratamientos()->exists() || $producto->detalleVentas()->exists()) {
            return redirect()->route('productos.index')->with('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar este registro porque ya está vinculado a tratamientos o ventas.',
            ]);
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Registro eliminado correctamente.',
        ]);
    }

    private function validateProducto(Request $request, ?Producto $producto = null): array
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'es_servicio' => 'nullable|boolean',
            'categoria' => 'nullable|string|max:80',
        ]);

        $validator->after(function ($validator) use ($request, $producto) {
            $nombre = trim((string) $request->input('nombre'));
            $descripcion = trim((string) $request->input('descripcion'));
            $esServicio = filter_var($request->input('es_servicio'), FILTER_VALIDATE_BOOLEAN);
            $stock = $request->input('stock');
            $categoria = (string) $request->input('categoria');

            if ($nombre === '') {
                $validator->errors()->add('nombre', 'Escribe un nombre para el producto o servicio.');
            }

            if (!$esServicio && ($stock === null || $stock === '')) {
                $validator->errors()->add('stock', 'Indica el stock disponible del producto.');
            }

            if ($esServicio && $descripcion === '') {
                $validator->errors()->add('descripcion', 'Agrega una descripción breve para el servicio.');
            }

            if ($categoria === '') {
                $validator->errors()->add('categoria', 'Selecciona una categoría para ordenar el registro.');
            }

            if ($categoria !== '') {
                $validCategories = $esServicio ? self::SERVICE_CATEGORIES : self::PRODUCT_CATEGORIES;

                if (!array_key_exists($categoria, $validCategories)) {
                    $validator->errors()->add('categoria', 'La categoría seleccionada no corresponde al tipo elegido.');
                }
            }

            $duplicateQuery = Producto::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)]);

            if ($producto) {
                $duplicateQuery->whereKeyNot($producto->id);
            }

            if ($duplicateQuery->exists()) {
                $validator->errors()->add('nombre', 'Ya existe un producto o servicio con ese nombre.');
            }
        });

        $validated = $validator->validateWithBag('productoStore');
        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['descripcion'] = trim((string) ($validated['descripcion'] ?? '')) ?: null;
        $validated['es_servicio'] = (bool) ($validated['es_servicio'] ?? false);
        $validated['stock'] = $validated['es_servicio'] ? 0 : (int) ($validated['stock'] ?? 0);
        $validated['categoria'] = $validated['categoria'] ?? ($validated['es_servicio'] ? 'consulta' : 'medicamento');

        return $validated;
    }

    private function categoryLabels(): array
    {
        return self::PRODUCT_CATEGORIES + self::SERVICE_CATEGORIES;
    }
}

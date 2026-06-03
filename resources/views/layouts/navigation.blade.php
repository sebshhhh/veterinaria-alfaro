@php
    $menuGroups = [
        [
            'title' => 'Operación diaria',
            'items' => [
                ['route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'citas.index', 'pattern' => 'citas.*', 'icon' => 'calendar', 'label' => 'Citas'],
                ['route' => 'atencion-rapida.index', 'pattern' => 'atencion-rapida.*', 'icon' => 'zap', 'label' => 'Nueva atención'],
                ['route' => 'vacunas.index', 'pattern' => 'vacunas.*', 'icon' => 'shield', 'label' => 'Vacunas'],
            ],
        ],
        [
            'title' => 'Consulta clínica',
            'items' => [
                ['route' => 'mascotas.index', 'pattern' => 'mascotas.*', 'icon' => 'heart', 'label' => 'Mascotas'],
                ['route' => 'historias-clinicas.index', 'pattern' => 'historias-clinicas.*', 'icon' => 'file-text', 'label' => 'Historial clínico'],
                ['route' => 'seguimientos.index', 'pattern' => 'seguimientos.*', 'icon' => 'git-merge', 'label' => 'Controles'],
                ['route' => 'tratamientos.index', 'pattern' => 'tratamientos.*', 'icon' => 'activity', 'label' => 'Tratamientos'],
                ['route' => 'recetas.index', 'pattern' => 'recetas.*', 'icon' => 'clipboard', 'label' => 'Recetas'],
            ],
        ],
        [
            'title' => 'Gestión',
            'items' => [
                ['route' => 'clientes.index', 'pattern' => 'clientes.*', 'icon' => 'user', 'label' => 'Clientes'],
                ['route' => 'productos.index', 'pattern' => 'productos.*', 'icon' => 'package', 'label' => 'Servicios e inventario'],
                ['route' => 'ventas.index', 'pattern' => 'ventas.*', 'icon' => 'shopping-bag', 'label' => 'Caja y cobros'],
                ['route' => 'reportes.index', 'pattern' => 'reportes.*', 'icon' => 'bar-chart-2', 'label' => 'Reportes'],
            ],
        ],
    ];

@endphp

<nav class="workspace-sidebar flex w-80 shrink-0 min-h-screen flex-col bg-gradient-to-b from-blue-900 via-blue-800 to-blue-700 text-white shadow-2xl"
     :class="{ 'workspace-sidebar--open': sidebarOpen }">
    <div class="px-6 py-6 lg:py-10 flex flex-col items-center justify-center bg-gradient-to-r from-blue-700 to-blue-600 rounded-b-3xl shadow-xl">
        <button type="button"
                class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/15 lg:hidden"
                @click="sidebarOpen = false"
                aria-label="Cerrar menú">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
        <h1 class="workspace-brand-title text-3xl font-extrabold text-white text-center drop-shadow-lg">
            {{ $workspaceClinicSettings['clinic_name'] ?? 'DRA. ALFARO' }}
        </h1>
        <p class="text-sm text-blue-100 mt-2 text-center uppercase tracking-widest">
            {{ $workspaceClinicSettings['clinic_subtitle'] ?? 'Sistema de Gestión Veterinaria' }}
        </p>
    </div>

    <div class="workspace-sidebar__scroll flex-1 px-5 py-5 pb-8">
        <div class="space-y-6">
            @foreach($menuGroups as $group)
                <section class="space-y-2">
                    <p class="px-2 text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/80">{{ $group['title'] }}</p>
                    <ul class="space-y-2">
                        @foreach($group['items'] as $item)
                            @php $itemActive = request()->routeIs($item['pattern']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @click="sidebarOpen = false"
                                   class="flex items-center gap-4 px-5 py-3 rounded-xl {{ $itemActive ? 'bg-gradient-to-r from-blue-600 to-blue-500 shadow-lg' : 'hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-400' }} transition-all duration-300 transform hover:scale-[1.01]">
                                    <i data-feather="{{ $item['icon'] }}"></i>
                                    <span class="text-lg font-medium">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</nav>



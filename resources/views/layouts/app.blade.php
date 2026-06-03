<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Veterinaria - DRA. ALFARO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>

<body class="workspace-body min-h-screen lg:flex" x-data="{ sidebarOpen: false }">
    

<!-- SONIDO LOGIN -->
<audio id="loginSound" src="{{ asset('sounds/login.mp3') }}" preload="auto"></audio>

<!-- ===================== -->
<!-- TOAST GLOBAL MODERNO -->
<!-- ===================== -->
<div id="toast"
     class="fixed bottom-6 right-6 px-5 py-4 rounded-xl shadow-2xl z-50
            opacity-0 pointer-events-none transform translate-y-6 scale-95
            transition-all duration-700 text-sm font-medium">
</div>

<script>
window.showToast = function(message, type = "success") {

    const toast = document.getElementById("toast");
    if (!toast) return;

    toast.innerText = message;

    // Reset base
    toast.className = `
        fixed bottom-6 right-6 px-5 py-4 rounded-xl shadow-lg z-50
        transition-all duration-700 transform
        opacity-0 translate-y-6 scale-95
        text-sm font-medium backdrop-blur-md
    `;

    // Estilo suave y corporativo
    if (type === "success") {
        toast.classList.add("bg-white", "text-gray-800", "border-l-4", "border-green-500");
    } 
    else if (type === "error") {
        toast.classList.add("bg-white", "text-gray-800", "border-l-4", "border-red-500");
    } 
    else {
        toast.classList.add("bg-white", "text-gray-800", "border-l-4", "border-blue-500");
    }

    // Entrada suave
    requestAnimationFrame(() => {
        toast.classList.remove("opacity-0", "translate-y-6", "scale-95");
        toast.classList.add("opacity-100", "translate-y-0", "scale-100");
    });

    // Salida suave
    setTimeout(() => {
        toast.classList.remove("opacity-100", "translate-y-0", "scale-100");
        toast.classList.add("opacity-0", "translate-y-6", "scale-95");
    }, 2800);
};
</script>

<!-- TOAST DESDE SESSION -->
@if(session('toast'))
<script>
document.addEventListener("DOMContentLoaded", () => {

    const message = @json(session('toast')['message']);
    const type = @json(session('toast')['type']);

    showToast(message, type);

    // sonido solo para login exitoso
    if (type === "success") {
        const sound = document.getElementById("loginSound");
        if (sound) {
            sound.volume = 0.5;
            sound.play().catch(() => {
                console.log("Autoplay bloqueado");
            });
        }
    }
});
</script>
@endif

<!-- Sidebar -->
<button type="button"
        class="workspace-sidebar-backdrop"
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        aria-label="Cerrar menu"></button>
@include('layouts.navigation')

<!-- Main content -->
<div class="workspace-shell flex min-w-0 flex-1 flex-col">

    <!-- HEADER -->
    <header class="workspace-header">
        <div class="flex min-w-0 items-center gap-3 lg:hidden">
            <button type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-blue-100 bg-white text-blue-700 shadow-sm transition hover:bg-blue-50"
                    @click="sidebarOpen = true"
                    aria-label="Abrir menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-slate-950">{{ $workspaceClinicSettings['clinic_name'] ?? 'DRA. ALFARO' }}</p>
                <p class="truncate text-[10px] font-bold uppercase tracking-[0.18em] text-blue-600">Sistema veterinario</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div id="workspaceAlertCenter" class="relative workspace-alert-center">
                <button id="workspaceAlertTrigger"
                        type="button"
                        class="workspace-alert-trigger"
                        aria-label="Abrir notificaciones"
                        aria-expanded="false"
                        aria-controls="workspaceAlertPanel">
                    <span class="workspace-alert-trigger__icon">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0A2 2 0 0 1 7.5 15.11V10a4.5 4.5 0 1 1 9 0v5.11a2 2 0 0 1-1.643 1.972ZM9.75 17.25a2.25 2.25 0 0 0 4.5 0" />
                        </svg>
                    </span>
                    <span class="workspace-alert-trigger__text">
                        <span class="workspace-alert-trigger__label">Alertas</span>
                        <span class="workspace-alert-trigger__meta">
                            {{ $workspaceNotifications['meta'] ?? 'Sin pendientes' }}
                        </span>
                    </span>
                    @if(($workspaceNotifications['has_items'] ?? false))
                        <span class="workspace-alert-trigger__badge">
                            {{ $workspaceNotifications['display_total'] ?? '0' }}
                        </span>
                    @endif
                </button>

                <div id="workspaceAlertPanel"
                     class="workspace-alert-panel"
                     hidden
                     aria-hidden="true">
                            <div class="workspace-alert-panel__hero">
                                <div class="workspace-alert-panel__hero-icon">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0A2 2 0 0 1 7.5 15.11V10a4.5 4.5 0 1 1 9 0v5.11a2 2 0 0 1-1.643 1.972ZM9.75 17.25a2.25 2.25 0 0 0 4.5 0" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-100/90">Centro de alertas</p>
                                    <h3 class="mt-1 text-xl font-bold text-white">{{ $workspaceNotifications['headline'] ?? 'Notificaciones' }}</h3>
                                    <p class="mt-1 text-sm text-blue-100/90">
                                        {{ $workspaceNotifications['active_items'] ?? 0 }} alerta{{ ($workspaceNotifications['active_items'] ?? 0) === 1 ? '' : 's' }}
                                        · {{ $workspaceNotifications['total'] ?? 0 }} caso{{ ($workspaceNotifications['total'] ?? 0) === 1 ? '' : 's' }} impactado{{ ($workspaceNotifications['total'] ?? 0) === 1 ? '' : 's' }}
                                    </p>
                                    <p class="mt-2 text-sm text-blue-100/80">{{ $workspaceNotifications['focus_line'] ?? 'Sin pendientes.' }}</p>
                                </div>
                                <button type="button"
                                        onclick="window.closeWorkspaceAlertCenter?.()"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/14 text-white transition hover:bg-white/20"
                                        aria-label="Cerrar alertas">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                                    </svg>
                                </button>
                            </div>

                            @if(($workspaceNotifications['has_items'] ?? false))
                                <div class="workspace-alert-panel__body">
                                    <div class="workspace-alert-overview">
                                        @foreach(($workspaceNotifications['overview_chips'] ?? []) as $overviewChip)
                                            <span class="workspace-alert-overview__chip workspace-alert-overview__chip--{{ $overviewChip['tone'] }}">
                                                <span>{{ $overviewChip['label'] }}</span>
                                                <strong>{{ $overviewChip['value'] }}</strong>
                                            </span>
                                        @endforeach
                                    </div>

                                    @if(!empty($workspaceNotifications['primary_action']))
                                        @php $primaryAction = $workspaceNotifications['primary_action']; @endphp
                                        <a href="{{ $primaryAction['url'] }}" class="workspace-alert-primary">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0 flex-1">
                                                    <p class="workspace-alert-primary__eyebrow">Atiende primero</p>
                                                    <h4 class="mt-1 text-lg font-bold text-slate-950">{{ $primaryAction['title'] }}</h4>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $primaryAction['detail'] }}</p>
                                                    <p class="mt-3 text-sm font-medium text-slate-500">{{ $primaryAction['hint'] }}</p>
                                                </div>
                                                <div class="workspace-alert-primary__stats">
                                                    <span class="workspace-alert-primary__severity workspace-alert-primary__severity--{{ strtolower($primaryAction['severity']) }}">{{ $primaryAction['severity'] }}</span>
                                                    <span class="workspace-alert-primary__count">{{ $primaryAction['count'] }}</span>
                                                    <span class="workspace-alert-primary__window">{{ $primaryAction['window'] }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex items-center justify-between gap-3">
                                                <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $primaryAction['section'] }}</span>
                                                <span class="text-sm font-bold text-blue-700">{{ $primaryAction['action'] }}</span>
                                            </div>
                                        </a>
                                    @endif

                                    @if(!empty($workspaceNotifications['quick_actions']))
                                        <div class="workspace-alert-quick-actions">
                                            @foreach(($workspaceNotifications['quick_actions'] ?? []) as $quickAction)
                                                <a href="{{ $quickAction['url'] }}" class="workspace-alert-quick-link">
                                                    {{ $quickAction['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="workspace-alert-summary-grid">
                                        @foreach(($workspaceNotifications['summary_cards'] ?? []) as $summaryCard)
                                            @php
                                                $summaryToneMap = [
                                                    'blue' => 'workspace-alert-summary-card--blue',
                                                    'rose' => 'workspace-alert-summary-card--rose',
                                                    'violet' => 'workspace-alert-summary-card--violet',
                                                ];
                                                $summaryTone = $summaryToneMap[$summaryCard['tone']] ?? 'workspace-alert-summary-card--slate';
                                            @endphp
                                            <div class="workspace-alert-summary-card {{ $summaryTone }}">
                                                <p class="workspace-alert-summary-card__label">{{ $summaryCard['label'] }}</p>
                                                <p class="workspace-alert-summary-card__value">{{ $summaryCard['value'] }}</p>
                                                <p class="workspace-alert-summary-card__caption">{{ $summaryCard['caption'] ?? '' }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-5 space-y-4">
                                        @foreach(($workspaceNotifications['sections'] ?? []) as $section)
                                            <div class="workspace-alert-section">
                                                <div class="workspace-alert-section__title">
                                                    <span class="workspace-alert-section__dot"></span>
                                                    <span>{{ $section['section'] }}</span>
                                                </div>

                                                <div class="space-y-3">
                                                    @foreach($section['items'] as $notification)
                                                        @php
                                                            $toneMap = [
                                                                'blue' => ['wrap' => 'workspace-alert-item__icon--blue', 'badge' => 'workspace-alert-item__badge--blue', 'rail' => 'workspace-alert-item--blue'],
                                                                'rose' => ['wrap' => 'workspace-alert-item__icon--rose', 'badge' => 'workspace-alert-item__badge--rose', 'rail' => 'workspace-alert-item--rose'],
                                                                'amber' => ['wrap' => 'workspace-alert-item__icon--amber', 'badge' => 'workspace-alert-item__badge--amber', 'rail' => 'workspace-alert-item--amber'],
                                                                'violet' => ['wrap' => 'workspace-alert-item__icon--violet', 'badge' => 'workspace-alert-item__badge--violet', 'rail' => 'workspace-alert-item--violet'],
                                                                'sky' => ['wrap' => 'workspace-alert-item__icon--sky', 'badge' => 'workspace-alert-item__badge--sky', 'rail' => 'workspace-alert-item--sky'],
                                                                'slate' => ['wrap' => 'workspace-alert-item__icon--slate', 'badge' => 'workspace-alert-item__badge--slate', 'rail' => 'workspace-alert-item--slate'],
                                                                'emerald' => ['wrap' => 'workspace-alert-item__icon--emerald', 'badge' => 'workspace-alert-item__badge--emerald', 'rail' => 'workspace-alert-item--emerald'],
                                                            ];
                                                            $tone = $toneMap[$notification['tone']] ?? $toneMap['blue'];
                                                        @endphp
                                                        <a href="{{ $notification['url'] }}" class="workspace-alert-item {{ $tone['rail'] }}">
                                                            <span class="workspace-alert-item__icon {{ $tone['wrap'] }}">
                                                                @switch($notification['icon'])
                                                                    @case('shield-alert')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /></svg>
                                                                        @break
                                                                    @case('shield')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /></svg>
                                                                        @break
                                                                    @case('clock')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                                        @break
                                                                    @case('activity')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 7-4-14-3 7H2" /></svg>
                                                                        @break
                                                                    @case('package')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15m-9 10.31 9-5.15m-9-5.16v10.31M3 6.75l9-5.25 9 5.25-9 5.25-9-5.25Z" /></svg>
                                                                        @break
                                                                    @case('heart')
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 20.25-.9-.82C5.4 14.28 2.25 11.39 2.25 7.88c0-2.77 2.17-4.88 4.95-4.88 1.56 0 3.06.72 4.05 1.85A5.34 5.34 0 0 1 15.3 3c2.78 0 4.95 2.11 4.95 4.88 0 3.51-3.15 6.4-8.85 11.55l-.9.82Z" /></svg>
                                                                        @break
                                                                    @default
                                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                                                @endswitch
                                                            </span>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-start justify-between gap-3">
                                                                    <div class="min-w-0">
                                                                        <p class="truncate text-sm font-bold text-slate-900">{{ $notification['title'] }}</p>
                                                                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $notification['detail'] }}</p>
                                                                    </div>
                                                                    <span class="workspace-alert-item__badge {{ $tone['badge'] }}">
                                                                        {{ $notification['count'] }}
                                                                    </span>
                                                                </div>
                                                                <div class="mt-3 flex items-center justify-between gap-3">
                                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $notification['window'] }}</span>
                                                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">{{ $notification['action'] }}</span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="px-6 py-10 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.4rem] bg-emerald-50 text-emerald-600 shadow-sm">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-base font-semibold text-slate-900">Todo al día</p>
                                    <p class="mt-1 text-sm text-slate-500">No hay alertas urgentes en agenda, preventivos ni gestión.</p>
                                </div>
                            @endif
                </div>
            </div>
        </div>

        <div class="relative" x-data="{ open: false }">

            <button @click="open = !open"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 shadow-sm transition-shadow hover:bg-gray-100 sm:gap-4 sm:px-5 sm:py-4">

                <img src="{{ Auth::user()->avatar }}"
                     class="h-11 w-11 rounded-full border-2 border-gray-300 object-cover sm:h-14 sm:w-14 lg:h-16 lg:w-16">

                <div class="hidden flex-col items-start sm:flex">
                    <span class="text-base font-bold lg:text-lg">{{ Auth::user()->name }}</span>
                    <span class="text-xs text-gray-600 lg:text-sm">
                        {{ Auth::user()->role->nombre ?? 'Rol no definido' }}
                    </span>
                </div>

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 transform transition-transform"
                     :class="{ 'rotate-180': open }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 9l-7 7-7-7" />
                </svg>

            </button>

            <div x-show="open"
                 @click.outside="open = false"
                 x-transition
                 class="absolute right-0 z-50 mt-3 w-56 max-w-[calc(100vw-1rem)] space-y-2 rounded-xl bg-white p-4 shadow-xl sm:w-64">

                <a href="{{ route('profile.edit') }}"
                   class="block px-4 py-2 hover:bg-gray-100 rounded-lg">
                    Perfil
                </a>

                <a href="{{ route('reportes.index') }}"
                   class="block px-4 py-2 hover:bg-gray-100 rounded-lg">
                    Reportes
                </a>

                <a href="{{ route('configuracion.index') }}"
                   class="block px-4 py-2 hover:bg-gray-100 rounded-lg">
                    Configuración
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg">
                        Cerrar sesión
                    </button>
                </form>

            </div>
        </div>
    </header>

    <!-- CONTENIDO -->
    <main class="workspace-main">
        <div class="workspace-stage">
            {{ $slot }}
        </div>
    </main>

</div>

<script>
    if (window.feather) {
        feather.replace();
    }
</script>
<script>
let deleteUrl = null;

// abrir modal
window.openDeleteModal = function(url) {

    deleteUrl = url;

    const modal = document.getElementById('deleteModal');
    const card = document.getElementById('deleteCard');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
};

// cerrar modal
window.closeDeleteModal = function() {

    const modal = document.getElementById('deleteModal');
    const card = document.getElementById('deleteCard');

    card.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 150);
};

// ejecutar delete
document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById('confirmDeleteBtn');

    if (!btn) return;

    btn.addEventListener("click", async () => {

        if (!deleteUrl) return;

        const res = await fetch(deleteUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: new URLSearchParams({
                _method: "DELETE"
            })
        });

        if (res.ok) {

            closeDeleteModal();

            showToast("Eliminado correctamente", "success");

            setTimeout(() => location.reload(), 700);

        } else {
            showToast("Error al eliminar", "error");
        }
    });
});
</script>

</body>
</html>



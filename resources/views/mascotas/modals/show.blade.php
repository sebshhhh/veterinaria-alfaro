<div id="mascotaModal"
     data-default-image="{{ asset('storage/default.png') }}"
     data-clinic-name="DRA. ALFARO"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-3 py-3 sm:px-5 sm:py-5"
     aria-hidden="true">
    <div class="flex min-h-full items-start justify-center">
        <div class="flex max-h-[calc(100vh-1.5rem)] w-full max-w-[92rem] flex-col overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="shrink-0 flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Ficha de mascota</p>
                    <h3 class="mt-1 text-2xl font-bold text-slate-900">Resumen clinico</h3>
                </div>

                <button type="button"
                        onclick="closeMascotaModal()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                        aria-label="Cerrar modal">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div id="modalContent" class="min-h-0 flex-1 overflow-y-auto px-7 py-6"></div>
        </div>
    </div>
</div>

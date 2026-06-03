<div id="mascotaModal"
     class="workspace-modal fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-950/60 px-4 py-6">
    <div class="w-full max-w-lg rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Mascotas del cliente</p>
                <h3 class="text-xl font-bold text-slate-900">Detalle rápido</h3>
            </div>

            <button type="button" onclick="closeModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <div id="modalContent" class="pt-5"></div>
    </div>
</div>

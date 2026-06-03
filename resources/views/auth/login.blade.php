<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingreso seguro - DRA. ALFARO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen overflow-x-hidden bg-slate-950 font-[Poppins] text-slate-900">
    <div class="relative flex min-h-dvh items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
        <img src="{{ asset('img/fondoDOC.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_18%,rgba(37,99,235,0.42),transparent_30%),radial-gradient(circle_at_78%_18%,rgba(14,165,233,0.28),transparent_25%),linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,64,175,0.76))]"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-slate-950/45 to-transparent"></div>

        <main class="relative z-10 grid w-full max-w-md overflow-visible rounded-[1.6rem] border border-white/20 bg-white/95 shadow-[0_36px_90px_-42px_rgba(15,23,42,0.92)] backdrop-blur-xl sm:rounded-[1.8rem] lg:max-w-6xl lg:grid-cols-[1.05fr_0.95fr] lg:rounded-[2.1rem]">
            <img
                src="{{ asset('img/dog-holding.png') }}"
                alt="Perrito sosteniendo el formulario"
                class="pointer-events-none absolute right-[23.5rem] top-[-3.9rem] z-30 hidden w-36 drop-shadow-[0_26px_30px_rgba(15,23,42,0.38)] lg:block xl:right-[25rem] xl:w-40">

            <section class="relative hidden min-h-[39rem] overflow-hidden rounded-l-[2.1rem] bg-gradient-to-br from-blue-700 via-blue-800 to-slate-950 p-10 text-white lg:block">
                <div class="absolute -left-16 top-12 h-52 w-52 rounded-full bg-cyan-300/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-emerald-300/18 blur-3xl"></div>

                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-3 rounded-2xl bg-white/12 px-4 py-3 ring-1 ring-white/18">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo Dra. Alfaro" class="h-12 w-12 rounded-xl bg-white object-contain p-1.5">
                            <div>
                                <p class="text-lg font-black leading-tight">DRA. ALFARO</p>
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-100">Sistema veterinario</p>
                            </div>
                        </div>

                        <div class="mt-12 max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.28em] text-cyan-100">Acceso administrativo</p>
                            <h1 class="mt-4 text-4xl font-black leading-tight xl:text-5xl">Control clínico listo para atender mejor.</h1>
                            <p class="mt-5 text-base leading-7 text-blue-100">
                                Gestiona citas, pacientes, atenciones, vacunas, controles, servicios e inventario desde un solo flujo de trabajo.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-2xl border border-white/16 bg-white/10 p-4 backdrop-blur">
                            <p class="text-sm font-black">Operación diaria</p>
                            <p class="mt-1 text-sm leading-6 text-blue-100">Agenda, atenciones y alertas conectadas para evitar registros duplicados.</p>
                        </div>
                        <div class="rounded-2xl border border-white/16 bg-white/10 p-4 backdrop-blur">
                            <p class="text-sm font-black">Seguimiento clínico</p>
                            <p class="mt-1 text-sm leading-6 text-blue-100">Cada paciente mantiene historial, vacunas y controles pendientes en una ficha clara.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative z-20 flex min-h-[35rem] items-center justify-center rounded-[1.6rem] bg-gradient-to-br from-white via-slate-50 to-blue-50 px-5 pb-8 pt-24 sm:min-h-[37rem] sm:rounded-[1.8rem] sm:px-8 lg:min-h-[39rem] lg:rounded-l-none lg:rounded-r-[2.1rem] lg:px-12 lg:py-8">
                <div class="absolute left-6 top-6 lg:hidden">
                    <div class="inline-flex items-center gap-3 rounded-2xl bg-blue-50 px-4 py-3">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo Dra. Alfaro" class="h-10 w-10 rounded-xl bg-white object-contain p-1">
                        <div>
                            <p class="text-sm font-black text-slate-950">DRA. ALFARO</p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-600">Sistema veterinario</p>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-md">
                    <div class="mb-8">
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-blue-600">Bienvenido</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-950 sm:text-3xl">Iniciar sesión</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Usa tu DNI y contraseña para entrar al sistema de gestión veterinaria.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5" autocomplete="on">
                        @csrf

                        <div>
                            <label for="dni" class="text-sm font-black text-slate-800">DNI</label>
                            <div class="mt-2 flex items-center rounded-2xl border bg-white px-4 py-3 shadow-sm transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100 {{ $errors->has('dni') ? 'border-rose-300' : 'border-slate-200' }}">
                                <svg class="mr-3 h-5 w-5 shrink-0 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                                </svg>
                                <input
                                    id="dni"
                                    type="text"
                                    name="dni"
                                    value="{{ old('dni') }}"
                                    inputmode="numeric"
                                    maxlength="8"
                                    pattern="[0-9]{8}"
                                    autocomplete="username"
                                    placeholder="Ingresa 8 dígitos"
                                    required
                                    class="w-full border-0 bg-transparent p-0 text-base font-bold text-slate-800 placeholder:text-slate-400 focus:ring-0">
                            </div>
                            @error('dni')
                                <p class="mt-1.5 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-black text-slate-800">Contraseña</label>
                            <div class="mt-2 flex items-center rounded-2xl border bg-white px-4 py-3 shadow-sm transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100 {{ $errors->has('password') ? 'border-rose-300' : 'border-slate-200' }}">
                                <svg class="mr-3 h-5 w-5 shrink-0 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 0 0-9 0V10.5m-.75 0h10.5A1.75 1.75 0 0 1 19 12.25v6A1.75 1.75 0 0 1 17.25 20H6.75A1.75 1.75 0 0 1 5 18.25v-6a1.75 1.75 0 0 1 1.75-1.75Z" />
                                </svg>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    autocomplete="current-password"
                                    placeholder="Ingresa tu contraseña"
                                    required
                                    class="w-full border-0 bg-transparent p-0 text-base font-bold text-slate-800 placeholder:text-slate-400 focus:ring-0">
                                <button type="button" onclick="togglePassword()" class="ml-3 rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    Ver
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                            <span>Acceso reservado al personal autorizado.</span>
                            <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Seguro</span>
                        </div>

                        <button type="submit" class="group flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-blue-700 to-blue-600 px-5 py-4 text-base font-black text-white shadow-[0_24px_42px_-24px_rgba(37,99,235,0.9)] transition hover:-translate-y-0.5 hover:from-blue-800 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Ingresar al sistema
                            <svg class="h-5 w-5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Sistema de Gestión Veterinaria
                    </p>
                </div>
            </section>
        </main>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>

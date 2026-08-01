<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS - Analisis Risiko Gempa sesuai SNI 1726:2019">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    @stack('styles')
</head>
<body class="min-h-screen bg-white relative">

    <!-- Dekorasi garis seismograf di sisi kiri & kanan (murni estetika, abu-abu muda) -->
    <div class="fixed inset-y-0 left-0 w-24 md:w-40 pointer-events-none opacity-[0.15] -z-10" aria-hidden="true">
        <svg width="100%" height="100%" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="seismic-left" width="80" height="140" patternUnits="userSpaceOnUse">
                    <path d="M0,70 L14,70 L20,25 L28,115 L36,10 L44,125 L52,35 L60,95 L68,70 L80,70"
                          fill="none" stroke="#94a3b8" stroke-width="1.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#seismic-left)"/>
        </svg>
    </div>
    <div class="fixed inset-y-0 right-0 w-24 md:w-40 pointer-events-none opacity-[0.15] -z-10" aria-hidden="true">
        <svg width="100%" height="100%" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="seismic-right" width="80" height="140" patternUnits="userSpaceOnUse">
                    <path d="M0,70 L14,70 L20,25 L28,115 L36,10 L44,125 L52,35 L60,95 L68,70 L80,70"
                          fill="none" stroke="#94a3b8" stroke-width="1.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#seismic-right)"/>
        </svg>
    </div>

    <!-- Header -->
    <header class="bg-blue-800 shadow-lg relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-4 flex items-center justify-between">
                <!-- Logo Institusi -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex items-center space-x-3">
                        <img src="{{ asset('images/logo-bmkg.png') }}" alt="Logo BMKG"
                             class="h-10 w-auto max-w-[44px] object-contain"
                             onerror="this.style.display='none'">
                        <img src="{{ asset('images/logo-stmkg.png') }}" alt="Logo STMKG"
                             class="h-10 w-auto max-w-[44px] object-contain"
                             onerror="this.style.display='none'">
                        <img src="{{ asset('images/logo-instrumentasi.png') }}" alt="Logo Instrumentasi"
                             class="h-10 w-auto max-w-[44px] object-contain"
                             onerror="this.style.display='none'">
                        <img src="{{ asset('images/logo-pusgen.png') }}" alt="Logo PuSGeN"
                             class="h-10 w-auto max-w-[64px] object-contain"
                             onerror="this.style.display='none'">
                    </div>
                    <div>
                        <h1 class="text-white text-lg sm:text-xl font-bold tracking-tight">
                            SISTEM PENDUKUNG KEPUTUSAN RISIKO GEMPA BUMI
                        </h1>
                        <p class="text-cyan-200 text-xs sm:text-sm">Berbasis WebGIS - SNI 1726:2019</p>
                    </div>
                </div>
                <!-- Admin Link -->
                <a href="{{ route('admin.login') }}" class="flex items-center gap-2 text-white/80 hover:text-white text-sm transition">
                    <span class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <span class="hidden sm:inline">Admin</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-4 mt-8 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS</p>
            <p class="mt-1">Sesuai SNI 1726:2019</p>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
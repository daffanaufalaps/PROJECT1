<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-800 to-cyan-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl font-bold">G</span>
                    </div>
                    <div>
                        <h1 class="text-white text-lg font-bold">Admin Dashboard</h1>
                        <p class="text-cyan-200 text-xs">Sistem SPK Risiko Gempa Bumi</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white/80 text-sm">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-white/80 hover:text-white text-sm bg-white/10 px-3 py-1 rounded transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-4">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-4 text-blue-600 border-b-2 border-blue-600 font-medium text-sm">Dashboard</a>
                <a href="{{ route('admin.history') }}" class="px-3 py-4 text-gray-600 hover:text-blue-600 font-medium text-sm transition">Riwayat Perhitungan</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Perhitungan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_calculations']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Perhitungan Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['calculations_today']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-cyan-100 text-cyan-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Template Narasi Aktif</p>
                        <p class="text-2xl font-bold text-gray-900">1</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Calculations -->
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">10 Perhitungan Terakhir</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Koordinat</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">PGA</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">MMI</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Kategori Risiko</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">KDS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($stats['recent_calculations'] as $history)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $history->latitude }}, {{ $history->longitude }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-blue-600">{{ $history->pga }} g</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $history->mmi }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if(strpos($history->risk_category, 'Sangat Tinggi') !== false) bg-red-100 text-red-800
                                    @elseif(strpos($history->risk_category, 'Tinggi') !== false) bg-orange-100 text-orange-800
                                    @elseif(strpos($history->risk_category, 'Sedang') !== false) bg-yellow-100 text-yellow-800
                                    @elseif(strpos($history->risk_category, 'Rendah') !== false) bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $history->risk_category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ strtoupper($history->kds) }}</td>
                        </tr>
                        @endforeach

                        @if($stats['recent_calculations']->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data perhitungan.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Perhitungan - {{ config('app.name') }}</title>
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
                        <h1 class="text-white text-lg font-bold">Riwayat Perhitungan</h1>
                        <p class="text-cyan-200 text-xs">Sistem SPK Risiko Gempa Bumi</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-white/80 hover:text-white text-sm bg-white/10 px-3 py-1 rounded transition">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-4">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-4 text-gray-600 hover:text-blue-600 font-medium text-sm transition">Dashboard</a>
                <a href="{{ route('admin.history') }}" class="px-3 py-4 text-blue-600 border-b-2 border-blue-600 font-medium text-sm">Riwayat Perhitungan</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Perhitungan</h2>
                <span class="text-sm text-gray-500">Total: {{ $histories->total() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Koordinat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">PGA</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">MMI</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Kategori Risiko</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">KDS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Ss/S1</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($histories as $history)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $history->latitude }}°, {{ $history->longitude }}°</td>
                            <td class="px-4 py-3 text-sm font-medium text-blue-600">{{ $history->pga }} g</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $history->mmi }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if(strpos($history->risk_category, 'Sangat Tinggi') !== false) bg-red-100 text-red-800
                                    @elseif(strpos($history->risk_category, 'Tinggi') !== false) bg-orange-100 text-orange-800
                                    @elseif(strpos($history->risk_category, 'Sedang') !== false) bg-yellow-100 text-yellow-800
                                    @elseif(strpos($history->risk_category, 'Rendah') !== false) bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $history->risk_category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ strtoupper($history->kds) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $history->ss }} / {{ $history->s1 }}</td>
                        </tr>
                        @endforeach

                        @if($histories->isEmpty())
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat perhitungan.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($histories->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $histories->links() }}
            </div>
            @endif
        </div>
    </main>
</body>
</html>

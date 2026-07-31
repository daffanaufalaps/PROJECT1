@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Sidebar - Input Panel (Left) -->
        <div class="lg:col-span-1 order-2 lg:order-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    Input Koordinat
                </h2>

                <form id="recalculateForm" method="POST" action="{{ route('calculate') }}">
                    @csrf

                    <!-- Latitude Input -->
                    <div class="mb-4">
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Lintang (Latitude)
                        </label>
                        <input
                            type="number"
                            id="latitude"
                            name="latitude"
                            step="any"
                            min="-8.8"
                            max="-5.8"
                            value="{{ $result['latitude'] }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                    </div>

                    <!-- Longitude Input -->
                    <div class="mb-4">
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                            Bujur (Longitude)
                        </label>
                        <input
                            type="number"
                            id="longitude"
                            name="longitude"
                            step="any"
                            min="105.0"
                            max="114.6"
                            value="{{ $result['longitude'] }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                    </div>

                    <!-- Site Class Selection -->
                    <div class="mb-6">
                        <label for="site_class" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas Situs
                        </label>
                        <select
                            id="site_class"
                            name="site_class"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                            <option value="">Otomatis</option>
                            <option value="A" {{ $result['site_class'] ?? '' == 'A' ? 'selected' : '' }}>A - Batuan Keras</option>
                            <option value="B" {{ $result['site_class'] ?? '' == 'B' ? 'selected' : '' }}>B - Batuan</option>
                            <option value="C" {{ $result['site_class'] ?? '' == 'C' ? 'selected' : '' }}>C - Tanah Sangat Padat</option>
                            <option value="D" {{ $result['site_class'] ?? '' == 'D' ? 'selected' : '' }}>D - Tanah Kaku</option>
                            <option value="E" {{ $result['site_class'] ?? '' == 'E' ? 'selected' : '' }}>E - Tanah Lunak</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        Hitung Ulang
                    </button>
                </form>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="block text-center text-sm text-blue-600 hover:text-blue-800">
                        &larr; Kembali ke halaman utama
                    </a>
                </div>
            </div>
        </div>

        <!-- Results Panel (Right) -->
        <div class="lg:col-span-3 order-1 lg:order-2">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
                <!-- Results Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-blue-50">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Hasil Analisis
                        </h2>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Parameter</th>
                                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-3 text-sm font-medium text-gray-900">PGA</td>
                                    <td class="px-3 py-3 text-sm text-blue-600 font-bold">{{ $result['pga'] }} g</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-3 text-sm font-medium text-gray-900">Skala SIG-BMKG</td>
                                    <td class="px-3 py-3 text-sm text-blue-600 font-bold">{{ $result['sig_bmkg_scale'] }} - {{ $sigBmkgDescription }}</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-3 text-sm font-medium text-gray-900">Kategori Risiko</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if(strpos($result['risk_category'], 'Sangat Tinggi') !== false) bg-red-100 text-red-800
                                            @elseif(strpos($result['risk_category'], 'Tinggi') !== false) bg-orange-100 text-orange-800
                                            @elseif(strpos($result['risk_category'], 'Sedang') !== false) bg-yellow-100 text-yellow-800
                                            @elseif(strpos($result['risk_category'], 'Rendah') !== false) bg-green-100 text-green-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $result['risk_category'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-3 text-sm font-medium text-gray-900">Nilai KDS</td>
                                    <td class="px-3 py-3 text-sm text-blue-600 font-bold">{{ strtoupper($result['kds']) }}</td>
                                </tr>
                                <tr><td colspan="2" class="px-3 py-2 bg-gray-100 text-xs text-gray-500 font-semibold">Parameter Detail SNI 1726:2019</td></tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">Ss</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['ss'] }} g</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">S1</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['s1'] }} g</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">Fa</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['fa'] }}</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">Fv</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['fv'] }}</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">SMs</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['sms'] }} g</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">SM1</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['sm1'] }} g</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">SDs</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['sds'] }} g</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">SD1</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $result['sd1'] }} g</td>
                                </tr>
                                <tr><td colspan="2" class="px-3 py-2 bg-gray-100 text-xs text-gray-500 font-semibold">Rekomendasi Sistem Pemikul Gaya Seismik (SPGS)</td></tr>
                                @forelse(($result['spgs_recommendations'] ?? []) as $spgs)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $spgs['sistem'] }} ({{ $spgs['kode'] }})</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $spgs['keterangan'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-2 text-sm text-gray-500 italic">Tidak ada rekomendasi tersedia untuk KDS ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Map -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            Lokasi
                        </h2>
                    </div>
                    <div id="map" class="h-64 w-full"></div>
                </div>
            </div>

            <!-- Narrative -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Narasi Hasil Analisis
                </h2>
                <div class="prose prose-blue max-w-none text-gray-700">
                    <p>{{ $narrative }}</p>
                </div>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Penjelasan Kategori Risiko:</h4>
                    <p class="text-sm text-gray-600">{{ $riskDescription }}</p>
                </div>
            </div>

            <!-- Gempa Historis Terdekat -->
            <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Gempa Historis Terdekat
                </h2>
                @if(!empty($result['nearest_earthquakes']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Magnitudo</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Kedalaman</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Jarak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($result['nearest_earthquakes'] as $eq)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-700">{{ \Carbon\Carbon::parse($eq['origin_time'])->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900 font-medium">M{{ $eq['magnitude'] }} ({{ $eq['magnitude_type'] }})</td>
                                <td class="px-3 py-2 text-sm text-gray-700">{{ $eq['depth'] }} km</td>
                                <td class="px-3 py-2 text-sm text-gray-700">{{ number_format($eq['distance_km'], 1) }} km</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500 italic">Tidak ditemukan data gempa historis dalam radius pencarian dari lokasi ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize map
    const map = L.map('map').setView([{{ $result['latitude'] }}, {{ $result['longitude'] }}], 12);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map);

    // Add marker
    const marker = L.marker([{{ $result['latitude'] }}, {{ $result['longitude'] }}]).addTo(map);
    marker.bindPopup(`
        <b>Lokasi Analisis</b><br>
        Lintang: {{ $result['latitude'] }}<br>
        Bujur: {{ $result['longitude'] }}<br>
        <hr>
        PGA: {{ $result['pga'] }} g<br>
        Skala SIG-BMKG: {{ $result['sig_bmkg_scale'] }}<br>
        Risiko: {{ $result['risk_category'] }}
    `).openPopup();

    // Recalculate form submission
    document.getElementById('recalculateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });
</script>
@endpush

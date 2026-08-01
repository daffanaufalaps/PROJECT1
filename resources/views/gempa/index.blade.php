@extends('layouts.app')

@section('content')

<!-- Mengunci ukuran kotak peta agar pas di dalam card sebelah kanan -->
<style>
    #map {
        width: 100% !important;
        height: 420px !important;
        z-index: 1;
    }
</style>

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Sidebar - Input Panel (Left) -->
        <div class="lg:col-span-1 order-2 lg:order-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Input Koordinat
                </h2>

                <form id="calculationForm" method="POST" action="{{ route('calculate') }}">
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
                            value="{{ $latitude ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Contoh: -6.2"
                        >
                        <p class="mt-1 text-xs text-gray-500">Rentang: -8.8 sampai -5.8 (Pulau Jawa)</p>
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
                            value="{{ $longitude ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Contoh: 106.8"
                        >
                        <p class="mt-1 text-xs text-gray-500">Rentang: 105.0 sampai 114.6 (Pulau Jawa)</p>
                    </div>

                    <!-- Site Class Selection (Optional) -->
                    <div class="mb-6">
                        <label for="site_class" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas Situs (Opsional)
                        </label>
                        <select
                            id="site_class"
                            name="site_class"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                            <option value="">Otomatis dari Data Spasial</option>
                            <option value="A">A - Batuan Keras</option>
                            <option value="B">B - Batuan</option>
                            <option value="C">C - Tanah Sangat Padat</option>
                            <option value="D">D - Tanah Kaku</option>
                            <option value="E">E - Tanah Lunak</option>
                        </select>
                    </div>

                    <!-- My Location Button -->
                    <button
                        type="button"
                        id="myLocationBtn"
                        class="w-full mb-4 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Gunakan Lokasi Saya
                    </button>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        id="calculateBtn"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Hitung
                    </button>
                </form>

                <form id="downloadReportForm" method="POST" action="{{ route('download.report') }}" class="mt-3 hidden">
                    @csrf
                    <input type="hidden" name="result_data" id="resultDataInput">
                    <button
                        type="submit"
                        class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition font-semibold flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh PDF
                    </button>
                </form>

                <!-- Validation Errors -->
                <div id="validationErrors" class="mt-4 hidden">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Map and Narrative Panel (Right) -->
        <div class="lg:col-span-3 order-1 lg:order-2">
            <!-- Map Container (Top) -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Peta Lokasi
                        <span class="ml-2 text-sm font-normal text-gray-500">Klik pada peta untuk memilih lokasi</span>
                    </h2>
                </div>
                <!-- Kotak Peta -->
                <div id="map"></div>
            </div>

            <!-- Narrative Container (Bottom) -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Narasi
                </h2>
                <div id="narrative" class="prose prose-blue max-w-none text-gray-700">
                    <p>{{ $initialNarrative ?? 'Pilih lokasi di peta atau masukkan koordinat Lintang dan Bujur untuk memulai analisis risiko gempa bumi sesuai SNI 1726:2019.' }}</p>
                </div>
                <div id="loadingIndicator" class="hidden mt-4 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600">Memproses perhitungan...</span>
                </div>
            </div>

<!-- Detailed Study Warning (Hidden by default) -->
<div id="detailedStudyWarning" class="hidden mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
    <div class="flex items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-yellow-800" id="detailedStudyWarningText"></p>
            <p class="text-sm text-yellow-700 mt-1">Kelas Situs pada lokasi ini: <span id="detailedStudyWarningSiteClass" class="font-semibold"></span> — sistem tidak dapat memberikan estimasi otomatis untuk kondisi tanah ini.</p>
        </div>
    </div>
</div>

            <!-- Results Section (Hidden by default, shown after calculation) -->
            <div id="resultsSection" class="hidden mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-blue-50">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Hasil Analisis
                        </h2>
                    </div>

                    <!-- Results Table -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Parameter</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Nilai</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="resultsTableBody" class="divide-y divide-gray-200">
                                    <!-- Results will be populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Result Narrative -->
                <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Narasi Hasil Analisis
                    </h3>
                    <div id="resultNarrative" class="prose prose-blue max-w-none text-gray-700">
                        <!-- Narrative will be populated via JavaScript -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Default diset ke Pulau Jawa (-7.6145, 110.7122) dengan Zoom level 7 jika belum ada koordinat yang dipilih
    const initialLat = @json($latitude ?? -7.6145);
    const initialLng = @json($longitude ?? 110.7122);
    const initialZoom = (@json($latitude && $longitude)) ? 12 : 7;

    // Batas wilayah Pulau Jawa — peta tidak bisa digeser/zoom keluar dari area ini
    const javaBounds = L.latLngBounds(
        L.latLng(-8.8, 105.0),  // titik barat daya
        L.latLng(-5.8, 114.6)   // titik timur laut
);

    const map = L.map('map', {
        maxBounds: javaBounds,
        maxBoundsViscosity: 1.0, // 1.0 = benar-benar tidak bisa digeser melewati batas
        minZoom: 7               // supaya user tidak bisa zoom out sampai keluar area
        }).setView([initialLat, initialLng], initialZoom);

    // Add Carto Positron base layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map);

    // Warna marker berdasarkan kategori risiko
    function getRiskColor(riskCategory) {
        switch (riskCategory) {
            case 'Risiko Sangat Rendah': return '#22c55e'; // hijau
            case 'Risiko Rendah':        return '#84cc16'; // hijau muda
            case 'Risiko Sedang':        return '#eab308'; // kuning
            case 'Risiko Tinggi':        return '#f97316'; // oranye
            case 'Risiko Sangat Tinggi': return '#dc2626'; // merah
            default:                    return '#2563eb'; // biru (belum dihitung)
        }
    }

    function createColoredIcon(color) {
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12],
        });
    }

    // Custom marker icon (default biru sebelum ada hasil perhitungan)
    const customIcon = createColoredIcon('#2563eb');

    // Legenda kategori risiko
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'info legend');
        div.style.background = 'white';
        div.style.padding = '10px 12px';
        div.style.borderRadius = '8px';
        div.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
        div.style.fontSize = '12px';
        div.style.lineHeight = '1.6';

        const categories = [
            { label: 'Sangat Rendah', color: '#22c55e' },
            { label: 'Rendah',        color: '#84cc16' },
            { label: 'Sedang',        color: '#eab308' },
            { label: 'Tinggi',        color: '#f97316' },
            { label: 'Sangat Tinggi', color: '#dc2626' },
        ];

        let html = '<strong>Kategori Risiko</strong><br>';
        categories.forEach(cat => {
            html += `<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:${cat.color};margin-right:6px;vertical-align:middle;"></span>${cat.label}<br>`;
        });
        div.innerHTML = html;
        return div;
    };
    legend.addTo(map);

    // Layer sesar aktif (data PuSGeN 2024, difilter untuk area Pulau Jawa)
    const faultLayer = L.geoJSON(null, {
        style: {
            color: '#dc2626',
            weight: 1.5,
            opacity: 0.75,
            dashArray: '4,3'
        },
        onEachFeature: function (feature, layer) {
            const p = feature.properties || {};
            layer.bindPopup(`
                <strong>${p.name || 'Sesar tidak diketahui'}</strong><br>
                Segmen: ${p.segment || '-'}<br>
                Wilayah: ${p.region || '-'}<br>
                Mmax: ${p.mmax ?? '-'}<br>
                Panjang: ${p.length_km ?? '-'} km<br>
                Laju geser: ${p.sliprate_m ?? '-'} mm/tahun<br>
                Tipe: ${p.type || '-'}
            `);
        }
    });

    fetch('{{ asset("data/sesar-jawa.geojson") }}')
        .then(res => res.json())
        .then(data => {
            faultLayer.addData(data);
            faultLayer.addTo(map);
        })
        .catch(err => console.error('Gagal memuat data sesar aktif:', err));

    L.control.layers(null, {
        'Sesar Aktif (PuSGeN 2024)': faultLayer
    }, { position: 'topright', collapsed: false }).addTo(map);

    // Initialize marker
    let marker = null;
    @if(isset($latitude) && isset($longitude) && $latitude && $longitude)
        marker = L.marker([{{ $latitude }}, {{ $longitude }}], { icon: customIcon }).addTo(map);
    @endif

    // Paksa peta menyesuaikan ukuran kotak agar rapi dan tidak pecah
    setTimeout(function () {
        map.invalidateSize();
    }, 200);

    // Map click event
    map.on('click', function(e) {
    if (!javaBounds.contains(e.latlng)) {
        alert('Lokasi yang dipilih berada di luar Pulau Jawa. Silakan pilih titik di dalam Pulau Jawa.');
        return;
    }

    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);

    // Update form fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    // Update marker position
    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng, { icon: customIcon }).addTo(map);
    }

    // Update narrative
    updateInitialNarrative(lat, lng);
    });

    // Input field change events
    document.getElementById('latitude').addEventListener('change', updateMarkerFromInput);
    document.getElementById('longitude').addEventListener('change', updateMarkerFromInput);

    function updateMarkerFromInput() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);

        if (!isNaN(lat) && !isNaN(lng)) {
            if (!javaBounds.contains(L.latLng(lat, lng))) {
                alert('Koordinat yang dimasukkan berada di luar Pulau Jawa.');
                return;
            }

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            }
            map.setView([lat, lng], 12);
            updateInitialNarrative(lat, lng);
        }
    }

    // My Location button
    document.getElementById('myLocationBtn').addEventListener('click', function() {
        if (navigator.geolocation) {
            this.disabled = true;
            this.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mendapatkan lokasi...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);

                    if (!javaBounds.contains(L.latLng(parseFloat(lat), parseFloat(lng)))) {
                        alert('Lokasi Anda saat ini berada di luar Pulau Jawa. Sistem ini hanya mendukung analisis untuk wilayah Pulau Jawa.');
                        document.getElementById('myLocationBtn').disabled = false;
                        document.getElementById('myLocationBtn').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>Gunakan Lokasi Saya';
                        return;
                    }

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
                    }
                    map.setView([lat, lng], 14);

                    updateInitialNarrative(lat, lng);

                    document.getElementById('myLocationBtn').disabled = false;
                    document.getElementById('myLocationBtn').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>Gunakan Lokasi Saya';
                },
                function(error) {
                    let errorMsg = 'Tidak dapat mendapatkan lokasi Anda.';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = 'Akses lokasi ditolak oleh pengguna.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'Waktu permintaan lokasi habis.';
                            break;
                    }
                    alert(errorMsg);
                    document.getElementById('myLocationBtn').disabled = false;
                    document.getElementById('myLocationBtn').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>Gunakan Lokasi Saya';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            alert('Browser Anda tidak mendukung geolokasi.');
        }
    });

    // Update initial narrative
    function updateInitialNarrative(lat, lng) {
        document.getElementById('narrative').innerHTML =
            `<p>Koordinat terpilih: ${lat}&deg; LS, ${lng}&deg; BT. Klik tombol 'Hitung' untuk melakukan analisis risiko gempa bumi.</p>`;
    }

    // Form submission via AJAX
    document.getElementById('calculationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const siteClass = document.getElementById('site_class').value;

        // Basic validation
        const errors = [];
        if (!lat || isNaN(lat)) {
            errors.push('Lintang (Latitude) wajib diisi dengan angka valid.');
        } else if (lat < -8.8 || lat > -5.8) {
            errors.push('Lintang harus antara -8.8 sampai -5.8 (wilayah Pulau Jawa).');
        }
        if (!lng || isNaN(lng)) {
            errors.push('Bujur (Longitude) wajib diisi dengan angka valid.');
        } else if (lng < 105.0 || lng > 114.6) {
            errors.push('Bujur harus antara 105.0 sampai 114.6 (wilayah Pulau Jawa).');
        }

        if (errors.length > 0) {
            const errorDiv = document.getElementById('validationErrors');
            errorDiv.querySelector('div').innerHTML = errors.join('<br>');
            errorDiv.classList.remove('hidden');
            return;
        }

        // Hide errors and show loading
        document.getElementById('validationErrors').classList.add('hidden');
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('calculateBtn').disabled = true;

        // Send AJAX request
        fetch('{{ route("api.calculate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                latitude: parseFloat(lat),
                longitude: parseFloat(lng),
                site_class: siteClass || null
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('calculateBtn').disabled = false;

            if (data.success) {
                displayResults(data);
            } else {
                const errorDiv = document.getElementById('validationErrors');
                let errorMsg = data.message || 'Terjadi kesalahan dalam perhitungan.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                errorDiv.querySelector('div').innerHTML = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('calculateBtn').disabled = false;
            console.error('Error:', error);
            const errorDiv = document.getElementById('validationErrors');
            errorDiv.querySelector('div').innerHTML = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
            errorDiv.classList.remove('hidden');
        });
    });

    // Display results
    function displayResults(data) {
        const results = data.data;

    if (results.requires_detailed_study) {
        document.getElementById('detailedStudyWarningText').textContent = results.warning_message;
        document.getElementById('detailedStudyWarningSiteClass').textContent = results.site_class || '-';
        document.getElementById('detailedStudyWarning').classList.remove('hidden');
        document.getElementById('resultsSection').classList.add('hidden');
        document.getElementById('narrative').innerHTML = `<p>${data.narrative}</p>`;
        document.getElementById('detailedStudyWarning').scrollIntoView({ behavior: 'smooth' });
        return;
    }
    document.getElementById('detailedStudyWarning').classList.add('hidden');

    // Simpan data hasil untuk fitur unduh laporan PDF, lalu tampilkan tombolnya
    document.getElementById('resultDataInput').value = JSON.stringify(results);
    document.getElementById('downloadReportForm').classList.remove('hidden');
    
    // Update warna marker sesuai kategori risiko hasil perhitungan
    if (marker) {
        marker.setIcon(createColoredIcon(getRiskColor(results.risk_category)));
    }

    // Update narrative
    document.getElementById('narrative').innerHTML = `<p>${data.narrative}</p>`;

    // Show results section and populate table
    document.getElementById('resultsSection').classList.remove('hidden');

        const tableBody = document.getElementById('resultsTableBody');
        tableBody.innerHTML = '';

        const rows = [
            { param: 'PGA (Peak Ground Acceleration)', value: results.pga + ' g (' + results.pga_gal + ' gal)', desc: 'Percepatan puncak tanah' },
            { param: 'Skala SIG-BMKG', value: results.sig_bmkg_scale + ' (setara MMI ' + results.sig_bmkg_mmi_equivalent + ')', desc: data.sig_bmkg_description },
            { param: 'Kategori Risiko', value: results.risk_category, desc: data.risk_description },
            { param: 'KDS (Kategori Desain Seismik)', value: results.kds, desc: 'Kategori desain seismik sesuai SNI 1726:2019' },
        ];

        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-3 text-sm font-medium text-gray-900">${row.param}</td>
                <td class="px-4 py-3 text-sm text-blue-600 font-semibold">${row.value}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${row.desc}</td>
            `;
            tableBody.appendChild(tr);
        });

        // Add detailed parameters
        const detailRows = [
            { param: 'Ss', value: results.ss + ' g', desc: 'Percepatan spektra MCE periode pendek' },
            { param: 'S1', value: results.s1 + ' g', desc: 'Percepatan spektra MCE periode 1 detik' },
            { param: 'Fa', value: results.fa, desc: 'Koefisien amplifikasi situs (periode pendek)' },
            { param: 'Fv', value: results.fv, desc: 'Koefisien amplifikasi situs (periode 1 detik)' },
            { param: 'SMs', value: results.sms + ' g', desc: 'Percepatan spektra MCE termodifikasi (periode pendek)' },
            { param: 'SM1', value: results.sm1 + ' g', desc: 'Percepatan spektra MCE termodifikasi (periode 1 detik)' },
            { param: 'SDs', value: results.sds + ' g', desc: 'Percepatan spektra desain (periode pendek)' },
            { param: 'SD1', value: results.sd1 + ' g', desc: 'Percepatan spektra desain (periode 1 detik)' },
        ];

        // Add separator
        const separator = document.createElement('tr');
        separator.innerHTML = `<td colspan="3" class="px-4 py-2 bg-gray-50 text-sm font-semibold text-gray-700">Parameter Detail (SNI 1726:2019)</td>`;
        tableBody.appendChild(separator);

        detailRows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-3 text-sm font-medium text-gray-900">${row.param}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${row.value}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${row.desc}</td>
            `;
            tableBody.appendChild(tr);
        });

        // Rekomendasi SPGS
        if (results.spgs_recommendations && results.spgs_recommendations.length > 0) {
            const spgsSeparator = document.createElement('tr');
            spgsSeparator.innerHTML = `<td colspan="3" class="px-4 py-2 bg-gray-50 text-sm font-semibold text-gray-700">Rekomendasi Sistem Pemikul Gaya Seismik (SPGS)</td>`;
            tableBody.appendChild(spgsSeparator);

            results.spgs_recommendations.forEach(spgs => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${spgs.sistem} (${spgs.kode})</td>
                    <td class="px-4 py-3 text-sm text-gray-700" colspan="2">${spgs.keterangan}</td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Rekomendasi Fondasi
        if (results.foundation_recommendation) {
            const foundationSeparator = document.createElement('tr');
            foundationSeparator.innerHTML = `<td colspan="3" class="px-4 py-2 bg-gray-50 text-sm font-semibold text-gray-700">Rekomendasi Fondasi</td>`;
            tableBody.appendChild(foundationSeparator);

            const frow = document.createElement('tr');
            frow.innerHTML = `
                <td class="px-4 py-3 text-sm font-medium text-gray-900">${results.foundation_recommendation.rekomendasi}</td>
                <td class="px-4 py-3 text-sm text-gray-700" colspan="2">${results.foundation_recommendation.keterangan}</td>
            `;
            tableBody.appendChild(frow);
        }

        // Gempa historis terdekat
        if (results.nearest_earthquakes && results.nearest_earthquakes.length > 0) {
            const eqSeparator = document.createElement('tr');
            eqSeparator.innerHTML = `<td colspan="3" class="px-4 py-2 bg-gray-50 text-sm font-semibold text-gray-700">Gempa Historis Terdekat</td>`;
            tableBody.appendChild(eqSeparator);

            results.nearest_earthquakes.forEach(eq => {
                const tr = document.createElement('tr');
                const distance = eq.distance_km ? parseFloat(eq.distance_km).toFixed(1) : '-';
                const date = eq.origin_time ? new Date(eq.origin_time).toLocaleDateString('id-ID') : '-';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">M${eq.magnitude} — ${date}</td>
                    <td class="px-4 py-3 text-sm text-gray-700" colspan="2">Berjarak ${distance} km dari lokasi, kedalaman ${eq.depth} km</td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Update result narrative
        document.getElementById('resultNarrative').innerHTML = `<p>${data.narrative}</p>`;

        // Scroll to results
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
@endpush
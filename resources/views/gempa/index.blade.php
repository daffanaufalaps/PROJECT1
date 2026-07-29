@extends('layouts.app')

@section('content')

<!-- Wajib memuat CSS Leaflet agar ubin peta tersusun rapi dan tidak pecah -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>

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
                            min="-11"
                            max="6"
                            value="{{ $latitude ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Contoh: -6.2"
                        >
                        <p class="mt-1 text-xs text-gray-500">Rentang: -11 sampai 6</p>
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
                            min="95"
                            max="141"
                            value="{{ $longitude ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Contoh: 106.8"
                        >
                        <p class="mt-1 text-xs text-gray-500">Rentang: 95 sampai 141</p>
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
                            <option value="D" selected>D - Tanah Kaku (Default)</option>
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

    const map = L.map('map').setView([initialLat, initialLng], initialZoom);

    // Add OpenStreetMap base layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    // Custom marker icon
    const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: #2563eb; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });

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
        } else if (lat < -11 || lat > 6) {
            errors.push('Lintang harus antara -11 sampai 6 (wilayah Indonesia).');
        }
        if (!lng || isNaN(lng)) {
            errors.push('Bujur (Longitude) wajib diisi dengan angka valid.');
        } else if (lng < 95 || lng > 141) {
            errors.push('Bujur harus antara 95 sampai 141 (wilayah Indonesia).');
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

        // Update narrative
        document.getElementById('narrative').innerHTML = `<p>${data.narrative}</p>`;

        // Show results section and populate table
        document.getElementById('resultsSection').classList.remove('hidden');

        const tableBody = document.getElementById('resultsTableBody');
        tableBody.innerHTML = '';

        const rows = [
            { param: 'PGA (Peak Ground Acceleration)', value: results.pga + ' g', desc: 'Percepatan puncak tanah' },
            { param: 'Skala MMI', value: results.mmi, desc: data.mmi_description },
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

        // Update result narrative
        document.getElementById('resultNarrative').innerHTML = `<p>${data.narrative}</p>`;

        // Scroll to results
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
@endpush
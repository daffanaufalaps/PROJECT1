<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Risiko Gempa Bumi</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
        }
        .logos {
            text-align: center;
            margin-bottom: 8px;
        }
        .logos img {
            height: 38px;
            margin: 0 5px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 15px;
            color: #1e40af;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 9px;
            color: #6b7280;
            margin: 0;
        }
        table.meta {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #374151;
        }
        table.meta td {
            padding: 2px 0;
        }
        .section-title {
            background-color: #eff6ff;
            color: #1e3a8a;
            font-weight: bold;
            font-size: 10.5px;
            padding: 6px 10px;
            margin-top: 14px;
            margin-bottom: 6px;
            border-left: 4px solid #2563eb;
        }
        table.stat-cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 4px;
        }
        table.stat-cards td {
            width: 25%;
            text-align: center;
            padding: 10px 6px;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            vertical-align: top;
        }
        .stat-value {
            font-size: 15px;
            font-weight: bold;
        }
        .stat-sub {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }
        .stat-label {
            font-size: 7.5px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 3px;
            letter-spacing: 0.5px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            font-size: 9.5px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .narrative-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            font-size: 10px;
            line-height: 1.6;
            margin-top: 6px;
        }
        .footer-note {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            font-size: 8.5px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logos">
        <img src="{{ public_path('images/logo-bmkg.png') }}" alt="BMKG">
        <img src="{{ public_path('images/logo-stmkg.png') }}" alt="STMKG">
        <img src="{{ public_path('images/logo-instrumentasi.png') }}" alt="Instrumentasi">
        <img src="{{ public_path('images/logo-pusgen.png') }}" alt="PuSGeN">
    </div>

    <div class="header">
        <h1>LAPORAN HASIL ANALISIS RISIKO GEMPA BUMI</h1>
        <p>Sistem Pendukung Keputusan Berbasis WebGIS &mdash; Sesuai SNI 1726:2019</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width:150px;"><strong>Tanggal Laporan</strong></td>
            <td>: {{ now()->format('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Koordinat Lokasi</strong></td>
            <td>: {{ number_format($result['latitude'], 6) }}&deg; LS, {{ number_format($result['longitude'], 6) }}&deg; BT</td>
        </tr>
        <tr>
            <td><strong>Kelas Situs</strong></td>
            <td>: {{ $result['site_class'] }} ({{ ($result['site_class_source'] ?? '') === 'manual' ? 'dipilih manual' : 'otomatis dari data spasial' }})</td>
        </tr>
    </table>

    @if($result['requires_detailed_study'] ?? false)
        <div class="section-title">Peringatan</div>
        <div class="narrative-box">
            {{ $result['warning_message'] }}
        </div>
    @else
        @php
            $riskColors = [
                'Risiko Sangat Rendah' => '#16a34a',
                'Risiko Rendah' => '#65a30d',
                'Risiko Sedang' => '#ca8a04',
                'Risiko Tinggi' => '#ea580c',
                'Risiko Sangat Tinggi' => '#dc2626',
            ];
            $riskColor = $riskColors[$result['risk_category']] ?? '#6b7280';
            $riskLabel = str_replace('Risiko ', '', $result['risk_category']);
        @endphp

        <div class="section-title">Ringkasan Hasil Analisis</div>
        <table class="stat-cards">
            <tr>
                <td>
                    <div class="stat-value" style="color:#1e3a8a;">{{ $result['pga'] }} g</div>
                    <div class="stat-sub">({{ $result['pga_gal'] }} gal)</div>
                    <div class="stat-label">Nilai PGA</div>
                </td>
                <td>
                    <div class="stat-value" style="color:#1e3a8a;">Skala {{ $result['sig_bmkg_scale'] }}</div>
                    <div class="stat-sub">(setara MMI {{ $result['sig_bmkg_mmi_equivalent'] }})</div>
                    <div class="stat-label">SIG-BMKG</div>
                </td>
                <td style="border-color: {{ $riskColor }};">
                    <div class="stat-value" style="color: {{ $riskColor }};">{{ $riskLabel }}</div>
                    <div class="stat-label">Kategori Risiko</div>
                </td>
                <td>
                    <div class="stat-value" style="color:#1e3a8a;">{{ strtoupper($result['kds']) }}</div>
                    <div class="stat-label">Kategori Desain Seismik (KDS)</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Parameter Detail (SNI 1726:2019)</div>
        <table class="data-table">
            <tr><th>Parameter</th><th>Nilai</th><th>Parameter</th><th>Nilai</th></tr>
            <tr>
                <td>Ss (Percepatan batuan dasar 0,2 detik)</td><td>{{ $result['ss'] }} g</td>
                <td>S1 (Percepatan batuan dasar 1,0 detik)</td><td>{{ $result['s1'] }} g</td>
            </tr>
            <tr>
                <td>Fa (Faktor amplifikasi situs 0,2 detik)</td><td>{{ $result['fa'] }}</td>
                <td>Fv (Faktor amplifikasi situs 1,0 detik)</td><td>{{ $result['fv'] }}</td>
            </tr>
            <tr>
                <td>SMS (Percepatan desain spektral 0,2 detik)</td><td>{{ $result['sms'] }} g</td>
                <td>SM1 (Percepatan desain spektral 1,0 detik)</td><td>{{ $result['sm1'] }} g</td>
            </tr>
            <tr>
                <td>SDS (Parameter percepatan desain 0,2 detik)</td><td>{{ $result['sds'] }} g</td>
                <td>SD1 (Parameter percepatan desain 1,0 detik)</td><td>{{ $result['sd1'] }} g</td>
            </tr>
        </table>

        @if(!empty($result['spgs_recommendations']) || !empty($result['foundation_recommendation']))
        <div class="section-title">Rekomendasi Rekayasa Struktur</div>
        <table class="data-table">
            <tr><th style="width:35%">Kategori</th><th>Keterangan / Rekomendasi</th></tr>
            @foreach($result['spgs_recommendations'] as $spgs)
            <tr>
                <td>Sistem Pemikul Gaya Seismik<br>({{ $spgs['sistem'] }} - {{ $spgs['kode'] }})</td>
                <td>{{ $spgs['keterangan'] }}</td>
            </tr>
            @endforeach
            @if(!empty($result['foundation_recommendation']))
            <tr>
                <td>Fondasi<br>({{ $result['foundation_recommendation']['rekomendasi'] }})</td>
                <td>{{ $result['foundation_recommendation']['keterangan'] }}</td>
            </tr>
            @endif
        </table>
        @endif

        @if(!empty($result['nearest_earthquakes']))
        <div class="section-title">Rekaman Gempa Historis Terdekat</div>
        <table class="data-table">
            <tr><th>Tanggal &amp; Waktu</th><th>Magnitudo</th><th>Kedalaman</th><th>Jarak Episentrum</th></tr>
            @foreach($result['nearest_earthquakes'] as $eq)
            <tr>
                <td>{{ \Carbon\Carbon::parse($eq['origin_time'])->format('d M Y H:i') }}</td>
                <td>M {{ $eq['magnitude'] }} ({{ $eq['magnitude_type'] }})</td>
                <td>{{ $eq['depth'] }} km</td>
                <td>{{ number_format($eq['distance_km'], 1) }} km</td>
            </tr>
            @endforeach
        </table>
        @endif

        <div class="section-title">Narasi Hasil Analisis</div>
        <div class="narrative-box">{{ $narrative }}</div>

        <div class="section-title">Penjelasan Kategori Risiko</div>
        <div class="narrative-box">{{ $riskDescription }}</div>
    @endif

    <div class="footer-note">
        Laporan ini dihasilkan secara otomatis oleh Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS.<br>
        Hasil bersifat indikatif sebagai referensi awal dan tidak ditujukan untuk menggantikan kajian teknik struktur secara rinci.
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 8;
            $color = array(0.5, 0.5, 0.5);
            $text = "Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS | Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>
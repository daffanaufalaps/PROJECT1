<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Risiko Gempa Bumi</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16px;
            color: #1e40af;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 10px;
            color: #6b7280;
            margin: 0;
        }
        table.meta {
            width: 100%;
            margin-bottom: 14px;
            font-size: 10px;
            color: #374151;
        }
        table.meta td {
            padding: 2px 0;
        }
        .section-title {
            background-color: #eff6ff;
            color: #1e3a8a;
            font-weight: bold;
            font-size: 11px;
            padding: 6px 10px;
            margin-top: 16px;
            margin-bottom: 6px;
            border-left: 4px solid #2563eb;
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
            font-size: 10px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .narrative-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            font-size: 10.5px;
            line-height: 1.6;
            margin-top: 6px;
        }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
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
        <div class="section-title">Ringkasan Hasil Analisis</div>
        <table class="data-table">
            <tr><th style="width:40%">Parameter</th><th>Nilai</th></tr>
            <tr><td>PGA (Peak Ground Acceleration)</td><td>{{ $result['pga'] }} g ({{ $result['pga_gal'] }} gal)</td></tr>
            <tr><td>Skala SIG-BMKG</td><td>{{ $result['sig_bmkg_scale'] }} (setara MMI {{ $result['sig_bmkg_mmi_equivalent'] }})</td></tr>
            <tr><td>Kategori Risiko</td><td>{{ $result['risk_category'] }}</td></tr>
            <tr><td>Kategori Desain Seismik (KDS)</td><td>{{ strtoupper($result['kds']) }}</td></tr>
        </table>

        <div class="section-title">Parameter Detail (SNI 1726:2019)</div>
        <table class="data-table">
            <tr><th style="width:40%">Parameter</th><th>Nilai</th></tr>
            <tr><td>Ss</td><td>{{ $result['ss'] }} g</td></tr>
            <tr><td>S1</td><td>{{ $result['s1'] }} g</td></tr>
            <tr><td>Fa</td><td>{{ $result['fa'] }}</td></tr>
            <tr><td>Fv</td><td>{{ $result['fv'] }}</td></tr>
            <tr><td>SMs</td><td>{{ $result['sms'] }} g</td></tr>
            <tr><td>SM1</td><td>{{ $result['sm1'] }} g</td></tr>
            <tr><td>SDs</td><td>{{ $result['sds'] }} g</td></tr>
            <tr><td>SD1</td><td>{{ $result['sd1'] }} g</td></tr>
        </table>

        @if(!empty($result['spgs_recommendations']))
        <div class="section-title">Rekomendasi Sistem Pemikul Gaya Seismik (SPGS)</div>
        <table class="data-table">
            <tr><th>Sistem</th><th>Keterangan</th></tr>
            @foreach($result['spgs_recommendations'] as $spgs)
            <tr><td>{{ $spgs['sistem'] }} ({{ $spgs['kode'] }})</td><td>{{ $spgs['keterangan'] }}</td></tr>
            @endforeach
        </table>
        @endif

        @if(!empty($result['nearest_earthquakes']))
        <div class="section-title">Gempa Historis Terdekat</div>
        <table class="data-table">
            <tr><th>Tanggal</th><th>Magnitudo</th><th>Kedalaman</th><th>Jarak</th></tr>
            @foreach($result['nearest_earthquakes'] as $eq)
            <tr>
                <td>{{ \Carbon\Carbon::parse($eq['origin_time'])->format('d M Y H:i') }}</td>
                <td>M{{ $eq['magnitude'] }} ({{ $eq['magnitude_type'] }})</td>
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

    <div class="footer">
        Laporan ini dihasilkan secara otomatis oleh Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS.<br>
        Hasil bersifat indikatif dan tidak menggantikan kajian teknik struktur secara rinci.
    </div>
</body>
</html>
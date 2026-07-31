<?php

namespace App\Services;

use App\Models\FaFactor;
use App\Models\FvFactor;
use App\Models\CalculationHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class for earthquake risk calculation based on SNI 1726:2019
 *
 * Ss, S1, PGA, Vs30, dan Kelas Situs diperoleh dari backend hazard API
 * (project WEBGIS-SNI-DSS) sebagai data grid statis hasil pra-proses
 * (georeferensi RSA PUPR via QGIS) — bukan perhitungan GMPE dinamis.
 *
 * Interpretasi dampak memakai Skala SIG-BMKG (Tabel 2.1) dengan metode
 * klasifikasi interval, BUKAN regresi empiris, sesuai metodologi yang
 * dituliskan di Bab 2.3.
 */
class GempaCalculationService
{
    public function calculate(float $latitude, float $longitude, ?string $siteClassOverride = null): array
    {
        $siteClassOverride = ($siteClassOverride !== null && $siteClassOverride !== '')
            ? strtoupper($siteClassOverride)
            : null;

        // Step 1: Ambil data hazard lengkap dari backend (grid statis: Ss, S1, PGA, Vs30, Kelas Situs)
        $hazard = $this->getHazardDataFromCoordinate($latitude, $longitude);

        // Step 1b: Ambil data gempa historis terdekat (informasi pendukung, tidak memengaruhi hitungan)
        $nearestEarthquakes = $this->getNearestEarthquakes($latitude, $longitude);

        // Step 2: Tentukan apakah user melakukan override Kelas Situs
        $isOverridden = $siteClassOverride !== null && $siteClassOverride !== $hazard['soil_class'];
        $effectiveSiteClass = $isOverridden ? $siteClassOverride : $hazard['soil_class'];

        // Step 3: Hitung Fa dan Fv berdasarkan Kelas Situs efektif
        $fa = $this->calculateFa($hazard['ss'], $effectiveSiteClass);
        $fv = $this->calculateFv($hazard['s1'], $effectiveSiteClass);

        // Kelas Situs tanpa koefisien resmi (mis. Kelas Situs F) tidak bisa
        // dihitung otomatis -- perlu kajian teknik lebih mendalam di lokasi ini.
        if ($fa === null || $fv === null) {
            $result = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'vs30' => $hazard['vs30'],
                'soil_name' => $hazard['soil_name'],
                'site_class' => $effectiveSiteClass,
                'site_class_source' => $isOverridden ? 'manual' : 'otomatis',
                'ss' => round($hazard['ss'], 4),
                's1' => round($hazard['s1'], 4),
                'fa' => $fa,
                'fv' => $fv,
                'sms' => null,
                'sm1' => null,
                'sds' => null,
                'sd1' => null,
                'pga' => null,
                'pga_gal' => null,
                'pga_source' => null,
                'sig_bmkg_scale' => null,
                'sig_bmkg_mmi_equivalent' => null,
                'risk_category' => null,
                'kds' => null,
                'spgs_recommendations' => [],
                'nearest_earthquakes' => $nearestEarthquakes,
                'requires_detailed_study' => true,
                'warning_message' => 'Diperlukan penghitungan yang lebih mendetail terhadap lokasi Anda.',
            ];

            $this->saveHistory($result);

            return $result;
        }

        // Step 4: Parameter spektral teradjustasi
        $sms = $this->calculateSMs($hazard['ss'], $fa);
        $sm1 = $this->calculateSM1($hazard['s1'], $fv);

        // Step 5: Parameter spektral desain
        $sds = $this->calculateSDs($sms);
        $sd1 = $this->calculateSD1($sm1);

        // Step 6: Tentukan PGA — pakai punya backend kecuali user override Kelas Situs
        if ($isOverridden) {
            $pga = $this->calculatePGA($sds, $hazard['ss'], $sms);
            $pgaSource = 'adjusted';
        } else {
            $pga = $hazard['pga'];
            $pgaSource = 'backend';
        }

        // Step 7: Konversi PGA ke Skala SIG-BMKG (Tabel 2.1) — klasifikasi interval, bukan regresi
        $sigBmkg = $this->convertPgaToSigBmkg($pga);

        // Step 8: Tentukan Kategori Risiko berdasarkan Skala SIG-BMKG
        $riskCategory = $this->determineRiskCategory($sigBmkg['scale']);

        // Step 9: Tentukan KDS
        $kds = $this->determineKds($sds, $sd1, $riskCategory);

        // Step 10: Rekomendasi SPGS berdasarkan KDS (Lampiran 2)
        $spgsRecommendations = $this->determineSpgsRecommendations($kds);

        $result = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'vs30' => $hazard['vs30'],
            'soil_name' => $hazard['soil_name'],
            'site_class' => $effectiveSiteClass,
            'site_class_source' => $isOverridden ? 'manual' : 'otomatis',
            'ss' => round($hazard['ss'], 4),
            's1' => round($hazard['s1'], 4),
            'fa' => round($fa, 4),
            'fv' => round($fv, 4),
            'sms' => round($sms, 4),
            'sm1' => round($sm1, 4),
            'sds' => round($sds, 4),
            'sd1' => round($sd1, 4),
            'pga' => round($pga, 4),
            'pga_gal' => $sigBmkg['pga_gal'],
            'pga_source' => $pgaSource,
            'sig_bmkg_scale' => $sigBmkg['scale'],
            'sig_bmkg_mmi_equivalent' => $sigBmkg['mmi_equivalent'],
            'risk_category' => $riskCategory,
            'kds' => $kds,
            'spgs_recommendations' => $spgsRecommendations,
            'nearest_earthquakes' => $nearestEarthquakes,
            'requires_detailed_study' => false,
        ];

        $this->saveHistory($result);

        return $result;
    }

    /**
     * Ambil data hazard lengkap (Ss, S1, PGA, Vs30, Kelas Situs) dari
     * backend hazard API (project WEBGIS-SNI-DSS) — data grid statis
     * hasil georeferensi RSA PUPR, bukan komputasi GMPE real-time.
     */
    public function getHazardDataFromCoordinate(float $latitude, float $longitude): array
    {
        try {
            $response = Http::timeout(5)->get(
                config('services.hazard_api.base_url') . '/hazard',
                ['lon' => $longitude, 'lat' => $latitude]
            );

            if ($response->successful()) {
                $body = $response->json();
                $rows = $body['data'] ?? $body;

                if (!empty($rows) && isset($rows[0])) {
                    $row = $rows[0];

                    return [
                        'ss' => (float) ($row['ss'] ?? 0.5),
                        's1' => (float) ($row['s1'] ?? 0.2),
                        'pga' => (float) ($row['pga'] ?? 0.2),
                        'vs30' => (float) ($row['vs30'] ?? 350),
                        'soil_class' => strtoupper($row['soil_class'] ?? 'D'),
                        'soil_name' => $row['soil_name'] ?? null,
                    ];
                }
            }

            Log::warning("Backend hazard API tidak mengembalikan data untuk koordinat: {$latitude},{$longitude}. Status: " . ($response->status() ?? 'tidak ada response') . ". Body: " . $response->body());

        } catch (\Exception $e) {
            Log::error('Gagal menghubungi backend hazard API: ' . $e->getMessage());
        }

        return [
            'ss' => 0.5,
            's1' => 0.2,
            'pga' => 0.2,
            'vs30' => 350,
            'soil_class' => 'D',
            'soil_name' => 'Tanah Sedang (nilai default/fallback)',
        ];
    }

    /**
     * Ambil daftar gempa historis terdekat dari backend (tabel earthquake_history).
     * Gagal koneksi tidak menghentikan proses utama -- cukup kembalikan array kosong.
     */
    public function getNearestEarthquakes(float $latitude, float $longitude, int $limit = 5, float $radiusKm = 500): array
    {
        try {
            $response = Http::timeout(5)->get(
                config('services.hazard_api.base_url') . '/nearest-earthquakes',
                ['lon' => $longitude, 'lat' => $latitude, 'limit' => $limit, 'radius_km' => $radiusKm]
            );

            if ($response->successful()) {
                $body = $response->json();
                return $body['data'] ?? [];
            }

            Log::warning("Backend tidak mengembalikan data gempa historis untuk koordinat: {$latitude},{$longitude}");

        } catch (\Exception $e) {
            Log::error('Gagal menghubungi endpoint nearest-earthquakes: ' . $e->getMessage());
        }

        return [];
    }

    public function calculateFa(float $ss, string $siteClass): ?float
    {
        // Sepenuhnya mengandalkan Tabel 2.3 (data resmi SNI 1726:2019).
        // Null berarti kombinasi Kelas Situs/Ss tidak tercakup tabel
        // (mis. Kelas Situs F), yang berarti butuh kajian lebih mendetail.
        return FaFactor::findFaValue($siteClass, $ss);
    }

    public function calculateFv(float $s1, string $siteClass): ?float
    {
        // Sepenuhnya mengandalkan Tabel 2.4 (data resmi SNI 1726:2019).
        return FvFactor::findFvValue($siteClass, $s1);
    }

    public function calculateSMs(float $ss, float $fa): float
    {
        return $ss * $fa;
    }

    public function calculateSM1(float $s1, float $fv): float
    {
        return $s1 * $fv;
    }

    public function calculateSDs(float $sms): float
    {
        return (2 / 3) * $sms;
    }

    public function calculateSD1(float $sm1): float
    {
        return (2 / 3) * $sm1;
    }

    public function calculatePGA(float $sds, float $ss, float $sms): float
    {
        return max(0.4 * $ss, 0.4 * $sms);
    }

    /**
     * Konversi PGA (satuan g) ke Skala Intensitas Gempa Bumi BMKG (SIG-BMKG)
     * menggunakan metode klasifikasi berbasis interval sesuai Tabel 2.1 —
     * BUKAN regresi empiris (mis. Wald et al.), sesuai metodologi Bab 2.3.
     *
     * @return array{scale: string, mmi_equivalent: string, pga_gal: float}
     */
    public function convertPgaToSigBmkg(float $pgaInG): array
    {
        // 1 g = 980,665 gal (percepatan gravitasi standar)
        $pgaGal = $pgaInG * 980.665;

        if ($pgaGal < 2.9) {
            $scale = 'I';
            $mmiEquivalent = '1-2';
        } elseif ($pgaGal < 89) {
            $scale = 'II';
            $mmiEquivalent = '3-5';
        } elseif ($pgaGal < 168) {
            $scale = 'III';
            $mmiEquivalent = '6';
        } elseif ($pgaGal < 565) {
            $scale = 'IV';
            $mmiEquivalent = '7-8';
        } else {
            $scale = 'V';
            $mmiEquivalent = '9-12';
        }

        return [
            'scale' => $scale,
            'mmi_equivalent' => $mmiEquivalent,
            'pga_gal' => round($pgaGal, 2),
        ];
    }

    /**
     * Kategori risiko diturunkan langsung dari Skala SIG-BMKG,
     * supaya satu sumber klasifikasi dipakai konsisten di seluruh sistem.
     */
    public function determineRiskCategory(string $sigBmkgScale): string
    {
        return match ($sigBmkgScale) {
            'I' => 'Risiko Sangat Rendah',
            'II' => 'Risiko Rendah',
            'III' => 'Risiko Sedang',
            'IV' => 'Risiko Tinggi',
            'V' => 'Risiko Sangat Tinggi',
            default => 'Risiko Tidak Diketahui',
        };
    }

    public function determineKds(float $sds, float $sd1, string $riskCategory): string
    {
        $isHighRisk = str_contains($riskCategory, 'Sangat Tinggi') || str_contains($riskCategory, 'Tinggi');

        if ($sds >= 0.75) {
            return $isHighRisk ? 'E' : 'D';
        } elseif ($sds >= 0.50) {
            return $isHighRisk ? 'D' : 'D';
        } elseif ($sds >= 0.25) {
            return $isHighRisk ? 'D' : 'C';
        } elseif ($sds >= 0.15) {
            return $isHighRisk ? 'C' : 'B';
        } else {
            return 'A';
        }
    }

    /**
     * Rekomendasi Sistem Pemikul Gaya Seismik (SPGS) berdasarkan KDS,
     * sesuai Lampiran 2.
     */
    public function determineSpgsRecommendations(string $kds): array
    {
        $table = [
            'A' => [
                ['sistem' => 'SRPM Biasa', 'kode' => 'SRPMB', 'keterangan' => 'Daerah bahaya rendah'],
                ['sistem' => 'Dinding Struktur Biasa', 'kode' => 'SDSB', 'keterangan' => 'Bangunan sederhana'],
            ],
            'B' => [
                ['sistem' => 'SRPM Biasa', 'kode' => 'SRPMB', 'keterangan' => 'Masih diperbolehkan'],
                ['sistem' => 'SRPM Menengah', 'kode' => 'SRPMM', 'keterangan' => 'Lebih baik'],
                ['sistem' => 'SRPM Khusus', 'kode' => 'SRPMK', 'keterangan' => 'Sangat baik'],
                ['sistem' => 'Dinding Struktur Biasa', 'kode' => 'SDSB', 'keterangan' => 'Alternatif'],
                ['sistem' => 'Dinding Struktur Khusus', 'kode' => 'SDSK', 'keterangan' => 'Alternatif'],
            ],
            'C' => [
                ['sistem' => 'SRPM Menengah', 'kode' => 'SRPMM', 'keterangan' => 'Direkomendasikan'],
                ['sistem' => 'SRPM Khusus', 'kode' => 'SRPMK', 'keterangan' => 'Direkomendasikan'],
                ['sistem' => 'Dinding Struktur Khusus', 'kode' => 'SDSK', 'keterangan' => 'Untuk bangunan tinggi'],
            ],
            'D' => [
                ['sistem' => 'SRPM Khusus', 'kode' => 'SRPMK', 'keterangan' => 'Wajib untuk sistem rangka momen beton'],
                ['sistem' => 'Dinding Struktur Khusus', 'kode' => 'SDSK', 'keterangan' => 'Direkomendasikan'],
                ['sistem' => 'Sistem Ganda', 'kode' => 'DUAL', 'keterangan' => 'Sangat direkomendasikan'],
            ],
            'E' => [
                ['sistem' => 'SRPM Khusus', 'kode' => 'SRPMK', 'keterangan' => 'Wajib'],
                ['sistem' => 'Dinding Struktur Khusus', 'kode' => 'SDSK', 'keterangan' => 'Wajib bila memakai dinding'],
                ['sistem' => 'Sistem Ganda', 'kode' => 'DUAL', 'keterangan' => 'Sangat direkomendasikan'],
            ],
            'F' => [
                ['sistem' => 'SRPM Khusus', 'kode' => 'SRPMK', 'keterangan' => 'Wajib'],
                ['sistem' => 'Dinding Struktur Khusus', 'kode' => 'SDSK', 'keterangan' => 'Wajib'],
                ['sistem' => 'Sistem Ganda', 'kode' => 'DUAL', 'keterangan' => 'Direkomendasikan'],
            ],
        ];

        return $table[$kds] ?? [];
    }

    protected function saveHistory(array $result): CalculationHistory
    {
        return CalculationHistory::create([
            'latitude' => $result['latitude'],
            'longitude' => $result['longitude'],
            'ss' => $result['ss'],
            's1' => $result['s1'],
            'fa' => $result['fa'],
            'fv' => $result['fv'],
            'sms' => $result['sms'],
            'sm1' => $result['sm1'],
            'sds' => $result['sds'],
            'sd1' => $result['sd1'],
            'pga' => $result['pga'],
            'sig_bmkg_scale' => $result['sig_bmkg_scale'],
            'risk_category' => $result['risk_category'],
            'kds' => $result['kds'],
        ]);
    }
}
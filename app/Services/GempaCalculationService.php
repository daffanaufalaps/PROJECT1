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
 * Ss, S1, PGA, Vs30, dan Kelas Situs otomatis diperoleh dari backend
 * hazard API (project WEBGIS-SNI-DSS). Perhitungan turunan (Fa, Fv,
 * SMs, SM1, SDs, SD1, MMI, kategori risiko, KDS) tetap dilakukan di
 * service ini (server-side), sesuai arsitektur pada skripsi Bab 3.3.2
 * bahwa seluruh analisis dilakukan di sisi server.
 */
class GempaCalculationService
{
    /**
     * Perform complete calculation for a given coordinate
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $siteClassOverride Kelas Situs manual dari user (opsional).
     *        Null/kosong berarti pakai soil_class otomatis dari backend.
     */
    public function calculate(float $latitude, float $longitude, ?string $siteClassOverride = null): array
    {
        // Normalisasi: string kosong dari form dianggap "tidak override"
        $siteClassOverride = ($siteClassOverride !== null && $siteClassOverride !== '')
            ? strtoupper($siteClassOverride)
            : null;

        // Step 1: Ambil data hazard lengkap dari backend (Ss, S1, PGA, Vs30, Kelas Situs)
        $hazard = $this->getHazardDataFromCoordinate($latitude, $longitude);

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
                'pga_source' => null,
                'mmi' => null,
                'risk_category' => null,
                'kds' => null,
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

        // Step 7: Konversi PGA ke MMI
        $mmi = $this->convertPgaToMmi($pga);

        // Step 8: Tentukan Kategori Risiko
        $riskCategory = $this->determineRiskCategory($pga, $mmi);

        // Step 9: Tentukan KDS
        $kds = $this->determineKds($sds, $sd1, $riskCategory);

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
            'pga_source' => $pgaSource,
            'mmi' => round($mmi, 2),
            'risk_category' => $riskCategory,
            'kds' => $kds,
            'requires_detailed_study' => false,
        ];

        $this->saveHistory($result);

        return $result;
    }

    /**
     * Ambil data hazard lengkap (Ss, S1, PGA, Vs30, Kelas Situs) dari
     * backend hazard API (project WEBGIS-SNI-DSS).
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
                $rows = $body['data'] ?? $body; // fleksibel: wrapped atau tidak

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

            Log::warning("Backend hazard API tidak mengembalikan data untuk koordinat: {$latitude},{$longitude}");

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

    public function calculateFa(float $ss, string $siteClass): ?float
    {
        $faValue = FaFactor::findFaValue($siteClass, $ss);

        if ($faValue !== null) {
            return $faValue;
        }

    // Fallback sementara selama tabel fa_factors belum terisi data resmi SNI.
    // Kelas Situs F (atau kelas tak dikenal) TIDAK diberi tebakan angka,
    // karena SNI 1726:2019 mewajibkan kajian spesifik lokasi untuk kelas ini.
        return match ($siteClass) {
            'A' => 0.8,
            'B' => 1.0,
            'C' => 1.2,
            'D' => 1.4,
            'E' => 1.7,
            default => null,
        };
    }

    public function calculateFv(float $s1, string $siteClass): ?float
    {
        $fvValue = FvFactor::findFvValue($siteClass, $s1);

        if ($fvValue !== null) {
            return $fvValue;
        }

        return match ($siteClass) {
            'A' => 0.8,
            'B' => 1.0,
            'C' => 1.7,
            'D' => 2.0,
            'E' => 3.2,
            default => null,
        };
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

    public function convertPgaToMmi(float $pga): float
    {
        if ($pga <= 0) {
            return 1.0;
        }

        $pgaPercent = $pga * 100;
        $mmi = 3.78 * log10($pgaPercent) + 1.47;
        $mmi = max(1, min(12, $mmi));

        return round($mmi * 2) / 2;
    }

    public function determineRiskCategory(float $pga, float $mmi): string
    {
        if ($pga >= 0.6 || $mmi >= 9.0) {
            return 'Risiko Sangat Tinggi';
        } elseif ($pga >= 0.4 || $mmi >= 7.5) {
            return 'Risiko Tinggi';
        } elseif ($pga >= 0.2 || $mmi >= 6.0) {
            return 'Risiko Sedang';
        } elseif ($pga >= 0.1 || $mmi >= 5.0) {
            return 'Risiko Rendah';
        } else {
            return 'Risiko Sangat Rendah';
        }
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
            'mmi' => $result['mmi'],
            'risk_category' => $result['risk_category'],
            'kds' => $result['kds'],
        ]);
    }
}
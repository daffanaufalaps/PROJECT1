<?php

namespace App\Services;

use App\Models\FaFactor;
use App\Models\FvFactor;
use App\Models\CalculationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class for earthquake risk calculation based on SNI 1726:2019
 *
 * This service handles the complete calculation pipeline for seismic
 * hazard parameters including PGA, MMI, Risk Category, and KDS.
 */
class GempaCalculationService
{
    protected ?string $defaultSiteClass = 'D'; // Default site class for Indonesia

    /**
     * Perform complete calculation for a given coordinate
     */
    public function calculate(float $latitude, float $longitude, ?string $siteClass = null): array
    {
        $siteClass = $siteClass ?? $this->defaultSiteClass;

        // Step 1: Get Ss and S1 from backend hazard API (project WEBGIS-SNI-DSS)
        $parameters = $this->getSsS1FromCoordinate($latitude, $longitude);

        // Step 2: Calculate Fa and Fv based on site class
        $fa = $this->calculateFa($parameters['ss'], $siteClass);
        $fv = $this->calculateFv($parameters['s1'], $siteClass);

        // Step 3: Calculate adjusted spectral parameters
        $sms = $this->calculateSMs($parameters['ss'], $fa);
        $sm1 = $this->calculateSM1($parameters['s1'], $fv);

        // Step 4: Calculate design spectral parameters
        $sds = $this->calculateSDs($sms);
        $sd1 = $this->calculateSD1($sm1);

        // Step 5: Calculate PGA
        $pga = $this->calculatePGA($sds, $parameters['ss'], $sms);

        // Step 6: Convert PGA to MMI
        $mmi = $this->convertPgaToMmi($pga);

        // Step 7: Determine Risk Category
        $riskCategory = $this->determineRiskCategory($pga, $mmi);

        // Step 8: Determine KDS (Seismic Design Category)
        $kds = $this->determineKds($sds, $sd1, $riskCategory);

        $result = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'site_class' => $siteClass,
            'ss' => round($parameters['ss'], 4),
            's1' => round($parameters['s1'], 4),
            'fa' => round($fa, 4),
            'fv' => round($fv, 4),
            'sms' => round($sms, 4),
            'sm1' => round($sm1, 4),
            'sds' => round($sds, 4),
            'sd1' => round($sd1, 4),
            'pga' => round($pga, 4),
            'mmi' => round($mmi, 2),
            'risk_category' => $riskCategory,
            'kds' => $kds,
        ];

        // Save to history
        $this->saveHistory($result);

        return $result;
    }

    /**
     * Ambil Ss dan S1 dari backend hazard API (project WEBGIS-SNI-DSS)
     * Menggantikan query PostGIS lokal — sekarang data diambil via HTTP
     * dari backend terpisah yang menyimpan data spasial hazard.
     */
    public function getSsS1FromCoordinate(float $latitude, float $longitude): array
    {
        try {
            $response = Http::timeout(5)->get(
                config('services.hazard_api.base_url') . '/hazard',
                ['lon' => $longitude, 'lat' => $latitude]
            );

            if ($response->successful()) {
                $body = $response->json();
                $rows = $body['data'] ?? [];

                if (!empty($rows)) {
                    $row = $rows[0]; // ambil hasil pertama dari fungsi get_hazard_at_location

                    return [
                        'ss' => (float) ($row['ss'] ?? 0.5),
                        's1' => (float) ($row['s1'] ?? 0.2),
                    ];
                }
            }

            Log::warning('Backend hazard API tidak mengembalikan data untuk koordinat: ' . $latitude . ',' . $longitude);

        } catch (\Exception $e) {
            Log::error('Gagal menghubungi backend hazard API: ' . $e->getMessage());
        }

        // Fallback kalau API gagal/timeout/data kosong — tetap ada nilai default
        return [
            'ss' => 0.5,
            's1' => 0.2,
        ];
    }

    /**
     * Calculate Fa (Site Coefficient for Short Period) based on SNI 1726:2019 Table 7
     */
    public function calculateFa(float $ss, string $siteClass): float
    {
        $faValue = FaFactor::findFaValue($siteClass, $ss);

        if ($faValue !== null) {
            return $faValue;
        }

        return match ($siteClass) {
            'A' => 0.8,
            'B' => 1.0,
            'C' => 1.2,
            'D' => 1.4,
            'E' => 1.7,
            default => 1.0,
        };
    }

    /**
     * Calculate Fv (Site Coefficient for 1-second Period) based on SNI 1726:2019 Table 8
     */
    public function calculateFv(float $s1, string $siteClass): float
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
            default => 1.0,
        };
    }

    /**
     * SMs = Fa × Ss
     */
    public function calculateSMs(float $ss, float $fa): float
    {
        return $ss * $fa;
    }

    /**
     * SM1 = Fv × S1
     */
    public function calculateSM1(float $s1, float $fv): float
    {
        return $s1 * $fv;
    }

    /**
     * SDs = (2/3) × SMs
     */
    public function calculateSDs(float $sms): float
    {
        return (2 / 3) * $sms;
    }

    /**
     * SD1 = (2/3) × SM1
     */
    public function calculateSD1(float $sm1): float
    {
        return (2 / 3) * $sm1;
    }

    /**
     * Calculate PGA (Peak Ground Acceleration) based on SNI 1726:2019
     */
    public function calculatePGA(float $sds, float $ss, float $sms): float
    {
        return max(0.4 * $ss, 0.4 * $sms);
    }

    /**
     * Convert PGA value to MMI (Modified Mercalli Intensity) scale
     */
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

    /**
     * Determine Risk Category based on PGA and MMI values
     */
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

    /**
     * Determine KDS (Kategori Desain Seismik / Seismic Design Category)
     */
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
     * Get site class for a given coordinate from local spatial database
     * (tetap query lokal — ini beda dari data Ss/S1 yang sudah pindah ke API)
     */
    public function getSiteClassFromCoordinate(float $latitude, float $longitude): ?string
    {
        $result = DB::select("
            SELECT site_class
            FROM site_classes
            WHERE ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))
            LIMIT 1
        ", [$longitude, $latitude]);

        if (!empty($result)) {
            return $result[0]->site_class;
        }

        return null;
    }

    /**
     * Save calculation result to history
     */
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
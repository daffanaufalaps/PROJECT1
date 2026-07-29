<?php

namespace App\Services;

use App\Models\NarrationTemplate;

/**
 * Service class for generating narrative text from calculation results
 * Uses template-based text generation with placeholder substitution
 */
class NarrationService
{
    /**
     * Generate narrative text from calculation results
     *
     * @param array $calculationResult Result from GempaCalculationService::calculate()
     * @param string|null $templateName Optional template name to use
     * @return string Generated narrative text
     */
    public function generateNarrative(array $calculationResult, ?string $templateName = null): string
    {
        $template = $this->getTemplate($templateName);

        if (!$template) {
            return $this->generateDefaultNarrative($calculationResult);
        }

        return $this->fillTemplate($template->template, $calculationResult);
    }

    /**
     * Get template by name or active default
     *
     * @param string|null $templateName
     * @return NarrationTemplate|null
     */
    protected function getTemplate(?string $templateName): ?NarrationTemplate
    {
        if ($templateName) {
            return NarrationTemplate::where('name', $templateName)->first();
        }

        return NarrationTemplate::active()->first();
    }

    /**
     * Fill template placeholders with actual values
     *
     * @param string $template Template string with {placeholder} syntax
     * @param array $data Data to fill placeholders
     * @return string Filled template
     */
    protected function fillTemplate(string $template, array $data): string
    {
        $narrative = $template;

        // Replace all placeholders with actual values
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $displayValue = $this->formatValue($key, $value);
            $narrative = str_replace($placeholder, $displayValue, $narrative);
        }

        return $narrative;
    }

    /**
     * Format value for display in narrative
     *
     * @param string $key Parameter key
     * @param mixed $value Parameter value
     * @return string Formatted value
     */
    protected function formatValue(string $key, mixed $value): string
    {
        // Handle null values
        if ($value === null) {
            return 'N/A';
        }

        // Format based on parameter type
        return match ($key) {
            'latitude' => number_format($value, 6),
            'longitude' => number_format($value, 6),
            'ss', 's1', 'fa', 'fv', 'sms', 'sm1', 'sds', 'sd1', 'pga' => number_format($value, 4),
            'mmi' => number_format($value, 1),
            'risk_category' => $value,
            'kds' => strtoupper($value),
            'site_class' => strtoupper($value),
            default => (string) $value,
        };
    }

    /**
     * Generate default narrative when no template is available
     *
     * @param array $calculationResult
     * @return string Default narrative text
     */
    protected function generateDefaultNarrative(array $calculationResult): string
    {
        $lat = $this->formatValue('latitude', $calculationResult['latitude']);
        $lon = $this->formatValue('longitude', $calculationResult['longitude']);
        $pga = $this->formatValue('pga', $calculationResult['pga']);
        $mmi = $this->formatValue('mmi', $calculationResult['mmi']);
        $riskCategory = $calculationResult['risk_category'];
        $kds = $this->formatValue('kds', $calculationResult['kds']);

        $narrative = "Analisis risiko gempa untuk koordinat {$lat}° LS, {$lon}° BT ";
        $narrative .= "menghasilkan nilai PGA sebesar {$pga}g yang setara dengan skala MMI {$mmi}. ";
        $narrative .= "Lokasi ini dikategorikan sebagai '{$riskCategory}' ";
        $narrative .= "dengan Kategori Desain Seismik (KDS) {$kds}.";

        return $narrative;
    }

    /**
     * Generate initial/preview narrative before calculation
     *
     * @param float|null $latitude
     * @param float|null $longitude
     * @return string Initial narrative text
     */
    public function generateInitialNarrative(?float $latitude, ?float $longitude): string
    {
        if ($latitude === null || $longitude === null) {
            return "Pilih lokasi di peta atau masukkan koordinat Lintang dan Bujur untuk memulai analisis risiko gempa bumi sesuai SNI 1726:2019.";
        }

        $lat = number_format($latitude, 6);
        $lon = number_format($longitude, 6);

        return "Koordinat terpilih: {$lat}° LS, {$lon}° BT. Klik tombol 'Hitung' untuk melakukan analisis risiko gempa bumi.";
    }

    /**
     * Generate MMI scale description for a given MMI value
     *
     * @param float $mmi MMI value
     * @return string MMI scale description in Indonesian
     */
    public function getMmiDescription(float $mmi): string
    {
        $mmiInt = (int) round($mmi);

        return match ($mmiInt) {
            1 => 'I - Tidak Terasa',
            2 => 'II - Terasa Ringan',
            3 => 'III - Lemah',
            4 => 'IV - Sedang',
            5 => 'V - Agak Kuat',
            6 => 'VI - Kuat',
            7 => 'VII - Sangat Kuat',
            8 => 'VIII - Merusak',
            9 => 'IX - Sangat Merusak',
            10 => 'X - Hebat',
            11 => 'XI - Sangat Hebat',
            12 => 'XII - Ekstem',
            default => 'N/A',
        };
    }

    /**
     * Generate risk category description
     *
     * @param string $riskCategory
     * @return string Detailed risk description
     */
    public function getRiskCategoryDescription(string $riskCategory): string
    {
        return match ($riskCategory) {
            'Risiko Sangat Tinggi' => 'Lokasi berada di zona gempa sangat tinggi. Diperlukan desain seismik ketat sesuai SNI 1726:2019.',
            'Risiko Tinggi' => 'Lokasi berada di zona gempa tinggi. Perlu perhatian khusus pada struktur bangunan.',
            'Risiko Sedang' => 'Lokasi berada di zona gempa sedang. Bangunan harus dirancang dengan standar gempa yang memadai.',
            'Risiko Rendah' => 'Lokasi berada di zona gempa rendah. Risiko gempa relatif kecil namun tetap perlu diperhatikan.',
            'Risiko Sangat Rendah' => 'Lokasi berada di zona gempa sangat rendah. Risiko gempa sangat kecil.',
            default => 'Kategori risiko tidak terdefinisi.',
        };
    }
}

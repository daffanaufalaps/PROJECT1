<?php

namespace App\Services;

use App\Models\NarrationTemplate;

class NarrationService
{
    public function generateNarrative(array $calculationResult, ?string $templateName = null): string
    {
        $template = $this->getTemplate($templateName);

        if (!$template) {
            return $this->generateDefaultNarrative($calculationResult);
        }

        return $this->fillTemplate($template->template, $calculationResult);
    }

    protected function getTemplate(?string $templateName): ?NarrationTemplate
    {
        if ($templateName) {
            return NarrationTemplate::where('name', $templateName)->first();
        }

        return NarrationTemplate::active()->first();
    }

    protected function fillTemplate(string $template, array $data): string
    {
        $narrative = $template;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue; // lewati field non-skalar seperti spgs_recommendations, nearest_earthquakes
            }
            $placeholder = '{' . $key . '}';
            $displayValue = $this->formatValue($key, $value);
            $narrative = str_replace($placeholder, $displayValue, $narrative);
        }

        return $narrative;
    }

    protected function formatValue(string $key, mixed $value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        return match ($key) {
            'latitude' => number_format($value, 6),
            'longitude' => number_format($value, 6),
            'ss', 's1', 'fa', 'fv', 'sms', 'sm1', 'sds', 'sd1', 'pga' => number_format($value, 4),
            'pga_gal' => number_format($value, 2) . ' gal',
            'sig_bmkg_scale' => (string) $value,
            'risk_category' => $value,
            'kds' => strtoupper($value),
            'site_class' => strtoupper($value),
            default => (string) $value,
        };
    }

    protected function generateDefaultNarrative(array $calculationResult): string
    {
        $lat = $this->formatValue('latitude', $calculationResult['latitude']);
        $lon = $this->formatValue('longitude', $calculationResult['longitude']);
        $pga = $this->formatValue('pga', $calculationResult['pga']);
        $pgaGal = $this->formatValue('pga_gal', $calculationResult['pga_gal']);
        $sigBmkgScale = $calculationResult['sig_bmkg_scale'];
        $riskCategory = $calculationResult['risk_category'];
        $kds = $this->formatValue('kds', $calculationResult['kds']);

        $narrative = "Analisis risiko gempa untuk koordinat {$lat}° LS, {$lon}° BT ";
        $narrative .= "menghasilkan nilai PGA sebesar {$pga}g ({$pgaGal}) yang termasuk dalam Skala SIG-BMKG {$sigBmkgScale}. ";
        $narrative .= "Lokasi ini dikategorikan sebagai '{$riskCategory}' ";
        $narrative .= "dengan Kategori Desain Seismik (KDS) {$kds}.";

        return $narrative;
    }

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
     * Deskripsi Skala SIG-BMKG, sesuai Tabel 2.1.
     */
    public function getSigBmkgDescription(string $scale): string
    {
        return match ($scale) {
            'I' => 'I - Tidak dirasakan atau dirasakan hanya oleh beberapa orang tetapi tidak terekam oleh alat.',
            'II' => 'II - Dirasakan oleh orang banyak tetapi tidak menimbulkan kerusakan. Benda-benda ringan yang digantung bergoyang dan jendela kaca bergetar.',
            'III' => 'III - Bagian non struktur bangunan mengalami kerusakan ringan, seperti retak rambut pada dinding, atap bergeser ke bawah dan sebagian berjatuhan.',
            'IV' => 'IV - Banyak retakan terjadi pada dinding bangunan sederhana, sebagian roboh, kaca pecah. Sebagian plester dinding lepas. Sebagian besar atap bergeser ke bawah atau runtuh. Struktur bangunan mengalami kerusakan ringan sampai sedang.',
            'V' => 'V - Sebagian besar dinding bangunan permanen roboh. Struktur bangunan mengalami kerusakan berat. Rel kereta api melengkung.',
            default => 'Skala tidak diketahui.',
        };
    }

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
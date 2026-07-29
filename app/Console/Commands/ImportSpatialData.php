<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportSpatialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:spatial-data
                            {--type=all : Type of data to import (all, earthquake, siteclass)}
                            {--file= : Path to GeoJSON file}
                            {--truncate : Truncate tables before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import spatial data (earthquake parameters, site classes) from GeoJSON files into PostGIS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $file = $this->option('file');
        $truncate = $this->option('truncate');

        $this->info('Starting spatial data import...');
        $this->newLine();

        // Verify PostGIS extension
        $postgis = DB::select("SELECT 1 FROM pg_extension WHERE extname = 'postgis'");
        if (empty($postgis)) {
            $this->error('PostGIS extension is not enabled. Run: CREATE EXTENSION postgis;');
            return self::FAILURE;
        }

        $this->info('PostGIS extension verified.');

        // Import based on type
        if ($type === 'all' || $type === 'earthquake') {
            $this->importEarthquakeParameters($file, $truncate);
        }

        if ($type === 'all' || $type === 'siteclass') {
            $this->importSiteClasses($file, $truncate);
        }

        $this->newLine();
        $this->info('Spatial data import completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Import earthquake parameters from GeoJSON
     */
    protected function importEarthquakeParameters(?string $file, bool $truncate): void
    {
        $this->info('Importing earthquake parameters...');

        if ($truncate) {
            DB::table('earthquake_parameters')->truncate();
            $this->info('Truncated earthquake_parameters table.');
        }

        // If file is provided, import from file
        if ($file) {
            $this->importFromGeoJson($file, 'earthquake_parameters');
        } else {
            $this->info('No file provided. Skipped earthquake parameters import.');
            $this->comment('Usage: php artisan import:spatial-data --file=path/to/data.geojson --type=earthquake');
        }
    }

    /**
     * Import site classes from GeoJSON
     */
    protected function importSiteClasses(?string $file, bool $truncate): void
    {
        $this->info('Importing site classes...');

        if ($truncate) {
            DB::table('site_classes')->whereNotNull('geom')->update(['geom' => null]);
            $this->info('Cleared site class geometries.');
        }

        // Default site classes are already seeded via migration
        $this->info('Default site classes are already seeded. Use --file to import spatial data.');
    }

    /**
     * Import data from GeoJSON file
     */
    protected function importFromGeoJson(string $filePath, string $table): void
    {
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Reading file: {$filePath}");

        $content = file_get_contents($filePath);
        $geoJson = json_decode($content, true);

        if (!$geoJson || !isset($geoJson['type']) || $geoJson['type'] !== 'FeatureCollection') {
            $this->error('Invalid GeoJSON format. Expected FeatureCollection.');
            return;
        }

        $features = $geoJson['features'] ?? [];
        $count = count($features);
        $this->info("Found {$count} features.");

        $bar = $this->output->createProgressBar($count);
        $imported = 0;

        foreach ($features as $feature) {
            if (!isset($feature['geometry']) || !isset($feature['properties'])) {
                $bar->advance();
                continue;
            }

            $geometry = json_encode($feature['geometry']);
            $properties = $feature['properties'];

            try {
                if ($table === 'earthquake_parameters') {
                    $this->insertEarthquakeParameter($geometry, $properties);
                } elseif ($table === 'site_classes') {
                    $this->insertSiteClass($geometry, $properties);
                }
                $imported++;
            } catch (\Exception $e) {
                $this->error("Error importing feature: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully imported {$imported} features into {$table}.");
    }

    /**
     * Insert earthquake parameter record
     */
    protected function insertEarthquakeParameter(string $geometry, array $properties): void
    {
        $id = \Illuminate\Support\Str::uuid()->toString();
        $ss = $properties['ss'] ?? $properties['SS'] ?? 0.5;
        $s1 = $properties['s1'] ?? $properties['S1'] ?? 0.2;
        $gridId = $properties['grid_id'] ?? $properties['id'] ?? \Illuminate\Support\Str::random(10);
        $metadata = json_encode($properties);

        DB::insert("
            INSERT INTO earthquake_parameters (id, grid_id, ss, s1, geom, metadata, created_at, updated_at)
            VALUES (?, ?, ?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), ?, NOW(), NOW())
        ", [$id, $gridId, $ss, $s1, $geometry, $metadata]);
    }

    /**
     * Insert site class record with geometry
     */
    protected function insertSiteClass(string $geometry, array $properties): void
    {
        $siteClass = $properties['site_class'] ?? $properties['class'] ?? 'D';

        DB::update("
            UPDATE site_classes
            SET geom = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), updated_at = NOW()
            WHERE site_class = ?
        ", [$geometry, $siteClass]);
    }
}

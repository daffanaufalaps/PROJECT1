# Sistem Pendukung Keputusan Risiko Gempa Bumi Berbasis WebGIS

Aplikasi web untuk analisis risiko gempa bumi berbasis WebGIS dengan perhitungan sesuai standar **SNI 1726:2019**.

## Fitur Utama

- **Halaman Input**: Peta interaktif untuk pemilihan lokasi dengan 3 cara input koordinat:
  - Klik langsung pada peta
  - Input manual field Lintang/Bujur
  - Gunakan lokasi GPS perangkat (Geolocation API)
- **Halaman Hasil**: Tabel hasil analisis lengkap dengan narasi otomatis
- **Parameter Perhitungan**: Ss, S1, Fa, Fv, SMs, SM1, SDs, SD1, PGA, MMI, Kategori Risiko, KDS
- **Admin Dashboard**: Monitoring riwayat perhitungan dan pengelolaan data
- **Database Spasial**: PostgreSQL + PostGIS untuk pengolahan data geografis

## Tech Stack

- **Backend**: Laravel 12.x
- **Database**: PostgreSQL dengan PostGIS (via Supabase)
- **Frontend**: Blade Templates + Tailwind CSS
- **Peta**: Leaflet.js dengan OpenStreetMap
- **Standar**: SNI 1726:2019 (Perencanaan Ketahanan Gempa untuk Struktur Bangunan)

## Prasyarat

- PHP 8.4+
- Composer
- Node.js 18+ & NPM
- PostgreSQL dengan ekstensi PostGIS
- Akun Supabase (untuk database terkelola)

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd gempa-webgis
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

Salin file `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Edit `.env` dan sesuaikan konfigurasi database:

```env
APP_NAME="Sistem SPK Risiko Gempa Bumi"
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=<your-database-host>
DB_PORT=5432
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-username>
DB_PASSWORD=<your-password>
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Aktifkan Ekstensi PostGIS

Jika menggunakan Supabase, PostGIS sudah aktif secara default. Jika menggunakan PostgreSQL lokal:

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
```

### 6. Jalankan Migrasi Database

```bash
php artisan migrate
```

Migrasi akan membuat tabel berikut:
- `admins` - Data admin untuk autentikasi
- `calculation_histories` - Riwayat perhitungan
- `narration_templates` - Template narasi hasil
- `earthquake_parameters` - Data spasial parameter gempa (Ss, S1)
- `site_classes` - Data kelas situs (A, B, C, D, E)
- `fa_factors` - Tabel Fa (Site Coefficient Short Period)
- `fv_factors` - Tabel Fv (Site Coefficient 1-Second Period)

### 7. Buat Admin User

```bash
php artisan admin:create --name="Admin" --email="admin@example.com" --password="secure_password"
```

Atau jalankan seeder:

```bash
php artisan db:seed --class=AdminSeeder
# Default: admin@gempa-webgis.test / admin123 (SEGERA GANTI PASSWORD!)
```

### 8. Import Data Spasial

Untuk mengimport data parameter gempa dari GeoJSON:

```bash
php artisan import:spatial-data --file=path/to/data.geojson --type=earthquake
```

Format GeoJSON yang diharapkan:

```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Polygon",
        "coordinates": [[[lon1, lat1], [lon2, lat2], ...]]
      },
      "properties": {
        "grid_id": "GRID001",
        "ss": 0.5,
        "s1": 0.2
      }
    }
  ]
}
```

### 9. Jalankan Aplikasi

Build assets:

```bash
npm run build
```

Jalankan development server:

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

## Struktur Proyek

```
app/
├── Console/Commands/
│   ├── CreateAdmin.php          # Command untuk membuat admin
│   └── ImportSpatialData.php    # Command import data spasial
├── Http/
│   ├── Controllers/
│   │   ├── AdminController.php  # Controller admin dashboard
│   │   └── GempaController.php  # Controller utama aplikasi
│   ├── Middleware/
│   │   └── AdminMiddleware.php  # Middleware autentikasi admin
│   └── Requests/
│       └── CalculateRequest.php # Form request validasi input
├── Models/
│   ├── Admin.php
│   ├── CalculationHistory.php
│   ├── EarthquakeParameter.php
│   ├── FaFactor.php
│   ├── FvFactor.php
│   ├── NarrationTemplate.php
│   └── SiteClass.php
└── Services/
    ├── GempaCalculationService.php  # Logika perhitungan SNI 1726:2019
    └── NarrationService.php         # Pembuatan narasi hasil
database/
├── migrations/                  # Migrasi database
└── seeders/
    └── AdminSeeder.php          # Seeder admin default
resources/views/
├── admin/
│   ├── dashboard.blade.php
│   ├── history.blade.php
│   └── login.blade.php
├── gempa/
│   ├── index.blade.php         # Halaman input
│   └── result.blade.php        # Halaman hasil
└── layouts/
    └── app.blade.php           # Layout utama
```

## API Endpoints

### Perhitungan Risiko Gempa

**POST** `/api/hitung`

Request:
```json
{
  "latitude": -6.2,
  "longitude": 106.8,
  "site_class": "D"  // opsional, default: D
}
```

Response:
```json
{
  "success": true,
  "data": {
    "latitude": -6.2,
    "longitude": 106.8,
    "site_class": "D",
    "ss": 0.5,
    "s1": 0.2,
    "fa": 1.4,
    "fv": 2.0,
    "sms": 0.7,
    "sm1": 0.4,
    "sds": 0.467,
    "sd1": 0.267,
    "pga": 0.28,
    "mmi": 7.5,
    "risk_category": "Risiko Sedang",
    "kds": "D"
  },
  "narrative": "Berdasarkan analisis...",
  "mmi_description": "VII - Sangat Kuat",
  "risk_description": "Lokasi berada di zona gempa sedang..."
}
```

## Pengembangan Lebih Lanjut

### Mengisi Logika Perhitungan

File `app/Services/GempaCalculationService.php` berisi kerangka method untuk perhitungan SNI 1726:2019. Isi logika perhitungan sesuai dengan rumus yang Anda miliki:

```php
// Contoh pengisian method
public function calculatePGA(float $sds, float $ss, float $sms): float
{
    // Ganti dengan rumus PGA sesuai data Anda
    return 0.4 * $ss; // placeholder
}
```

### Menambah Template Narasi

Template narasi disimpan di tabel `narration_templates`. Gunakan placeholder `{nama_variabel}`:

```
Berdasarkan analisis titik koordinat {latitude}° LS, {longitude}° BT,
diperoleh nilai PGA sebesar {pga}g yang setara dengan skala MMI {mmi}.
```

### Menambah Data Spasial

1. Siapkan data dalam format GeoJSON/Shapefile
2. Pastikan memiliki properti `ss`, `s1`, dan `geometry`
3. Jalankan command import:

```bash
php artisan import:spatial-data --file=data/earthquake_params.geojson
```

## Deployment

### Oracle Cloud / VPS

1. Setup server dengan PHP 8.4+, Nginx, PostgreSQL
2. Install ekstensi PostGIS: `CREATE EXTENSION postgis;`
3. Clone repository dan install dependencies
4. Konfigurasi `.env` untuk production
5. Build assets: `npm run build`
6. Configure queue worker jika diperlukan
7. Setup SSL certificate

### IDCloudHost

1. Buat aplikasi baru di panel IDCloudHost
2. Set PHP version ke 8.4
3. Upload files atau gunakan Git deployment
4. Konfigurasi database PostgreSQL
5. Run migrations dan seeders via SSH

## Kontribusi

Silakan buat Pull Request untuk perbaikan atau penambahan fitur.

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## Referensi

- [SNI 1726:2019](https://www.bsn.or.id/) - Tata cara perencanaan ketahanan gempa untuk struktur bangunan
- [Laravel Documentation](https://laravel.com/docs)
- [PostGIS Documentation](https://postgis.net/documentation/)
- [Leaflet.js](https://leafletjs.com/)

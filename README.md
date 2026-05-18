# Page Builder Custom Module

Modul page builder reusable berbasis **nwidart/laravel-modules** untuk project Laravel. Dirancang agar bisa dipakai di banyak project dengan cara pull via **git submodule**.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.1+, Laravel 10+ |
| Module system | [nwidart/laravel-modules](https://nwidart.com/laravel-modules) ^10.0 |
| Frontend | React (JSX), Vite |
| Database | MySQL / MariaDB (via Laravel Eloquent) |

---

## Fitur

- **Pages** — buat halaman publik dengan block-based editor
- **Blocks** — reusable block builder (hero, text, image, video, FAQ, CTA, dll.)
- **Core Layouts** — atur layout utama halaman (template wrapper)
- **Chrome Layouts** — header & footer variants (classic, centered, minimal, dll.)
- **Content Types & Entries** — dynamic content collection (mirip CMS)
- **Media** — upload & manajemen gambar untuk page builder
- **Presets** — template halaman siap pakai
- **Library** — manajemen plugin & theme block types
- **Readiness Check** — diagnostic tool untuk memverifikasi setup modul

---

## Prasyarat Host Application

Sebelum menggunakan modul ini, pastikan project Laravel Anda memiliki:

1. **`nwidart/laravel-modules` ^10.0** terinstall:
   ```bash
   composer require nwidart/laravel-modules
   ```

2. **Autoload namespace `Modules\`** di `composer.json`:
   ```json
   "autoload": {
       "psr-4": {
           "App\\": "app/",
           "Modules\\": "Modules/"
       }
   }
   ```

3. **Helper functions** berikut tersedia di host app (biasanya di `app/helpers.php`):
   - `site_setting(string $key, mixed $default)` — membaca site setting dari database
   - `to_boolean(mixed $value): bool` — konversi value ke boolean

4. **Model** yang direferensikan controller (host-app dependency):
   - `App\Models\Forms\Form` — dipakai di page editor untuk embed form
   - `App\Models\SiteSetting` — dipakai oleh Support classes

5. **Middleware `auth`** sudah terkonfigurasi di host app.

6. **Layout Blade** berikut tersedia di host app:
   - `layouts.app` — layout admin utama
   - `layouts.page-builder-front` — layout halaman publik page builder

---

## Instalasi

### 1. Tambahkan sebagai Git Submodule

Di dalam root project Laravel Anda:

```bash
git submodule add https://github.com/samsul97/page-builder-custom.git Modules/PageBuilder
git submodule update --init --recursive
```

File `.gitmodules` akan otomatis dibuat:

```ini
[submodule "Modules/PageBuilder"]
    path = Modules/PageBuilder
    url = https://github.com/samsul97/page-builder-custom.git
    branch = main
```

### 2. Register Middleware

Di `app/Http/Kernel.php`, tambahkan di array `$middlewareAliases`:

```php
'page_builder_enabled' => \Modules\PageBuilder\Http\Middleware\EnsurePageBuilderEnabled::class,
```

### 3. Enable Module

```bash
php artisan module:enable PageBuilder
```

Atau langsung edit `modules_statuses.json` di root project:

```json
{
    "PageBuilder": true
}
```

### 4. Jalankan Migrasi

```bash
php artisan migrate
```

Modul akan membuat tabel-tabel berikut:
- `pb_pages`
- `pb_reusable_blocks`
- `pb_layouts` (chrome layouts)
- `pb_core_layouts`
- `pb_content_types`
- `pb_content_entries`
- `pb_media`

### 5. Regenerate Autoload

```bash
composer dump-autoload
```

---

## Clone Project yang Sudah Pakai Submodule

Saat ada developer baru clone project:

```bash
# Opsi 1 — clone + pull submodule sekaligus
git clone --recurse-submodules https://github.com/<org>/<project>.git

# Opsi 2 — jika sudah terlanjur clone tanpa submodule
git submodule update --init --recursive
```

---

## Konfigurasi

Modul ini punya 3 file config di `Config/`:

| File | Keterangan |
|---|---|
| `page_builder_block_types.php` | Daftar block types yang aktif (hero, text, image, dll.) |
| `page_builder_library.php` | Konfigurasi library plugin & theme |
| `page_builder_presets.php` | Daftar preset/template halaman siap pakai |

Untuk override config di host app, publish config:

```bash
php artisan vendor:publish --tag=config --provider="Modules\PageBuilder\Providers\PageBuilderServiceProvider"
```

### Toggle Page Builder On/Off

Modul bisa di-disable per-site via site setting. Di database `site_settings`, set key `page_builder_enabled` ke `false` untuk menyembunyikan semua halaman page builder dari user.

---

## Views

Views modul menggunakan namespace `pagebuilder::`.

Contoh referensi dari controller:
```php
return view('pagebuilder::pages.index', compact('pages'));
return view('pagebuilder::public.show', $data);
```

Contoh `@include` di blade:
```blade
@include('pagebuilder::pages._form')
@includeIf('pagebuilder::public.chrome.header.' . $variant)
```

Untuk override view di host app, publish views:
```bash
php artisan vendor:publish --tag=pagebuilder-module-views
```
Views akan di-copy ke `resources/views/modules/pagebuilder/`.

---

## Routes

Semua route didaftarkan otomatis oleh `RouteServiceProvider` modul.

### Public (tanpa auth)

| Method | URI | Name |
|---|---|---|
| GET | `/landing-pages/{slug}` | `page-builder.public.show` |

### Admin (memerlukan `auth` + `page_builder_enabled`)

| Prefix | Keterangan |
|---|---|
| `GET /page-builder` | Dashboard |
| `/page-builder/pages` | CRUD halaman |
| `/page-builder/blocks` | CRUD reusable blocks |
| `/page-builder/core-layouts` | CRUD core layouts |
| `/page-builder/chrome-layouts` | CRUD chrome layouts (header/footer) |
| `/page-builder/content-types` | CRUD content types |
| `/page-builder/content-types/{id}/entries` | CRUD content entries |
| `/page-builder/media` | Upload & kelola media |
| `/page-builder/presets` | Lihat & instantiate preset |
| `/page-builder/plugins-theme` | Manajemen library/plugin |
| `/page-builder/readiness` | Cek readiness modul |

Lihat daftar lengkap:
```bash
php artisan route:list --path=page-builder
```

---

## Struktur Direktori

```
Modules/PageBuilder/
├── module.json                   ← manifest nwidart
├── README.md
├── Config/
│   ├── page_builder_block_types.php
│   ├── page_builder_library.php
│   └── page_builder_presets.php
├── Database/
│   └── Migrations/               ← 6 migration files (prefix: pb_)
├── Helpers/
│   └── helpers.php               ← page_builder_enabled()
├── Http/
│   ├── Controllers/              ← 12 controller files
│   ├── Middleware/
│   │   └── EnsurePageBuilderEnabled.php
│   └── Requests/                 ← 14 form request files
├── Models/                       ← 7 Eloquent models
├── Providers/
│   ├── PageBuilderServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   ├── js/                       ← React components (PageBuilderEditor, dll.)
│   └── views/                    ← 38 blade files
├── Routes/
│   └── web.php
└── Support/
    ├── PageBuilderBlockTypeRegistry.php
    ├── PageBuilderLibraryCatalog.php
    ├── PageBuilderPresetCatalog.php
    └── PageBuilderPresetInstantiator.php
```

---

## Update Modul

### Pull versi terbaru ke semua project

```bash
# Di dalam folder project (misal: nexcity)
git submodule update --remote Modules/PageBuilder
git add Modules/PageBuilder
git commit -m "chore: update PageBuilder module to latest"
```

### Kontribusi / edit modul

```bash
# Masuk ke folder submodule
cd Modules/PageBuilder

# Edit kode, lalu commit & push di repo modul
git add .
git commit -m "feat: ..."
git push origin main

# Kembali ke parent repo, update pointer submodule
cd ../..
git add Modules/PageBuilder
git commit -m "chore: bump PageBuilder module"
```

> **Penting:** Setiap project "mengunci" ke commit tertentu dari repo modul. Update tidak otomatis — harus dilakukan manual dengan `git submodule update --remote`.

---

## Integrasi ke Project Baru

Checklist lengkap saat mau pakai modul ini di project Laravel baru:

- [ ] `nwidart/laravel-modules ^10.0` sudah diinstall
- [ ] Namespace `Modules\\` sudah di `composer.json` autoload
- [ ] `git submodule add ...` sudah dijalankan
- [ ] Middleware `page_builder_enabled` sudah di `Kernel.php`
- [ ] `php artisan module:enable PageBuilder` sudah dijalankan
- [ ] `php artisan migrate` sudah dijalankan
- [ ] Helper `site_setting()` dan `to_boolean()` tersedia di host app
- [ ] Model `App\Models\Forms\Form` dan `App\Models\SiteSetting` tersedia
- [ ] Layout `layouts.app` dan `layouts.page-builder-front` tersedia
- [ ] Jalankan `composer dump-autoload`

---

## Lisensi

Private — internal use only.

# Local Video Library

Local Video Library adalah aplikasi web untuk mengelola dan memutar koleksi video lokal. Aplikasi ini dibangun dengan Laravel dan menyediakan halaman koleksi publik serta dashboard admin untuk mengelola video dan kategori.

Video tidak diunggah atau disalin ke project. Admin memilih file dari folder komputer melalui fitur **Browse file**. Aplikasi hanya menyimpan path asli file, kemudian Laravel menyajikan file tersebut melalui endpoint streaming saat video dibuka.

## Kegunaan

- Mengelola koleksi video berdasarkan judul, kategori, dan model.
- Memutar video dari folder lokal tanpa menggandakan file.
- Menampilkan preview frame video pada kartu koleksi.
- Scrub preview dengan menggeser pointer dari kiri ke kanan pada thumbnail.
- Mengambil durasi video otomatis dari metadata file.
- Mendukung URL video langsung dan link YouTube.
- Mencari video berdasarkan judul, kategori, atau model.
- Mengelola kategori dan data video dari dashboard admin.

## Tampilan

Screenshot belum disertakan dalam repository ini. Setelah aplikasi dijalankan, halaman yang tersedia adalah:

- Koleksi publik: `/`
- Detail dan pemutar video: `/videos/{id}`
- Dashboard admin: `/admin`

## Persyaratan

- Windows dengan XAMPP (Apache dan PHP 8.2+)
- Composer
- Laravel 12
- Database SQLite atau database lain yang didukung Laravel
- Browser modern yang mendukung HTML5 video

## Instalasi di XAMPP

1. Clone repository ke folder `htdocs`:

   ```bash
   cd D:\xampp\htdocs
   git clone <URL-REPOSITORY> videohub
   cd videohub
   ```

   Jika repository sudah berada di `D:\xampp\htdocs\videohub\videohub`, langsung masuk ke folder tersebut.

2. Install dependency PHP:

   ```bash
   composer install
   ```

3. Siapkan file environment:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. Atur URL aplikasi di `.env`:

   ```env
   APP_URL=http://localhost/videohub/videohub/public
   ```

5. Untuk SQLite, buat file database jika belum ada dan pastikan `.env` berisi:

   ```env
   DB_CONNECTION=sqlite
   ```

   Di PowerShell, file dapat dibuat dengan `New-Item database\database.sqlite -ItemType File`.

6. Jalankan migration dan bersihkan cache:

   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

7. Nyalakan **Apache** dari XAMPP Control Panel, lalu buka:

   ```text
   http://localhost/videohub/videohub/public
   ```

## Cara Menambahkan Video

1. Buka `http://localhost/videohub/videohub/public/admin`.
2. Pilih atau buat kategori, lalu isi judul dan model video.
3. Pilih **Video dari folder komputer** dan klik **Browse file**.
4. Masuk ke folder video dan pilih file `.mp4`, `.webm`, `.ogg`, `.mov`, `.m4v`, atau `.avi`.
5. Durasi akan dibaca otomatis dari metadata video.
6. Klik **Simpan video**.

File tetap berada di folder asal. Contoh path yang disimpan:

```text
D:\Design\Agustus 2026\konten 4\spideman vid.mp4
```

Komputer yang menjalankan Apache/Laravel harus tetap bisa membaca path tersebut. Jika file dipindahkan, diganti nama, atau drive tidak tersedia, video tidak dapat diputar.

## URL Eksternal

Mode URL dapat digunakan untuk URL file video langsung seperti `.mp4` atau `.webm`, serta link YouTube yang didukung. URL halaman biasa dari layanan streaming belum tentu bisa diputar karena pembatasan provider, CORS, DRM, atau aturan embed.

## Pengujian

```bash
php artisan test
php artisan view:cache
```

## Catatan Keamanan

Route `/admin` pada versi ini belum dilindungi autentikasi. Tambahkan login dan authorization middleware sebelum aplikasi digunakan di jaringan publik. Fitur browser file juga sebaiknya dibatasi ke folder video tertentu pada deployment production.

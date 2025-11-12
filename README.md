# Sistem Presensi & Aktivitas Magang (SIMANG)

Sistem Presensi & Aktivitas Magang (SIMANG) adalah aplikasi berbasis web yang dirancang untuk memodernisasi dan mengelola seluruh proses kehadiran dan pelaporan kegiatan peserta magang. Aplikasi ini dibangun menggunakan PHP Native dengan fokus pada keamanan, fungsionalitas modern, dan kemudahan penggunaan.

Proyek ini merupakan hasil pengembangan ulang dan penambahan fitur signifikan dari sebuah sistem absensi sederhana, yang kini dilengkapi dengan verifikasi kehadiran canggih dan alur kerja administratif yang lengkap.

![Screenshot Dashboard](path/to/your/screenshot_dashboard.jpg)
*(Ganti `path/to/your/screenshot_dashboard.jpg` dengan link ke screenshot aplikasi Anda)*

---

## ✨ Fitur-Fitur Unggulan

Sistem ini dirancang dengan dua peran utama: **Administrator** untuk manajemen dan **Peserta Magang** sebagai pengguna harian.

### Fitur untuk Administrator
- **Dashboard Analitik:** Tampilan ringkasan data real-time, menampilkan statistik kehadiran harian (Hadir, Izin, Tidak Hadir, Belum Absen) dan grafik rekapitulasi 7 hari terakhir.
- **Manajemen Peserta (CRUD):** Mengelola data lengkap peserta magang dari berbagai jenis instansi (universitas, sekolah, dll.).
- **Pemantauan Absensi Real-time:** Melihat seluruh riwayat absensi, termasuk jam masuk/pulang, status, keterangan keterlambatan, dan bukti kehadiran.
- **Alur Kerja Persetujuan Izin:** Menerima pengajuan izin dari peserta, meninjau alasan dan file bukti, lalu menyetujui (`Approve`) atau menolak (`Reject`) pengajuan.
- **Cetak Laporan PDF:** Membuat laporan PDF profesional untuk absensi dan kegiatan per peserta dalam rentang tanggal tertentu.
- **Pengaturan Sistem Dinamis:**
    - Mengatur koordinat GPS lokasi kantor untuk validasi jarak.
    - Mengatur jendela waktu absensi (jam mulai/selesai untuk masuk dan pulang).
    - Mengedit template pesan notifikasi WhatsApp.
- **Manajemen Akun:** Mengelola akun (username & password) untuk administrator lain dan peserta.

### Fitur untuk Peserta Magang
- **Dashboard Personal:** Tampilan personal yang berisi statistik performa (total hadir, izin, terlambat), progres periode magang, dan panel aksi cepat.
- **Presensi 2 Faktor (Wajib):**
    - **Foto Selfie:** Menggunakan kamera perangkat secara langsung untuk bukti visual.
    - **Validasi Lokasi (Geolocation):** Memastikan absensi dilakukan dalam radius jarak yang valid dari lokasi kantor.
- **Absensi Masuk & Pulang:** Sistem *clock-in/clock-out* untuk mencatat durasi kerja.
- **Pengajuan Izin:** Mengirim permohonan izin dengan menyertakan alasan tertulis dan meng-upload file bukti (PDF/Gambar).
- **Laporan Kegiatan Harian:** Mengisi jurnal kegiatan harian.
- **Manajemen Profil:** Memperbarui data personal (No. Telepon, Alamat, Foto Profil) dan mengubah password secara mandiri.

### Fitur Otomatis
- **Notifikasi WhatsApp:** Mengirim notifikasi real-time ke orang tua dan guru/pembimbing saat peserta berhasil melakukan absensi, lengkap dengan detail waktu, keterangan, dan foto bukti.
- **Status "Tidak Hadir" Otomatis:** Sistem secara otomatis menandai peserta yang tidak memberikan kabar sebagai "Tidak Hadir" pada hari kerja sebelumnya.

---

## 🛠️ Tumpukan Teknologi (Technology Stack)

- **Backend:** PHP Native 8.x
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Database:** MySQL / MariaDB
- **Library & API:**
    - **Chart.js:** Untuk visualisasi data grafik di dashboard admin.
    - **FPDF:** Untuk pembuatan laporan dalam format PDF.
    - **PHPMailer (jika digunakan):** Untuk notifikasi via Email.
    - **WhatsApp Gateway API (Fonnte):** Untuk notifikasi real-time via WhatsApp.
    - **Browser APIs:** Geolocation API & MediaDevices API (getUserMedia) untuk fitur presensi.

---

## 🚀 Cara Instalasi & Menjalankan (Untuk Pengembang Selanjutnya)

Berikut adalah langkah-langkah untuk menjalankan proyek ini di lingkungan pengembangan lokal (misalnya, menggunakan XAMPP).

### Prasyarat
- Server web lokal (XAMPP, WAMP, Laragon) dengan PHP 8.0+
- Database MySQL atau MariaDB
- Browser modern (Chrome, Firefox, Edge)

### Langkah-langkah Instalasi
1.  **Clone atau Unduh Repositori**
    - Unduh file ZIP proyek ini atau clone menggunakan Git.
    - Letakkan seluruh folder proyek (misalnya, `absensi-magang`) di dalam direktori `htdocs` XAMPP Anda.

2.  **Setup Database**
    - Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    - Buat database baru, misalnya `db_magang`.
    - Pilih database yang baru dibuat, lalu klik tab **"Import"**.
    - Pilih file `db_magang.sql` yang ada di dalam folder `database/` proyek ini.
    - Klik "Go" atau "Import" untuk menjalankan migrasi. Semua tabel dan beberapa data contoh akan dibuat.

3.  **Konfigurasi Koneksi**
    - Buka file `config/database.php`.
    - Sesuaikan variabel `$host`, `$user`, `$password`, dan `$db` dengan konfigurasi database lokal Anda.
    ```php
    $host = "localhost";
    $user = "root";
    $password = "";
    $db = "db_magang";
    ```

4.  **Konfigurasi Fitur Lanjutan (Opsional)**
    - **Notifikasi WhatsApp:**
        - Daftar di layanan WA Gateway (misalnya Fonnte).
        - Buka `config/function.php`, cari fungsi `kirimNotifikasiWA()`.
        - Ganti `YOUR_FONNTE_TOKEN` dengan API Token Anda.
    - **Lokasi Absensi:**
        - Login sebagai admin, buka menu "Pengaturan".
        - Masukkan koordinat **Latitude** dan **Longitude** lokasi kantor Anda. Anda bisa mendapatkannya dengan klik kanan di Google Maps.

5.  **Akses Aplikasi**
    - Buka browser dan akses proyek melalui `http://localhost/nama-folder-proyek/` (misalnya, `http://localhost/absensi-magang/`).
    - Gunakan akun default di bawah ini untuk login:
        - **Admin:** `username: pradana`, `password: 123456`
        - **Mahasiswa:** Periksa data di `tbl_mahasiswa` dan `tbl_user` untuk akun contoh.

### Catatan Penting untuk Pengembangan
- **Akses Kamera & Lokasi:** Fitur ini memerlukan konteks aman. Browser akan mengizinkannya di `http://localhost`. Jika Anda mengakses melalui IP lokal (misal, `http://192.168.x.x`), fitur ini akan diblokir oleh browser.
- **Notifikasi WA dengan Gambar Lokal:** Untuk menguji pengiriman gambar via WhatsApp dari localhost, Anda perlu menggunakan layanan tunneling seperti **ngrok** untuk membuat `localhost` Anda bisa diakses dari internet.
- **Jika setelah dites aplikasi bermasalah lihat pada index dan pastikan style css menggunakan directory/path yang benar sesuai dengan proyek Anda**  

---

Terima kasih telah menggunakan dan berkontribusi pada proyek ini. Semoga bermanfaat!
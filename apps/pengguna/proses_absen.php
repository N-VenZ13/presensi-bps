<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

// 1. Validasi Akses Awal
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['id_mahasiswa'])) {
    header("Location: ../../index.php");
    exit();
}

// 2. Sertakan File Konfigurasi
include '../../config/database.php';
include '../../config/function.php';

// 3. Siapkan Variabel Umum
$id_mahasiswa = $_SESSION['id_mahasiswa'];
$tanggal_hari_ini = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$aksi = $_POST['aksi'];

// 4. Ambil Pengaturan dari Database
$hasil_setting_situs = mysqli_query($kon, "SELECT latitude_kantor, longitude_kantor, template_wa FROM tbl_site LIMIT 1");
$setting_situs = mysqli_fetch_assoc($hasil_setting_situs);
$hasil_setting_absen = mysqli_query($kon, "SELECT masuk_akhir, pulang_mulai FROM tbl_setting_absensi LIMIT 1");
$setting_absen = mysqli_fetch_assoc($hasil_setting_absen);

// 5. Cek Duplikasi Data Absensi untuk Hari Ini
$stmt_check = mysqli_prepare($kon, "SELECT id_absensi FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
mysqli_stmt_bind_param($stmt_check, "is", $id_mahasiswa, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$sudah_ada_data = (mysqli_num_rows($result_check) > 0);

// =======================================================
// PROSES UTAMA BERDASARKAN AKSI
// =======================================================

// --- AKSI: MASUK HADIR (DENGAN FOTO & LOKASI) ---
if ($aksi == 'masuk_hadir' && !$sudah_ada_data) {

    $latitude_pengguna = (float)$_POST['latitude'];
    $longitude_pengguna = (float)$_POST['longitude'];

    // Validasi Jarak
    $jarak = hitungJarak($setting_situs['latitude_kantor'], $setting_situs['longitude_kantor'], $latitude_pengguna, $longitude_pengguna);
    $batas_jarak = 100; // Jarak maksimal dalam meter

    if ($jarak > $batas_jarak) {
        header("Location: ../../index.php?page=absen&error=jarak_terlalu_jauh");
        exit();
    }

    // Simpan Foto Selfie dari Base64
    $data_base64 = $_POST['foto_base64'];
    list($type, $data_gambar) = explode(';', $data_base64);
    list(, $data_gambar) = explode(',', $data_gambar);
    $gambar_decode = base64_decode($data_gambar);
    $nama_file_foto = 'absen_' . $id_mahasiswa . '_' . $tanggal_hari_ini . '.jpg';
    file_put_contents('../mahasiswa/foto_absen/' . $nama_file_foto, $gambar_decode);

    // Cek Keterlambatan
    $keterangan = null;
    if ($waktu_sekarang > $setting_absen['masuk_akhir']) {
        $awal = new DateTime($setting_absen['masuk_akhir']);
        $akhir = new DateTime($waktu_sekarang);
        $diff = $akhir->diff($awal);
        $keterangan = "Terlambat " . $diff->i . " menit";
    }

    // Simpan ke Database
    $status_hadir = 1;
    $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, keterangan, latitude_absen, longitude_absen, foto_absen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisssdds", $id_mahasiswa, $status_hadir, $waktu_sekarang, $tanggal_hari_ini, $keterangan, $latitude_pengguna, $longitude_pengguna, $nama_file_foto);
    $eksekusi_berhasil = mysqli_stmt_execute($stmt);

    // Kirim Notifikasi WhatsApp jika eksekusi berhasil
    if ($eksekusi_berhasil) {
        // 1. Ambil nama mahasiswa, no telp ortu, dan no telp guru
        $stmt_mhs = mysqli_prepare($kon, "SELECT nama, no_telp_ortu, no_telp_guru FROM tbl_mahasiswa WHERE id_mahasiswa = ?");
        mysqli_stmt_bind_param($stmt_mhs, "i", $id_mahasiswa);
        mysqli_stmt_execute($stmt_mhs);
        $data_mahasiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_mhs));

        if ($data_mahasiswa) {
            // 2. Siapkan URL gambar dan isi pesan (sama untuk keduanya)
            $base_url = "https://magang.sipaten.web.id/";
            $url_gambar_publik = $base_url . "apps/mahasiswa/foto_absen/" . $nama_file_foto;

            // $pesan = "Yth. Bapak/Ibu Orang Tua/Wali/Pembimbing dari " . $data_mahasiswa['nama'] . ",\n\n";
            // $pesan .= "Ananda telah berhasil melakukan presensi masuk pada:\n";
            // $pesan .= "Tanggal: " . date('d/m/Y') . "\n";
            // $pesan .= "Waktu: " . $waktu_sekarang . "\n\n";
            // if ($keterangan) {
            //     $pesan .= "Keterangan: " . $keterangan . "\n\n";
            // }
            // $pesan .= "Terlampir adalah foto bukti kehadiran. Terima kasih.";

            // edit pesan dengan template
            // 1. Ambil template mentah dari database

            $template_mentah = $setting_situs['template_wa'];

            // 2. Siapkan data pengganti
            $data_pengganti = [
                '{nama_mahasiswa}' => $data_mahasiswa['nama'],
                '{tanggal}'        => date('d/m/Y'),
                '{waktu}'          => $waktu_sekarang,
                '{keterangan}'     => $keterangan ? $keterangan : 'Tepat Waktu' // Beri nilai default jika tidak terlambat
            ];

            // 3. Ganti semua placeholder dengan data sebenarnya
            $pesan = str_replace(array_keys($data_pengganti), array_values($data_pengganti), $template_mentah);

            // 4. Tambahkan ID Pesan acak untuk testing (opsional)
            $pesan .= "\n\n(ID Pesan: #" . rand(1000, 9999) . ")";

            // 5. Panggil fungsi pengirim (tetap sama)
            // kirimNotifikasiWA($data_mahasiswa['no_telp_ortu'], $pesan, $url_gambar_publik);

            // 3. Kirim notifikasi ke Orang Tua (jika nomor ada)
            if (!empty($data_mahasiswa['no_telp_ortu'])) {
                kirimNotifikasiWA($data_mahasiswa['no_telp_ortu'], $pesan, $url_gambar_publik);
                sleep(2); // Beri jeda 2 detik untuk menghindari rate limit API
            }

            // 4. Kirim notifikasi ke Guru (jika nomor ada)
            if (!empty($data_mahasiswa['no_telp_guru'])) {
                kirimNotifikasiWA($data_mahasiswa['no_telp_guru'], $pesan, $url_gambar_publik);
            }
        }
    }
}
// --- AKSI: MASUK IZIN ---
elseif ($aksi == 'masuk_izin' && !$sudah_ada_data) {
    $alasan = htmlspecialchars($_POST['alasan']);
    $nama_file_final = null;
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        $file_bukti = $_FILES['file_bukti'];
        $nama_file = $file_bukti['name'];
        $lokasi_tmp = $file_bukti['tmp_name'];
        $ukuran_file = $file_bukti['size'];
        $ekstensi_diizinkan = ['pdf', 'png', 'jpg', 'jpeg'];
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        if (in_array($ekstensi, $ekstensi_diizinkan) && $ukuran_file < 2000000) {
            $nama_file_final = $id_mahasiswa . '_' . $tanggal_hari_ini . '_' . $nama_file;
            move_uploaded_file($lokasi_tmp, '../mahasiswa/bukti_izin/' . $nama_file_final);
        }
    }

    $status_izin = 5;
    $stmt_absensi = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_absensi, "iis", $id_mahasiswa, $status_izin, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt_absensi);

    $stmt_alasan = mysqli_prepare($kon, "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal, file_bukti) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_alasan, "isss", $id_mahasiswa, $alasan, $tanggal_hari_ini, $nama_file_final);
    mysqli_stmt_execute($stmt_alasan);
}
// --- AKSI: PULANG ---
elseif ($aksi == 'pulang') {
    $keterangan = null;
    if ($waktu_sekarang < $setting_absen['pulang_mulai']) {
        $keterangan = "Pulang lebih awal";
    }
    $stmt_update = mysqli_prepare($kon, "UPDATE tbl_absensi SET waktu_pulang = ?, keterangan = CONCAT(IFNULL(keterangan,''), ' ', ?) WHERE id_mahasiswa = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt_update, "ssis", $waktu_sekarang, $keterangan, $id_mahasiswa, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt_update);
}

// =======================================================
// REDIRECT PENGGUNA
// =======================================================
$redirect_url = "../../index.php?page=absen";
if ($aksi == 'masuk_hadir') {
    $redirect_url .= "&absen=sukses_masuk";
} elseif ($aksi == 'masuk_izin') {
    $redirect_url .= "&absen=sukses_izin";
} elseif ($aksi == 'pulang') {
    $redirect_url .= "&absen=sukses_pulang";
}

header("Location: " . $redirect_url);
exit();

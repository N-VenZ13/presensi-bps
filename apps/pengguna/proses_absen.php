<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

// Pastikan hanya mahasiswa yang login yang bisa mengakses
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['id_mahasiswa'])) {
    header("Location: ../../index.php");
    exit();
}

include '../../config/database.php';
include '../../config/function.php'; // Pastikan fungsi hitungJarak ada di sini

$id_mahasiswa = $_SESSION['id_mahasiswa'];
$tanggal_hari_ini = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$aksi = $_POST['aksi'];

// Ambil pengaturan dari database
$hasil_setting_situs = mysqli_query($kon, "SELECT latitude_kantor, longitude_kantor FROM tbl_site LIMIT 1");
$setting_situs = mysqli_fetch_assoc($hasil_setting_situs);
$hasil_setting_absen = mysqli_query($kon, "SELECT masuk_akhir, pulang_mulai FROM tbl_setting_absensi LIMIT 1");
$setting_absen = mysqli_fetch_assoc($hasil_setting_absen);

// Cek dulu apakah sudah ada data absensi untuk hari ini, untuk mencegah duplikasi
$stmt_check = mysqli_prepare($kon, "SELECT id_absensi FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
mysqli_stmt_bind_param($stmt_check, "is", $id_mahasiswa, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$sudah_ada_data = (mysqli_num_rows($result_check) > 0);

// =======================================================
// PROSES BERDASARKAN AKSI
// =======================================================

// --- AKSI: MASUK HADIR (DENGAN FOTO & LOKASI) ---
if ($aksi == 'masuk_hadir' && !$sudah_ada_data) {

    $latitude_pengguna = (float)$_POST['latitude'];
    $longitude_pengguna = (float)$_POST['longitude'];

    // 1. Validasi Jarak
    $jarak = hitungJarak($setting_situs['latitude_kantor'], $setting_situs['longitude_kantor'], $latitude_pengguna, $longitude_pengguna);
    $batas_jarak = 50; // Jarak maksimal dalam meter (bisa disesuaikan)

    // if ($jarak > $batas_jarak) {
    //     header("Location: ../../index.php?page=absen&error=jarak_terlalu_jauh");
    //     exit();
    // }

    // =======================================================
    // TAMBAHKAN BLOK DEBUG DI SINI
    // =======================================================
    // echo "<pre>";
    // echo "<strong>--- DEBUG VALIDASI JARAK ---</strong><br>";
    // echo "Koordinat Kantor (dari DB): " . $setting_situs['latitude_kantor'] . ", " . $setting_situs['longitude_kantor'] . "<br>";
    // echo "Koordinat Anda (dari HP/Laptop): " . $latitude_pengguna . ", " . $longitude_pengguna . "<br>";
    // echo "Jarak yang Dihitung: <strong>" . round($jarak, 2) . " meter</strong><br>";
    // echo "Batas Jarak yang Diizinkan: " . $batas_jarak . " meter<br>";

    // if ($jarak > $batas_jarak) {
    //     echo "<strong style='color:red;'>HASIL: DITOLAK (Jarak > Batas)</strong>";
    // } else {
    //     echo "<strong style='color:green;'>HASIL: DITERIMA (Jarak <= Batas)</strong>";
    // }
    // echo "</pre>";
    // die("--- Debug berhenti. ---");
    // =======================================================
    // AKHIR BLOK DEBUG
    // =======================================================

    // 2. Simpan Foto Selfie dari Base64
    $data_base64 = $_POST['foto_base64'];
    list($type, $data_gambar) = explode(';', $data_base64);
    list(, $data_gambar) = explode(',', $data_gambar);
    $gambar_decode = base64_decode($data_gambar);

    // Buat nama file unik untuk foto absensi
    $nama_file_foto = 'absen_' . $id_mahasiswa . '_' . $tanggal_hari_ini . '.jpg';
    // Simpan file ke folder baru: foto_absen
    file_put_contents('../mahasiswa/foto_absen/' . $nama_file_foto, $gambar_decode);

    // 3. Cek Keterlambatan
    $keterangan = null;
    if ($waktu_sekarang > $setting_absen['masuk_akhir']) {
        $awal = new DateTime($setting_absen['masuk_akhir']);
        $akhir = new DateTime($waktu_sekarang);
        $diff = $akhir->diff($awal);
        $keterangan = "Terlambat " . $diff->i . " menit";
    }

    // --- BAGIAN DEBUGGING QUERY ---
    // echo "<pre style='background: #111; color: #eee; padding: 10px;'>";
    // echo "<strong>--- DEBUG EKSEKUSI QUERY INSERT ---</strong><br>";

    $status_hadir = 1;
    $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, keterangan, latitude_absen, longitude_absen, foto_absen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisssdds", $id_mahasiswa, $status_hadir, $waktu_sekarang, $tanggal_hari_ini, $keterangan, $latitude_pengguna, $longitude_pengguna, $nama_file_foto);
    mysqli_stmt_execute($stmt);

    // if ($stmt === false) {
    //     die("GAGAL MEMPERSIAPKAN QUERY: " . mysqli_error($kon));
    // }



    // Coba eksekusi dan cek hasilnya
    // if (mysqli_stmt_execute($stmt)) {
    //     echo "<strong>SUKSES:</strong> Query INSERT berhasil dieksekusi.<br>";
    //     echo "Data seharusnya sudah masuk ke database.";
    // } else {
    //     echo "<strong>GAGAL:</strong> Query INSERT GAGAL dieksekusi.<br>";
    //     echo "<strong>Pesan Error dari Database:</strong> " . mysqli_stmt_error($stmt);
    // }

    // echo "</pre>";
    // die("--- Debug berhenti. Proses redirect dibatalkan sementara. ---");
    // --- AKHIR BAGIAN DEBUGGING ---

    // 4. Simpan ke Database
    // $status_hadir = 1;
    // $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, keterangan, latitude_absen, longitude_absen, foto_absen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    // mysqli_stmt_bind_param($stmt, "iisssdds", $id_mahasiswa, $status_hadir, $waktu_sekarang, $tanggal_hari_ini, $keterangan, $latitude_pengguna, $longitude_pengguna, $nama_file_foto);
    // mysqli_stmt_execute($stmt);
}
// --- AKSI: MASUK IZIN (DENGAN ALASAN & BUKTI) ---
elseif ($aksi == 'masuk_izin' && !$sudah_ada_data) {

    $alasan = htmlspecialchars($_POST['alasan']);
    $nama_file_final = null;

    // Logika upload file bukti
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        // ... (Logika upload file yang sudah kita buat sebelumnya)
    }

    // Simpan status 5 = Menunggu Persetujuan
    $status_izin = 5;
    $stmt_absensi = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_absensi, "iis", $id_mahasiswa, $status_izin, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt_absensi);

    // Simpan alasan dan nama file bukti
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
    $stmt = mysqli_prepare($kon, "UPDATE tbl_absensi SET waktu_pulang = ?, keterangan = CONCAT(IFNULL(keterangan,''), ' ', ?) WHERE id_mahasiswa = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt, "ssis", $waktu_sekarang, $keterangan, $id_mahasiswa, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt);
}

// Setelah memproses, kembalikan pengguna ke halaman absensi
// header("Location: ../../index.php?page=absen");

$redirect_url = "../../index.php?page=absen";

// Tambahkan parameter notifikasi berdasarkan aksi
if ($aksi == 'masuk_hadir') {
    $redirect_url .= "&absen=sukses_masuk";
} elseif ($aksi == 'masuk_izin') {
    $redirect_url .= "&absen=sukses_izin";
} elseif ($aksi == 'pulang') {
    $redirect_url .= "&absen=sukses_pulang";
}

header("Location: " . $redirect_url);
exit();

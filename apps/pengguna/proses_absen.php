<?php

date_default_timezone_set('Asia/Jakarta');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_mahasiswa'])) {

    include '../../config/database.php';

    $id_mahasiswa = $_SESSION['id_mahasiswa'];
    $tanggal_hari_ini = date("Y-m-d");
    $waktu_sekarang = date("H:i:s");
    $aksi = $_POST['aksi'];

    // Ambil pengaturan waktu
    $hasil_setting = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
    $setting = mysqli_fetch_assoc($hasil_setting);

    if ($aksi == 'masuk') {
        $status = $_POST['status'];
        $keterangan = null;
        $alasan = htmlspecialchars($_POST['alasan']);

        // Cek dulu apakah sudah ada data untuk mencegah duplikasi
        $stmt_check = mysqli_prepare($kon, "SELECT id_absensi FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
        mysqli_stmt_bind_param($stmt_check, "is", $id_mahasiswa, $tanggal_hari_ini);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) == 0) {

            if ($status == 1) { // Jika Hadir
                if ($waktu_sekarang > $setting['masuk_akhir']) {
                    $awal = new DateTime($setting['masuk_akhir']);
                    $akhir = new DateTime($waktu_sekarang);
                    $diff = $akhir->diff($awal);
                    $keterangan = "Terlambat " . $diff->i . " menit " . $diff->s . " detik";
                }
                // Simpan ke tbl_absensi
                $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, keterangan) VALUES (?, 1, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "isss", $id_mahasiswa, $waktu_sekarang, $tanggal_hari_ini, $keterangan);
                mysqli_stmt_execute($stmt);
            } elseif ($status == 2 || $status == 3) { // [DIUBAH] Jika Izin atau Tidak Hadir
                // Simpan ke tbl_absensi (tanpa waktu)
                $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iss", $id_mahasiswa, $status, $tanggal_hari_ini);
                mysqli_stmt_execute($stmt);

                // Jika ada alasan, simpan ke tbl_alasan
                if (!empty($alasan)) {
                    $stmt_alasan = mysqli_prepare($kon, "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_alasan, "iss", $id_mahasiswa, $alasan, $tanggal_hari_ini);
                    mysqli_stmt_execute($stmt_alasan);
                }
            }
        }
    } elseif ($aksi == 'pulang') {
        $keterangan = null;
        if ($waktu_sekarang < $setting['pulang_mulai']) {
            $keterangan = "Pulang lebih awal";
        }
        $stmt = mysqli_prepare($kon, "UPDATE tbl_absensi SET waktu_pulang = ?, keterangan = CONCAT(IFNULL(keterangan,''), ' ', ?) WHERE id_mahasiswa = ? AND tanggal = ?");
        mysqli_stmt_bind_param($stmt, "ssis", $waktu_sekarang, $keterangan, $id_mahasiswa, $tanggal_hari_ini);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../../index.php?page=absen");
    exit();
}

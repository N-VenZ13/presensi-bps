<?php
session_start();
include '../../config/database.php';

// Pastikan hanya admin yang bisa mengakses
if (isset($_POST['id_absensi']) && isset($_SESSION['level']) && $_SESSION['level'] == 'Admin') {

    $id_absensi = (int)$_POST['id_absensi'];
    $aksi = $_POST['aksi']; // 'setujui' atau 'tolak'

    $status_baru = 0;
    if ($aksi == 'setujui') {
        $status_baru = 2; // Kode untuk 'Izin Disetujui'
    } elseif ($aksi == 'tolak') {
        $status_baru = 3; // Kode untuk 'Izin Ditolak'
    }

    if ($status_baru != 0) {
        $stmt = mysqli_prepare($kon, "UPDATE tbl_absensi SET status = ? WHERE id_absensi = ?");
        mysqli_stmt_bind_param($stmt, "ii", $status_baru, $id_absensi);

        if (mysqli_stmt_execute($stmt)) {
            // Berhasil
            echo "sukses";
        } else {
            // Gagal
            echo "gagal";
        }
    } else {
        echo "aksi_tidak_valid";
    }
} else {
    echo "akses_ditolak";
}

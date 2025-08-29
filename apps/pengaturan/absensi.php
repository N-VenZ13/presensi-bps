<?php
session_start();
include '../../config/database.php';

if (isset($_POST['ubah_absen'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    $id_waktu = $_POST['id_waktu'];
    $mulai_absen = $_POST['mulai_absen'];
    $akhir_absen = $_POST['akhir_absen'];

    // [PERBAIKAN KEAMANAN]
    $sql = "UPDATE tbl_setting_absensi SET mulai_absen=?, akhir_absen=? WHERE id_waktu=?";
    $stmt = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $mulai_absen, $akhir_absen, $id_waktu);

    if (mysqli_stmt_execute($stmt)) {
        header("Location:../../index.php?page=pengaturan&absen=berhasil");
    } else {
        header("Location:../../index.php?page=pengaturan&absen=gagal");
    }
    exit();
}
?>
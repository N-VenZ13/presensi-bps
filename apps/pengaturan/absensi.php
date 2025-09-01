<?php
session_start();
include '../../config/database.php';

if (isset($_POST['ubah_absen'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    $id_waktu = $_POST['id_waktu'];
    $masuk_mulai = $_POST['masuk_mulai'];
    $masuk_akhir = $_POST['masuk_akhir'];
    $pulang_mulai = $_POST['pulang_mulai'];
    $pulang_akhir = $_POST['pulang_akhir'];

    // [PERBAIKAN] Query UPDATE dengan nama kolom yang baru dan prepared statement
    $sql = "UPDATE tbl_setting_absensi SET masuk_mulai=?, masuk_akhir=?, pulang_mulai=?, pulang_akhir=? WHERE id_waktu=?";
    $stmt = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $masuk_mulai, $masuk_akhir, $pulang_mulai, $pulang_akhir, $id_waktu);

    if (mysqli_stmt_execute($stmt)) {
        header("Location:../../index.php?page=pengaturan&absen=berhasil");
    } else {
        header("Location:../../index.php?page=pengaturan&absen=gagal");
    }
    exit();
}

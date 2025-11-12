<?php
session_start();
// Pastikan hanya admin yang bisa mengakses
if (isset($_SESSION['level']) && $_SESSION['level'] == 'Admin') {

    include '../../config/database.php';

    // Ambil ID dari URL
    $id_mahasiswa = (int)$_GET["id_mahasiswa"];
    $kode_mahasiswa = $_GET["kode_mahasiswa"];

    // Memulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Hapus dari tbl_mahasiswa
    $stmt1 = mysqli_prepare($kon, "DELETE FROM tbl_mahasiswa WHERE id_mahasiswa=?");
    mysqli_stmt_bind_param($stmt1, "i", $id_mahasiswa);
    $hapus_mahasiswa = mysqli_stmt_execute($stmt1);

    // [PERBAIKAN] Hapus juga dari tbl_user
    $stmt2 = mysqli_prepare($kon, "DELETE FROM tbl_user WHERE kode_pengguna=?");
    mysqli_stmt_bind_param($stmt2, "s", $kode_mahasiswa);
    $hapus_user = mysqli_stmt_execute($stmt2);

    // Finalisasi transaksi
    if ($hapus_mahasiswa && $hapus_user) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=mahasiswa&hapus=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=mahasiswa&hapus=gagal");
    }

} else {
    // Redirect jika diakses tanpa hak
    header("Location:../../index.php?page=mahasiswa&hapus=gagal");
}
?>
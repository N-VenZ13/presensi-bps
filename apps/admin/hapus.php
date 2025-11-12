<?php
session_start();
if (isset($_SESSION['level']) && $_SESSION['level'] == 'Admin') {

    include '../../config/database.php';
    
    $id_admin = (int)$_GET["id_admin"];
    $kode_admin = $_GET["kode_admin"];

    mysqli_query($kon, "START TRANSACTION");

    // Hapus dari tbl_admin
    $stmt1 = mysqli_prepare($kon, "DELETE FROM tbl_admin WHERE id_admin=?");
    mysqli_stmt_bind_param($stmt1, "i", $id_admin);
    $hapus_admin = mysqli_stmt_execute($stmt1);

    // [PERBAIKAN] Hapus juga dari tbl_user
    $stmt2 = mysqli_prepare($kon, "DELETE FROM tbl_user WHERE kode_pengguna=?");
    mysqli_stmt_bind_param($stmt2, "s", $kode_admin);
    $hapus_user = mysqli_stmt_execute($stmt2);

    if ($hapus_admin && $hapus_user) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=admin&hapus=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=admin&hapus=gagal");
    }

} else {
    header("Location:../../index.php?page=admin&hapus=gagal");
}
?>
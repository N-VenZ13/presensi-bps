<?php
session_start();
if (isset($_POST['tambah_admin'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    include '../../config/database.php';

    mysqli_query($kon, "START TRANSACTION");

    $nama = htmlspecialchars($_POST["nama"]);
    $nip = htmlspecialchars($_POST["nip"]);
    $email = htmlspecialchars($_POST["email"]);

    $query_id = mysqli_query($kon, "SELECT MAX(id_admin) AS id_terbesar FROM tbl_admin");
    $data_id = mysqli_fetch_array($query_id);
    $id_baru = $data_id['id_terbesar'] + 1;
    $kode_admin = "A" . sprintf("%03s", $id_baru);

    // [PERBAIKAN KEAMANAN]
    $level_admin = "Admin";
    $stmt_user = mysqli_prepare($kon, "INSERT INTO tbl_user (kode_pengguna, level) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_user, "ss", $kode_admin, $level_admin);
    $simpan_pengguna = mysqli_stmt_execute($stmt_user);

    // [PERBAIKAN KEAMANAN]
    $stmt_admin = mysqli_prepare($kon, "INSERT INTO tbl_admin (kode_admin, nama, nip, email) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_admin, "ssss", $kode_admin, $nama, $nip, $email);
    $simpan_admin = mysqli_stmt_execute($stmt_admin);

    if ($simpan_pengguna && $simpan_admin) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=admin&add=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=admin&add=gagal");
    }
    exit();
}
?>

<form action="apps/admin/tambah.php" method="post">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nomor Induk Pegawai (NIP)</label>
            <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP" required>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Masukkan Email" required>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="tambah_admin" class="btn btn-primary me-2"><i class="bi bi-check-lg"></i> Daftar</button>
        <button type="reset" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
    </div>
</form>
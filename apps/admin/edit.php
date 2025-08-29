<?php
session_start();
include '../../config/database.php';

if (isset($_POST['edit_admin'])) {
    
    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    mysqli_query($kon, "START TRANSACTION");

    $id_admin = $_POST["id_admin"];
    $nama = htmlspecialchars($_POST["nama"]);
    $nip = htmlspecialchars($_POST["nip"]);
    $email = htmlspecialchars($_POST["email"]);

    // [PERBAIKAN KEAMANAN]
    $stmt = mysqli_prepare($kon, "UPDATE tbl_admin SET nama=?, nip=?, email=? WHERE id_admin=?");
    mysqli_stmt_bind_param($stmt, "sssi", $nama, $nip, $email, $id_admin);
    $edit_admin = mysqli_stmt_execute($stmt);

    if ($edit_admin) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=admin&edit=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=admin&edit=gagal");
    }
    exit();
}

// [PERBAIKAN KEAMANAN]
$id_admin = $_POST["id_admin"];
$stmt_select = mysqli_prepare($kon, "SELECT * FROM tbl_admin WHERE id_admin = ?");
mysqli_stmt_bind_param($stmt_select, "i", $id_admin);
mysqli_stmt_execute($stmt_select);
$hasil = mysqli_stmt_get_result($stmt_select);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/admin/edit.php" method="post">
    <input type="hidden" name="id_admin" value="<?php echo $data['id_admin']; ?>">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nomor Induk Pegawai (NIP)</label>
            <input type="text" name="nip" class="form-control" value="<?php echo htmlspecialchars($data['nip']); ?>" required>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>" required>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="edit_admin" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
    </div>
</form>
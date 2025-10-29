<?php
session_start();
include '../../config/database.php';

if (isset($_POST['ubah_aplikasi'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    $id = $_POST['id'];
    $nama_instansi = htmlspecialchars($_POST['nama_instansi']);
    $pimpinan = htmlspecialchars($_POST['pimpinan']);
    $pembimbing = htmlspecialchars($_POST['pembimbing']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_telp = htmlspecialchars($_POST['no_telp']);
    $website = htmlspecialchars($_POST['website']);
    $latitude_kantor = $_POST['latitude_kantor'];
    $longitude_kantor = $_POST['longitude_kantor'];
    $template_wa = $_POST['template_wa'];
    $logo_sebelumnya = $_POST['logo_sebelumnya'];
    $logo_final = $logo_sebelumnya;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg', 'gif'];
        $nama_logo = $_FILES['logo']['name'];
        $x = explode('.', $nama_logo);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['logo']['tmp_name'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $logo_final = time() . '_' . $nama_logo;
            if (move_uploaded_file($file_tmp, 'logo/' . $logo_final)) {
                if (file_exists('logo/' . $logo_sebelumnya)) {
                    unlink('logo/' . $logo_sebelumnya);
                }
            } else {
                $logo_final = $logo_sebelumnya;
            }
        }
    }

    // [PERBAIKAN KEAMANAN]
    // $sql = "UPDATE tbl_site SET nama_instansi=?, pimpinan=?, pembimbing=?, alamat=?, no_telp=?, website=?, logo=? WHERE id_site=?";
    // $stmt = mysqli_prepare($kon, $sql);
    // mysqli_stmt_bind_param($stmt, "sssssssi", $nama_instansi, $pimpinan, $pembimbing, $alamat, $no_telp, $website, $logo_final, $id);
    $sql = "UPDATE tbl_site SET nama_instansi=?, pimpinan=?, pembimbing=?, alamat=?, no_telp=?, website=?, logo=?, latitude_kantor=?, longitude_kantor=?, template_wa=? WHERE id_site=?";
    $stmt = mysqli_prepare($kon, $sql);
    // Perhatikan 'dd' ditambahkan untuk tipe data decimal dan 2 variabel baru
    mysqli_stmt_bind_param($stmt, "ssssssssssi", $nama_instansi, $pimpinan, $pembimbing, $alamat, $no_telp, $website, $logo_final, $latitude_kantor, $longitude_kantor, $template_wa, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location:../../index.php?page=pengaturan&edit=berhasil");
    } else {
        header("Location:../../index.php?page=pengaturan&edit=gagal");
    }
    exit();
}

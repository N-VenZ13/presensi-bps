<?php
session_start();
include '../../config/database.php';

// Pastikan hanya mahasiswa yang login yang bisa memproses
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'Mahasiswa') {
    die("Akses ditolak.");
}

// =======================================================
// PROSES SIMPAN PROFIL (NO. TELP & ALAMAT)
// =======================================================
if (isset($_POST['simpan_profil'])) {
    $id_mahasiswa = $_POST['id_mahasiswa'];
    $no_telp = htmlspecialchars($_POST['no_telp']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $stmt = mysqli_prepare($kon, "UPDATE tbl_mahasiswa SET no_telp = ?, alamat = ? WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $no_telp, $alamat, $id_mahasiswa);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../../index.php?page=profil&edit_profil=berhasil");
    } else {
        header("Location: ../../index.php?page=profil&edit_profil=gagal");
    }
    exit();
}


// =======================================================
// PROSES SIMPAN FOTO PROFIL
// =======================================================
if (isset($_POST['simpan_foto'])) {
    $id_mahasiswa = $_POST['id_mahasiswa'];
    $foto_saat_ini = $_POST['foto_saat_ini'];
    $kode_pengguna = $_SESSION['kode_pengguna'];
    $foto_final = $foto_saat_ini;

    if (isset($_FILES['foto_baru']) && $_FILES['foto_baru']['error'] == 0) {
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg', 'gif'];
        $nama_foto = $_FILES['foto_baru']['name'];
        $x = explode('.', $nama_foto);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['foto_baru']['tmp_name'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $foto_final = $kode_pengguna . '_profil.' . $ekstensi;
            if (move_uploaded_file($file_tmp, '../mahasiswa/foto/' . $foto_final)) {
                if ($foto_saat_ini != 'foto_default.png' && file_exists('../mahasiswa/foto/' . $foto_saat_ini)) {
                    unlink('../mahasiswa/foto/' . $foto_saat_ini);
                }
            } else {
                $foto_final = $foto_saat_ini;
            }
        }
    }

    $stmt = mysqli_prepare($kon, "UPDATE tbl_mahasiswa SET foto = ? WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt, "si", $foto_final, $id_mahasiswa);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session foto agar langsung berubah di sidebar
        $_SESSION['foto'] = $foto_final;
        header("Location: ../../index.php?page=profil&edit_foto=berhasil");
    } else {
        header("Location: ../../index.php?page=profil&edit_foto=gagal");
    }
    exit();
}


// =======================================================
// PROSES SIMPAN PASSWORD BARU
// =======================================================
if (isset($_POST['simpan_password'])) {
    $kode_pengguna = $_POST['kode_pengguna'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi password baru
    if ($password_baru != $konfirmasi_password) {
        header("Location: ../../index.php?page=profil&ubah_password=gagal");
        exit();
    }

    // Cek password lama
    $stmt_cek = mysqli_prepare($kon, "SELECT password FROM tbl_user WHERE kode_pengguna = ?");
    mysqli_stmt_bind_param($stmt_cek, "s", $kode_pengguna);
    mysqli_stmt_execute($stmt_cek);
    $result = mysqli_stmt_get_result($stmt_cek);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password_lama, $user['password'])) {
        // Jika password lama benar, hash password baru dan update
        $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        
        $stmt_update = mysqli_prepare($kon, "UPDATE tbl_user SET password = ? WHERE kode_pengguna = ?");
        mysqli_stmt_bind_param($stmt_update, "ss", $hash_password_baru, $kode_pengguna);
        
        if(mysqli_stmt_execute($stmt_update)) {
            header("Location: ../../index.php?page=profil&ubah_password=berhasil");
        } else {
            header("Location: ../../index.php?page=profil&ubah_password=gagal");
        }
    } else {
        // Jika password lama salah
        header("Location: ../../index.php?page=profil&ubah_password=gagal");
    }
    exit();
}

// Redirect jika diakses langsung
header("Location: ../../index.php");
exit();
?>
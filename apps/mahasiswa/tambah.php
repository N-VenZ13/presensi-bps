<?php
session_start();
if (isset($_POST['tambah_mahasiswa'])) {

    // Keluar jika tidak ada session admin
    if ($_SESSION['level'] != 'Admin') {
        die("Akses ditolak.");
    }

    include '../../config/database.php';

    // Memulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // [DIUBAH] Mengambil data dengan pengamanan dasar
    $nama = htmlspecialchars($_POST["nama"]);
    $nama_instansi_asal = htmlspecialchars($_POST["nama_instansi_asal"]);
    $jurusan = htmlspecialchars($_POST["jurusan"]);
    $nim = htmlspecialchars($_POST["nim"]);
    $mulai_magang = $_POST["mulai_magang"];
    $akhir_magang = $_POST["akhir_magang"];
    $no_telp = htmlspecialchars($_POST["no_telp"]);
    $alamat = htmlspecialchars($_POST["alamat"]);

    // [PERBAIKAN KEAMANAN] Generate kode_mahasiswa dengan lebih aman
    $query_id = mysqli_query($kon, "SELECT MAX(id_mahasiswa) as id_terbesar FROM tbl_mahasiswa");
    $data_id = mysqli_fetch_array($query_id);
    $id_baru = $data_id['id_terbesar'] + 1;
    $kode_mahasiswa = "M" . sprintf("%03s", $id_baru);

    // [PERBAIKAN KEAMANAN] Insert ke tbl_user menggunakan prepared statement
    $level_mahasiswa = "Mahasiswa";
    $stmt_user = mysqli_prepare($kon, "INSERT INTO tbl_user (kode_pengguna, level) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_user, "ss", $kode_mahasiswa, $level_mahasiswa);
    $simpan_pengguna = mysqli_stmt_execute($stmt_user);

    // Proses Upload Foto
    $foto_final = "foto_default.png"; // Default foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'gif');
        $nama_foto = $_FILES['foto']['name'];
        $x = explode('.', $nama_foto);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['foto']['tmp_name'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            // Buat nama file unik untuk menghindari duplikasi
            $foto_final = $kode_mahasiswa . '_' . time() . '.' . $ekstensi;
            move_uploaded_file($file_tmp, 'foto/' . $foto_final);
        }
    }

    // [PERBAIKAN KEAMANAN] Insert ke tbl_mahasiswa menggunakan prepared statement
    $sql_mahasiswa = "INSERT INTO tbl_mahasiswa (kode_mahasiswa, nama, nama_instansi_asal, jurusan, nim, mulai_magang, akhir_magang, alamat, no_telp, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_mahasiswa = mysqli_prepare($kon, $sql_mahasiswa);
    mysqli_stmt_bind_param($stmt_mahasiswa, "ssssssssss", $kode_mahasiswa, $nama, $nama_instansi_asal, $jurusan, $nim, $mulai_magang, $akhir_magang, $alamat, $no_telp, $foto_final);
    $simpan_mahasiswa = mysqli_stmt_execute($stmt_mahasiswa);

    // Finalisasi Transaksi
    if ($simpan_pengguna && $simpan_mahasiswa) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=mahasiswa&add=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=mahasiswa&add=gagal");
    }
    exit();
}
?>

<!-- ======================================================= -->
<!-- BAGIAN TAMPILAN FORM (YANG MUNCUL DI MODAL) -->
<!-- ======================================================= -->
<form action="apps/mahasiswa/tambah.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan Nama Lengkap" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="nim" class="form-label">Nomor Induk</label>
            <input type="text" name="nim" id="nim" class="form-control" placeholder="Masukkan Nomor Induk" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="nama_instansi_asal" class="form-label">Asal Instansi</label>
            <input type="text" name="nama_instansi_asal" id="nama_instansi_asal" class="form-control" placeholder="Masukkan Nama Instansi Asal" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="jurusan" class="form-label">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" placeholder="Masukkan Nama Jurusan" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="mulai_magang" class="form-label">Mulai Magang</label>
            <input type="date" name="mulai_magang" id="mulai_magang" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="akhir_magang" class="form-label">Akhir Magang</label>
            <input type="date" name="akhir_magang" id="akhir_magang" class="form-control" required>
        </div>
        <div class="col-md-12 mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" id="no_telp" class="form-control" placeholder="Masukkan Nomor Telepon Aktif" required>
        </div>
        <div class="col-md-12 mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" id="alamat" rows="3" placeholder="Masukkan Alamat Lengkap"></textarea>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Foto Profil</label>
            <!-- [DIUBAH] Input file kustom dengan gaya Bootstrap 5 -->
            <div class="input-group">
                <input type="text" class="form-control" disabled placeholder="Pilih foto..." id="file">
                <button type="button" id="pilih_foto" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Pilih File</button>
            </div>
            <input type="file" name="foto" class="file d-none"> <!-- d-none untuk menyembunyikan -->
            <img src="source/img/size.png" id="preview" class="img-thumbnail mt-2" style="max-height: 150px;">
            <div class="form-text">Ukuran file maksimal 2MB. Format: JPG, PNG, GIF.</div>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="tambah_mahasiswa" class="btn btn-primary me-2">
            <i class="bi bi-check-lg"></i> Daftar
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>
    </div>
</form>

<!-- [TETAP] Fungsionalitas input file kustom Anda. Tidak perlu diubah. -->
<script>
    $(document).on("click", "#pilih_foto", function() {
        var file = $(this).closest('.col-md-12').find(".file");
        file.trigger("click");
    });

    $('input[type="file"]').change(function(e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>
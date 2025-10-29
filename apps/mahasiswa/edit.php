<?php
session_start();
include '../../config/database.php';

// =======================================================
// BAGIAN 1: PROSES UPDATE DATA SETELAH FORM DISUBMIT
// =======================================================
if (isset($_POST['edit_mahasiswa'])) {

    // Keluar jika tidak ada session admin
    if ($_SESSION['level'] != 'Admin') {
        die("Akses ditolak.");
    }

    // Memulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Mengambil data dengan pengamanan dasar
    $id_mahasiswa = $_POST["id_mahasiswa"];
    $nama = htmlspecialchars($_POST["nama"]);
    $nama_instansi_asal = htmlspecialchars($_POST["nama_instansi_asal"]);
    $jurusan = htmlspecialchars($_POST["jurusan"]);
    $nim = htmlspecialchars($_POST["nim"]);
    $mulai_magang = $_POST["mulai_magang"];
    $akhir_magang = $_POST["akhir_magang"];
    $no_telp = htmlspecialchars($_POST["no_telp"]);
    $alamat = htmlspecialchars($_POST["alamat"]);
    $foto_saat_ini = $_POST['foto_saat_ini'];
    $foto_final = $foto_saat_ini; // Set foto final ke foto saat ini sebagai default
    $no_telp_ortu = htmlspecialchars($_POST["no_telp_ortu"]);
    $no_telp_guru = htmlspecialchars($_POST["no_telp_guru"]);

    // Proses upload foto baru jika ada
    if (isset($_FILES['foto_baru']) && $_FILES['foto_baru']['error'] == 0) {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'gif');
        $nama_foto = $_FILES['foto_baru']['name'];
        $x = explode('.', $nama_foto);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['foto_baru']['tmp_name'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            // Ambil kode mahasiswa untuk nama file unik
            $stmt_kode = mysqli_prepare($kon, "SELECT kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa=?");
            mysqli_stmt_bind_param($stmt_kode, "i", $id_mahasiswa);
            mysqli_stmt_execute($stmt_kode);
            $result_kode = mysqli_stmt_get_result($stmt_kode);
            $data_kode = mysqli_fetch_assoc($result_kode);
            $kode_mahasiswa = $data_kode['kode_mahasiswa'];

            // Buat nama file unik
            $foto_final = $kode_mahasiswa . '_' . time() . '.' . $ekstensi;

            // Pindahkan file dan hapus foto lama
            if (move_uploaded_file($file_tmp, 'foto/' . $foto_final)) {
                if ($foto_saat_ini != 'foto_default.png' && file_exists('foto/' . $foto_saat_ini)) {
                    unlink('foto/' . $foto_saat_ini);
                }
            } else {
                $foto_final = $foto_saat_ini; // Jika upload gagal, kembalikan ke foto lama
            }
        }
    }

    // [PERBAIKAN KEAMANAN] Update tbl_mahasiswa menggunakan prepared statement
    // $sql_update = "UPDATE tbl_mahasiswa SET nama=?, nama_instansi_asal=?, jurusan=?, nim=?, mulai_magang=?, akhir_magang=?, alamat=?, no_telp=?, foto=? WHERE id_mahasiswa=?";
    // $stmt_update = mysqli_prepare($kon, $sql_update);
    // mysqli_stmt_bind_param($stmt_update, "sssssssssi", $nama, $nama_instansi_asal, $jurusan, $nim, $mulai_magang, $akhir_magang, $alamat, $no_telp, $foto_final, $id_mahasiswa);
    // $edit_mahasiswa = mysqli_stmt_execute($stmt_update);
    // baru untuk whatsapp
    // $sql_update = "UPDATE tbl_mahasiswa SET nama=?, nama_instansi_asal=?, jurusan=?, nim=?, mulai_magang=?, akhir_magang=?, alamat=?, no_telp=?, no_telp_ortu=?, no_telp_guru=?, foto=? WHERE id_mahasiswa=?";
    // $stmt_update = mysqli_prepare($kon, $sql_update);
    // // Perhatikan ada 's' tambahan dan variabel $no_telp_ortu
    // mysqli_stmt_bind_param($stmt_update, "sssssssssssi", $nama, $nama_instansi_asal, $jurusan, $nim, $mulai_magang, $akhir_magang, $alamat, $no_telp, $no_telp_ortu, $no_telp_guru, $foto_final, $id_mahasiswa);
    // $edit_mahasiswa = mysqli_stmt_execute($stmt_update);

    $sql_update = "UPDATE tbl_mahasiswa SET nama=?, nama_instansi_asal=?, jurusan=?, nim=?, mulai_magang=?, akhir_magang=?, alamat=?, no_telp=?, no_telp_ortu=?, no_telp_guru=?, foto=? WHERE id_mahasiswa=?";
    $stmt_update = mysqli_prepare($kon, $sql_update);
    // Tipe data diubah menjadi "sssssssssssi" (12 parameter)
    mysqli_stmt_bind_param($stmt_update, "sssssssssssi", $nama, $nama_instansi_asal, $jurusan, $nim, $mulai_magang, $akhir_magang, $alamat, $no_telp, $no_telp_ortu, $no_telp_guru, $foto_final, $id_mahasiswa);
    $edit_mahasiswa = mysqli_stmt_execute($stmt_update);

    // Finalisasi Transaksi
    if ($edit_mahasiswa) {
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=mahasiswa&edit=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=mahasiswa&edit=gagal");
    }
    exit();
}


// =======================================================
// BAGIAN 2: MENGAMBIL DATA UNTUK DITAMPILKAN DI FORM
// =======================================================
$id_mahasiswa = $_POST["id_mahasiswa"];
// [PERBAIKAN KEAMANAN] Menggunakan prepared statement untuk mengambil data
$stmt_select = mysqli_prepare($kon, "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_select, "i", $id_mahasiswa);
mysqli_stmt_execute($stmt_select);
$hasil = mysqli_stmt_get_result($stmt_select);
$data = mysqli_fetch_array($hasil);
?>

<!-- ======================================================= -->
<!-- BAGIAN 3: TAMPILAN FORM (YANG MUNCUL DI MODAL) -->
<!-- ======================================================= -->
<form action="apps/mahasiswa/edit.php" method="post" enctype="multipart/form-data">
    <!-- Input tersembunyi untuk ID dan foto saat ini -->
    <input type="hidden" name="id_mahasiswa" value="<?php echo $data['id_mahasiswa']; ?>">
    <input type="hidden" name="foto_saat_ini" value="<?php echo $data['foto']; ?>">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="nim" class="form-label">Nomor Induk Mahasiswa (NIM)</label>
            <input type="text" name="nim" id="nim" class="form-control" value="<?php echo htmlspecialchars($data['nim']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="nama_instansi_asal" class="form-label">Asal Instansi</label>
            <input type="text" name="nama_instansi_asal" id="nama_instansi_asal" class="form-control" value="<?php echo htmlspecialchars($data['nama_instansi_asal']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="jurusan" class="form-label">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" value="<?php echo htmlspecialchars($data['jurusan']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="mulai_magang" class="form-label">Mulai Magang</label>
            <input type="date" name="mulai_magang" id="mulai_magang" class="form-control" value="<?php echo $data['mulai_magang']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="akhir_magang" class="form-label">Akhir Magang</label>
            <input type="date" name="akhir_magang" id="akhir_magang" class="form-control" value="<?php echo $data['akhir_magang']; ?>" required>
        </div>
        <div class="col-md-12 mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" id="no_telp" class="form-control" value="<?php echo htmlspecialchars($data['no_telp']); ?>" required>
        </div>
        <!-- [TAMBAHAN] Input field baru untuk No. Telepon Orang Tua -->
        <div class="col-md-6 mb-3">
            <label for="no_telp_ortu" class="form-label">No. Telepon Orang Tua (WhatsApp)</label>
            <input type="text" name="no_telp_ortu" id="no_telp_ortu" class="form-control" value="<?php echo htmlspecialchars($data['no_telp_ortu']); ?>" placeholder="Format: 628xxxxxxxxxx">
            <div class="form-text">Awali dengan 62. Kosongkan jika tidak ada.</div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="no_telp_guru" class="form-label">No. Telepon Guru/Pembimbing (WhatsApp)</label>
            <input type="text" name="no_telp_guru" id="no_telp_guru" class="form-control" value="<?php echo htmlspecialchars($data['no_telp_guru']); ?>" placeholder="Format: 628xxxxxxxxxx">
            <div class="form-text">Awali dengan 62. Kosongkan jika tidak ada.</div>
        </div>

        <div class="col-md-12 mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" id="alamat" rows="3"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
        </div>
        <div class="col-md-12 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Foto Saat Ini:</label>
                    <img src="apps/mahasiswa/foto/<?php echo $data['foto']; ?>" id="preview" class="img-thumbnail" alt="Foto Profil">
                </div>
                <div class="col-md-9">
                    <label for="foto_baru" class="form-label">Ganti Foto (Opsional)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" disabled placeholder="Pilih foto baru..." id="file">
                        <button type="button" id="pilih_foto" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Pilih File</button>
                    </div>
                    <input type="file" name="foto_baru" class="file d-none">
                    <div class="form-text">Biarkan kosong jika tidak ingin mengganti foto.</div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="edit_mahasiswa" class="btn btn-warning">
            <i class="bi bi-save"></i> Update
        </button>
    </div>
</form>

<!-- [TETAP] Fungsionalitas input file kustom Anda. Tidak perlu diubah. -->
<script>
    $(document).on("click", "#pilih_foto", function() {
        var file = $(this).closest('.col-md-9').find(".file");
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
<?php
if ($_SESSION["level"] != 'Admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
    </ol>
</nav>

<!-- Notifikasi -->
<div class="row">
    <div class="col-md-12">
        <?php
        function showAlert($type, $message)
        {
            $icon = ($type == 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill';
            echo "<div class='alert alert-{$type} d-flex align-items-center' role='alert'><i class='bi bi-{$icon} me-2'></i><div>{$message}</div></div>";
        }
        if (isset($_GET['edit'])) {
            if ($_GET['edit'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Pengaturan profil instansi telah diupdate.');
            else showAlert('danger', '<strong>Gagal!</strong> Pengaturan profil instansi gagal diupdate.');
        }
        if (isset($_GET['absen'])) {
            if ($_GET['absen'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Pengaturan absensi telah diupdate.');
            else showAlert('danger', '<strong>Gagal!</strong> Pengaturan absensi gagal diupdate.');
        }
        ?>
    </div>
</div>


<div class="row">
    <!-- Kolom Kiri: Profil Instansi -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profil Instansi</h5>
            </div>
            <div class="card-body">
                <?php
                $hasil = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
                $data = mysqli_fetch_array($hasil);
                ?>
                <form action="apps/pengaturan/edit.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $data['id_site']; ?>">
                    <input type="hidden" name="logo_sebelumnya" value="<?php echo $data['logo']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Instansi</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['nama_instansi']); ?>" name="nama_instansi" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pimpinan</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['pimpinan']); ?>" name="pimpinan" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pembimbing</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['pembimbing']); ?>" name="pembimbing" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['alamat']); ?>" name="alamat">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['no_telp']); ?>" name="no_telp">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['website']); ?>" name="website">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo Instansi</label>
                        <div class="row">
                            <div class="col-md-3">
                                <img src="apps/pengaturan/logo/<?php echo $data['logo']; ?>" id="preview" class="img-thumbnail">
                            </div>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" disabled placeholder="Pilih logo baru..." id="file">
                                    <button type="button" id="pilih_logo" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Pilih File</button>
                                </div>
                                <input type="file" name="logo" class="file d-none">
                                <div class="form-text">Biarkan kosong jika tidak ingin mengganti logo.</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" name="ubah_aplikasi"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pengaturan Absensi -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pengaturan Jam Absensi</h5>
            </div>
            <div class="card-body">
                <?php
                // Mengambil data dari tbl_setting_absensi
                $query_absensi = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
                $row_absensi = mysqli_fetch_array($query_absensi);
                ?>
                <form action="apps/pengaturan/absensi.php" method="post">
                    <!-- Pastikan ada input hidden untuk id_waktu -->
                    <input type="hidden" value="<?php echo $row_absensi['id_waktu']; ?>" name="id_waktu">

                    <h6 class="text-muted">Jendela Waktu Absen Masuk</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Mulai</label>
                            <!-- [PERBAIKAN] Menggunakan nama kolom baru: masuk_mulai -->
                            <input type="time" class="form-control" value="<?php echo $row_absensi['masuk_mulai']; ?>" name="masuk_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Selesai</label>
                            <!-- [PERBAIKAN] Menggunakan nama kolom baru: masuk_akhir -->
                            <input type="time" class="form-control" value="<?php echo $row_absensi['masuk_akhir']; ?>" name="masuk_akhir" required>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted">Jendela Waktu Absen Pulang</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Mulai</label>
                            <!-- [PERBAIKAN] Menggunakan nama kolom baru: pulang_mulai -->
                            <input type="time" class="form-control" value="<?php echo $row_absensi['pulang_mulai']; ?>" name="pulang_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Selesai</label>
                            <!-- [PERBAIKAN] Menggunakan nama kolom baru: pulang_akhir -->
                            <input type="time" class="form-control" value="<?php echo $row_absensi['pulang_akhir']; ?>" name="pulang_akhir" required>
                        </div>
                    </div>

                    <hr>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" name="ubah_absen"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on("click", "#pilih_logo", function() {
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
<?php
    if ($_SESSION["level"] != 'Mahasiswa') {
        echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
        exit;
    }

    // [PERBAIKAN KEAMANAN] Menggunakan prepared statement untuk mengambil data
    $kode_pengguna = $_SESSION["kode_pengguna"];
    $stmt = mysqli_prepare($kon, "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $kode_pengguna);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_array($hasil);
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Profil Saya</li>
    </ol>
</nav>

<div class="row">
    <!-- Kolom Kiri: Foto dan Info Utama -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="apps/mahasiswa/foto/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto Profil" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="my-3"><?php echo htmlspecialchars($data['nama']); ?></h5>
                <p class="text-muted mb-1">NIM: <?php echo htmlspecialchars($data['nim']); ?></p>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($data['nama_instansi_asal']); ?></p>
                <div class="d-grid">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUbahFoto">
                        <i class="bi bi-camera-fill"></i> Ubah Foto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Detail dan Form Edit -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Notifikasi -->
                <?php
                    if (isset($_GET['edit_profil'])) {
                        if ($_GET['edit_profil'] == 'berhasil') echo "<div class='alert alert-success'><strong>Berhasil!</strong> Profil Anda telah diupdate.</div>";
                        else echo "<div class='alert alert-danger'><strong>Gagal!</strong> Profil gagal diupdate.</div>";
                    }
                    if (isset($_GET['edit_foto'])) {
                        if ($_GET['edit_foto'] == 'berhasil') echo "<div class='alert alert-success'><strong>Berhasil!</strong> Foto profil telah diupdate.</div>";
                        else echo "<div class='alert alert-danger'><strong>Gagal!</strong> Foto profil gagal diupdate.</div>";
                    }
                     if (isset($_GET['ubah_password'])) {
                        if ($_GET['ubah_password'] == 'berhasil') echo "<div class='alert alert-success'><strong>Berhasil!</strong> Password Anda telah diubah.</div>";
                        else echo "<div class='alert alert-danger'><strong>Gagal!</strong> Password gagal diubah, pastikan password lama Anda benar.</div>";
                    }
                ?>
                <form action="apps/pengguna/proses_edit_profil.php" method="post">
                    <input type="hidden" name="id_mahasiswa" value="<?php echo $data['id_mahasiswa']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">Nama Lengkap</h6></div>
                        <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($data['nama']); ?></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">Asal Instansi</h6></div>
                        <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($data['nama_instansi_asal']); ?></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">Jurusan</h6></div>
                        <div class="col-sm-9 text-secondary"><?php echo htmlspecialchars($data['jurusan']); ?></div>
                    </div>
                    <hr>
                    <div class="row mb-3 align-items-center">
                        <div class="col-sm-3"><label for="no_telp" class="mb-0">No. Telepon</label></div>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3 align-items-center">
                        <div class="col-sm-3"><label for="alamat" class="mb-0">Alamat</label></div>
                        <div class="col-sm-9">
                           <textarea class="form-control" name="alamat" id="alamat" rows="3"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9 text-secondary">
                            <button type="submit" name="simpan_profil" class="btn btn-primary px-4">Simpan Perubahan</button>
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-toggle="modal" data-bs-target="#modalUbahPassword">Ubah Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubah Foto -->
<div class="modal fade" id="modalUbahFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="apps/pengguna/proses_edit_profil.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_mahasiswa" value="<?php echo $data['id_mahasiswa']; ?>">
                    <input type="hidden" name="foto_saat_ini" value="<?php echo $data['foto']; ?>">
                    <p>Pilih foto baru untuk diunggah. File akan otomatis di-resize.</p>
                    <input type="file" name="foto_baru" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_foto" class="btn btn-primary">Unggah Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="modalUbahPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="apps/pengguna/proses_edit_profil.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="kode_pengguna" value="<?php echo $kode_pengguna; ?>">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_password" class="btn btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
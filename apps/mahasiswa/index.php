<?php 
    // [TETAP] Pengecekan Hak Akses Admin
    if ($_SESSION["level"] != 'Admin') {
        echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
        exit;
    }
?>

<!-- [BARU] Breadcrumb dengan gaya Bootstrap 5 -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data Peserta</li>
    </ol>
</nav>

<!-- [BARU] Bungkus semua dalam satu card untuk konsistensi -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Peserta</h5>
        <!-- [DIUBAH] Tombol Tambah dengan gaya dan ikon baru. ID tetap sama untuk AJAX -->
        <button type="button" class="btn btn-primary" id="tombol_tambah">
            <i class="bi bi-plus-lg me-1"></i> Tambah Peserta
        </button>
    </div>
    <div class="card-body">
        
        <!-- [DIUBAH] Notifikasi dengan gaya dan ikon Bootstrap 5 -->
        <?php
            function showAlert($type, $message) {
                $icon = ($type == 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill';
                echo "<div class='alert alert-{$type} d-flex align-items-center' role='alert'>";
                echo "<i class='bi bi-{$icon} me-2'></i>";
                echo "<div>{$message}</div>";
                echo "</div>";
            }

            if (isset($_GET['add'])) {
                if ($_GET['add'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Data Mahasiswa telah disimpan.');
                else showAlert('danger', '<strong>Gagal!</strong> Data Mahasiswa gagal disimpan.');
            }
            if (isset($_GET['edit'])) {
                if ($_GET['edit'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Data Mahasiswa telah diupdate.');
                else showAlert('danger', '<strong>Gagal!</strong> Data Mahasiswa gagal diupdate.');
            }
            if (isset($_GET['pengguna'])) {
                if ($_GET['pengguna'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Pengaturan pengguna telah disimpan.');
                else showAlert('danger', '<strong>Gagal!</strong> Pengaturan pengguna gagal disimpan.');
            }
            if (isset($_GET['hapus'])) {
                if ($_GET['hapus'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Data Mahasiswa telah dihapus.');
                else showAlert('danger', '<strong>Gagal!</strong> Data Mahasiswa gagal dihapus.');
            }
        ?>

        <!-- [DIUBAH] Form Pencarian dengan gaya Bootstrap 5 -->
        <form action="index.php" method="GET" class="mb-4">
            <input type="hidden" name="page" value="mahasiswa"/>
            <div class="input-group">
                <input type="text" name="cari" class="form-control" placeholder="Cari berdasarkan Nama, NIM, atau Instansi..." value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
                <button type="submit" class="btn btn-info">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <!-- [DIUBAH] Table dengan gaya Bootstrap 5 -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Asal Instansi</th>
                        <th>Nomor Induk</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th class="text-center">Foto</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // [PERBAIKAN KEAMANAN] Menggunakan prepared statement untuk pencarian
                        $cari = $_GET['cari'] ?? '';
                        if (!empty($cari)) {
                            $sql = "SELECT * FROM tbl_mahasiswa WHERE nama LIKE ? OR nim LIKE ? OR nama_instansi_asal LIKE ?";
                            $stmt = mysqli_prepare($kon, $sql);
                            $searchTerm = "%" . $cari . "%";
                            mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
                            mysqli_stmt_execute($stmt);
                            $hasil = mysqli_stmt_get_result($stmt);
                        } else {
                            $sql = "SELECT * FROM tbl_mahasiswa ORDER BY nama ASC";
                            $hasil = mysqli_query($kon, $sql);
                        }
                        
                        $no = 0;
                        while ($data = mysqli_fetch_array($hasil)):
                        $no++;
                    ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo htmlspecialchars($data['nama']); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_instansi_asal']); ?></td>
                        <td><?php echo htmlspecialchars($data['nim']);?></td>
                        <td><?php echo date('d-m-Y', strtotime($data["mulai_magang"])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($data["akhir_magang"])); ?></td>
                        <td class="text-center">
                            <img src="apps/mahasiswa/foto/<?php echo htmlspecialchars($data["foto"]); ?>" width="80" class="img-thumbnail">
                        </td>
                        <td class="text-center">
                            <!-- [DIUBAH] Tombol Aksi dengan Ikon, ukuran kecil, dan Tooltip -->
                            <button type="button" class="btn btn-info btn-sm tombol_detail" id_mahasiswa="<?php echo $data['id_mahasiswa'];?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm tombol_setting" kode_mahasiswa="<?php echo $data['kode_mahasiswa'];?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Setting Pengguna">
                                <i class="bi bi-person-gear"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm tombol_edit" id_mahasiswa="<?php echo $data['id_mahasiswa'];?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="apps/mahasiswa/hapus.php?id_mahasiswa=<?php echo $data['id_mahasiswa']; ?>&kode_mahasiswa=<?php echo $data['kode_mahasiswa']; ?>" class="btn btn-danger btn-sm btn-hapus-mahasiswa" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Data">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- [DIUBAH] Modal dengan gaya Bootstrap 5 -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="judul"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="tampil_data"></div>  
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- [PENTING] JavaScript untuk inisialisasi Tooltip -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>


<!-- [TETAP] Semua script AJAX Anda. Tidak perlu diubah sama sekali. -->
<script>
    $('#tombol_tambah').on('click',function(){
        $.ajax({
            url: 'apps/mahasiswa/tambah.php',
            method: 'post',
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Mahasiswa';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.tombol_detail').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/detail.php',
            method: 'post',
            data: {id_mahasiswa:id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Detail Mahasiswa';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.tombol_setting').on('click',function(){
        var kode_mahasiswa = $(this).attr("kode_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/pengguna.php',
            method: 'post',
            data: {kode_mahasiswa:kode_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Setting Mahasiswa';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.tombol_edit').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/edit.php',
            method: 'post',
            data: {id_mahasiswa:id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Edit Mahasiswa';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

   $('.btn-hapus-mahasiswa').on('click',function(){
        return confirm("Apakah Anda yakin ingin menghapus data mahasiswa ini?");
    });
</script>
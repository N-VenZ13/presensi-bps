<?php 
    if ($_SESSION["level"] != 'Admin') {
        echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
        exit;
    }
    // Sertakan fungsi kustom Anda
    include 'config/function.php';
?>

<!-- [BARU] Breadcrumb dengan gaya Bootstrap 5 -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data Kegiatan</li>
    </ol>
</nav>

<!-- [BARU] Bungkus semua dalam satu card untuk konsistensi -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Kegiatan Harian Mahasiswa</h5>
        <button type="button" class="btn btn-primary" id="tambah_kegiatan">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kegiatan
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
            if (isset($_GET['tambah']) && $_GET['tambah'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Kegiatan harian telah ditambahkan.');
            if (isset($_GET['edit']) && $_GET['edit'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Kegiatan harian telah diubah.');
            if (isset($_GET['hapus']) && $_GET['hapus'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Kegiatan harian telah dihapus.');
        ?>

        <!-- [DIUBAH] Form Filter dengan gaya Bootstrap 5 -->
        <div class="p-3 mb-4 rounded" style="background-color: #f8f9fa;">
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="data_kegiatan"/>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="nama" class="form-label">Nama Mahasiswa</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Cari nama..." value="<?php echo isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_awal" class="form-label">Dari Tanggal</label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="<?php echo isset($_GET['tanggal_awal']) ? htmlspecialchars($_GET['tanggal_awal']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_akhir" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="<?php echo isset($_GET['tanggal_akhir']) ? htmlspecialchars($_GET['tanggal_akhir']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- [DIUBAH] Table dengan gaya Bootstrap 5 -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Hari, Tanggal</th>
                        <th>Jam</th>
                        <th>Kegiatan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // [PERBAIKAN KEAMANAN] Query pencarian ditulis ulang dengan prepared statement
                        $base_sql = "SELECT k.*, m.nama, DAYNAME(k.tanggal) as hari, CONCAT(k.waktu_awal, ' - ', k.waktu_akhir) as waktu
                                     FROM tbl_kegiatan k
                                     JOIN tbl_mahasiswa m ON k.id_mahasiswa = m.id_mahasiswa";
                        
                        $conditions = [];
                        $params = [];
                        $types = "";

                        if (!empty($_GET['nama'])) {
                            $conditions[] = "m.nama LIKE ?";
                            $params[] = "%" . $_GET['nama'] . "%";
                            $types .= "s";
                        }
                        if (!empty($_GET['tanggal_awal'])) {
                            $conditions[] = "k.tanggal >= ?";
                            $params[] = $_GET['tanggal_awal'];
                            $types .= "s";
                        }
                        if (!empty($_GET['tanggal_akhir'])) {
                            $conditions[] = "k.tanggal <= ?";
                            $params[] = $_GET['tanggal_akhir'];
                            $types .= "s";
                        }

                        if (!empty($conditions)) {
                            $base_sql .= " WHERE " . implode(" AND ", $conditions);
                        }
                        
                        $base_sql .= " ORDER BY k.tanggal DESC, k.waktu_awal DESC";

                        $stmt = mysqli_prepare($kon, $base_sql);
                        if (!empty($params)) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        }
                        mysqli_stmt_execute($stmt);
                        $hasil = mysqli_stmt_get_result($stmt);
                        
                        $no = 0;
                        while ($data = mysqli_fetch_array($hasil)):
                        $no++;
                    ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo htmlspecialchars($data['nama']); ?></td>
                        <td>
                            <?php 
                                echo MendapatkanHari(strtolower($data["hari"])) . ", " . 
                                     date('d', strtotime($data['tanggal'])) . ' ' . 
                                     MendapatkanBulan(date('m', strtotime($data['tanggal']))) . ' ' . 
                                     date('Y', strtotime($data['tanggal']));
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($data['waktu']); ?></td>
                        <td><?php echo htmlspecialchars($data['kegiatan']); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm ubah_kegiatan" id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" id_kegiatan="<?php echo $data['id_kegiatan']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Kegiatan">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="apps/data_kegiatan/hapus.php?id_kegiatan=<?php echo $data['id_kegiatan']; ?>" class="btn btn-danger btn-sm btn-hapus-kegiatan" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Kegiatan">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                            <button type="button" class="btn btn-primary btn-sm cetak_kegiatan" id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Laporan">
                                <i class="bi bi-printer-fill"></i>
                            </button>
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

<!-- ======================================================= -->
<!-- JAVASCRIPT KHUSUS UNTUK HALAMAN INI -->
<!-- ======================================================= -->
<script>
    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // AJAX untuk Tambah Kegiatan
    $('#tambah_kegiatan').on('click',function(){
        $.ajax({
            url: 'apps/data_kegiatan/tambah.php',
            method: 'post',
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Kegiatan Harian';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    // AJAX untuk Edit Kegiatan
    $('.ubah_kegiatan').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        var id_kegiatan = $(this).attr("id_kegiatan");
        $.ajax({
            url: 'apps/data_kegiatan/edit.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa, id_kegiatan: id_kegiatan},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Edit Kegiatan Harian';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });
    
    // Konfirmasi Hapus
    $('.btn-hapus-kegiatan').on('click',function(){
        return confirm("Apakah Anda yakin ingin menghapus kegiatan ini?");
    });

    // AJAX untuk Cetak Laporan
    $('.cetak_kegiatan').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_kegiatan/cetak.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Cetak Laporan Kegiatan';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });
</script>
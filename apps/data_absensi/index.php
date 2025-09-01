<?php
if ($_SESSION["level"] != 'Admin') {
    echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
include 'config/function.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data Absensi</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Absensi</h5>
        <button type="button" class="btn btn-primary" id="tambah_absensi">
            <i class="bi bi-plus-lg me-1"></i> Tambah Absensi Manual
        </button>
    </div>
    <div class="card-body">

        <?php
        function showAlert($type, $message)
        {
            $icon = ($type == 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill';
            echo "<div class='alert alert-{$type} d-flex align-items-center' role='alert'>";
            echo "<i class='bi bi-{$icon} me-2'></i>";
            echo "<div>{$message}</div>";
            echo "</div>";
        }
        if (isset($_GET['mulai'])) {
            if ($_GET['mulai'] == 'berhasil') showAlert('success', '<strong>Berhasil!</strong> Data absensi berhasil ditambahkan.');
            else if ($_GET['mulai'] == 'gagal') showAlert('warning', '<strong>Gagal!</strong> Data absensi untuk mahasiswa tersebut pada tanggal itu sudah ada.');
        }
        ?>

        <div class="p-3 mb-4 rounded" style="background-color: #f8f9fa;">
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="data_absensi" />
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="nama" class="form-label">Nama Peserta</label>
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

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Hari, Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $base_sql = "SELECT a.*, m.nama, m.nama_instansi_asal, DAYNAME(a.tanggal) as hari, 
                                     CASE a.status 
                                         WHEN 1 THEN 'Hadir' 
                                         WHEN 2 THEN 'Izin' 
                                         WHEN 3 THEN 'Tidak Hadir' 
                                         ELSE 'Tidak Diketahui' 
                                     END as status_text 
                                     FROM tbl_absensi a 
                                     JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa";

                    $conditions = [];
                    $params = [];
                    $types = "";

                    if (!empty($_GET['nama'])) {
                        $conditions[] = "m.nama LIKE ?";
                        $params[] = "%" . $_GET['nama'] . "%";
                        $types .= "s";
                    }
                    if (!empty($_GET['tanggal_awal'])) {
                        $conditions[] = "a.tanggal >= ?";
                        $params[] = $_GET['tanggal_awal'];
                        $types .= "s";
                    }
                    if (!empty($_GET['tanggal_akhir'])) {
                        $conditions[] = "a.tanggal <= ?";
                        $params[] = $_GET['tanggal_akhir'];
                        $types .= "s";
                    }

                    if (!empty($conditions)) {
                        $base_sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $base_sql .= " ORDER BY a.tanggal DESC, a.waktu DESC";

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
                            <td>
                                <strong><?php echo htmlspecialchars($data['nama']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($data['nama_instansi_asal']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($data['status_text']); ?></td>
                            <!-- <td><?php echo htmlspecialchars($data['waktu']); ?></td> -->
                            <td><?php echo $data['waktu'] ? date('H:i:s', strtotime($data['waktu'])) : '-'; ?></td>
                            <td><?php echo $data['waktu_pulang'] ? date('H:i:s', strtotime($data['waktu_pulang'])) : '-'; ?></td>
                            <td>
                                <?php
                                echo MendapatkanHari(($data["hari"])) . ", " .
                                    date('d', strtotime($data['tanggal'])) . ' ' .
                                    MendapatkanBulan(date('m', strtotime($data['tanggal']))) . ' ' .
                                    date('Y', strtotime($data['tanggal']));
                                ?>
                            </td>
                            <td>
                                <?php
                                $keterangan_final_admin = $data['keterangan'];
                                if ($data['status'] == 2) {
                                    // Jika Izin, kita perlu query tambahan kecil untuk mengambil alasannya
                                    $stmt_alasan = mysqli_prepare($kon, "SELECT alasan FROM tbl_alasan WHERE id_mahasiswa = ? AND tanggal = ?");
                                    mysqli_stmt_bind_param($stmt_alasan, "is", $data['id_mahasiswa'], $data['tanggal']);
                                    mysqli_stmt_execute($stmt_alasan);
                                    $result_alasan = mysqli_stmt_get_result($stmt_alasan);
                                    if ($data_alasan = mysqli_fetch_assoc($result_alasan)) {
                                        $keterangan_final_admin = $data_alasan['alasan'];
                                    }
                                }
                                echo htmlspecialchars($keterangan_final_admin);
                                ?>
                            </td>
                            
                            <td class="text-center">
                                <button type="button" class="btn btn-success btn-sm absensi" id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" id_absensi="<?php echo $data['id_absensi']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Ubah Absensi">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm cetak" id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Absensi">
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
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Script AJAX untuk halaman Data Absensi
    $('#tambah_absensi').on('click', function() {
        $.ajax({
            url: 'apps/data_absensi/tambah.php',
            method: 'post',
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Absensi Manual';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.absensi').on('click', function() {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        var id_absensi = $(this).attr("id_absensi");
        $.ajax({
            url: 'apps/data_absensi/absensi.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa,
                id_absensi: id_absensi
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Ubah Data Absensi';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.cetak').on('click', function() {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_absensi/cetak.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Cetak Laporan Absensi';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });
</script>
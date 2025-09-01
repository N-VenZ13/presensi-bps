<?php
if ($_SESSION["level"] != 'Mahasiswa') {
    echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
include 'config/function.php';
$id_mahasiswa = $_SESSION['id_mahasiswa'];
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Riwayat Absensi</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Kehadiran Anda</h5>
    </div>
    <div class="card-body">
        <div class="p-3 mb-4 rounded" style="background-color: #f8f9fa;">
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="riwayat" />
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="tanggal_awal" class="form-control" value="<?php echo isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" class="form-control" value="<?php echo isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-info w-100"><i class="bi bi-search"></i> Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Hari, Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Keterangan/Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // [PERBAIKAN KEAMANAN] Query dengan prepared statement
                    $sql = "SELECT a.*, al.alasan
                                FROM tbl_absensi a
                                LEFT JOIN tbl_alasan al ON a.id_mahasiswa = al.id_mahasiswa AND a.tanggal = al.tanggal
                                WHERE a.id_mahasiswa = ?";

                    $params = [$id_mahasiswa];
                    $types = "i";

                    if (!empty($_GET['tanggal_awal'])) {
                        $sql .= " AND a.tanggal >= ?";
                        $params[] = $_GET['tanggal_awal'];
                        $types .= "s";
                    }
                    if (!empty($_GET['tanggal_akhir'])) {
                        $sql .= " AND a.tanggal <= ?";
                        $params[] = $_GET['tanggal_akhir'];
                        $types .= "s";
                    }
                    $sql .= " ORDER BY a.tanggal DESC";

                    $stmt = mysqli_prepare($kon, $sql);
                    mysqli_stmt_bind_param($stmt, $types, ...$params);
                    mysqli_stmt_execute($stmt);
                    $hasil = mysqli_stmt_get_result($stmt);

                    $no = 0;
                    while ($data = mysqli_fetch_array($hasil)):
                        $no++;

                        $status_text = '';
                        switch ($data['status']) {
                            case 1:
                                $status_text = 'Hadir';
                                break;
                            case 2:
                                $status_text = 'Izin';
                                break;
                            case 3:
                                $status_text = 'Tidak Hadir';
                                break;
                        }
                    ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td>
                                <?php
                                // echo MendapatkanHari(strtolower($data["hari"])) . ", " . date('d/m/Y', strtotime($data['tanggal']));
                                $nama_hari_inggris = date('l', strtotime($data['tanggal']));
                                echo MendapatkanHari($nama_hari_inggris) . ", " . date('d/m/Y', strtotime($data['tanggal']));
                                ?>
                            </td>
                            <td><?php echo $data['waktu'] ? date('H:i:s', strtotime($data['waktu'])) : '-'; ?></td>
                            <td><?php echo $data['waktu_pulang'] ? date('H:i:s', strtotime($data['waktu_pulang'])) : '-'; ?></td>
                            <!-- <td><?php echo $status_text; ?></td> -->
                            <td>
                                <?php
                                // Gabungkan keterangan (misal: "Terlambat") dengan alasan izin jika ada
                                $keterangan_final = $data['keterangan'];
                                if ($data['status'] == 2 && !empty($data['alasan'])) {
                                    $keterangan_final = $data['alasan'];
                                }
                                echo htmlspecialchars($keterangan_final);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($data['alasan']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
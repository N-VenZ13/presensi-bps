<?php
session_start();
include '../../config/database.php';
include '../../config/function.php';
require('../../source/plugin/fpdf/fpdf.php');

// [PERBAIKAN] Logika pengecekan yang lebih fleksibel
$akses_diberikan = false;
$id_mahasiswa = null;
$tanggal_awal = null;
$tanggal_akhir = null;

// Skenario 1: Admin mencetak dari form POST
if (isset($_POST['cetak']) && isset($_SESSION['level']) && $_SESSION['level'] == 'Admin') {
    $akses_diberikan = true;
    $id_mahasiswa = (int)$_POST['id_mahasiswa'];
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
}
// Skenario 2: Mahasiswa mencetak dari link GET
elseif (isset($_GET['cetak_mahasiswa']) && isset($_SESSION['level']) && $_SESSION['level'] == 'Mahasiswa') {
    // Validasi: pastikan mahasiswa hanya bisa mencetak datanya sendiri
    if ($_GET['id_mahasiswa'] == $_SESSION['id_mahasiswa']) {
        $akses_diberikan = true;
        $id_mahasiswa = (int)$_GET['id_mahasiswa'];
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
    }
}

// Jika akses diberikan, lanjutkan membuat PDF
if ($akses_diberikan) {

    // ... SELURUH KODE PEMBUATAN PDF ANDA DARI SINI ...
    // (dari $query_site = ... sampai $pdf->Output();)

    // Ambil info instansi untuk kop surat
    $query_site = mysqli_query($kon, "select * from tbl_site limit 1");
    $row_site = mysqli_fetch_array($query_site);
    $pembimbing = $row_site['pembimbing'];

    // Ambil info mahasiswa
    $stmt_mhs = mysqli_prepare($kon, "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt_mhs, "i", $id_mahasiswa);
    mysqli_stmt_execute($stmt_mhs);
    $data_mahasiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_mhs));
    $nama_mahasiswa = $data_mahasiswa['nama'];
    $namafile = 'Absensi-' . $nama_mahasiswa . '-' . date('YmdHis') . '.pdf';

    // Atur Header untuk output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $namafile . '"');

    // Inisialisasi FPDF
    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->AddPage();

    // Membuat KOP Laporan
    $pdf->Image('../../apps/pengaturan/logo/' . $row_site['logo'], 15, 5, 20, 20);
    $pdf->SetFont('Arial', 'B', 21);
    $pdf->Cell(0, 7, strtoupper($row_site['nama_instansi']), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, $row_site['alamat'] . ', Telp ' . $row_site['no_telp'], 0, 1, 'C');
    $pdf->Cell(0, 7, $row_site['website'], 0, 1, 'C');
    $pdf->SetLineWidth(1);
    $pdf->Line(10, 31, 206, 31);
    $pdf->SetLineWidth(0);
    $pdf->Line(10, 32, 206, 32);

    // Membuat Judul Laporan
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, '', 0, 1, 'C');
    $pdf->Cell(0, 7, 'DAFTAR HADIR PESERTA MAGANG', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 7, 'Periode: ' . date('d/m/Y', strtotime($tanggal_awal)) . ' - ' . date('d/m/Y', strtotime($tanggal_akhir)), 0, 1, 'C');
    $pdf->Cell(0, 7, '', 0, 1, 'C');

    // Menampilkan Info Mahasiswa
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 6, 'Nama Peserta', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data_mahasiswa['nama'], 0, 1);
    $pdf->Cell(40, 6, 'NIM / Nomor Induk', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data_mahasiswa['nim'], 0, 1);
    $pdf->Cell(40, 6, 'Asal Instansi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $data_mahasiswa['nama_instansi_asal'], 0, 1);

    // Membuat Header Tabel Laporan
    $pdf->Cell(10, 10, '', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 8, 'No', 1, 0, 'C');
    $pdf->Cell(45, 8, 'Hari, Tanggal', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Jam Masuk', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Jam Pulang', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Status', 1, 0, 'C');
    $pdf->Cell(60, 8, 'Keterangan/Alasan', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 10);

    // [PERBAIKAN KEAMANAN] Query untuk mengambil data absensi
    $sql = "SELECT a.*, al.alasan FROM tbl_absensi a LEFT JOIN tbl_alasan al ON a.id_mahasiswa=al.id_mahasiswa AND a.tanggal=al.tanggal WHERE a.id_mahasiswa = ? AND a.tanggal BETWEEN ? AND ? ORDER BY a.tanggal ASC";
    $stmt_absen = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt_absen, "iss", $id_mahasiswa, $tanggal_awal, $tanggal_akhir);
    mysqli_stmt_execute($stmt_absen);
    $hasil_absen = mysqli_stmt_get_result($stmt_absen);

    $no = 0;
    while ($data = mysqli_fetch_assoc($hasil_absen)) {
        $no++;
        // Format data untuk ditampilkan
        $hari_tanggal = MendapatkanHari(date('l', strtotime($data['tanggal']))) . ", " . date('d-m-Y', strtotime($data['tanggal']));
        $waktu_masuk = $data['waktu'] ? date("H:i", strtotime($data['waktu'])) : '-';
        $waktu_pulang = $data['waktu_pulang'] ? date("H:i", strtotime($data['waktu_pulang'])) : '-';
        $status = StatusAbsensi($data['status']); // Menggunakan fungsi Anda
        $keterangan = htmlspecialchars($data['alasan'] ?? $data['keterangan']);

        $pdf->Cell(10, 7, $no, 1, 0, 'C');
        $pdf->Cell(45, 7, $hari_tanggal, 1, 0, 'C');
        $pdf->Cell(25, 7, $waktu_masuk, 1, 0, 'C');
        $pdf->Cell(25, 7, $waktu_pulang, 1, 0, 'C');
        $pdf->Cell(30, 7, $status, 1, 0, 'C');
        $pdf->Cell(60, 7, $keterangan, 1, 1, 'L'); // 'L' untuk Left align agar alasan mudah dibaca
    }

    // Bagian Tanda Tangan
    $pdf->Cell(0, 20, '', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(130); // Geser ke kanan
    $pdf->Cell(60, 6, 'Palembang, ' . date('d') . ' ' . MendapatkanBulan(date('m')) . ' ' . date('Y'), 0, 1, 'C');
    $pdf->Cell(130);
    $pdf->Cell(60, 6, 'Pembimbing Magang', 0, 1, 'C');
    $pdf->Cell(0, 20, '', 0, 1, 'C'); // Spasi untuk tanda tangan
    $pdf->Cell(130);
    $pdf->Cell(60, 6, $pembimbing, 0, 1, 'C');

    // Output PDF
    $pdf->Output();
} else {
    // Jika diakses secara tidak sah
    echo "Akses ditolak atau parameter tidak lengkap.";
}

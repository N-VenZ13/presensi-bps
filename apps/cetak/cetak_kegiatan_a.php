<?php
session_start();
include '../../config/database.php';
include '../../config/function.php';
require('../../source/plugin/fpdf/fpdf.php');

// Cek apakah form disubmit dan hanya admin yang bisa mengakses
if (isset($_POST['cetak']) && isset($_SESSION['level']) && $_SESSION['level'] == 'Admin') {

    $id_mahasiswa = (int)$_POST['id_mahasiswa'];
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];

    // Ambil info instansi
    $query_site = mysqli_query($kon, "select * from tbl_site limit 1");    
    $row_site = mysqli_fetch_array($query_site);
    $pembimbing = $row_site['pembimbing'];

    // Ambil info mahasiswa
    $stmt_mhs = mysqli_prepare($kon, "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt_mhs, "i", $id_mahasiswa);
    mysqli_stmt_execute($stmt_mhs);
    $data_mahasiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_mhs));
    $nama_mahasiswa = $data_mahasiswa['nama'];
    $namafile = 'Kegiatan-'.$nama_mahasiswa.'-'.date('YmdHis').'.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="'.$namafile.'"');

    $pdf = new FPDF('P', 'mm','Letter');
    // $pdf->AddPage();

    // KOP Laporan
    // $pdf->Image('../../apps/pengaturan/logo/'.$row_site['logo'],15,5,20,20);
    // $pdf->SetFont('Arial','B',21);
    // $pdf->Cell(0,7,strtoupper($row_site['nama_instansi']),0,1,'C');
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(0,7,$row_site['alamat'].', Telp '.$row_site['no_telp'],0,1,'C');
    // $pdf->Cell(0,7,$row_site['website'],0,1,'C');
    // $pdf->SetLineWidth(1);
    // $pdf->Line(10,31,206,31);
    // $pdf->SetLineWidth(0);
    // $pdf->Line(10,32,206,32);

    $pdf->AddPage();

    // --- Pengaturan Posisi & Ukuran ---
    $pageWidth = $pdf->GetPageWidth();
    $leftMargin = 15;
    $rightMargin = 15;

    // Posisi Y awal untuk semua elemen header
    $yPos = 10;

    // --- Bagian Kiri: Logo Utama & Teks Instansi ---
    $logoUtama = '../../apps/pengaturan/logo/' . $row_site['logo'];
    $logoUtamaWidth = 25; // Lebar logo utama dalam mm
    $pdf->Image($logoUtama, $leftMargin, $yPos, $logoUtamaWidth);

    // Set posisi X untuk blok teks, di sebelah kanan logo
    $textBlockX = $leftMargin + $logoUtamaWidth + 3; // 3mm spasi
    $pdf->SetXY($textBlockX, $yPos + 2); // Sedikit turun agar sejajar

    // Tulis blok teks baris per baris
    $pdf->SetFont('Arial', 'BI', 16);
    $pdf->Cell(0, 7, 'BADAN PUSAT STATISTIK', 0, 1);
    $pdf->SetX($textBlockX); // Pindahkan kursor kembali ke posisi X yang benar
    $pdf->Cell(0, 7, 'KABUPATEN MUARA ENIM', 0, 1);

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX($textBlockX);
    $pdf->Cell(0, 4, 'Jalan Bambang Utoyo No.44, Muara Enim', 0, 1);
    $pdf->SetX($textBlockX);
    $pdf->Cell(0, 4, 'Homepage : https://muaraenimkab.bps.go.id | Email : bps1603@bps.go.id', 0, 1);


    // --- Bagian Kanan: Logo Tambahan (Sensus & BerAKHLAK) ---
    // $logoSensus = '../../apps/pengaturan/logo/logo.png'; // Pastikan nama file ini benar
    $logoBerakhlak = '../../apps/pengaturan/logo/Logo_Berakhlak.png'; // Pastikan nama file ini benar

    // $logoSensusWidth = 28;
    $logoBerakhlakWidth = 40;
    // $spasiAntarLogo = 3;

    // // Hitung posisi X untuk logo paling kanan agar rata kanan
    $berakhlakX = $pageWidth - $rightMargin - $logoBerakhlakWidth;
    // $sensusX = $berakhlakX - $spasiAntarLogo - $logoSensusWidth;

    // $pdf->Image($logoSensus, $sensusX, $yPos, $logoSensusWidth);
    $pdf->Image($logoBerakhlak, $berakhlakX, $yPos, $logoBerakhlakWidth);

    $pdf->SetLineWidth(1);
    $pdf->Line(10, 40, 206, 40);
    $pdf->SetLineWidth(0);
    $pdf->Line(10, 41, 206, 41);

    $pdf->Ln(5);

    // Judul Laporan
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'',0,1,'C');
    $pdf->Cell(0,7,'JURNAL KEGIATAN HARIAN',0,1,'C');
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,7,'Periode: '.date('d/m/Y', strtotime($tanggal_awal)).' - '.date('d/m/Y', strtotime($tanggal_akhir)),0,1,'C');
    $pdf->Cell(0,7,'',0,1,'C');

    // Info Mahasiswa
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(40,6,'Nama Peserta',0,0);
    $pdf->Cell(5,6,':',0,0);
    $pdf->Cell(0,6,$data_mahasiswa['nama'],0,1);
    $pdf->Cell(40,6,'NIM / Nomor Induk',0,0);
    $pdf->Cell(5,6,':',0,0);
    $pdf->Cell(0,6,$data_mahasiswa['nim'],0,1);
    $pdf->Cell(40,6,'Asal Instansi',0,0);
    $pdf->Cell(5,6,':',0,0);
    $pdf->Cell(0,6,$data_mahasiswa['nama_instansi_asal'],0,1);
    
    // Header Tabel
    $pdf->Cell(10,10,'',0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,8,'No',1,0,'C');
    $pdf->Cell(45,8,'Hari, Tanggal',1,0,'C');
    $pdf->Cell(30,8,'Jam',1,0,'C');
    $pdf->Cell(110,8,'Uraian Kegiatan',1,1,'C');
    $pdf->SetFont('Arial','',10);

    // [PERBAIKAN KEAMANAN] Query untuk mengambil data kegiatan
    $sql="SELECT *, CONCAT(waktu_awal, ' - ', waktu_akhir) as waktu FROM tbl_kegiatan WHERE id_mahasiswa = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal ASC, waktu_awal ASC";
    $stmt_kegiatan = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt_kegiatan, "iss", $id_mahasiswa, $tanggal_awal, $tanggal_akhir);
    mysqli_stmt_execute($stmt_kegiatan);
    $hasil_kegiatan = mysqli_stmt_get_result($stmt_kegiatan);

    $no = 0;
    while ($data = mysqli_fetch_assoc($hasil_kegiatan)){
        $no++;
        $hari_tanggal = MendapatkanHari(date('l', strtotime($data['tanggal']))) . ", " . date('d-m-Y', strtotime($data['tanggal']));
        $waktu = date("H:i", strtotime($data['waktu_awal'])).' - '.date("H:i", strtotime($data['waktu_akhir']));
        
        // Mengatasi multi-baris untuk kolom kegiatan
        $cellWidth = 110;
        $cellHeight = 7;
        
        // Cek jika teks terlalu panjang
        if($pdf->GetStringWidth($data['kegiatan']) < $cellWidth){
            $line = 1;
        } else {
            $textLength = strlen($data['kegiatan']);
            $errMargin = 10;
            $startChar = 0;
            $maxChar = 0;
            $textArray = array();
            $tmpString = "";
            while($startChar < $textLength){
                while(
                $pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
                ($startChar+$maxChar) < $textLength ) {
                    $maxChar++;
                    $tmpString=substr($data['kegiatan'],$startChar,$maxChar);
                }
                $startChar=$startChar+$maxChar;
                array_push($textArray,$tmpString);
                $maxChar=0;
                $tmpString='';
            }
            $line=count($textArray);
        }

        // Cetak baris, sesuaikan tinggi
        $pdf->Cell(10,($line * $cellHeight),$no,1,0,'C');
        $pdf->Cell(45,($line * $cellHeight),$hari_tanggal,1,0,'C');
        $pdf->Cell(30,($line * $cellHeight),$waktu,1,0,'C');

        // Simpan posisi X
        $xPos=$pdf->GetX();
        $pdf->MultiCell($cellWidth,$cellHeight,$data['kegiatan'],1,'L');
    }
    
    // Tanda Tangan
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,15,'',0,1,'C');
    $pdf->Cell(130);
    $pdf->Cell(60,6,'Muara Enim, '.date('d').' '.MendapatkanBulan(date('m')).' '.date('Y'),0,1,'C');
    $pdf->Cell(130);
    $pdf->Cell(60,6,'Pembimbing Magang',0,1,'C');
    $pdf->Cell(0,20,'',0,1,'C');
    $pdf->Cell(130);
    $pdf->Cell(60,6,'................',0,1,'C');

    $pdf->Output();

} else {
    echo "Akses ditolak atau tidak ada data yang dikirim.";
}
?>
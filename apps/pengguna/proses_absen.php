<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_mahasiswa'])) {

    include '../../config/database.php';

    $id_mahasiswa = $_SESSION['id_mahasiswa'];
    $tanggal_hari_ini = date("Y-m-d");
    $waktu_sekarang = date("H:i:s");
    $aksi = $_POST['aksi'];

    $hasil_setting = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
    $setting = mysqli_fetch_assoc($hasil_setting);

    if ($aksi == 'masuk') {
        $status = $_POST['status'];

        // Cek dulu apakah sudah ada data untuk mencegah duplikasi
        $stmt_check = mysqli_prepare($kon, "SELECT id_absensi FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
        mysqli_stmt_bind_param($stmt_check, "is", $id_mahasiswa, $tanggal_hari_ini);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) == 0) {

            if ($status == 1) { // Jika Hadir
                $keterangan = null;
                if ($waktu_sekarang > $setting['masuk_akhir']) {
                    $awal = new DateTime($setting['masuk_akhir']);
                    $akhir = new DateTime($waktu_sekarang);
                    $diff = $akhir->diff($awal);
                    $keterangan = "Terlambat " . $diff->i . " menit " . $diff->s . " detik";
                }
                $stmt = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, keterangan) VALUES (?, 1, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "isss", $id_mahasiswa, $waktu_sekarang, $tanggal_hari_ini, $keterangan);
                mysqli_stmt_execute($stmt);
            } elseif ($status == 2) { // Jika Izin
                $alasan = htmlspecialchars($_POST['alasan']);
                $nama_file_final = null;

                // --- [BARU] LOGIKA UPLOAD FILE BUKTI ---
                if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
                    $file_bukti = $_FILES['file_bukti'];
                    $nama_file = $file_bukti['name'];
                    $lokasi_tmp = $file_bukti['tmp_name'];
                    $ukuran_file = $file_bukti['size'];
                    $ekstensi_diizinkan = ['pdf', 'png', 'jpg', 'jpeg'];

                    $x = explode('.', $nama_file);
                    $ekstensi = strtolower(end($x));

                    // Validasi file
                    if (in_array($ekstensi, $ekstensi_diizinkan) && $ukuran_file < 2000000) { // Maks 2MB
                        // Buat nama file unik: IDMahasiswa_Tanggal_NamaAsliFile
                        $nama_file_final = $id_mahasiswa . '_' . $tanggal_hari_ini . '_' . $nama_file;
                        // Pindahkan file ke folder tujuan
                        move_uploaded_file($lokasi_tmp, '../mahasiswa/bukti_izin/' . $nama_file_final);
                    }
                }
                // --- AKHIR LOGIKA UPLOAD FILE ---

                // [DIUBAH] Simpan status 5 = Menunggu Persetujuan
                $stmt_absensi = mysqli_prepare($kon, "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal) VALUES (?, 5, ?)");
                mysqli_stmt_bind_param($stmt_absensi, "is", $id_mahasiswa, $tanggal_hari_ini);
                mysqli_stmt_execute($stmt_absensi);

                // Simpan alasan dan nama file bukti ke tbl_alasan
                $stmt_alasan = mysqli_prepare($kon, "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal, file_bukti) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_alasan, "isss", $id_mahasiswa, $alasan, $tanggal_hari_ini, $nama_file_final);
                mysqli_stmt_execute($stmt_alasan);
            }
        }
    } elseif ($aksi == 'pulang') {
        // Logika absen pulang (tidak berubah)
        $keterangan = null;
        if ($waktu_sekarang < $setting['pulang_mulai']) {
            $keterangan = "Pulang lebih awal";
        }
        $stmt = mysqli_prepare($kon, "UPDATE tbl_absensi SET waktu_pulang = ?, keterangan = CONCAT(IFNULL(keterangan,''), ' ', ?) WHERE id_mahasiswa = ? AND tanggal = ?");
        mysqli_stmt_bind_param($stmt, "ssis", $waktu_sekarang, $keterangan, $id_mahasiswa, $tanggal_hari_ini);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../../index.php?page=absen");
    exit();
}

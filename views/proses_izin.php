<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['id_user'];
    $jenis_izin = $_POST['jenis'];
    $no_hp = $_POST['no_hp'];
    $tgl_pengajuan = $_POST['tgl_pengajuan'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['alasan']);
    $status = "Pending"; // Status default saat pertama kali diajukan

    // --- LOGIKA UPLOAD DOKUMEN ---
    $nama_file = $_FILES['dokumen']['name'];
    $ukuran_file = $_FILES['dokumen']['size'];
    $error_file = $_FILES['dokumen']['error'];
    $tmp_name = $_FILES['dokumen']['tmp_name'];

    $file_final = ""; // Default jika tidak ada file

    if ($nama_file != "") {
        // Cek Ekstensi
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'pdf'];
        $ekstensi_file = explode('.', $nama_file);
        $ekstensi_file = strtolower(end($ekstensi_file));

        if (!in_array($ekstensi_file, $ekstensi_valid)) {
            echo "<script>alert('Format file tidak valid! Gunakan JPG/PNG/PDF'); window.history.back();</script>";
            exit;
        }

        // Cek Ukuran (Maks 2MB)
        if ($ukuran_file > 2000000) {
            echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB'); window.history.back();</script>";
            exit;
        }

        // Generate Nama File Baru agar tidak duplikat
        $file_final = uniqid() . "." . $ekstensi_file;
        $tujuan = "../assets/dokumen/" . $file_final;

        // Pindahkan file ke folder tujuan
        move_uploaded_file($tmp_name, $tujuan);
    }

    // --- SIMPAN KE DATABASE ---
    $query = "INSERT INTO perizinan (id_user, jenis_izin, tgl_mulai, tgl_selesai, keterangan, lampiran, status) 
              VALUES ('$id_user', '$jenis_izin', '$tgl_mulai', '$tgl_selesai', '$keterangan', '$file_final', '$status')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Pengajuan perizinan berhasil dikirim!');
                window.location.href = 'rekap_izin.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: form_perizinan.php");
}
?>
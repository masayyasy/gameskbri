<?php
session_start();
// Pastikan path ke koneksi.php sudah benar (naik satu folder ke config)
include '../config/koneksi.php';

// Memberitahu browser bahwa file ini akan mengirimkan respon JSON
header('Content-Type: application/json');

// Mengambil data JSON yang dikirim melalui fetch/AJAX
$content = file_get_contents("php://input");
$input = json_decode($content, true);

// Pastikan data faceData ada dan user sudah login
if (isset($input['faceData']) && isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    
    // Mengamankan data agar bisa masuk ke database dengan aman
    // faceData berupa string JSON yang sangat panjang
    $faceData = mysqli_real_escape_string($conn, $input['faceData']);

    // Query untuk menyimpan/update data wajah ke kolom face_data di tabel users
    $query = "UPDATE users SET face_data = '$faceData' WHERE id_user = '$id_user'";
    
    if (mysqli_query($conn, $query)) {
        // Jika sukses
        echo json_encode(['success' => true]);
    } else {
        // Jika terjadi kesalahan pada database
        echo json_encode([
            'success' => false, 
            'error' => 'Gagal Update: ' . mysqli_error($conn)
        ]);
    }
} else {
    // Jika data tidak lengkap atau sesi login hilang
    echo json_encode([
        'success' => false, 
        'error' => 'Data tidak terkirim atau Sesi Login berakhir'
    ]);
}
?>
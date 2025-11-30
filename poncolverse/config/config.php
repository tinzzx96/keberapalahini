<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'DatabaseFilm');

// Konfigurasi Upload
define('UPLOAD_POSTER_DIR', dirname(__DIR__) . '/uploads/posters/');
define('UPLOAD_ACTOR_DIR', dirname(__DIR__) . '/uploads/actors/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk upload file
function uploadFile($file, $type = 'poster') {
    $targetDir = ($type === 'poster') ? UPLOAD_POSTER_DIR : UPLOAD_ACTOR_DIR;
    
    // Cek apakah folder exists, jika tidak buat
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . time() . '_' . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP'];
    }
    
    if ($file["size"] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        // Return relative path untuk disimpan di database
        $relativePath = 'uploads/' . ($type === 'poster' ? 'posters/' : 'actors/') . basename($targetFilePath);
        return ['success' => true, 'path' => $relativePath];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

// Start session
session_start();
?>
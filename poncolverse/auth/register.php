<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = clean_input($_POST['firstName']);
    $lastName = clean_input($_POST['lastName']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $recoveryEmail = clean_input($_POST['recoveryEmail']);
    
    // Validasi input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($recoveryEmail)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }
    
    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }
    
    if (!filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email pemulihan tidak valid']);
        exit;
    }
    
    // Validasi password minimal 6 karakter
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        exit;
    }
    
    // Cek apakah email sudah terdaftar
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }
    
    // Cek apakah email pemulihan sama dengan email utama
    if ($email === $recoveryEmail) {
        echo json_encode(['success' => false, 'message' => 'Email pemulihan tidak boleh sama dengan email utama']);
        exit;
    }
    
    // Hash password dengan password_hash (lebih aman dari MD5)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $joinDate = date('Y-m-d');
    
    // Insert user baru ke database
    $sql = "INSERT INTO users (firstName, lastName, email, password, recoveryEmail, role, status, joinDate) 
            VALUES (?, ?, ?, ?, ?, 'user', 'Penonton', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $recoveryEmail, $joinDate);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Pendaftaran berhasil! Silakan login dengan email dan password Anda.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mendaftar: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
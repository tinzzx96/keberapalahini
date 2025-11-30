<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk memberikan testimoni']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = clean_input($_POST['message']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName'];
    
    // Validasi input
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
        exit;
    }
    
    // Validasi rating (1-5 bintang)
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
        exit;
    }
    
    // Cek apakah user sudah pernah memberikan testimoni
    $check_sql = "SELECT id FROM website_testimonials WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah pernah memberikan testimoni']);
        exit;
    }
    
    // Insert testimoni ke database (otomatis approved untuk demo)
    $sql = "INSERT INTO website_testimonials (user_id, user_name, message, rating, is_approved) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $user_id, $user_name, $message, $rating);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Terima kasih atas testimoni Anda!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan testimoni: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
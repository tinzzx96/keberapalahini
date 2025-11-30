<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk berkomentar']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = intval($_POST['movie_id']);
    $comment = clean_input($_POST['comment']);
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName'];
    
    // Validasi input
    if (empty($movie_id) || empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Komentar tidak boleh kosong']);
        exit;
    }
    
    // Validasi rating (opsional, 0-10)
    if ($rating !== null && ($rating < 0 || $rating > 10)) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 0-10']);
        exit;
    }
    
    // Insert komentar ke database
    $sql = "INSERT INTO movie_comments (movie_id, user_id, user_name, comment, rating) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissd", $movie_id, $user_id, $user_name, $comment, $rating);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Komentar berhasil ditambahkan',
            'comment' => [
                'id' => $conn->insert_id,
                'user_name' => $user_name,
                'comment' => $comment,
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan komentar: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
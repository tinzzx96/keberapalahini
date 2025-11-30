<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Cek apakah user adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID film tidak valid']);
        exit;
    }
    
    // Ambil data film dulu (untuk hapus file poster)
    $sqlGet = "SELECT poster FROM movies WHERE id = ?";
    $stmtGet = $conn->prepare($sqlGet);
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $resultGet = $stmtGet->get_result();
    $movie = $resultGet->fetch_assoc();
    
    // Ambil semua foto aktor
    $sqlCast = "SELECT actor_photo FROM cast_members WHERE movie_id = ?";
    $stmtCast = $conn->prepare($sqlCast);
    $stmtCast->bind_param("i", $id);
    $stmtCast->execute();
    $resultCast = $stmtCast->get_result();
    
    // Hapus file poster dari server
    if ($movie && !empty($movie['poster']) && file_exists('../../' . $movie['poster'])) {
        unlink('../../' . $movie['poster']);
    }
    
    // Hapus file foto aktor dari server
    while ($cast = $resultCast->fetch_assoc()) {
        if (!empty($cast['actor_photo']) && file_exists('../../' . $cast['actor_photo'])) {
            unlink('../../' . $cast['actor_photo']);
        }
    }
    
    // Hapus cast members dulu (foreign key constraint)
    $sqlDeleteCast = "DELETE FROM cast_members WHERE movie_id = ?";
    $stmtDeleteCast = $conn->prepare($sqlDeleteCast);
    $stmtDeleteCast->bind_param("i", $id);
    $stmtDeleteCast->execute();
    
    // Hapus film dari database
    $sql = "DELETE FROM movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Film berhasil dihapus'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus film: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
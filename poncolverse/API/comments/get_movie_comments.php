<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

if (isset($_GET['movie_id'])) {
    $movie_id = intval($_GET['movie_id']);
    
    // Ambil semua komentar untuk film ini
    $sql = "SELECT mc.*, u.firstName, u.lastName 
            FROM movie_comments mc 
            LEFT JOIN users u ON mc.user_id = u.id 
            WHERE mc.movie_id = ? 
            ORDER BY mc.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => $row['id'],
            'user_name' => $row['user_name'],
            'comment' => $row['comment'],
            'rating' => $row['rating'],
            'created_at' => date('d M Y H:i', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode($comments);
} else {
    echo json_encode([]);
}
?>
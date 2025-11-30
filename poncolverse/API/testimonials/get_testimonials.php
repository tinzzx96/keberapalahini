<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Ambil semua testimoni yang sudah approved
$sql = "SELECT * FROM website_testimonials WHERE is_approved = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);

$testimonials = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $testimonials[] = [
            'id' => $row['id'],
            'user_name' => $row['user_name'],
            'message' => $row['message'],
            'rating' => $row['rating'],
            'created_at' => date('d M Y', strtotime($row['created_at']))
        ];
    }
}

echo json_encode($testimonials);
?>
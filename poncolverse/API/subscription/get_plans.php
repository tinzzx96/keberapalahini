<?php
header('Content-Type: application/json');
header('Cache-Control: max-age=300'); // Browser cache 5 menit

require_once '../../config/config.php';

try {
    // Simple query tanpa overhead
    $sql = "SELECT id, name, price, duration_days, features, max_profiles, video_quality, concurrent_streams 
            FROM subscription_plans 
            ORDER BY price ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $plans = [];
    while($row = $result->fetch_assoc()) {
        $plans[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'duration_days' => (int)$row['duration_days'],
            'features' => $row['features'],
            'max_profiles' => (int)$row['max_profiles'],
            'video_quality' => $row['video_quality'],
            'concurrent_streams' => (int)$row['concurrent_streams']
        ];
    }
    
    echo json_encode($plans, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
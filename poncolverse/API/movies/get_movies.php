<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    // Query semua film
    $sql = "SELECT * FROM movies ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $movies = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Decode genre dari JSON string ke array
            $genreArray = json_decode($row['genre'], true);
            if (!is_array($genreArray)) {
                // Fallback jika bukan JSON, split by comma
                $genreArray = array_map('trim', explode(',', $row['genre']));
            }
            
            // Ambil cast members untuk film ini
            $castArray = [];
            $sqlCast = "SELECT actor_name, actor_photo, character_name FROM cast_members WHERE movie_id = ?";
            $stmtCast = $conn->prepare($sqlCast);
            if ($stmtCast) {
                $stmtCast->bind_param("i", $row['id']);
                $stmtCast->execute();
                $resultCast = $stmtCast->get_result();
                
                while($castRow = $resultCast->fetch_assoc()) {
                    $castArray[] = [
                        'name' => $castRow['actor_name'],
                        'photo' => $castRow['actor_photo'],
                        'character' => $castRow['character_name'] ?? ''
                    ];
                }
                $stmtCast->close();
            }
            
            $movies[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'rating' => (float)$row['rating'],
                'poster' => $row['poster'],
                'trailer' => $row['trailer'],
                'watchLink' => $row['watchLink'] ?? '',
                'year' => $row['year'],
                'duration' => $row['duration'],
                'genre' => $genreArray,
                'director' => $row['director'] ?? '',
                'directorPhoto' => $row['directorPhoto'] ?? '',
                'plot' => $row['plot'] ?? '',
                'cast' => $castArray,
                'reviews' => []
            ];
        }
    }
    
    echo json_encode($movies, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    // Log error untuk debugging
    error_log("Error in get_movies.php: " . $e->getMessage());
    
    // Return empty array jika error
    echo json_encode([]);
}
?>
<?php
require_once '../../config/config.php';
require_once '../../config/tmdb_config.php';

header('Content-Type: application/json');

try {
    $allMovies = [];
    
    // 1. Ambil film dari DATABASE (custom admin)
    $sqlDb = "SELECT * FROM movies ORDER BY created_at DESC";
    $resultDb = $conn->query($sqlDb);
    
    if ($resultDb && $resultDb->num_rows > 0) {
        while($row = $resultDb->fetch_assoc()) {
            $genreArray = json_decode($row['genre'], true);
            if (!is_array($genreArray)) {
                $genreArray = array_map('trim', explode(',', $row['genre']));
            }
            
            // Ambil cast members
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
            
            $allMovies[] = [
                'id' => 'db_' . $row['id'], // Prefix untuk bedakan dari TMDb
                'title' => $row['title'],
                'rating' => (float)$row['rating'],
                'poster' => $row['poster'],
                'trailer' => $row['trailer'],
                'watchLink' => $row['watchLink'] ?? '',
                'year' => $row['year'],
                'duration' => $row['duration'],
                'genre' => $genreArray,
                'director' => $row['director'] ?? '',
                'plot' => $row['plot'] ?? '',
                'cast' => $castArray,
                'source' => 'database'
            ];
        }
    }
    
    // 2. Ambil film dari TMDb API (kalau masih kurang dari 21)
    $remaining = 21 - count($allMovies);
    
    if ($remaining > 0) {
        $data = fetchTMDb('/discover/movie?sort_by=popularity.desc&page=1');
        
        if ($data && isset($data['results'])) {
            $tmdbMovies = array_slice($data['results'], 0, $remaining);
            
            foreach ($tmdbMovies as $movie) {
                $converted = convertTMDbMovie($movie);
                $converted['source'] = 'tmdb';
                $allMovies[] = $converted;
            }
        }
    }
    
    // Limit ke 21 film
    $allMovies = array_slice($allMovies, 0, 21);
    
    echo json_encode($allMovies, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log("Error in get_all_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>
<?php
// TMDb API Configuration
define('TMDB_API_KEY', '1ae43be23c47bdb5684288efa521b6cc');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

// Fungsi untuk fetch dari TMDb API
function fetchTMDb($endpoint) {
    $url = TMDB_BASE_URL . $endpoint;
    $url .= (strpos($url, '?') !== false) ? '&' : '?';
    $url .= 'api_key=' . TMDB_API_KEY . '&language=id-ID';
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return null;
    }
    
    return json_decode($response, true);
}

// Fungsi untuk convert data TMDb ke format website
function convertTMDbMovie($movie) {
    return [
        'id' => $movie['id'],
        'title' => $movie['title'] ?? $movie['original_title'] ?? 'No Title',
        'rating' => round($movie['vote_average'] ?? 0, 1),
        'poster' => !empty($movie['poster_path']) ? TMDB_IMAGE_BASE . $movie['poster_path'] : 'https://via.placeholder.com/500x750?text=No+Poster',
        'trailer' => 'https://www.youtube.com/embed/' . getYouTubeTrailer($movie['id']), // Function terpisah
        'watchLink' => '#',
        'year' => !empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A',
        'duration' => 'N/A', // TMDb gak kasih durasi di list, harus fetch detail
        'genre' => isset($movie['genre_ids']) ? getGenreNames($movie['genre_ids']) : [],
        'plot' => $movie['overview'] ?? 'No description available',
        'director' => 'N/A',
        'cast' => []
    ];
}

// Fungsi untuk convert genre IDs ke nama genre
function getGenreNames($genreIds) {
    $genreMap = [
        28 => 'Action',
        12 => 'Adventure',
        16 => 'Animation',
        35 => 'Comedy',
        80 => 'Crime',
        99 => 'Documentary',
        18 => 'Drama',
        10751 => 'Family',
        14 => 'Fantasy',
        36 => 'History',
        27 => 'Horror',
        10402 => 'Music',
        9648 => 'Mystery',
        10749 => 'Romance',
        878 => 'Sci-Fi',
        10770 => 'TV Movie',
        53 => 'Thriller',
        10752 => 'War',
        37 => 'Western'
    ];
    
    $genres = [];
    foreach ($genreIds as $id) {
        if (isset($genreMap[$id])) {
            $genres[] = $genreMap[$id];
        }
    }
    
    return $genres;
}

// Fungsi untuk get YouTube trailer (butuh API request tambahan)
function getYouTubeTrailer($movieId) {
    $data = fetchTMDb("/movie/{$movieId}/videos");
    
    if ($data && isset($data['results'])) {
        foreach ($data['results'] as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                return $video['key'];
            }
        }
    }
    
    return 'dQw4w9WgXcQ'; // Fallback: Rick Roll 😂
}
?>
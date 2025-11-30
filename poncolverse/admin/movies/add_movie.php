<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

// Cek apakah user adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log received data
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    $title = clean_input($_POST['title']);
    $rating = floatval($_POST['rating']);
    $year = clean_input($_POST['year']);
    $duration = clean_input($_POST['duration']);
    $trailer = clean_input($_POST['trailer']);
    $watchLink = isset($_POST['watchLink']) ? clean_input($_POST['watchLink']) : null;
    $genre = isset($_POST['genre']) ? json_encode($_POST['genre']) : json_encode([]);
    $plot = isset($_POST['plot']) ? clean_input($_POST['plot']) : '';
    $director = isset($_POST['director']) ? clean_input($_POST['director']) : '';
    
    // Validasi input wajib
    if (empty($title) || empty($rating) || empty($year) || empty($duration) || empty($trailer)) {
        echo json_encode(['success' => false, 'message' => 'Field wajib harus diisi (Judul, Rating, Tahun, Durasi, Trailer)']);
        exit;
    }
    
    // Upload poster - FIXED: Check if file exists and has no errors
    if (!isset($_FILES['poster'])) {
        echo json_encode(['success' => false, 'message' => 'File poster tidak ditemukan. Pastikan form memiliki enctype="multipart/form-data"']);
        exit;
    }
    
    if ($_FILES['poster']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Upload error: ';
        switch($_FILES['poster']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMsg .= 'File terlalu besar (melebihi upload_max_filesize)';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg .= 'File terlalu besar (melebihi MAX_FILE_SIZE)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg .= 'File hanya terupload sebagian';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg .= 'Tidak ada file yang diupload';
                break;
            default:
                $errorMsg .= 'Error code: ' . $_FILES['poster']['error'];
        }
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }
    
    $posterUpload = uploadFile($_FILES['poster'], 'poster');
    if (!$posterUpload['success']) {
        echo json_encode(['success' => false, 'message' => 'Upload poster gagal: ' . $posterUpload['message']]);
        exit;
    }
    
    $posterPath = $posterUpload['path'];
    
    // ✅ FIXED - Pastikan kolom created_at ada di database
    $sql = "INSERT INTO movies (title, rating, poster, trailer, watchLink, year, duration, genre, director, directorPhoto, plot, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement gagal: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("sdssssssss", $title, $rating, $posterPath, $trailer, $watchLink, $year, $duration, $genre, $director, $plot);
    
    if ($stmt->execute()) {
        $movieId = $conn->insert_id;
        
        // Insert cast members jika ada
        if (isset($_POST['actor_names']) && is_array($_POST['actor_names'])) {
            $actorNames = $_POST['actor_names'];
            $characterNames = isset($_POST['character_names']) ? $_POST['character_names'] : [];
            
            foreach ($actorNames as $index => $actorName) {
                if (!empty($actorName) && isset($_FILES['actor_photos']['name'][$index])) {
                    $characterName = isset($characterNames[$index]) ? clean_input($characterNames[$index]) : '';
                    
                    // Prepare file upload untuk aktor
                    $actorFile = [
                        'name' => $_FILES['actor_photos']['name'][$index],
                        'type' => $_FILES['actor_photos']['type'][$index],
                        'tmp_name' => $_FILES['actor_photos']['tmp_name'][$index],
                        'error' => $_FILES['actor_photos']['error'][$index],
                        'size' => $_FILES['actor_photos']['size'][$index]
                    ];
                    
                    if ($actorFile['error'] === UPLOAD_ERR_OK) {
                        $actorUpload = uploadFile($actorFile, 'actor');
                        if ($actorUpload['success']) {
                            $actorPhotoPath = $actorUpload['path'];
                            $actorNameClean = clean_input($actorName);
                            
                            $sqlCast = "INSERT INTO cast_members (movie_id, actor_name, actor_photo, character_name) VALUES (?, ?, ?, ?)";
                            $stmtCast = $conn->prepare($sqlCast);
                            $stmtCast->bind_param("isss", $movieId, $actorNameClean, $actorPhotoPath, $characterName);
                            $stmtCast->execute();
                        }
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Film berhasil ditambahkan',
            'id' => $movieId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan film: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
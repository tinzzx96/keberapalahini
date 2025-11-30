<?php 
require_once '../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>PoncolVerse - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<div class="admin-container">
  <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
  <header class="admin-header">
    <h1>PoncolVerse - Admin Panel</h1>
    <p>Kelola koleksi film Anda dengan mudah</p>
  </header>
  <button class="add-movie-btn" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah Film Baru</button>
  <div class="movies-table">
    <table>
      <thead>
        <tr>
          <th>Poster</th>
          <th>Judul</th>
          <th>Rating</th>
          <th>Tahun</th>
          <th>Genre</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="moviesTableBody">
        <tr><td colspan="6" class="no-data">Memuat data film...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div id="movieModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Tambah Film Baru</h2>
    <form id="movieForm" enctype="multipart/form-data">
      <input type="hidden" id="movieId" name="id">
      <div class="form-grid">
        <div class="form-group">
          <label for="title">Judul Film *</label>
          <input type="text" id="title" name="title" required />
        </div>
        <div class="form-group">
          <label for="rating">Rating (0-10) *</label>
          <input type="number" id="rating" name="rating" step="0.1" min="0" max="10" required />
        </div>
        <div class="form-group">
          <label for="year">Tahun *</label>
          <input type="text" id="year" name="year" placeholder="2024" required />
        </div>
        <div class="form-group">
          <label for="duration">Durasi *</label>
          <input type="text" id="duration" name="duration" placeholder="2h 30m" required />
        </div>
        <div class="form-group">
          <label for="director">Sutradara</label>
          <input type="text" id="director" name="director" placeholder="Nama Sutradara" />
        </div>
        <div class="form-group">
          <label for="poster">Poster Film * (Upload Gambar)</label>
          <input type="file" id="poster" name="poster" accept="image/*" required />
          <small>Format: JPG, PNG, GIF, WEBP. Max 5MB</small>
        </div>
        <div class="form-group full-width">
          <label for="trailer">Trailer YouTube Embed URL *</label>
          <input type="url" id="trailer" name="trailer" placeholder="https://www.youtube.com/embed/..." required />
        </div>
        <div class="form-group full-width">
          <label for="watchLink">Link Nonton Film (Opsional)</label>
          <input type="url" id="watchLink" name="watchLink" placeholder="https://..." />
        </div>
        <div class="form-group full-width">
          <label for="plot">Sinopsis Film</label>
          <textarea id="plot" name="plot" rows="5" placeholder="Ceritakan tentang film ini..."></textarea>
        </div>
        <div class="form-group full-width">
          <label>Genre *</label>
          <div class="genre-checkboxes">
            <div class="genre-checkbox"><input type="checkbox" id="genre-action" name="genre[]" value="Action" /><label for="genre-action">Action</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-adventure" name="genre[]" value="Adventure" /><label for="genre-adventure">Adventure</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-scifi" name="genre[]" value="Sci-Fi" /><label for="genre-scifi">Sci-Fi</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-drama" name="genre[]" value="Drama" /><label for="genre-drama">Drama</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-fantasy" name="genre[]" value="Fantasy" /><label for="genre-fantasy">Fantasy</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-comedy" name="genre[]" value="Comedy" /><label for="genre-comedy">Comedy</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-horror" name="genre[]" value="Horror" /><label for="genre-horror">Horror</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-romance" name="genre[]" value="Romance" /><label for="genre-romance">Romance</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-thriller" name="genre[]" value="Thriller" /><label for="genre-thriller">Thriller</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-crime" name="genre[]" value="Crime" /><label for="genre-crime">Crime</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-mystery" name="genre[]" value="Mystery" /><label for="genre-mystery">Mystery</label></div>
            <div class="genre-checkbox"><input type="checkbox" id="genre-animation" name="genre[]" value="Animation" /><label for="genre-animation">Animation</label></div>
          </div>
        </div>
      </div>

      <div class="cast-section">
        <h3>Pemeran (Opsional)</h3>
        <button type="button" class="add-cast-btn" onclick="addCastField()"><i class="fas fa-plus"></i> Tambah Aktor</button>
        <div id="castContainer"></div>
      </div>

      <div class="form-actions">
        <button type="button" class="form-btn secondary" onclick="closeModal()">Batal</button>
        <button type="submit" class="form-btn primary">Simpan Film</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/admin.js"></script>


</body>
</html>
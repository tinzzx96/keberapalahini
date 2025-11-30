<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PoncolVerse - Nonton Bioskop Online</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/main.css">

  <!-- yang belom diganti itu semua file masih config yg lama ya tin, blm lu ganti semua, ini cuma jadi pengingat kalo lu buka vscode lagi oke tin -->
</head>

<body>
  <div class="toast-container" id="toastContainer"></div>
  <div class="particles" id="particles"></div>

  <nav class="navbar">
    <div class="logo-container">
      <a href="#" class="logo-text">PoncolVerse</a>
    </div>
    <ul class="nav-links">
      <li><a href="#beranda">Beranda</a></li>
      <li><a href="#film">Film Populer</a></li>
      <li class="genre-dropdown">
        <a href="#">Genre <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i></a>
        <div class="genre-menu">
          <div class="genre-item" onclick="filterAllMoviesByGenre('Action')">Action</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Adventure')">Adventure</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Sci-Fi')">Sci-Fi</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Drama')">Drama</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Fantasy')">Fantasy</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Comedy')">Comedy</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Horror')">Horror</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Romance')">Romance</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Thriller')">Thriller</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Crime')">Crime</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Mystery')">Mystery</div>
          <div class="genre-item" onclick="filterAllMoviesByGenre('Animation')">Animation</div>
        </div>
      </li>
      <li><a href="#semua-film">Semua Film</a></li>
      <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
        <li><a href="#paket">Paket</a></li>
      <?php endif; ?>
      <li><a href="#tentang">Tentang</a></li>
      <li>
        <div class="search-container">
          <i class="fas fa-search search-icon"></i>
          <input type="text" class="search-input" id="searchInput" placeholder="Cari film...">
        </div>
      </li>
      <li id="authButtonContainer">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div style="display: flex; align-items: center; gap: 1rem;">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
              <a href="admin/index-admin.php" class="btn-admin-icon" title="Admin Panel">
                <i class="fas fa-cog"></i>
              </a>
            <?php endif; ?>
            <div class="user-profile">
              <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_firstName'], 0, 1) . substr($_SESSION['user_lastName'], 0, 1)); ?>
              </div>
              <div class="user-dropdown">
                <div class="user-info">
                  <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_firstName'], 0, 1) . substr($_SESSION['user_lastName'], 0, 1)); ?>
                  </div>
                  <h3><?php echo $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName']; ?></h3>
                </div>
                <div class="user-details">
                  <div class="user-detail">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_email']; ?></span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_status']; ?></span>
                  </div>
                  <div class="user-detail">
                    <span class="detail-label">Bergabung:</span>
                    <span class="detail-value"><?php echo $_SESSION['user_joinDate']; ?></span>
                  </div>

                  <?php
                  // Ambil data subscription
                  $user_id = $_SESSION['user_id'];
                  $sql_sub = "SELECT u.subscription_status, u.subscription_end, sp.name as plan_name 
                FROM users u 
                LEFT JOIN subscription_plans sp ON u.subscription_plan_id = sp.id 
                WHERE u.id = ?";
                  $stmt_sub = $conn->prepare($sql_sub);
                  $stmt_sub->bind_param("i", $user_id);
                  $stmt_sub->execute();
                  $result_sub = $stmt_sub->get_result();
                  $subscription = $result_sub->fetch_assoc();
                  ?>

                  <div class="user-detail">
                    <span class="detail-label">Langganan:</span>
                    <span class="detail-value">
                      <?php
                      if ($subscription['subscription_status'] === 'active') {
                        echo $subscription['plan_name'];
                      } else {
                        echo 'Tidak Aktif';
                      }
                      ?>
                    </span>
                  </div>

                  <div class="user-detail">
                    <span class="detail-label">Billing:</span>
                    <span class="detail-value">
                      <?php
                      if ($subscription['subscription_status'] === 'active' && $subscription['subscription_end']) {
                        $end_date = strtotime($subscription['subscription_end']);
                        $now = time();
                        $days_left = floor(($end_date - $now) / 86400);

                        if ($days_left > 0) {
                          echo $days_left . ' hari lagi';
                        } else {
                          echo 'Expired';
                        }
                      } else {
                        echo '-';
                      }
                      ?>
                    </span>
                  </div>
                </div>
                <a href="auth/logout.php" class="logout-btn">Keluar</a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <button class="btn-login" onclick="openLogin()">Login</button>
        <?php endif; ?>
      </li>
    </ul>
  </nav>

  <header class="hero" id="beranda">
    <h1>Welcome To Poncol nonton</h1>
    <p>Nikmati pengalaman menonton film blockbuster dengan kualitas sinematik terbaik — semua dalam satu platform.</p>
    <button class="btn-watch" onclick="scrollToMovies()">Mulai Nonton Sekarang</button>
  </header>

  <section class="movies" id="film">
    <h2>Film Populer Hari Ini</h2>
    <div class="movie-grid" id="movieGrid">
      <div class="loading">
        <div class="spinner"></div>Memuat film...
      </div>
    </div>
  </section>

  <section class="all-movies" id="semua-film">
    <h2>Semua Film</h2>
    <div class="movie-grid" id="allMoviesGrid">
      <div class="loading">
        <div class="spinner"></div>Memuat semua film...
      </div>
    </div>
  </section>

  </section>

  <!-- ===== PRICING SECTION (HANYA UNTUK USER, BUKAN ADMIN) ===== -->
  <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
    <section class="pricing-section" id="paket">
      <h2>Pilih Paket Berlangganan</h2>
      <p class="pricing-subtitle">Nikmati pengalaman menonton tanpa batas dengan berbagai keuntungan eksklusif</p>

      <div class="pricing-cards" id="pricingCards">
        <div class="loading">
          <div class="spinner"></div>
          <p>Memuat paket berlangganan...</p>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="about" id="tentang">
    <h2>Tentang PoncolVerse</h2>
    <div class="about-content">
      <div class="about-text">
        <p>PoncolVerse adalah platform streaming film terdepan di Indonesia yang menyajikan pengalaman menonton terbaik
          dengan kualitas sinematik tinggi. Kami menyediakan berbagai film dari berbagai genre untuk memenuhi kebutuhan
          hiburan Anda.</p>
        <p>Dengan antarmuka yang user-friendly dan koleksi film yang terus diperbarui, PoncolVerse menjadi pilihan utama
          bagi para pecinta film untuk menikmati konten berkualitas kapan saja dan di mana saja.</p>
        <div class="about-features">
          <div class="feature">
            <div class="feature-icon"><i class="fas fa-film"></i></div>
            <div class="feature-text">Koleksi Film Terlengkap</div>
          </div>
          <div class="feature">
            <div class="feature-icon"><i class="fas fa-hd-video"></i></div>
            <div class="feature-text">Kualitas HD & 4K</div>
          </div>
          <div class="feature">
            <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="feature-text">Akses Multi-Device</div>
          </div>
          <div class="feature">
            <div class="feature-icon"><i class="fas fa-sync"></i></div>
            <div class="feature-text">Update Konten Rutin</div>
          </div>
        </div>
      </div>
      <div class="about-image">
        <img
          src="https://th.bing.com/th/id/OIP.IRWiPdOQi07jlhXF0_lhGAHaHa?w=186&h=186&c=7&r=0&o=7&cb=12&dpr=1.3&pid=1.7&rm=3"
          alt="PoncolVerse Experience">
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-column">
        <h3>PoncolVerse</h3>
        <p style="color: #aaa; line-height: 1.6;">Platform streaming film terbaik dengan pengalaman menonton yang tak
          terlupakan. Nikmati berbagai film berkualitas dengan mudah.</p>
        <div class="social-links">
          <a class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a class="social-link"><i class="fab fa-twitter"></i></a>
          <a class="social-link"><i class="fab fa-instagram"></i></a>
          <a class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3>Menu</h3>
        <ul class="footer-links">
          <li><a>Beranda</a></li>
          <li><a>Film</a></li>
          <li><a>Semua Film</a></li>
          <li><a>Tentang</a></li>
          <li><a>Kontak</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Genre</h3>
        <ul class="footer-links">
          <li><a>Action</a></li>
          <li><a>Adventure</a></li>
          <li><a>Sci-Fi</a></li>
          <li><a>Drama</a></li>
          <li><a>Fantasy</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Bantuan</h3>
        <ul class="footer-links">
          <li><a>FAQ</a></li>
          <li><a>Cara Berlangganan</a></li>
          <li><a>Pusat Bantuan</a></li>
          <li><a>Syarat & Ketentuan</a></li>
          <li><a>Kebijakan Privasi</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 PoncolVerse. All rights reserved. Made with <i class="fas fa-heart" style="color: #ff003c;"></i>
        for movie lovers.</p>
    </div>
  </footer>

  <!-- Login Modal -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeLogin()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1rem;">Login ke PoncolVerse</h2>
      <form id="loginForm">
        <div class="form-group">
          <label for="loginEmail">Email</label>
          <input type="email" id="loginEmail" required>
          <div class="error-message" id="loginEmailError">Email tidak valid</div>
        </div>
        <div class="form-group">
          <label for="loginPassword">Password</label>
          <input type="password" id="loginPassword" required>
          <div class="error-message" id="loginPasswordError">Password salah</div>
        </div>
        <button type="submit" class="form-btn primary">Masuk</button>
      </form>
      <div class="form-footer">
        <p>Belum punya akun? <a id="showRegister">Daftar sekarang</a></p>
      </div>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeRegister()">&times;</span>
      <h2 style="text-align:center; color:#ff003c; margin-bottom:1rem;">Daftar ke PoncolVerse</h2>
      <form id="registerForm">
        <div class="form-group">
          <label for="firstName">Nama Depan</label>
          <input type="text" id="firstName" required>
          <div class="error-message" id="firstNameError">Nama depan harus diisi</div>
        </div>
        <div class="form-group">
          <label for="lastName">Nama Belakang</label>
          <input type="text" id="lastName" required>
          <div class="error-message" id="lastNameError">Nama belakang harus diisi</div>
        </div>
        <div class="form-group">
          <label for="registerEmail">Email</label>
          <input type="email" id="registerEmail" required>
          <div class="error-message" id="registerEmailError">Email tidak valid</div>
        </div>
        <div class="form-group">
          <label for="registerPassword">Password</label>
          <input type="password" id="registerPassword" required>
          <div class="error-message" id="registerPasswordError">Password harus minimal 6 karakter</div>
        </div>
        <div class="form-group">
          <label for="recoveryEmail">Email Pemulihan</label>
          <input type="email" id="recoveryEmail" required>
          <div class="error-message" id="recoveryEmailError">Email pemulihan tidak valid</div>
        </div>
        <button type="submit" class="form-btn primary">Daftar Sekarang</button>
      </form>
      <div class="form-footer">
        <p>Sudah punya akun? <a id="showLogin">Masuk</a></p>
      </div>
    </div>
  </div>

  <!-- Trailer Modal -->
  <div id="trailerModal" class="modal">
    <div class="modal-content trailer">
      <span class="close" onclick="closeTrailer()">&times;</span>
      <div id="trailerContainer"></div>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>

</html>

kotol
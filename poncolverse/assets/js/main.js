let allMovies = [];
let searchTimeout;

function createParticles() {
  const particlesContainer = document.getElementById("particles");
  for (let i = 0; i < 30; i++) {
    const particle = document.createElement("div");
    particle.className = "particle";
    const left = Math.random() * 100;
    const delay = Math.random() * 15;
    const duration = 15 + Math.random() * 10;
    particle.style.left = `${left}%`;
    particle.style.animationDelay = `${delay}s`;
    particle.style.animationDuration = `${duration}s`;
    particlesContainer.appendChild(particle);
  }
}

function scrollToMovies() {
  document.getElementById("film").scrollIntoView({ behavior: "smooth" });
}

// Load film populer (14 film dari TMDb API)
async function loadPopularMovies() {
  try {
    const response = await fetch("API/movies/get_popular_movies.php");
    const movies = await response.json();
    renderPopularMovies(movies);
  } catch (error) {
    console.error("Error loading popular movies:", error);
    document.getElementById("movieGrid").innerHTML =
      '<div class="no-movies">Gagal memuat film populer. Silakan refresh halaman.</div>';
  }
}

// Load semua film (21 film dari TMDb API)
async function loadAllMovies() {
  try {
    const response = await fetch("API/movies/get_all_movies.php");
    const movies = await response.json();
    allMovies = movies;
    renderAllMovies(movies);
  } catch (error) {
    console.error("Error loading all movies:", error);
    document.getElementById("allMoviesGrid").innerHTML =
      '<div class="no-movies">Gagal memuat film. Silakan refresh halaman.</div>';
  }
}

function renderPopularMovies(movies) {
  const grid = document.getElementById("movieGrid");
  if (movies.length === 0) {
    grid.innerHTML = '<div class="no-movies">Belum ada film populer.</div>';
    return;
  }
  grid.innerHTML = movies
    .map(
      (movie) => `
    <div class="movie-card">
      <img src="${movie.poster}" alt="${movie.title}" class="movie-poster">
      <div class="overlay">
        <button class="trailer-btn" onclick="openTrailer('${
          movie.trailer
        }')">▶ Trailer</button>
        ${
          movie.watchLink && movie.watchLink !== "#"
            ? `<a href="${movie.watchLink}" target="_blank" class="watch-btn">🎬 Tonton</a>`
            : '<button class="watch-btn" onclick="alert(\'Link nonton belum tersedia\')">🎬 Tonton</button>'
        }
      </div>
      <div class="movie-info">
        <h3 class="movie-title">${movie.title}</h3>
        <div class="movie-rating">⭐ ${movie.rating}</div>
      </div>
    </div>
  `
    )
    .join("");
}

function renderAllMovies(movies) {
  const grid = document.getElementById("allMoviesGrid");
  if (movies.length === 0) {
    grid.innerHTML = '<div class="no-movies">Belum ada film.</div>';
    return;
  }
  grid.innerHTML = movies
    .map(
      (movie) => `
    <div class="movie-card">
      <img src="${movie.poster}" alt="${movie.title}" class="movie-poster">
      <div class="overlay">
        <button class="trailer-btn" onclick="openTrailer('${
          movie.trailer
        }')">▶ Trailer</button>
        ${
          movie.watchLink && movie.watchLink !== "#"
            ? `<a href="${movie.watchLink}" target="_blank" class="watch-btn">🎬 Tonton</a>`
            : '<button class="watch-btn" onclick="alert(\'Link nonton belum tersedia\')">🎬 Tonton</button>'
        }
      </div>
      <div class="movie-info">
        <h3 class="movie-title">${movie.title}</h3>
        <div class="movie-rating">⭐ ${movie.rating}</div>
      </div>
    </div>
  `
    )
    .join("");
}

// Search functionality (pakai TMDb API)
document.getElementById("searchInput").addEventListener("input", (e) => {
  const searchTerm = e.target.value.trim();

  // Clear timeout sebelumnya
  clearTimeout(searchTimeout);

  if (searchTerm.length < 2) {
    // Kalau kurang dari 2 karakter, load semua film
    loadAllMovies();
    return;
  }

  // Debounce: tunggu 500ms setelah user berhenti mengetik
  searchTimeout = setTimeout(async () => {
    try {
      const response = await fetch(
        `API/movies/search_movies.php?query=${encodeURIComponent(searchTerm)}`
      );
      const movies = await response.json();
      renderAllMovies(movies);
    } catch (error) {
      console.error("Error searching movies:", error);
      document.getElementById("allMoviesGrid").innerHTML =
        '<div class="no-movies">Gagal melakukan pencarian. Silakan coba lagi.</div>';
    }
  }, 500);
});

function filterAllMoviesByGenre(genreName) {
  const filteredMovies = allMovies.filter(
    (movie) => Array.isArray(movie.genre) && movie.genre.includes(genreName)
  );
  renderAllMovies(filteredMovies);
  document.getElementById("semua-film").scrollIntoView({ behavior: "smooth" });
}

function openLogin() {
  document.getElementById("loginModal").classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeLogin() {
  document.getElementById("loginModal").classList.remove("active");
  document.body.style.overflow = "";
}

function openRegister() {
  document.getElementById("registerModal").classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeRegister() {
  document.getElementById("registerModal").classList.remove("active");
  document.body.style.overflow = "";
}

function openTrailer(url) {
  document.getElementById(
    "trailerContainer"
  ).innerHTML = `<iframe width="100%" height="600" src="${url}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
  document.getElementById("trailerModal").classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeTrailer() {
  document.getElementById("trailerModal").classList.remove("active");
  document.getElementById("trailerContainer").innerHTML = "";
  document.body.style.overflow = "";
}

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Login form handler
document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  document
    .querySelectorAll("#loginForm .error-message")
    .forEach((el) => (el.style.display = "none"));

  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  if (!validateEmail(email)) {
    document.getElementById("loginEmailError").style.display = "block";
    showToast(
      "error",
      "Email Tidak Valid",
      "Silakan masukkan email yang benar"
    );
    return;
  }

  showToast("info", "Memproses...", "Sedang memverifikasi akun Anda");

  try {
    const formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    const response = await fetch("auth/login.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showToast(
        "success",
        "Login Berhasil! 🎉",
        `Selamat datang kembali, ${result.user.firstName}!`
      );
      closeLogin();
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      showToast("error", "Login Gagal", result.message);

      if (result.message.includes("Email")) {
        document.getElementById("loginEmailError").textContent = result.message;
        document.getElementById("loginEmailError").style.display = "block";
      } else {
        document.getElementById("loginPasswordError").textContent =
          result.message;
        document.getElementById("loginPasswordError").style.display = "block";
      }
    }
  } catch (error) {
    showToast(
      "error",
      "Terjadi Kesalahan",
      "Tidak dapat terhubung ke server. Silakan coba lagi."
    );
    console.error("Login error:", error);
  }
});

// Register form handler
document
  .getElementById("registerForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    document
      .querySelectorAll("#registerForm .error-message")
      .forEach((el) => (el.style.display = "none"));

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const email = document.getElementById("registerEmail").value;
    const password = document.getElementById("registerPassword").value;
    const recoveryEmail = document.getElementById("recoveryEmail").value;

    let isValid = true;

    if (!firstName) {
      document.getElementById("firstNameError").style.display = "block";
      isValid = false;
    }

    if (!lastName) {
      document.getElementById("lastNameError").style.display = "block";
      isValid = false;
    }

    if (!validateEmail(email)) {
      document.getElementById("registerEmailError").style.display = "block";
      isValid = false;
    }

    if (password.length < 6) {
      document.getElementById("registerPasswordError").style.display = "block";
      isValid = false;
    }

    if (!validateEmail(recoveryEmail)) {
      document.getElementById("recoveryEmailError").style.display = "block";
      isValid = false;
    }

    if (email === recoveryEmail) {
      document.getElementById("recoveryEmailError").textContent =
        "Email pemulihan tidak boleh sama dengan email utama";
      document.getElementById("recoveryEmailError").style.display = "block";
      isValid = false;
    }

    if (!isValid) {
      showToast(
        "error",
        "Form Tidak Lengkap",
        "Silakan lengkapi semua field yang diperlukan"
      );
      return;
    }

    showToast("info", "Mendaftar...", "Sedang membuat akun baru untuk Anda");

    try {
      const formData = new FormData();
      formData.append("firstName", firstName);
      formData.append("lastName", lastName);
      formData.append("email", email);
      formData.append("password", password);
      formData.append("recoveryEmail", recoveryEmail);

      const response = await fetch("auth/register.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showToast(
          "success",
          "Pendaftaran Berhasil! 🎉",
          "Akun Anda telah dibuat. Silakan login"
        );
        closeRegister();
        document.getElementById("registerForm").reset();

        setTimeout(() => {
          openLogin();
        }, 1000);
      } else {
        showToast("error", "Pendaftaran Gagal", result.message);
      }
    } catch (error) {
      showToast(
        "error",
        "Terjadi Kesalahan",
        "Tidak dapat terhubung ke server. Silakan coba lagi."
      );
      console.error("Registration error:", error);
    }
  });

document.getElementById("showRegister").addEventListener("click", () => {
  closeLogin();
  openRegister();
});

document.getElementById("showLogin").addEventListener("click", () => {
  closeRegister();
  openLogin();
});

function showToast(type, title, message, duration = 4000) {
  let container = document.getElementById("toastContainer");
  if (!container) {
    container = document.createElement("div");
    container.id = "toastContainer";
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  const iconMap = {
    success: "fa-check-circle",
    error: "fa-exclamation-circle",
    info: "fa-info-circle",
    warning: "fa-exclamation-triangle",
  };

  toast.innerHTML = `
    <div class="toast-icon">
      <i class="fas ${iconMap[type]}"></i>
    </div>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      <div class="toast-message">${message}</div>
    </div>
    <button class="toast-close" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
    <div class="toast-progress"></div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = "slideOut 0.3s ease-out forwards";
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

window.addEventListener("load", () => {
  createParticles();
  loadPopularMovies();
  loadAllMovies();
  window.scrollTo(0, 0);
});

// ===== USER PROFILE DROPDOWN (CLICK TOGGLE) =====
document.addEventListener("DOMContentLoaded", () => {
  const userProfile = document.querySelector(".user-profile");
  const userDropdown = document.querySelector(".user-dropdown");

  if (userProfile && userDropdown) {
    // Toggle dropdown saat klik user avatar
    userProfile.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("active");
    });

    // Close dropdown saat klik di luar
    document.addEventListener("click", (e) => {
      if (!userProfile.contains(e.target)) {
        userDropdown.classList.remove("active");
      }
    });

    // Prevent close saat klik di dalam dropdown
    userDropdown.addEventListener("click", (e) => {
      e.stopPropagation();
    });
  }
});

// ===== LOAD PRICING PLANS =====
async function loadPricingPlans() {
  try {
    const response = await fetch('API/subscription/get_plans.php');
    const plans = await response.json();

    const container = document.getElementById('pricingCards');
    
    if (!container) return; // Kalau gak ada container (admin view), skip

    if (!plans || plans.length === 0) {
      container.innerHTML = '<div class="loading"><p>Tidak ada paket tersedia</p></div>';
      return;
    }

    const iconMap = {
      'Basic': 'fa-play-circle',
      'Standard': 'fa-star',
      'Premium': 'fa-crown'
    };

    container.innerHTML = plans.map(plan => {
      const features = plan.features.split(',');
      const isRecommended = plan.name === 'Standard';

      return `
        <div class="pricing-card ${isRecommended ? 'recommended' : ''}">
          ${isRecommended ? '<div class="recommended-badge">🔥 Terpopuler</div>' : ''}
          
          <div class="plan-icon">
            <i class="fas ${iconMap[plan.name] || 'fa-film'}"></i>
          </div>

          <h3 class="plan-name">${plan.name}</h3>
          <div class="plan-price">
            Rp${parseInt(plan.price).toLocaleString('id-ID')}
            <span>/bulan</span>
          </div>
          <p class="plan-duration">${plan.duration_days} hari akses</p>

          <ul class="plan-features">
            ${features.map(feature => `
              <li>
                <i class="fas fa-check-circle"></i>
                <span>${feature.trim()}</span>
              </li>
            `).join('')}
          </ul>

          <button class="subscribe-btn" onclick="subscribePlan(${plan.id}, '${plan.name}', ${plan.price})">
            Berlangganan Sekarang
          </button>
        </div>
      `;
    }).join('');
  } catch (error) {
    console.error('Error loading pricing plans:', error);
    const container = document.getElementById('pricingCards');
    if (container) {
      container.innerHTML = '<div class="loading"><p>Gagal memuat paket. Silakan refresh halaman.</p></div>';
    }
  }
}

// ===== SUBSCRIBE TO PLAN (ULTRA FAST VERSION) =====
function subscribePlan(planId, planName, price) {
  // Disable button untuk prevent double click
  const buttons = document.querySelectorAll('.subscribe-btn');
  buttons.forEach(btn => btn.disabled = true);
  
  // Show loading toast
  showToast('info', 'Processing...', 'Mohon tunggu');
  
  // Check login + redirect (NO DELAY!)
  fetch('auth/check_login.php', {
    method: 'GET',
    cache: 'no-cache',
    headers: {
      'Cache-Control': 'no-cache'
    }
  })
    .then(res => {
      if (!res.ok) throw new Error('Network error');
      return res.json();
    })
    .then(data => {
      if (!data.logged_in) {
        showToast('error', 'Login Required', 'Silakan login terlebih dahulu!');
        buttons.forEach(btn => btn.disabled = false);
        setTimeout(() => openLogin(), 300);
        return;
      }

      // LANGSUNG REDIRECT (NO setTimeout!)
      window.location.href = `payment.php?plan_id=${planId}&plan_name=${encodeURIComponent(planName)}&price=${price}`;
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('error', 'Error', 'Gagal terhubung ke server. Coba lagi.');
      buttons.forEach(btn => btn.disabled = false);
    });
}

// Load pricing saat page load (kalau ada section pricing)
window.addEventListener('load', () => {
  createParticles();
  loadPopularMovies();
  loadAllMovies();
  
  // Load pricing kalau section ada (bukan admin)
  if (document.getElementById('pricingCards')) {
    loadPricingPlans();
  }
  
  window.scrollTo(0, 0);
});
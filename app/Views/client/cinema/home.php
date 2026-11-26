<?php
/**
 * app/Views/client/cinema/home.php - Trang chủ Đặt Vé Xem Phim MovieGo
 * Kết nối CSDL thật: Phim đang chiếu, Phim sắp chiếu, Tìm kiếm & Lọc thể loại
 */
$pageTitle = 'MovieGo - Trang Chủ Đặt Vé Xem Phim';
include __DIR__ . '/layouts/header.php';

$movies        = $movies ?? [];
$nowShowing    = $nowShowing ?? [];
$comingSoon    = $comingSoon ?? [];
$featured      = $featured ?? (!empty($movies) ? $movies[0] : null);
$bannerMovies  = $bannerMovies ?? ($featured ? [$featured] : []);
$genresList    = $genresList ?? [];
$search        = $search ?? '';
$selectedGenre = $selectedGenre ?? '';
?>

<!-- ─── Hero Carousel Section ─── -->
<?php if (!empty($bannerMovies)): ?>
<section id="hero-carousel" class="relative h-[500px] sm:h-[580px] overflow-hidden select-none">
  <?php foreach ($bannerMovies as $idx => $slide):
    $isActive = ($idx === 0);
  ?>
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out <?= $isActive ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' ?>" data-slide="<?= $idx ?>">
      <img src="<?= htmlspecialchars($slide['backdrop']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>" class="absolute inset-0 w-full h-full object-cover">
      <!-- Gradient overlays -->
      <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/85 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-gray-950/40"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center pb-8">
        <div class="max-w-xl space-y-4">
          <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/20 border border-amber-500/40 px-3 py-1 text-xs font-semibold text-amber-400">
              <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
              <?= number_format($slide['rating'], 1) ?>/10
            </span>
            <span class="rounded-full bg-red-500/20 border border-red-500/40 px-2.5 py-0.5 text-xs font-bold text-red-400">
              <?= htmlspecialchars($slide['ageRating']) ?>
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold border <?= $slide['status'] === 'now_showing' ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-blue-500/15 border-blue-500/40 text-blue-300' ?>">
              <?= $slide['status'] === 'now_showing' ? 'Đang chiếu' : 'Sắp chiếu' ?>
            </span>
          </div>

          <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight">
            <?= htmlspecialchars($slide['title']) ?>
          </h1>

          <div class="flex flex-wrap gap-2 text-xs text-gray-300">
            <?php foreach ($slide['genre'] as $g): ?>
              <span class="rounded-full bg-gray-800/80 border border-gray-700 px-3 py-1"><?= htmlspecialchars($g) ?></span>
            <?php endforeach; ?>
            <span class="flex items-center gap-1 text-gray-400 py-1">
              <i data-lucide="clock" class="w-3.5 h-3.5"></i> <?= (int)$slide['duration'] ?> phút
            </span>
          </div>

          <p class="text-gray-300 text-sm leading-relaxed line-clamp-3">
            <?= htmlspecialchars($slide['description']) ?>
          </p>

          <div class="flex items-center gap-3 pt-2">
            <a href="/cinema/detail?id=<?= $slide['id'] ?>" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold px-6 py-3 text-sm transition-all shadow-lg shadow-amber-500/20">
              <i data-lucide="ticket" class="w-4 h-4"></i>
              Đặt vé ngay
            </a>
            <a href="/cinema/detail?id=<?= $slide['id'] ?>" class="inline-flex items-center gap-2 rounded-xl bg-gray-900/80 hover:bg-gray-800 border border-gray-700 text-white font-medium px-5 py-3 text-sm transition-colors">
              <i data-lucide="play" class="w-4 h-4 text-amber-400"></i>
              Xem thông tin
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (count($bannerMovies) > 1): ?>
    <!-- Nút điều hướng Trái / Phải -->
    <button type="button" onclick="prevHeroSlide()" aria-label="Phim trước"
            class="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-gray-950/60 hover:bg-amber-500 text-white hover:text-gray-950 border border-gray-700/70 items-center justify-center backdrop-blur transition-all">
      <i data-lucide="chevron-left" class="w-5 h-5"></i>
    </button>
    <button type="button" onclick="nextHeroSlide()" aria-label="Phim tiếp theo"
            class="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-gray-950/60 hover:bg-amber-500 text-white hover:text-gray-950 border border-gray-700/70 items-center justify-center backdrop-blur transition-all">
      <i data-lucide="chevron-right" class="w-5 h-5"></i>
    </button>

    <!-- Chỉ báo các slide (Dots) -->
    <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
      <?php foreach ($bannerMovies as $idx => $slide): ?>
        <button type="button" onclick="goToHeroSlide(<?= $idx ?>)" aria-label="Chuyển tới <?= htmlspecialchars($slide['title']) ?>"
                class="hero-dot h-2 rounded-full transition-all duration-300 <?= $idx === 0 ? 'w-7 bg-amber-400' : 'w-2 bg-gray-500/70 hover:bg-gray-300' ?>"
                data-dot="<?= $idx ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- ─── Search & Filter Bar ─── -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-7 relative z-20">
  <div class="bg-gray-900/90 backdrop-blur-md border border-gray-800 rounded-2xl p-4 shadow-xl">
    <form action="/cinema" method="get" class="flex flex-col md:flex-row gap-3">
      <!-- Input tìm kiếm -->
      <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên phim, diễn viên, nội dung..."
               class="w-full bg-gray-950 border border-gray-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 transition-colors">
      </div>

      <!-- Lọc thể loại -->
      <div class="w-full md:w-56">
        <select name="genre" onchange="this.form.submit()"
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-300 focus:outline-none focus:border-amber-500 transition-colors">
          <option value="">Tất cả thể loại</option>
          <?php foreach ($genresList as $gn): ?>
            <option value="<?= htmlspecialchars($gn['name']) ?>" <?= $selectedGenre === $gn['name'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($gn['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nút tìm kiếm -->
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-bold flex items-center justify-center gap-2 transition-colors">
        <i data-lucide="filter" class="w-4 h-4"></i>
        <span>Lọc phim</span>
      </button>

      <?php if ($search !== '' || $selectedGenre !== ''): ?>
        <a href="/cinema" class="px-4 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-medium flex items-center justify-center transition-colors">
          Bỏ lọc
        </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- ─── Movies Grid Section ─── -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" id="now-showing">
  <span id="coming-soon" class="block -mt-20 pt-20 pointer-events-none"></span>
  <!-- Tabs bar -->
  <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-8">
    <div class="flex gap-4 sm:gap-8">
      <button type="button" id="tab-now" onclick="switchTab('now_showing')" class="text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-amber-500 text-amber-400 transition-colors">
        Phim đang chiếu (<?= count($nowShowing) ?>)
      </button>
      <button type="button" id="tab-soon" onclick="switchTab('coming_soon')" class="text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-transparent text-gray-400 hover:text-white transition-colors">
        Phim sắp chiếu (<?= count($comingSoon) ?>)
      </button>
    </div>
    <span class="text-xs text-gray-500 hidden sm:inline">Dữ liệu cập nhật theo thời gian thực</span>
  </div>

  <?php if (empty($movies)): ?>
    <div class="text-center py-16 bg-gray-900/40 rounded-3xl border border-gray-800">
      <i data-lucide="film" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
      <p class="text-gray-400 text-sm">Không tìm thấy phim nào phù hợp với tìm kiếm của bạn.</p>
      <a href="/cinema" class="inline-block mt-4 text-xs font-bold text-amber-400 hover:underline">&larr; Xem tất cả phim</a>
    </div>
  <?php else: ?>
    <!-- Movies Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6" id="movie-grid">
      <?php foreach ($movies as $m): ?>
        <article class="movie-card group bg-gray-900/60 rounded-2xl overflow-hidden border border-gray-800 hover:border-amber-500/40 transition-all duration-300 flex flex-col" data-status="<?= $m['status'] ?>">
          <!-- Poster container -->
          <a href="/cinema/detail?id=<?= $m['id'] ?>" class="relative aspect-[2/3] overflow-hidden bg-gray-800 block">
            <img src="<?= htmlspecialchars($m['poster']) ?>" alt="<?= htmlspecialchars($m['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
            
            <div class="absolute top-2.5 left-2.5 bg-gray-950/80 backdrop-blur px-2 py-0.5 rounded-md text-[11px] font-bold text-amber-400 flex items-center gap-1">
              <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
              <?= number_format($m['rating'], 1) ?>
            </div>

            <div class="absolute top-2.5 right-2.5 bg-red-600/90 text-white px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase">
              <?= htmlspecialchars($m['ageRating']) ?>
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center p-4">
              <span class="w-full py-2 bg-amber-500 text-gray-950 font-bold text-xs rounded-xl text-center shadow-lg">
                Xem suất chiếu
              </span>
            </div>
          </a>

          <!-- Content -->
          <div class="p-4 flex-1 flex flex-col justify-between">
            <div>
              <p class="text-[11px] text-gray-400 mb-1 truncate"><?= htmlspecialchars(implode(', ', $m['genre'])) ?></p>
              <h3 class="font-bold text-sm sm:text-base text-white group-hover:text-amber-400 transition-colors line-clamp-1">
                <a href="/cinema/detail?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></a>
              </h3>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400 pt-3 border-t border-gray-800/80 mt-3">
              <span class="flex items-center gap-1">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                <?= (int)$m['duration'] ?>'
              </span>
              <a href="/cinema/detail?id=<?= $m['id'] ?>" class="text-amber-400 hover:underline font-medium">
                Đặt vé &rarr;
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Filter Script -->
<script>
  function switchTab(status) {
    const tabNow = document.getElementById('tab-now');
    const tabSoon = document.getElementById('tab-soon');
    const cards = document.querySelectorAll('.movie-card');

    if (!tabNow || !tabSoon) return;

    if (status === 'now_showing') {
      tabNow.className = 'text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-amber-500 text-amber-400 transition-colors';
      tabSoon.className = 'text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-transparent text-gray-400 hover:text-white transition-colors';
    } else {
      tabSoon.className = 'text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-amber-500 text-amber-400 transition-colors';
      tabNow.className = 'text-lg sm:text-xl font-bold pb-4 -mb-4 border-b-2 border-transparent text-gray-400 hover:text-white transition-colors';
    }

    cards.forEach(card => {
      if (card.getAttribute('data-status') === status) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function syncTabFromHashOrResult() {
    if (window.location.hash === '#coming-soon') {
      switchTab('coming_soon');
      return;
    }
    if (window.location.hash === '#now-showing') {
      switchTab('now_showing');
      return;
    }
    const defaultTab = <?= (empty($nowShowing) && !empty($comingSoon)) ? "'coming_soon'" : "'now_showing'" ?>;
    switchTab(defaultTab);
  }

  // Khởi tạo tab mặc định & lắng nghe đổi hash (#coming-soon / #now-showing)
  document.addEventListener('DOMContentLoaded', syncTabFromHashOrResult);
  window.addEventListener('hashchange', syncTabFromHashOrResult);

  // ── Hero Banner Auto-Slider ──
  const heroSlides = document.querySelectorAll('.hero-slide');
  const heroDots   = document.querySelectorAll('.hero-dot');
  let heroIndex    = 0;
  let heroTimer    = null;

  function renderHeroSlide(index) {
    if (heroSlides.length <= 1) return;
    heroIndex = (index + heroSlides.length) % heroSlides.length;

    heroSlides.forEach((slide, i) => {
      if (i === heroIndex) {
        slide.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
        slide.classList.add('opacity-100', 'z-10');
      } else {
        slide.classList.remove('opacity-100', 'z-10');
        slide.classList.add('opacity-0', 'z-0', 'pointer-events-none');
      }
    });

    heroDots.forEach((dot, i) => {
      dot.className = 'hero-dot h-2 rounded-full transition-all duration-300 ' +
        (i === heroIndex ? 'w-7 bg-amber-400' : 'w-2 bg-gray-500/70 hover:bg-gray-300');
    });
  }

  function startHeroAutoPlay() {
    if (heroSlides.length <= 1) return;
    stopHeroAutoPlay();
    heroTimer = setInterval(() => renderHeroSlide(heroIndex + 1), 5000);
  }

  function stopHeroAutoPlay() {
    if (heroTimer) {
      clearInterval(heroTimer);
      heroTimer = null;
    }
  }

  function nextHeroSlide() {
    renderHeroSlide(heroIndex + 1);
    startHeroAutoPlay();
  }

  function prevHeroSlide() {
    renderHeroSlide(heroIndex - 1);
    startHeroAutoPlay();
  }

  function goToHeroSlide(idx) {
    renderHeroSlide(idx);
    startHeroAutoPlay();
  }

  const heroSection = document.getElementById('hero-carousel');
  if (heroSection && heroSlides.length > 1) {
    startHeroAutoPlay();
    heroSection.addEventListener('mouseenter', stopHeroAutoPlay);
    heroSection.addEventListener('mouseleave', startHeroAutoPlay);

    let touchStartX = 0;
    heroSection.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    heroSection.addEventListener('touchend', e => {
      const diff = e.changedTouches[0].screenX - touchStartX;
      if (Math.abs(diff) > 40) {
        diff < 0 ? nextHeroSlide() : prevHeroSlide();
      }
    }, { passive: true });
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

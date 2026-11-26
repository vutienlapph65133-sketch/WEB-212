<?php
/**
 * app/Views/client/cinema/detail.php - Chi tiết phim & Suất chiếu thực tế từ CSDL
 */
$pageTitle = ($movie['title'] ?? 'Chi Tiết Phim') . ' - MovieGo';
include __DIR__ . '/layouts/header.php';

$movie     = $movie ?? [];
$cinemas   = $cinemas ?? [];
$showtimes = $showtimes ?? [];
$firstCinemaId = !empty($cinemas) ? $cinemas[0]['id'] : 1;
?>

<!-- ─── Movie Banner ─── -->
<div class="relative min-h-[380px] sm:min-h-[460px] overflow-hidden">
  <img src="<?= htmlspecialchars($movie['backdrop']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
  <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/80 to-transparent"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/70 to-transparent"></div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 flex flex-col md:flex-row gap-8 items-start md:items-end">
    <!-- Poster -->
    <div class="w-44 sm:w-56 shrink-0 rounded-2xl overflow-hidden shadow-2xl border-2 border-gray-700/80 bg-gray-900">
      <img src="<?= htmlspecialchars($movie['poster']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-auto object-cover">
    </div>

    <!-- Info -->
    <div class="flex-1 space-y-3">
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full bg-red-600 text-white font-extrabold text-xs"><?= htmlspecialchars($movie['ageRating']) ?></span>
        <span class="flex items-center gap-1 text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2.5 py-0.5 rounded-full font-bold">
          <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
          <?= number_format($movie['rating'], 1) ?>/10
        </span>
        <span class="text-xs text-gray-400">• <?= (int)$movie['duration'] ?> phút</span>
        <span class="text-xs text-gray-400">• <?= htmlspecialchars($movie['language']) ?></span>
      </div>

      <h1 class="text-2xl sm:text-4xl font-black text-white"><?= htmlspecialchars($movie['title']) ?></h1>

      <div class="flex flex-wrap gap-2 text-xs">
        <?php foreach ($movie['genre'] as $g): ?>
          <span class="rounded-full bg-gray-800/80 border border-gray-700 px-3 py-1 text-gray-300"><?= htmlspecialchars($g) ?></span>
        <?php endforeach; ?>
      </div>

      <p class="text-xs sm:text-sm text-gray-300 max-w-2xl leading-relaxed">
        <?= htmlspecialchars($movie['description']) ?>
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-400 pt-2 border-t border-gray-800/80">
        <div><strong class="text-white">Đạo diễn:</strong> <?= htmlspecialchars($movie['director']) ?></div>
        <div><strong class="text-white">Khởi chiếu:</strong> <?= htmlspecialchars($movie['releaseDate']) ?></div>
        <div class="sm:col-span-2"><strong class="text-white">Diễn viên:</strong> <?= htmlspecialchars(implode(', ', $movie['cast'])) ?></div>
      </div>

      <?php if (!empty($movie['trailerUrl'])): ?>
        <div class="pt-2">
          <button type="button" onclick="openTrailerModal()" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold px-5 py-2.5 text-xs transition-all shadow-lg shadow-amber-500/20">
            <i data-lucide="play" class="w-4 h-4"></i>
            Xem Trailer chính thức
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ─── Showtimes Section ─── -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="showtimes-section">
  <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
    <i data-lucide="calendar" class="w-5 h-5 text-amber-400"></i>
    Lịch Chiếu & Suất Chiếu
  </h2>

  <!-- Cinema selector buttons -->
  <div class="flex flex-wrap gap-2 mb-8" id="cinema-tabs">
    <?php foreach ($cinemas as $idx => $c): ?>
      <button type="button" onclick="selectCinema(<?= $c['id'] ?>)" id="cinema-btn-<?= $c['id'] ?>" 
              class="cinema-tab-btn px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all <?= $c['id'] === $firstCinemaId ? 'bg-amber-500 text-gray-950 border-amber-500 shadow-md' : 'bg-gray-900 border-gray-800 text-gray-300 hover:border-gray-700' ?>">
        <?= htmlspecialchars($c['name']) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Showtime Cards Container -->
  <div class="space-y-6">
    <?php foreach ($cinemas as $c): 
      $cinemaShows = array_filter($showtimes, fn($s) => $s['cinemaId'] === (int)$c['id']);
    ?>
      <div id="cinema-content-<?= $c['id'] ?>" class="cinema-schedule bg-gray-900/60 rounded-2xl p-6 border border-gray-800">
        <div class="flex items-start justify-between border-b border-gray-800 pb-4 mb-4">
          <div>
            <h3 class="font-bold text-base text-white"><?= htmlspecialchars($c['name']) ?></h3>
            <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-400"></i>
              <?= htmlspecialchars($c['address']) ?> (<?= htmlspecialchars($c['city']) ?>)
            </p>
          </div>
          <span class="text-xs <?= !empty($cinemaShows) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-800 text-gray-400' ?> px-2.5 py-1 rounded-full font-medium">
            <?= !empty($cinemaShows) ? 'Có ' . count($cinemaShows) . ' suất chiếu' : 'Chưa có suất' ?>
          </span>
        </div>

        <?php if (empty($cinemaShows)): ?>
          <div class="py-8 text-center text-xs text-gray-500">
            Hiện tại cụm rạp này chưa có suất chiếu cho phim này. Vui lòng chọn cụm rạp khác.
          </div>
        <?php else: ?>
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Chọn suất chiếu phù hợp:</p>
          
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($cinemaShows as $show): ?>
              <a href="/cinema/seats?showtime_id=<?= $show['id'] ?>" class="group p-3.5 rounded-xl bg-gray-950 border border-gray-800 hover:border-amber-500 hover:scale-[1.02] transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-lg font-black text-amber-400 group-hover:text-amber-300"><?= $show['time'] ?></span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $show['format'] === 'IMAX' ? 'bg-indigo-600 text-white' : ($show['format'] === '3D' ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-300') ?>">
                    <?= $show['format'] ?>
                  </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-400">
                  <span><?= htmlspecialchars($show['screen']) ?> • <?= $show['date'] ?></span>
                  <span class="font-semibold text-white"><?= number_format($show['price'], 0, ',', '.') ?>₫</span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
  function selectCinema(cinemaId) {
    document.querySelectorAll('.cinema-tab-btn').forEach(btn => {
      btn.className = 'cinema-tab-btn px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all bg-gray-900 border-gray-800 text-gray-300 hover:border-gray-700';
    });
    const activeBtn = document.getElementById('cinema-btn-' + cinemaId);
    if (activeBtn) {
      activeBtn.className = 'cinema-tab-btn px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all bg-amber-500 text-gray-950 border-amber-500 shadow-md';
    }

    document.querySelectorAll('.cinema-schedule').forEach(sec => {
      sec.style.display = 'none';
    });
    const target = document.getElementById('cinema-content-' + cinemaId);
    if (target) {
      target.style.display = 'block';
    }
  }

  // Mặc định hiển thị rạp đầu tiên
  document.addEventListener('DOMContentLoaded', () => {
    selectCinema(<?= $firstCinemaId ?>);
  });

  function openTrailerModal() {
    const url = <?= json_encode($movie['trailerUrl'] ?? '') ?>;
    if (!url) return;
    const container = document.getElementById('trailer-player');
    let embedUrl = '';
    const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
    if (ytMatch) {
      embedUrl = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
      container.innerHTML = '<iframe class="w-full h-full rounded-2xl" src="' + embedUrl + '" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
    } else if (/^https?:\/\//i.test(url)) {
      container.innerHTML = '<video class="w-full h-full rounded-2xl bg-black" controls autoplay src="' + url.replace(/"/g, '&quot;') + '"></video>';
    }
    document.getElementById('trailer-modal').classList.remove('hidden');
  }

  function closeTrailerModal() {
    document.getElementById('trailer-player').innerHTML = '';
    document.getElementById('trailer-modal').classList.add('hidden');
  }
</script>

<!-- Trailer Modal -->
<div id="trailer-modal" onclick="if(event.target===this) closeTrailerModal()" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-gray-900 border border-gray-800 rounded-3xl max-w-3xl w-full p-4 sm:p-6 shadow-2xl space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-bold text-white">Trailer: <?= htmlspecialchars($movie['title'] ?? '') ?></h3>
      <button type="button" onclick="closeTrailerModal()" class="p-1.5 rounded-lg hover:bg-gray-800 text-gray-400">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <div id="trailer-player" class="aspect-video w-full bg-black rounded-2xl overflow-hidden"></div>
  </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

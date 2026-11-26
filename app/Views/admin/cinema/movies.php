<?php
/**
 * app/Views/admin/cinema/movies.php - Quản lý Phim MovieGo Admin kết nối CSDL thật
 */
$pageTitle = 'Quản Lý Phim - MovieGo Admin';
$activeMenu = 'movies';
include __DIR__ . '/layouts/sidebar.php';

$movies     = $movies ?? [];
$genresList = $genresList ?? [];
$msg        = $msg ?? '';
$error      = $error ?? '';
?>

<div class="space-y-6">
  <?php if (!empty($msg)): ?>
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
      <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
      <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <!-- Top bar -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Phim Chiếu (<?= count($movies) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Danh sách các bộ phim đang và sắp công chiếu lưu trực tiếp trong CSDL.</p>
    </div>
    <button type="button" onclick="openMovieModal('add')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> Thêm phim mới
    </button>
  </div>

  <!-- Filter & Search -->
  <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
      <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
      <input type="text" id="movie-search" oninput="filterMovies()" placeholder="Tìm kiếm tên phim..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs text-gray-800 focus:outline-none focus:border-blue-600">
    </div>
    <select id="movie-status-filter" onchange="filterMovies()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs text-gray-700 focus:outline-none focus:border-blue-600">
      <option value="all">Tất cả trạng thái</option>
      <option value="now_showing">Đang chiếu</option>
      <option value="coming_soon">Sắp chiếu</option>
      <option value="stopped">Ngừng chiếu</option>
    </select>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs" id="movies-table">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Poster</th>
            <th class="py-3 px-4">Tên phim</th>
            <th class="py-3 px-4">Thể loại</th>
            <th class="py-3 px-4">Thời lượng</th>
            <th class="py-3 px-4">Đánh giá</th>
            <th class="py-3 px-4">Suất chiếu</th>
            <th class="py-3 px-4">Trạng thái</th>
            <th class="py-3 px-4 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($movies as $mv): ?>
            <tr class="movie-row hover:bg-gray-50/80 transition-colors" data-title="<?= strtolower(htmlspecialchars($mv['title'])) ?>" data-status="<?= $mv['status'] ?>">
              <td class="py-3 px-4">
                <img src="<?= htmlspecialchars($mv['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500') ?>" class="w-10 h-14 object-cover rounded-lg border border-gray-200 bg-gray-100">
              </td>
              <td class="py-3 px-4 font-bold text-gray-900">
                <a href="/cinema/detail?id=<?= $mv['id'] ?>" target="_blank" class="hover:text-blue-600 flex items-center gap-1">
                  <span><?= htmlspecialchars($mv['title']) ?></span>
                  <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                </a>
              </td>
              <td class="py-3 px-4 text-gray-500"><?= htmlspecialchars($mv['genre_names'] ?: 'Điện ảnh') ?></td>
              <td class="py-3 px-4 text-gray-600"><?= (int)$mv['duration'] ?> phút</td>
              <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold text-[11px] border border-amber-100">
                  ★ <?= number_format($mv['rating'], 1) ?>
                </span>
              </td>
              <td class="py-3 px-4 font-semibold text-gray-700">
                <?= (int)($mv['showtimes_count'] ?? 0) ?> suất
              </td>
              <td class="py-3 px-4">
                <?php if ($mv['status'] === 'now_showing'): ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Đang chiếu</span>
                <?php elseif ($mv['status'] === 'coming_soon'): ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Sắp chiếu</span>
                <?php else: ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">Ngừng chiếu</span>
                <?php endif; ?>
              </td>
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1">
                  <!-- Sửa -->
                  <button type="button" 
                          onclick='openEditModal(<?= json_encode($mv, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                          class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Chỉnh sửa">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>
                  <!-- Xóa -->
                  <form method="post" action="/admin/cinema/movies" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim này khỏi CSDL?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $mv['id'] ?>">
                    <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa phim">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ─── Modal Thêm / Chỉnh Sửa Phim (Form POST lưu vào CSDL) ─── -->
<div id="movie-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="modal-title">Thêm phim mới</h3>
      <button type="button" onclick="closeMovieModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="post" action="/admin/cinema/movies" id="movie-form" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="form-action" value="add">
      <input type="hidden" name="id" id="form-id" value="">

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Tên phim <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="form-title" required placeholder="Nhập tên phim..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Thời lượng (phút)</label>
          <input type="number" name="duration" id="form-duration" value="120" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Đánh giá (0-10)</label>
          <input type="number" step="0.1" min="0" max="10" name="rating" id="form-rating" value="8.5" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Ngày khởi chiếu</label>
          <input type="date" name="release_date" id="form-release-date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Độ tuổi</label>
          <select name="age_rating" id="form-age-rating" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
            <option value="P">P - Mọi độ tuổi</option>
            <option value="T13">T13 - Trên 13 tuổi</option>
            <option value="T16">T16 - Trên 16 tuổi</option>
            <option value="T18">T18 - Trên 18 tuổi</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Trạng thái công chiếu</label>
          <select name="status" id="form-status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
            <option value="now_showing">Đang chiếu (Now Showing)</option>
            <option value="coming_soon">Sắp chiếu (Coming Soon)</option>
            <option value="stopped">Ngừng chiếu</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Đạo diễn</label>
          <input type="text" name="director" id="form-director" placeholder="VD: Joe Russo" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Ngôn ngữ</label>
          <input type="text" name="language" id="form-language" value="Tiếng Anh - Phụ đề Tiếng Việt" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Diễn viên (cách nhau bằng dấu phẩy)</label>
        <input type="text" name="cast_list" id="form-cast" placeholder="VD: Robert Downey Jr., Tom Holland" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <?php if (!empty($genresList)): ?>
      <div>
        <label class="block font-semibold text-gray-700 mb-1.5">Thể loại</label>
        <div class="flex flex-wrap gap-2 bg-gray-50 border border-gray-200 rounded-xl p-2.5">
          <?php foreach ($genresList as $gn): ?>
            <label class="inline-flex items-center gap-1.5 text-[11px] text-gray-700 cursor-pointer">
              <input type="checkbox" name="genres[]" value="<?= (int)$gn['id'] ?>" class="genre-checkbox rounded text-blue-600">
              <span><?= htmlspecialchars($gn['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Link ảnh Poster (URL)</label>
          <input type="url" name="poster" id="form-poster" placeholder="https://..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Link ảnh Nền / Backdrop</label>
          <input type="url" name="backdrop" id="form-backdrop" placeholder="https://..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Link Trailer (YouTube hoặc MP4 URL)</label>
        <input type="url" name="trailer_url" id="form-trailer" placeholder="https://www.youtube.com/watch?v=..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Mô tả tóm tắt nội dung</label>
        <textarea name="description" id="form-description" rows="3" placeholder="Nhập tóm tắt kịch bản phim..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600"></textarea>
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closeMovieModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Lưu vào CSDL</button>
      </div>
    </form>
  </div>
</div>

<script>
  function filterMovies() {
    const q = document.getElementById('movie-search').value.toLowerCase().trim();
    const st = document.getElementById('movie-status-filter').value;
    document.querySelectorAll('.movie-row').forEach(row => {
      const matchTitle = row.getAttribute('data-title').includes(q);
      const matchStatus = (st === 'all' || row.getAttribute('data-status') === st);
      row.style.display = (matchTitle && matchStatus) ? '' : 'none';
    });
  }

  function openMovieModal(mode) {
    document.getElementById('modal-title').innerText = 'Thêm phim mới vào CSDL';
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('form-title').value = '';
    document.getElementById('form-duration').value = '120';
    document.getElementById('form-rating').value = '8.0';
    document.getElementById('form-release-date').value = '';
    document.getElementById('form-age-rating').value = 'P';
    document.getElementById('form-status').value = 'now_showing';
    document.getElementById('form-director').value = '';
    document.getElementById('form-language').value = 'Tiếng Anh - Phụ đề Tiếng Việt';
    document.getElementById('form-cast').value = '';
    document.getElementById('form-poster').value = '';
    document.getElementById('form-backdrop').value = '';
    document.getElementById('form-trailer').value = '';
    document.getElementById('form-description').value = '';
    document.querySelectorAll('.genre-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('movie-modal').classList.remove('hidden');
  }

  function openEditModal(mv) {
    document.getElementById('modal-title').innerText = 'Chỉnh sửa: ' + mv.title;
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = mv.id;
    document.getElementById('form-title').value = mv.title || '';
    document.getElementById('form-duration').value = mv.duration || 120;
    document.getElementById('form-rating').value = mv.rating || 8.0;
    document.getElementById('form-release-date').value = mv.release_date || '';
    document.getElementById('form-age-rating').value = mv.age_rating || 'P';
    document.getElementById('form-status').value = mv.status || 'now_showing';
    document.getElementById('form-director').value = mv.director || '';
    document.getElementById('form-language').value = mv.language || '';
    document.getElementById('form-cast').value = mv.cast_list || '';
    document.getElementById('form-poster').value = mv.poster || '';
    document.getElementById('form-backdrop').value = mv.backdrop || '';
    document.getElementById('form-trailer').value = mv.trailer_url || '';
    document.getElementById('form-description').value = mv.description || '';
    const gIds = (mv.genre_ids ? String(mv.genre_ids).split(',') : []);
    document.querySelectorAll('.genre-checkbox').forEach(cb => {
      cb.checked = gIds.includes(String(cb.value));
    });
    document.getElementById('movie-modal').classList.remove('hidden');
  }

  function closeMovieModal() {
    document.getElementById('movie-modal').classList.add('hidden');
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

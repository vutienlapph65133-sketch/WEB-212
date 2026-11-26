<?php
/**
 * app/Views/admin/cinema/showtimes.php - Quản lý Suất chiếu MovieGo Admin
 */
$pageTitle = 'Quản Lý Suất Chiếu - MovieGo Admin';
$activeMenu = 'showtimes';
include __DIR__ . '/layouts/sidebar.php';

$showtimes   = $showtimes ?? [];
$moviesList  = $moviesList ?? [];
$screensList = $screensList ?? [];
$msg         = $msg ?? '';
$error       = $error ?? '';
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

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Lịch & Suất Chiếu (<?= count($showtimes) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Sắp xếp khung giờ, định dạng phòng chiếu (2D/3D/IMAX) và giá vé trong CSDL.</p>
    </div>
    <button type="button" onclick="openShowtimeModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> Tạo suất chiếu mới
    </button>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Tên phim</th>
            <th class="py-3 px-4">Cụm rạp</th>
            <th class="py-3 px-4">Phòng chiếu</th>
            <th class="py-3 px-4">Thời gian chiếu</th>
            <th class="py-3 px-4">Định dạng</th>
            <th class="py-3 px-4">Giá vé cơ bản</th>
            <th class="py-3 px-4 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($showtimes as $st): ?>
            <tr class="hover:bg-gray-50/80 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900"><?= htmlspecialchars($st['movie_title']) ?></td>
              <td class="py-3.5 px-4 text-gray-600"><?= htmlspecialchars($st['cinema_name']) ?></td>
              <td class="py-3.5 px-4 font-semibold text-gray-800"><?= htmlspecialchars($st['screen_name']) ?></td>
              <td class="py-3.5 px-4 font-black text-blue-600 text-sm">
                <?= date('H:i - d/m/Y', strtotime($st['starts_at'])) ?>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $st['format'] === 'IMAX' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : ($st['format'] === '3D' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-gray-100 text-gray-700') ?>">
                  <?= htmlspecialchars($st['format']) ?>
                </span>
              </td>
              <td class="py-3.5 px-4 font-bold text-gray-900"><?= number_format($st['base_price'], 0, ',', '.') ?>₫</td>
              <td class="py-3.5 px-4 text-right">
                <div class="inline-flex items-center gap-1">
                  <button type="button" onclick='openEditShowtimeModal(<?= json_encode($st, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                          class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Chỉnh sửa suất chiếu">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>
                  <form method="post" action="/admin/cinema/showtimes" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa suất chiếu này?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$st['id'] ?>">
                    <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa suất chiếu">
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

<!-- Modal Tạo / Sửa Suất Chiếu -->
<div id="showtime-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="showtime-modal-title">Tạo Suất Chiếu Mới</h3>
      <button type="button" onclick="closeShowtimeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="post" action="/admin/cinema/showtimes" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="showtime-action" value="add">
      <input type="hidden" name="id" id="showtime-id" value="">

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Chọn Phim Chiếu <span class="text-red-500">*</span></label>
        <select name="movie_id" id="showtime-movie" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
          <?php foreach ($moviesList as $m): ?>
            <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Chọn Cụm Rạp & Phòng Chiếu <span class="text-red-500">*</span></label>
        <select name="screen_id" id="showtime-screen" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
          <?php foreach ($screensList as $sc): ?>
            <option value="<?= (int)$sc['id'] ?>"><?= htmlspecialchars($sc['cinema_name']) ?> - <?= htmlspecialchars($sc['screen_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Thời gian bắt đầu chiếu <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="starts_at" id="showtime-starts" required value="<?= date('Y-m-d\T19:00') ?>"
                 class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Định dạng chiếu</label>
          <select name="format" id="showtime-format" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
            <option value="2D">2D Digital</option>
            <option value="3D">3D Digital</option>
            <option value="IMAX">IMAX Laser</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Giá vé cơ bản (ghế standard) - VNĐ</label>
        <input type="number" step="5000" min="0" name="base_price" id="showtime-price" value="75000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closeShowtimeModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" id="showtime-submit-btn" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Tạo Suất Chiếu</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openShowtimeModal() {
    document.getElementById('showtime-modal-title').innerText = 'Tạo Suất Chiếu Mới';
    document.getElementById('showtime-action').value = 'add';
    document.getElementById('showtime-id').value = '';
    document.getElementById('showtime-starts').value = '<?= date('Y-m-d\T19:00') ?>';
    document.getElementById('showtime-format').value = '2D';
    document.getElementById('showtime-price').value = '75000';
    document.getElementById('showtime-submit-btn').innerText = 'Tạo Suất Chiếu';
    document.getElementById('showtime-modal').classList.remove('hidden');
  }
  function openEditShowtimeModal(st) {
    document.getElementById('showtime-modal-title').innerText = 'Chỉnh sửa suất chiếu #' + st.id;
    document.getElementById('showtime-action').value = 'edit';
    document.getElementById('showtime-id').value = st.id;
    document.getElementById('showtime-movie').value = st.movie_id;
    document.getElementById('showtime-screen').value = st.screen_id;
    document.getElementById('showtime-starts').value = st.starts_at ? st.starts_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('showtime-format').value = st.format || '2D';
    document.getElementById('showtime-price').value = st.base_price || 75000;
    document.getElementById('showtime-submit-btn').innerText = 'Lưu Thay Đổi';
    document.getElementById('showtime-modal').classList.remove('hidden');
  }
  function closeShowtimeModal() {
    document.getElementById('showtime-modal').classList.add('hidden');
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

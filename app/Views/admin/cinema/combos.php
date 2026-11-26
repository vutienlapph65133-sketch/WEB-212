<?php
/**
 * app/Views/admin/cinema/combos.php - Quản lý Combo Bắp Nước & Thể Loại Phim
 */
$pageTitle = 'Bắp Nước & Thể Loại - MovieGo Admin';
$activeMenu = 'combos';
include __DIR__ . '/layouts/sidebar.php';

$combos = $combos ?? [];
$genres = $genres ?? [];
$msg    = $msg ?? '';
$error  = $error ?? '';
?>

<div class="space-y-8">
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

  <!-- Phần 1: Combo Bắp Nước -->
  <div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Combo Bắp Nước (<?= count($combos) ?>)</h1>
        <p class="text-xs text-gray-500 mt-0.5">Thêm, sửa, bật/tắt các combo hiển thị ở màn hình chọn ghế.</p>
      </div>
      <button type="button" onclick="openComboModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm combo mới
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
            <tr>
              <th class="py-3 px-4">Icon</th>
              <th class="py-3 px-4">Tên combo</th>
              <th class="py-3 px-4">Mô tả</th>
              <th class="py-3 px-4">Giá bán</th>
              <th class="py-3 px-4">Trạng thái</th>
              <th class="py-3 px-4 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($combos as $cb): ?>
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3.5 px-4 text-xl"><?= htmlspecialchars($cb['icon'] ?: '🍿') ?></td>
                <td class="py-3.5 px-4 font-bold text-gray-900"><?= htmlspecialchars($cb['name']) ?></td>
                <td class="py-3.5 px-4 text-gray-600"><?= htmlspecialchars($cb['description'] ?: '') ?></td>
                <td class="py-3.5 px-4 font-bold text-gray-900"><?= number_format((int)$cb['price'], 0, ',', '.') ?>₫</td>
                <td class="py-3.5 px-4">
                  <?php if ($cb['is_active']): ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Đang bán</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Tạm ẩn</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <button type="button" onclick='openEditComboModal(<?= json_encode($cb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Sửa">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <form method="post" action="/admin/cinema/combos" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="toggle_combo">
                      <input type="hidden" name="id" value="<?= (int)$cb['id'] ?>">
                      <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200">
                        <?= $cb['is_active'] ? 'Ẩn' : 'Hiện' ?>
                      </button>
                    </form>
                    <form method="post" action="/admin/cinema/combos" class="inline" onsubmit="return confirm('Xóa combo này?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_combo">
                      <input type="hidden" name="id" value="<?= (int)$cb['id'] ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Xóa">
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

  <!-- Phần 2: Thể loại phim -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 pb-4">
      <div>
        <h2 class="text-base font-black text-gray-900">Thể loại Phim (<?= count($genres) ?>)</h2>
        <p class="text-xs text-gray-500">Quản lý danh sách thể loại dùng để gắn cho phim và lọc ở trang chủ.</p>
      </div>
      <form method="post" action="/admin/cinema/combos" class="flex gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_genre">
        <input type="text" name="genre_name" required placeholder="Tên thể loại mới..." class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs">+ Thêm</button>
      </form>
    </div>

    <div class="flex flex-wrap gap-2">
      <?php foreach ($genres as $g): ?>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-xs">
          <span class="font-semibold text-gray-800"><?= htmlspecialchars($g['name']) ?></span>
          <span class="text-[10px] text-gray-400">(<?= (int)$g['movie_count'] ?>)</span>
          <form method="post" action="/admin/cinema/combos" class="inline" onsubmit="return confirm('Xóa thể loại này?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_genre">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button type="submit" class="text-gray-400 hover:text-red-600 font-bold ml-1" title="Xóa thể loại">&times;</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Modal Combo -->
<div id="combo-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="combo-modal-title">Thêm Combo Mới</h3>
      <button type="button" onclick="closeComboModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form method="post" action="/admin/cinema/combos" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="combo-action" value="add_combo">
      <input type="hidden" name="id" id="combo-id" value="">

      <div class="grid grid-cols-4 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Icon</label>
          <input type="text" name="icon" id="combo-icon" value="🍿" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-center text-sm focus:outline-none focus:border-blue-600">
        </div>
        <div class="col-span-3">
          <label class="block font-semibold text-gray-700 mb-1">Tên combo <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="combo-name" required placeholder="VD: Combo Solo" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Mô tả chi tiết</label>
        <input type="text" name="description" id="combo-desc" placeholder="1 Bắp lớn + 1 Nước lớn" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Giá bán (VNĐ) <span class="text-red-500">*</span></label>
        <input type="number" name="price" id="combo-price" required min="1000" step="1000" value="79000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closeComboModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Lưu Combo</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openComboModal() {
    document.getElementById('combo-modal-title').innerText = 'Thêm Combo Mới';
    document.getElementById('combo-action').value = 'add_combo';
    document.getElementById('combo-id').value = '';
    document.getElementById('combo-icon').value = '🍿';
    document.getElementById('combo-name').value = '';
    document.getElementById('combo-desc').value = '';
    document.getElementById('combo-price').value = '79000';
    document.getElementById('combo-modal').classList.remove('hidden');
  }
  function openEditComboModal(cb) {
    document.getElementById('combo-modal-title').innerText = 'Chỉnh sửa: ' + cb.name;
    document.getElementById('combo-action').value = 'edit_combo';
    document.getElementById('combo-id').value = cb.id;
    document.getElementById('combo-icon').value = cb.icon || '🍿';
    document.getElementById('combo-name').value = cb.name || '';
    document.getElementById('combo-desc').value = cb.description || '';
    document.getElementById('combo-price').value = cb.price || 79000;
    document.getElementById('combo-modal').classList.remove('hidden');
  }
  function closeComboModal() {
    document.getElementById('combo-modal').classList.add('hidden');
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

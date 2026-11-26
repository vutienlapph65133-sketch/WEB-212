<?php
/**
 * app/Views/admin/cinema/promotions.php - Quản lý Khuyến Mãi & Voucher MovieGo Admin
 */
$pageTitle = 'Quản Lý Khuyến Mãi - MovieGo Admin';
$activeMenu = 'promotions';
include __DIR__ . '/layouts/sidebar.php';

$promotions = $promotions ?? [];
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

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Khuyến Mãi & Voucher (<?= count($promotions) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Tạo mã giảm giá và cài đặt chương trình ưu đãi cho khách hàng.</p>
    </div>
    <button type="button" onclick="openPromoModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> Tạo mã khuyến mãi mới
    </button>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Mã Voucher</th>
            <th class="py-3 px-4">Mô tả chương trình</th>
            <th class="py-3 px-4">Loại giảm</th>
            <th class="py-3 px-4">Mức giảm</th>
            <th class="py-3 px-4">Hạn dùng</th>
            <th class="py-3 px-4">Trạng thái</th>
            <th class="py-3 px-4 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($promotions)): ?>
            <tr><td colspan="7" class="py-8 text-center text-gray-400">Chưa có mã khuyến mãi nào trong hệ thống.</td></tr>
          <?php else: ?>
            <?php foreach ($promotions as $pr): ?>
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3.5 px-4 font-mono font-black text-blue-600 text-sm">
                  <?= htmlspecialchars($pr['code']) ?>
                </td>
                <td class="py-3.5 px-4 text-gray-700 max-w-xs">
                  <?= htmlspecialchars($pr['description'] ?: 'Ưu đãi đặt vé online') ?>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $pr['discount_type'] === 'percent' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' ?>">
                    <?= $pr['discount_type'] === 'percent' ? 'Theo %' : 'Số tiền cố định' ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 font-bold text-gray-900 text-sm">
                  <?= $pr['discount_type'] === 'percent' ? $pr['discount_value'] . '%' : number_format($pr['discount_value'], 0, ',', '.') . '₫' ?>
                </td>
                <td class="py-3.5 px-4 text-gray-500">
                  <?= $pr['expires_at'] ? date('d/m/Y H:i', strtotime($pr['expires_at'])) : 'Không thời hạn' ?>
                </td>
                <td class="py-3.5 px-4">
                  <?php if ($pr['is_active']): ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Đang bật</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Tắt</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Sửa -->
                    <button type="button"
                            onclick='openEditPromoModal(<?= json_encode($pr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Chỉnh sửa voucher">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <!-- Toggle Bật/Tắt -->
                    <form method="post" action="/admin/cinema/promotions" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= $pr['id'] ?>">
                      <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-colors <?= $pr['is_active'] ? 'bg-gray-50 hover:bg-gray-100 text-gray-600 border-gray-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' ?>">
                        <?= $pr['is_active'] ? 'Tắt mã' : 'Bật mã' ?>
                      </button>
                    </form>

                    <!-- Xóa -->
                    <form method="post" action="/admin/cinema/promotions" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã voucher này?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $pr['id'] ?>">
                      <button type="submit" class="p-1 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa voucher">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tạo / Chỉnh sửa Voucher -->
<div id="promo-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="promo-modal-title">Tạo Mã Khuyến Mãi Mới</h3>
      <button type="button" onclick="closePromoModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="post" action="/admin/cinema/promotions" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="promo-form-action" value="add">
      <input type="hidden" name="id" id="promo-form-id" value="">

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Mã Voucher (Code) <span class="text-red-500">*</span></label>
        <input type="text" name="code" id="promo-form-code" required placeholder="Ví dụ: TET2026, SUMMER20" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs uppercase focus:outline-none focus:border-blue-600">
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Mô tả ưu đãi</label>
        <input type="text" name="description" id="promo-form-desc" placeholder="Ví dụ: Giảm 20% cho học sinh sinh viên..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Loại giảm giá</label>
          <select name="discount_type" id="promo-form-type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
            <option value="percent">Theo phần trăm (%)</option>
            <option value="fixed">Số tiền cố định (VNĐ)</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Mức giảm <span class="text-red-500">*</span></label>
          <input type="number" name="discount_value" id="promo-form-value" required min="1" value="20" placeholder="VD: 20 hoặc 50000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Ngày hết hạn (để trống nếu không hết hạn)</label>
        <input type="datetime-local" name="expires_at" id="promo-form-expires" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closePromoModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" id="promo-form-submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Tạo Voucher</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openPromoModal() {
    document.getElementById('promo-modal-title').innerText = 'Tạo Mã Khuyến Mãi Mới';
    document.getElementById('promo-form-action').value = 'add';
    document.getElementById('promo-form-id').value = '';
    document.getElementById('promo-form-code').value = '';
    document.getElementById('promo-form-desc').value = '';
    document.getElementById('promo-form-type').value = 'percent';
    document.getElementById('promo-form-value').value = '20';
    document.getElementById('promo-form-expires').value = '';
    document.getElementById('promo-form-submit').innerText = 'Tạo Voucher';
    document.getElementById('promo-modal').classList.remove('hidden');
  }
  function openEditPromoModal(pr) {
    document.getElementById('promo-modal-title').innerText = 'Chỉnh sửa Voucher: ' + pr.code;
    document.getElementById('promo-form-action').value = 'edit';
    document.getElementById('promo-form-id').value = pr.id;
    document.getElementById('promo-form-code').value = pr.code || '';
    document.getElementById('promo-form-desc').value = pr.description || '';
    document.getElementById('promo-form-type').value = pr.discount_type || 'percent';
    document.getElementById('promo-form-value').value = pr.discount_value || 0;
    document.getElementById('promo-form-expires').value = pr.expires_at ? pr.expires_at.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo-form-submit').innerText = 'Lưu Thay Đổi';
    document.getElementById('promo-modal').classList.remove('hidden');
  }
  function closePromoModal() {
    document.getElementById('promo-modal').classList.add('hidden');
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

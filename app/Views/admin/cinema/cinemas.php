<?php
/**
 * app/Views/admin/cinema/cinemas.php - Quản lý Cụm Rạp & Phòng Chiếu MovieGo Admin
 */
$pageTitle = 'Quản Lý Cụm Rạp - MovieGo Admin';
$activeMenu = 'cinemas';
include __DIR__ . '/layouts/sidebar.php';

$cinemas = $cinemas ?? [];
$msg     = $msg ?? '';
$error   = $error ?? '';
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
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Cụm Rạp Chiếu (<?= count($cinemas) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Danh sách các cơ sở rạp, phòng chiếu và quy mô ghế lưu trong CSDL.</p>
    </div>
    <button type="button" onclick="openCinemaModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> Thêm cụm rạp mới
    </button>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Tên rạp</th>
            <th class="py-3 px-4">Địa chỉ</th>
            <th class="py-3 px-4">Khu vực / Tỉnh thành</th>
            <th class="py-3 px-4">Hotline</th>
            <th class="py-3 px-4">Số phòng chiếu</th>
            <th class="py-3 px-4">Tổng số ghế</th>
            <th class="py-3 px-4 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($cinemas as $c): ?>
            <tr class="hover:bg-gray-50/80 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="building" class="w-4 h-4 text-blue-600"></i>
                <span><?= htmlspecialchars($c['name']) ?></span>
              </td>
              <td class="py-3.5 px-4 text-gray-600"><?= htmlspecialchars($c['address']) ?></td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold text-[10px]">
                  <?= htmlspecialchars($c['city']) ?>
                </span>
              </td>
              <td class="py-3.5 px-4 font-mono text-gray-500"><?= htmlspecialchars($c['phone'] ?: '1900 6017') ?></td>
              <td class="py-3.5 px-4 font-semibold text-gray-800"><?= (int)$c['screens_count'] ?> phòng</td>
              <td class="py-3.5 px-4 font-bold text-gray-900"><?= (int)$c['seats_count'] ?> ghế</td>
              <td class="py-3.5 px-4 text-right">
                <div class="inline-flex items-center gap-1">
                  <button type="button" onclick='openScreenModal(<?= (int)$c['id'] ?>, <?= json_encode($c['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= (int)$c['screens_count'] + 1 ?>)'
                          class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[11px] font-semibold" title="Thêm phòng chiếu">
                    + Phòng
                  </button>
                  <button type="button" onclick='openEditCinemaModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                          class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Chỉnh sửa rạp">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>
                  <form method="post" action="/admin/cinema/cinemas" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cụm rạp này? Toàn bộ phòng chiếu và suất chiếu đi kèm cũng sẽ bị xóa.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa rạp">
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

<!-- Modal Thêm / Sửa Rạp -->
<div id="cinema-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="cinema-modal-title">Thêm Cụm Rạp Mới</h3>
      <button type="button" onclick="closeCinemaModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="post" action="/admin/cinema/cinemas" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="cinema-action" value="add">
      <input type="hidden" name="id" id="cinema-id" value="">

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Tên cụm rạp <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="cinema-name" required placeholder="Ví dụ: MovieGo Thủ Đức" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div>
        <label class="block font-semibold text-gray-700 mb-1">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
        <input type="text" name="address" id="cinema-address" required placeholder="Số nhà, tên đường, phường..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Tỉnh / Thành phố</label>
          <input type="text" name="city" id="cinema-city" required value="TP.HCM" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Số điện thoại</label>
          <input type="tel" name="phone" id="cinema-phone" placeholder="028 1234 5678" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
        </div>
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closeCinemaModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" id="cinema-submit-btn" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Lưu Cụm Rạp</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Thêm Phòng Chiếu -->
<div id="screen-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900" id="screen-modal-title">Thêm Phòng Chiếu</h3>
      <button type="button" onclick="closeScreenModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form method="post" action="/admin/cinema/cinemas" class="space-y-3.5 text-xs">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_screen">
      <input type="hidden" name="cinema_id" id="screen-cinema-id" value="">
      <div>
        <label class="block font-semibold text-gray-700 mb-1">Tên phòng chiếu (tự động tạo 96 ghế)</label>
        <input type="text" name="screen_name" id="screen-name-input" required placeholder="VD: Phòng 3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-600">
      </div>
      <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" onclick="closeScreenModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">Hủy</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md">Thêm Phòng</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openCinemaModal() {
    document.getElementById('cinema-modal-title').innerText = 'Thêm Cụm Rạp Mới';
    document.getElementById('cinema-action').value = 'add';
    document.getElementById('cinema-id').value = '';
    document.getElementById('cinema-name').value = '';
    document.getElementById('cinema-address').value = '';
    document.getElementById('cinema-city').value = 'TP.HCM';
    document.getElementById('cinema-phone').value = '';
    document.getElementById('cinema-submit-btn').innerText = 'Lưu Cụm Rạp';
    document.getElementById('cinema-modal').classList.remove('hidden');
  }
  function openEditCinemaModal(c) {
    document.getElementById('cinema-modal-title').innerText = 'Sửa rạp: ' + c.name;
    document.getElementById('cinema-action').value = 'edit';
    document.getElementById('cinema-id').value = c.id;
    document.getElementById('cinema-name').value = c.name || '';
    document.getElementById('cinema-address').value = c.address || '';
    document.getElementById('cinema-city').value = c.city || '';
    document.getElementById('cinema-phone').value = c.phone || '';
    document.getElementById('cinema-submit-btn').innerText = 'Cập nhật';
    document.getElementById('cinema-modal').classList.remove('hidden');
  }
  function closeCinemaModal() {
    document.getElementById('cinema-modal').classList.add('hidden');
  }
  function openScreenModal(cinemaId, cinemaName, nextNum) {
    document.getElementById('screen-modal-title').innerText = 'Thêm phòng cho ' + cinemaName;
    document.getElementById('screen-cinema-id').value = cinemaId;
    document.getElementById('screen-name-input').value = 'Phòng ' + nextNum;
    document.getElementById('screen-modal').classList.remove('hidden');
  }
  function closeScreenModal() {
    document.getElementById('screen-modal').classList.add('hidden');
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

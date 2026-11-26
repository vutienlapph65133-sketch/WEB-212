<?php
/**
 * app/Views/admin/cinema/orders.php - Quản lý Đơn hàng MovieGo Admin kết nối CSDL thật
 */
$pageTitle = 'Quản Lý Đơn Hàng - MovieGo Admin';
$activeMenu = 'orders';
include __DIR__ . '/layouts/sidebar.php';

$orders = $orders ?? [];
$msg    = $msg ?? '';
$error  = $error ?? '';
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
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Đơn Hàng & Vé Đã Bán (<?= count($orders) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Tra cứu danh sách giao dịch, trạng thái thanh toán và thông tin vé đặt lưu trong CSDL.</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Mã đơn</th>
            <th class="py-3 px-4">Khách hàng</th>
            <th class="py-3 px-4">Phim & Rạp</th>
            <th class="py-3 px-4">Ghế ngồi</th>
            <th class="py-3 px-4">Tổng tiền</th>
            <th class="py-3 px-4">Thanh toán</th>
            <th class="py-3 px-4">Trạng thái</th>
            <th class="py-3 px-4 text-right">Đổi trạng thái</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($orders)): ?>
            <tr><td colspan="8" class="py-8 text-center text-gray-400">Chưa có đơn hàng nào trong hệ thống.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $od): ?>
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-blue-600">
                  <a href="/cinema/ticket?code=<?= urlencode($od['code']) ?>" target="_blank" class="hover:underline flex items-center gap-1">
                    <span><?= htmlspecialchars($od['code']) ?></span>
                    <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                  </a>
                </td>
                <td class="py-3.5 px-4">
                  <strong class="text-gray-900 block"><?= htmlspecialchars($od['customer_name']) ?></strong>
                  <span class="text-gray-400 text-[11px]"><?= htmlspecialchars($od['customer_phone']) ?></span>
                </td>
                <td class="py-3.5 px-4">
                  <span class="font-semibold text-gray-800 block"><?= htmlspecialchars($od['movie_title']) ?></span>
                  <span class="text-gray-400 text-[11px]"><?= htmlspecialchars($od['cinema_name']) ?></span>
                </td>
                <td class="py-3.5 px-4 font-bold text-amber-600">
                  <?= htmlspecialchars($od['seats_list'] ?: 'Chưa gán ghế') ?>
                </td>
                <td class="py-3.5 px-4 font-bold text-gray-900"><?= number_format($od['total'], 0, ',', '.') ?>₫</td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $od['payment_method'] === 'momo' ? 'bg-pink-50 text-pink-700' : ($od['payment_method'] === 'vnpay' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') ?>">
                    <?= htmlspecialchars($od['payment_method']) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <?php if ($od['status'] === 'completed'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Hoàn thành</span>
                  <?php elseif ($od['status'] === 'pending'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Chờ xử lý</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Đã hủy</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <?php if ($od['status'] === 'cancelled'): ?>
                    <span class="text-[11px] text-gray-400 italic">Đã hủy & hoàn ghế</span>
                  <?php else: ?>
                    <form method="post" action="/admin/cinema/orders" class="inline-flex items-center gap-1">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="id" value="<?= (int)$od['id'] ?>">
                      <select name="status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-[11px] text-gray-700 focus:outline-none focus:border-blue-600">
                        <option value="completed" <?= $od['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="pending" <?= $od['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="cancelled">Hủy đơn</option>
                      </select>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

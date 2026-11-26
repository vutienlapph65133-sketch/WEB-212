<?php
/**
 * app/Views/client/cinema/my_tickets.php - Danh sách vé đã đặt của khách hàng đang đăng nhập
 */
$pageTitle = 'Vé Của Tôi - MovieGo';
include __DIR__ . '/layouts/header.php';

$orders = $orders ?? [];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-2">
        <i data-lucide="ticket" class="w-6 h-6 text-amber-400"></i>
        Vé Của Tôi (<?= count($orders) ?>)
      </h1>
      <p class="text-xs text-gray-400 mt-1">Lịch sử các vé xem phim bạn đã đặt khi đăng nhập tài khoản MovieGo.</p>
    </div>
    <a href="/cinema" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs transition-colors">
      + Đặt vé mới
    </a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="text-center py-16 bg-gray-900/50 rounded-3xl border border-gray-800">
      <i data-lucide="ticket" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
      <p class="text-sm text-white font-bold mb-1">Bạn chưa có vé nào</p>
      <p class="text-xs text-gray-400 mb-5">Hãy chọn một bộ phim yêu thích để trải nghiệm đặt vé nhanh chóng!</p>
      <a href="/cinema" class="inline-flex px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs">Khám phá phim đang chiếu</a>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($orders as $od): ?>
        <div class="bg-gray-900/80 border border-gray-800 hover:border-amber-500/40 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between transition-colors">
          <div class="flex gap-4 items-start">
            <img src="<?= htmlspecialchars($od['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500') ?>"
                 alt="<?= htmlspecialchars($od['movie_title']) ?>"
                 class="w-16 h-24 object-cover rounded-xl border border-gray-800 shrink-0 bg-gray-950">
            <div class="space-y-1 text-xs">
              <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono font-bold text-amber-400"><?= htmlspecialchars($od['code']) ?></span>
                <?php if ($od['status'] === 'completed'): ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Hợp lệ</span>
                <?php elseif ($od['status'] === 'cancelled'): ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/15 text-red-400 border border-red-500/30">Đã hủy</span>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">Chờ xử lý</span>
                <?php endif; ?>
                <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-300 text-[10px] font-bold"><?= htmlspecialchars($od['format']) ?></span>
              </div>
              <h2 class="text-base font-bold text-white"><?= htmlspecialchars($od['movie_title']) ?></h2>
              <p class="text-gray-400"><?= htmlspecialchars($od['cinema_name']) ?> • <?= htmlspecialchars($od['screen_name']) ?></p>
              <p class="text-gray-300">Suất: <strong class="text-white"><?= date('H:i - d/m/Y', strtotime($od['starts_at'])) ?></strong> • Ghế: <strong class="text-amber-400"><?= htmlspecialchars($od['seats_list'] ?: 'Đã hủy ghế') ?></strong></p>
            </div>
          </div>

          <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-800">
            <div class="text-left sm:text-right">
              <p class="text-[11px] text-gray-400">Tổng thanh toán</p>
              <p class="text-lg font-black text-amber-400"><?= number_format((int)$od['total'], 0, ',', '.') ?>₫</p>
            </div>
            <a href="/cinema/ticket?code=<?= urlencode($od['code']) ?>" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-amber-500 hover:text-gray-950 text-white font-bold text-xs transition-colors flex items-center gap-1.5">
              <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
              Xem vé QR
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

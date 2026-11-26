<?php
/**
 * app/Views/client/cinema/ticket.php - Vé Xem Phim Điện Tử & Tra Cứu Vé
 */
$pageTitle = 'Vé Điện Tử & Tra Cứu Vé - MovieGo';
include __DIR__ . '/layouts/header.php';

$order         = $order ?? null;
$seats         = $seats ?? [];
$combos        = $combos ?? [];
$code          = $code ?? '';
$phone         = $phone ?? '';
$searchResults = $searchResults ?? [];
$searched      = $searched ?? false;
?>

<style>
  @media print {
    body { background: #fff !important; color: #000 !important; }
    #ticket-print-area { border: 1px solid #d1d5db !important; box-shadow: none !important; }
  }
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
  <!-- ─── Hộp Tra cứu vé ─── -->
  <div class="bg-gray-900/90 border border-gray-800 rounded-3xl p-6 mb-8 shadow-xl print:hidden">
    <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2">
      <i data-lucide="search" class="w-5 h-5 text-amber-400"></i>
      Tra cứu thông tin vé xem phim
    </h2>
    <p class="text-xs text-gray-400 mb-4">Nhập mã đặt vé (ví dụ: ORD-20261101-001) hoặc số điện thoại để tra cứu vé của bạn.</p>

    <form method="post" action="/cinema/ticket" class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <i data-lucide="ticket" class="w-4 h-4 text-gray-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="search_code" value="<?= htmlspecialchars($code) ?>" placeholder="Nhập mã vé (ORD-...)"
               class="w-full bg-gray-950 border border-gray-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white uppercase focus:outline-none focus:border-amber-500">
      </div>
      <div class="relative flex-1">
        <i data-lucide="phone" class="w-4 h-4 text-gray-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="tel" name="search_phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Hoặc nhập số điện thoại"
               class="w-full bg-gray-950 border border-gray-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors">
        <span>Tra cứu</span>
      </button>
    </form>

    <!-- Kết quả tìm kiếm theo SĐT nếu có nhiều vé -->
    <?php if (!empty($searchResults) && count($searchResults) > 1): ?>
      <div class="mt-4 pt-4 border-t border-gray-800">
        <p class="text-xs text-amber-400 font-semibold mb-2">Tìm thấy <?= count($searchResults) ?> vé của số điện thoại này:</p>
        <div class="space-y-1.5 max-h-40 overflow-y-auto">
          <?php foreach ($searchResults as $sr): ?>
            <a href="/cinema/ticket?code=<?= urlencode($sr['code']) ?>" 
               class="flex items-center justify-between p-2 rounded-lg bg-gray-950 hover:bg-gray-800 text-xs text-gray-300 transition-colors <?= $sr['code'] === $code ? 'border border-amber-500/50 text-white' : '' ?>">
              <span class="font-mono font-bold text-amber-400"><?= htmlspecialchars($sr['code']) ?></span>
              <span><?= htmlspecialchars($sr['movie_title']) ?></span>
              <span class="text-gray-500"><?= date('d/m/Y H:i', strtotime($sr['starts_at'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($order): ?>
    <!-- ─── Banner chúc mừng nếu là vừa đặt xong hoặc đã tìm thấy ─── -->
    <div class="text-center mb-8 print:hidden">
      <?php if ($order['status'] === 'completed'): ?>
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 mb-3 animate-bounce">
          <i data-lucide="check-circle" class="w-8 h-8"></i>
        </div>
      <?php elseif ($order['status'] === 'cancelled'): ?>
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 mb-3">
          <i data-lucide="x-circle" class="w-8 h-8"></i>
        </div>
      <?php else: ?>
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 mb-3">
          <i data-lucide="clock" class="w-8 h-8"></i>
        </div>
      <?php endif; ?>
      <span class="block w-fit mx-auto px-3 py-1 rounded-full border text-xs font-bold mb-2 <?= $order['status'] === 'completed' ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : ($order['status'] === 'cancelled' ? 'bg-red-500/10 border-red-500/30 text-red-400' : 'bg-amber-500/10 border-amber-500/30 text-amber-300') ?>">
        <?= $order['status'] === 'completed' ? 'Vé hợp lệ • Sẵn sàng check-in' : ($order['status'] === 'cancelled' ? 'Vé đã bị hủy' : 'Đang xử lý thanh toán') ?>
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-white">Vé Xem Phim Điện Tử</h1>
      <p class="text-xs text-gray-400 mt-1">Xuất trình mã QR dưới đây tại quầy vé hoặc cửa soát vé tự động.</p>
    </div>

    <!-- ─── Thẻ Vé Phim Cao Cấp ─── -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl relative" id="ticket-print-area">
      <!-- Ribbon top -->
      <div class="bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-500 px-6 py-3 flex items-center justify-between text-gray-950">
        <div class="flex items-center gap-2 font-black text-xs uppercase tracking-wider">
          <i data-lucide="ticket" class="w-4 h-4"></i>
          <span>MovieGo E-Ticket</span>
        </div>
        <span class="font-mono font-bold text-xs bg-gray-950/20 px-2 py-0.5 rounded">MÃ: <?= htmlspecialchars($order['code']) ?></span>
      </div>

      <!-- Thân vé -->
      <div class="p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
          <img src="<?= htmlspecialchars($order['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500') ?>" class="w-24 sm:w-28 rounded-xl object-cover shadow-lg border border-gray-800 shrink-0 bg-gray-950">
          
          <div class="space-y-3 flex-1">
            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-600/30 text-indigo-400 border border-indigo-500/40">
              <?= htmlspecialchars($order['format']) ?>
            </span>
            <h2 class="text-xl font-black text-white leading-tight"><?= htmlspecialchars($order['movie_title']) ?></h2>

            <div class="grid grid-cols-2 gap-3 text-xs text-gray-400 pt-2 border-y border-gray-800/80">
              <div><span class="block text-gray-500">Rạp chiếu</span><strong class="text-white"><?= htmlspecialchars($order['cinema_name']) ?></strong></div>
              <div><span class="block text-gray-500">Phòng chiếu</span><strong class="text-amber-400"><?= htmlspecialchars($order['screen_name']) ?></strong></div>
              <div><span class="block text-gray-500">Suất chiếu</span><strong class="text-white"><?= date('H:i - d/m/Y', strtotime($order['starts_at'])) ?></strong></div>
              <div>
                <span class="block text-gray-500">Bắp nước</span>
                <strong class="text-white">
                  <?php if (!empty($combos)): ?>
                    <?= implode(', ', array_map(fn($c) => $c['name'] . ' (x' . $c['quantity'] . ')', $combos)) ?>
                  <?php else: ?>
                    Không chọn
                  <?php endif; ?>
                </strong>
              </div>
            </div>

            <div>
              <span class="text-xs text-gray-500 block mb-1">Ghế ngồi:</span>
              <div class="flex flex-wrap gap-2">
                <?php if (!empty($seats)): ?>
                  <?php foreach ($seats as $s): ?>
                    <span class="px-3 py-1 rounded-lg bg-amber-500 text-gray-950 font-black text-xs shadow">
                      <?= htmlspecialchars($s['row_label'] . $s['seat_number']) ?>
                    </span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-xs text-gray-400">Đang cập nhật ghế</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Đường đứt xé vé -->
      <div class="relative my-2">
        <div class="absolute -left-4 -top-3.5 w-7 h-7 rounded-full bg-gray-950 border-r border-gray-800"></div>
        <div class="border-b-2 border-dashed border-gray-800 w-full"></div>
        <div class="absolute -right-4 -top-3.5 w-7 h-7 rounded-full bg-gray-950 border-l border-gray-800"></div>
      </div>

      <!-- QR Section -->
      <div class="p-6 sm:p-8 bg-gray-950/60 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1 text-center sm:text-left text-xs">
          <p class="text-gray-400">Người đặt: <strong class="text-white"><?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_phone']) ?>)</strong></p>
          <div class="py-1 text-[11px] text-gray-400 space-y-0.5">
            <?php if (!empty($order['discount']) && $order['discount'] > 0): ?>
              <p class="text-emerald-400">Voucher giảm giá <?= !empty($order['promo_code']) ? '(' . htmlspecialchars($order['promo_code']) . ')' : '' ?>: -<?= number_format($order['discount'], 0, ',', '.') ?>₫</p>
            <?php endif; ?>
            <?php if ((int)($order['points_used'] ?? 0) > 0): ?>
              <p class="text-amber-400">Đã dùng <?= (int)$order['points_used'] ?> điểm (-<?= number_format((int)$order['points_discount'], 0, ',', '.') ?>₫)</p>
            <?php endif; ?>
            <?php if ((int)($order['points_earned'] ?? 0) > 0): ?>
              <p class="text-emerald-400 font-semibold">⭐ Điểm tích lũy từ đơn này: +<?= (int)$order['points_earned'] ?> điểm</p>
            <?php endif; ?>
          </div>
          <p class="text-amber-400 font-bold pt-1">Tổng tiền: <?= number_format($order['total'], 0, ',', '.') ?>₫ (Thanh toán qua <?= strtoupper(htmlspecialchars($order['payment_method'])) ?>)</p>
          <p class="text-gray-500 text-[10px]">Vui lòng có mặt tại rạp trước giờ chiếu 15 phút.</p>
        </div>

        <!-- Dynamic SVG QR Code with Order Code -->
        <?php
          $qrHash = hash('sha256', (string)$order['code']);
          $qrSize = 21;
          $isFinder = function (int $r, int $c) use ($qrSize): ?bool {
              $boxes = [[0, 0], [0, $qrSize - 7], [$qrSize - 7, 0]];
              foreach ($boxes as [$br, $bc]) {
                  if ($r >= $br && $r < $br + 7 && $c >= $bc && $c < $bc + 7) {
                      $dr = $r - $br; $dc = $c - $bc;
                      return ($dr === 0 || $dr === 6 || $dc === 0 || $dc === 6 || ($dr >= 2 && $dr <= 4 && $dc >= 2 && $dc <= 4));
                  }
              }
              if (($r === 7 && ($c <= 7 || $c >= $qrSize - 8)) || ($c === 7 && ($r <= 7 || $r >= $qrSize - 8)) || ($r === $qrSize - 8 && $c <= 7) || ($c === $qrSize - 8 && $r <= 7)) {
                  return false;
              }
              return null;
          };
        ?>
        <div class="flex flex-col items-center bg-white p-3 rounded-2xl shadow-xl">
          <svg class="w-24 h-24" viewBox="0 0 21 21" fill="black" shape-rendering="crispEdges">
            <?php for ($r = 0; $r < $qrSize; $r++): ?>
              <?php for ($c = 0; $c < $qrSize; $c++):
                $f = $isFinder($r, $c);
                if ($f === null) {
                    $idx = ($r * $qrSize + $c);
                    $byte = hexdec(substr($qrHash, ($idx % 32) * 2, 2));
                    $on = (($byte >> ($idx % 8)) & 1) === 1;
                } else {
                    $on = $f;
                }
                if ($on): ?>
                  <rect x="<?= $c ?>" y="<?= $r ?>" width="1" height="1"/>
                <?php endif; ?>
              <?php endfor; ?>
            <?php endfor; ?>
          </svg>
          <span class="text-[10px] font-mono text-gray-800 font-bold mt-1"><?= htmlspecialchars($order['code']) ?></span>
        </div>
      </div>
    </div>

    <!-- Nút Thao tác -->
    <div class="flex flex-wrap items-center justify-center gap-3 mt-8 print:hidden">
      <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-medium text-xs border border-gray-700 flex items-center gap-2">
        <i data-lucide="printer" class="w-4 h-4 text-amber-400"></i> In / Lưu vé PDF
      </button>
      <a href="/cinema" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20">
        <i data-lucide="home" class="w-4 h-4"></i> Về trang chủ
      </a>
    </div>

  <?php elseif ($searched): ?>
    <div class="text-center py-16 bg-gray-900/40 rounded-3xl border border-gray-800">
      <div class="w-12 h-12 rounded-full bg-red-500/10 text-red-400 flex items-center justify-center mx-auto mb-3">
        <i data-lucide="alert-circle" class="w-6 h-6"></i>
      </div>
      <h3 class="text-white text-base font-bold mb-1">Không tìm thấy vé</h3>
      <p class="text-xs text-gray-400 max-w-sm mx-auto">Vui lòng kiểm tra lại mã đơn hàng hoặc số điện thoại đã sử dụng khi đặt vé.</p>
    </div>
  <?php else: ?>
    <!-- Hướng dẫn mặc định khi chưa tìm -->
    <div class="text-center py-16 bg-gray-900/30 rounded-3xl border border-gray-800/80">
      <i data-lucide="qr-code" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
      <h3 class="text-white text-base font-bold mb-1">Tra cứu vé đã đặt</h3>
      <p class="text-xs text-gray-400 max-w-sm mx-auto">Bạn có thể nhập mã vé nhận được sau khi thanh toán hoặc số điện thoại của bạn ở ô phía trên để xem lại vé điện tử.</p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

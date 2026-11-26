<?php
/**
 * app/Views/client/cinema/seats.php - Sơ đồ chọn ghế tương tác thời gian thực
 */
$pageTitle = 'Chọn Ghế Ngồi - ' . ($showtime['movie_title'] ?? 'MovieGo');
include __DIR__ . '/layouts/header.php';

$showtimeId = (int)($showtimeId ?? 1);
$showtime   = $showtime ?? [];
$seatMap    = $seatMap ?? [];
$seatTypes  = $seatTypes ?? [];
$soldSeats  = $soldSeats ?? [];
$combos     = $combos ?? [];

$rows = !empty($seatMap) ? array_keys($seatMap) : ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$basePrice = (int)($showtime['base_price'] ?? 75000);
$vipSurcharge    = (int)($seatTypes['vip']['surcharge'] ?? 20000);
$coupleSurcharge = (int)($seatTypes['couple']['surcharge'] ?? 85000);
$stdSurcharge    = (int)($seatTypes['standard']['surcharge'] ?? 0);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <?php if (!empty($_SESSION['checkout_error'])): ?>
    <div class="mb-6 p-4 rounded-2xl bg-red-950/60 border border-red-500/50 text-red-300 text-xs flex items-center justify-between">
      <span><?= htmlspecialchars($_SESSION['checkout_error']) ?></span>
      <?php unset($_SESSION['checkout_error']); ?>
    </div>
  <?php endif; ?>

  <!-- Top Info Bar -->
  <div class="flex items-center gap-4 mb-8 border-b border-gray-800 pb-4">
    <a href="/cinema/detail?id=<?= $showtime['movie_id'] ?? 1 ?>" class="p-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-300">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-white"><?= htmlspecialchars($showtime['movie_title'] ?? 'Đang tải...') ?></h1>
      <p class="text-xs text-gray-400 mt-0.5">
        <?= htmlspecialchars($showtime['cinema_name'] ?? '') ?> • <?= htmlspecialchars($showtime['screen_name'] ?? '') ?> • <?= date('d/m/Y - H:i', strtotime($showtime['starts_at'] ?? 'now')) ?> • <?= htmlspecialchars($showtime['format'] ?? '2D') ?>
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <!-- ─── Sơ đồ ghế (8 cols) ─── -->
    <div class="lg:col-span-8 bg-gray-900/60 border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
      <!-- Curved Screen Indicator -->
      <div class="relative w-full max-w-md mx-auto mb-10 text-center">
        <div class="h-2 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full shadow-[0_0_20px_rgba(245,158,11,0.6)]"></div>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">MÀN HÌNH CHIẾU</p>
      </div>

      <!-- Seat Grid Container -->
      <div class="overflow-x-auto pb-4">
        <div class="min-w-[500px] flex flex-col gap-2.5 items-center">
          <?php foreach ($rows as $row):
            $rowSeats = $seatMap[$row] ?? [];
          ?>
            <div class="flex items-center gap-2">
              <span class="w-6 text-xs font-bold text-gray-500 text-center"><?= htmlspecialchars($row) ?></span>

              <div class="flex gap-1.5 sm:gap-2">
                <?php for ($col = 1; $col <= 12; $col++):
                  $dbSeat = $rowSeats[$col] ?? null;
                  if (!empty($seatMap) && !$dbSeat) continue;
                  $seatType  = $dbSeat['seat_type'] ?? ($row === 'H' ? 'couple' : (in_array($row, ['D', 'E', 'F'], true) ? 'vip' : 'standard'));
                  $surcharge = $dbSeat ? (int)$dbSeat['surcharge'] : ($seatType === 'couple' ? $coupleSurcharge : ($seatType === 'vip' ? $vipSurcharge : $stdSurcharge));
                  $price     = $basePrice + $surcharge;
                  $isVIP     = ($seatType === 'vip');
                  $isCouple  = ($seatType === 'couple');
                  $seatId    = $row . $col;
                  $isSold    = in_array($seatId, $soldSeats, true);
                ?>
                  <button
                    type="button"
                    id="seat-<?= htmlspecialchars($seatId) ?>"
                    data-seat="<?= htmlspecialchars($seatId) ?>"
                    data-price="<?= $price ?>"
                    data-type="<?= htmlspecialchars($seatType) ?>"
                    <?= $isSold ? 'disabled' : '' ?>
                    onclick="toggleSeat('<?= htmlspecialchars($seatId) ?>', <?= $price ?>)"
                    class="seat-btn w-7 h-7 sm:w-8 sm:h-8 rounded-lg text-[10px] font-bold flex items-center justify-center transition-all
                    <?= $isSold ? 'bg-red-950/40 text-red-500 border border-red-500/30 cursor-not-allowed opacity-50' :
                       ($isVIP ? 'bg-gray-800 text-gray-200 border border-amber-500/50 hover:bg-amber-500 hover:text-gray-950' :
                       ($isCouple ? 'bg-purple-950/60 text-purple-300 border border-purple-500/50 hover:bg-purple-500 hover:text-white' :
                       'bg-gray-800 text-gray-300 hover:bg-amber-500 hover:text-gray-950 border border-gray-700')) ?>"
                    title="Ghế <?= htmlspecialchars($seatId) ?> - <?= number_format($price, 0, ',', '.') ?>₫">
                    <?= $col ?>
                  </button>
                <?php endfor; ?>
              </div>

              <span class="w-6 text-xs font-bold text-gray-500 text-center"><?= htmlspecialchars($row) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Legend -->
      <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mt-8 pt-6 border-t border-gray-800 text-xs text-gray-400">
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded bg-gray-800 border border-gray-700"></span>
          <span>Thường (<?= number_format($basePrice + $stdSurcharge, 0, ',', '.') ?>₫)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded bg-gray-800 border border-amber-500/60"></span>
          <span>VIP (<?= number_format($basePrice + $vipSurcharge, 0, ',', '.') ?>₫)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded bg-purple-950/60 border border-purple-500/60"></span>
          <span>Đôi (<?= number_format($basePrice + $coupleSurcharge, 0, ',', '.') ?>₫)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded bg-amber-500 shadow"></span>
          <span>Đang chọn</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded bg-red-950/40 border border-red-500/30 opacity-60"></span>
          <span>Đã bán</span>
        </div>
      </div>
    </div>

    <!-- ─── Bảng Tóm tắt Đơn hàng (4 cols) ─── -->
    <div class="lg:col-span-4 space-y-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl sticky top-24">
        <h2 class="text-base font-bold text-white mb-4 border-b border-gray-800 pb-3 flex items-center justify-between">
          <span>Thông tin đặt vé</span>
          <span class="text-xs font-normal text-gray-400" id="seat-count">0 vé</span>
        </h2>

        <!-- Danh sách ghế chọn -->
        <div class="mb-4">
          <span class="text-xs text-gray-400 block mb-1.5">Ghế bạn đã chọn:</span>
          <div id="selected-seats-badge" class="flex flex-wrap gap-1.5 min-h-[32px] items-center text-xs text-gray-500 italic">
            Chưa có ghế nào được chọn.
          </div>
        </div>

        <!-- Nút mở Modal Chọn Bắp Nước -->
        <div class="py-3 border-y border-gray-800 mb-4">
          <button type="button" onclick="openComboModal()" class="w-full py-2.5 px-3 rounded-xl bg-gray-800 hover:bg-gray-700 text-amber-400 text-xs font-bold transition-colors flex items-center justify-between border border-gray-700">
            <span class="flex items-center gap-2">
              <i data-lucide="popcorn" class="w-4 h-4"></i>
              Chọn Bắp & Nước Uống
            </span>
            <span class="bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded text-[11px]" id="combo-badge">0 món</span>
          </button>
        </div>

        <!-- Bảng tính giá -->
        <div class="space-y-2 text-xs text-gray-400 mb-6">
          <div class="flex justify-between">
            <span>Tiền vé xem phim:</span>
            <span class="text-white font-medium" id="ticket-total-display">0₫</span>
          </div>
          <div class="flex justify-between">
            <span>Tiền bắp nước:</span>
            <span class="text-white font-medium" id="combo-total-display">0₫</span>
          </div>
          <div class="pt-3 border-t border-gray-800 flex justify-between items-baseline">
            <span class="text-sm font-bold text-white">Tổng cộng:</span>
            <span class="text-2xl font-black text-amber-400" id="grand-total-display">0₫</span>
          </div>
        </div>

        <!-- Nút Tiếp tục -->
        <button type="button" id="btn-submit" onclick="proceedToCheckout()" disabled class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-gray-950 font-extrabold text-sm transition-all shadow-lg shadow-amber-500/20 cursor-pointer disabled:cursor-not-allowed">
          Tiếp tục thanh toán &rarr;
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ─── Modal Chọn Bắp Nước ─── -->
<div id="combo-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-gray-900 border border-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-800 pb-3">
      <h3 class="text-lg font-bold text-white flex items-center gap-2">
        <span>🍿</span>
        Chọn Combo Bắp & Nước Uống
      </h3>
      <button type="button" onclick="closeComboModal()" class="p-1 rounded-lg hover:bg-gray-800 text-gray-400">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
      <?php foreach ($combos as $cb): ?>
        <div class="p-3.5 rounded-2xl bg-gray-950 border border-gray-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="text-2xl"><?= htmlspecialchars($cb['icon'] ?? '🍿') ?></span>
            <div>
              <h4 class="font-bold text-sm text-white"><?= htmlspecialchars($cb['name']) ?></h4>
              <p class="text-xs text-gray-400"><?= htmlspecialchars($cb['desc'] ?? '') ?></p>
              <span class="text-xs font-semibold text-amber-400"><?= number_format($cb['price'], 0, ',', '.') ?>₫</span>
            </div>
          </div>

          <div class="flex items-center gap-2 bg-gray-900 border border-gray-800 rounded-xl p-1">
            <button type="button" onclick='updateCombo(<?= (int)$cb['id'] ?>, -1, <?= (int)$cb['price'] ?>, <?= json_encode($cb['name'], JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)' class="w-7 h-7 rounded-lg bg-gray-800 text-white flex items-center justify-center hover:bg-gray-700 font-bold">-</button>
            <span id="qty-<?= (int)$cb['id'] ?>" class="w-6 text-center text-xs font-bold text-white">0</span>
            <button type="button" onclick='updateCombo(<?= (int)$cb['id'] ?>, 1, <?= (int)$cb['price'] ?>, <?= json_encode($cb['name'], JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)' class="w-7 h-7 rounded-lg bg-amber-500 text-gray-950 flex items-center justify-center hover:bg-amber-400 font-bold">+</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="pt-3 border-t border-gray-800 flex items-center justify-between">
      <div>
        <span class="text-xs text-gray-400">Tiền bắp nước:</span>
        <span class="block text-base font-bold text-amber-400" id="modal-combo-total">0₫</span>
      </div>
      <button type="button" onclick="closeComboModal()" class="px-6 py-2.5 rounded-xl bg-amber-500 text-gray-950 font-bold text-sm hover:bg-amber-400 transition-colors">
        Xác nhận
      </button>
    </div>
  </div>
</div>

<script>
  let selectedSeats = []; // [{id, price}]
  let selectedCombos = {}; // {id: {qty, price, name}}

  function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
  }

  function toggleSeat(seatId, price) {
    const btn = document.getElementById('seat-' + seatId);
    const index = selectedSeats.findIndex(s => s.id === seatId);

    if (index > -1) {
      selectedSeats.splice(index, 1);
      btn.classList.remove('bg-amber-500', 'text-gray-950', 'ring-2', 'ring-amber-300');
    } else {
      selectedSeats.push({ id: seatId, price: price });
      btn.classList.add('bg-amber-500', 'text-gray-950', 'ring-2', 'ring-amber-300');
    }

    renderSummary();
  }

  function updateCombo(id, delta, price, name) {
    if (!selectedCombos[id]) {
      selectedCombos[id] = { qty: 0, price: price, name: name };
    }
    selectedCombos[id].qty = Math.max(0, selectedCombos[id].qty + delta);
    document.getElementById('qty-' + id).innerText = selectedCombos[id].qty;
    renderSummary();
  }

  function renderSummary() {
    // Ticket sum
    const ticketTotal = selectedSeats.reduce((sum, s) => sum + s.price, 0);
    document.getElementById('ticket-total-display').innerText = formatMoney(ticketTotal);
    document.getElementById('seat-count').innerText = selectedSeats.length + ' vé';

    // Combo sum
    let comboTotal = 0;
    let comboCount = 0;
    for (let id in selectedCombos) {
      comboTotal += selectedCombos[id].qty * selectedCombos[id].price;
      comboCount += selectedCombos[id].qty;
    }
    document.getElementById('combo-total-display').innerText = formatMoney(comboTotal);
    document.getElementById('modal-combo-total').innerText = formatMoney(comboTotal);
    document.getElementById('combo-badge').innerText = comboCount + ' món';

    // Grand total
    const grandTotal = ticketTotal + comboTotal;
    document.getElementById('grand-total-display').innerText = formatMoney(grandTotal);

    // Badges
    const badgeContainer = document.getElementById('selected-seats-badge');
    if (selectedSeats.length === 0) {
      badgeContainer.innerHTML = 'Chưa có ghế nào được chọn.';
      badgeContainer.className = 'flex flex-wrap gap-1.5 min-h-[32px] items-center text-xs text-gray-500 italic';
      document.getElementById('btn-submit').disabled = true;
    } else {
      badgeContainer.className = 'flex flex-wrap gap-1.5 min-h-[32px] items-center';
      badgeContainer.innerHTML = selectedSeats.map(s => 
        `<span class="px-2 py-0.5 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-400 text-xs font-bold">${s.id}</span>`
      ).join('');
      document.getElementById('btn-submit').disabled = false;
    }
  }

  function openComboModal() {
    document.getElementById('combo-modal').classList.remove('hidden');
  }

  function closeComboModal() {
    document.getElementById('combo-modal').classList.add('hidden');
  }

  function proceedToCheckout() {
    if (selectedSeats.length === 0) return;
    const seatParam = encodeURIComponent(selectedSeats.map(s => s.id).join(','));
    const comboParam = encodeURIComponent(JSON.stringify(selectedCombos));
    window.location.href = `<?= url('/cinema/checkout') ?>?showtime_id=<?= $showtimeId ?>&seats=${seatParam}&combos=${comboParam}`;
  }
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

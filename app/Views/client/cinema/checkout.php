<?php
/**
 * app/Views/client/cinema/checkout.php - Trang Thanh Toán Đặt Vé (Voucher nhanh + Điểm thưởng Loyalty + CSRF)
 */
$pageTitle = 'Thanh Toán Vé Xem Phim - MovieGo';
include __DIR__ . '/layouts/header.php';

$showtime        = $showtime ?? [];
$showtimeId      = (int)($showtimeId ?? 1);
$seatList        = $seatList ?? ['D5', 'D6'];
$seatsParam      = $seatsParam ?? implode(', ', $seatList);
$combosParam     = $combosParam ?? '{}';
$comboSummary    = $comboSummary ?? [];
$ticketTotal     = (int)($ticketTotal ?? 150000);
$comboTotal      = (int)($comboTotal ?? 0);
$grandTotal      = (int)($grandTotal ?? ($ticketTotal + $comboTotal));
$user            = $user ?? (function_exists('currentUser') ? currentUser() : null);
$availablePromos = $availablePromos ?? [];
$promoMap        = $promoMap ?? [];
$loyalty         = $loyalty ?? null;

$defaultName  = $user['full_name'] ?? '';
$defaultEmail = $user['email'] ?? '';
$defaultPhone = $user['phone'] ?? '';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <?php if (!empty($_SESSION['checkout_error'])): ?>
    <div class="mb-6 p-4 rounded-2xl bg-red-950/60 border border-red-500/50 text-red-300 text-xs flex items-center justify-between">
      <span><?= htmlspecialchars($_SESSION['checkout_error']) ?></span>
      <?php unset($_SESSION['checkout_error']); ?>
    </div>
  <?php endif; ?>

  <div id="timeout-banner" class="hidden mb-6 p-4 rounded-2xl bg-red-950/60 border border-red-500/50 text-red-300 text-xs flex flex-wrap items-center justify-between gap-3">
    <span>Đã hết thời gian 10 phút giữ ghế. Vui lòng chọn lại ghế để tiếp tục đặt vé.</span>
    <a href="/cinema/seats?showtime_id=<?= $showtimeId ?>" class="px-4 py-1.5 rounded-xl bg-amber-500 text-gray-950 font-bold hover:bg-amber-400">Chọn lại ghế</a>
  </div>

  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <a href="/cinema/seats?showtime_id=<?= $showtimeId ?>" class="inline-flex items-center gap-2 text-xs text-gray-400 hover:text-white">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Quay lại chọn ghế
    </a>

    <div class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/30 text-amber-400 px-4 py-1.5 rounded-full text-xs font-semibold">
      <i data-lucide="clock" class="w-4 h-4 animate-pulse"></i>
      <span>Thời gian giữ vé còn:</span>
      <span class="font-mono text-sm font-bold" id="timer">10:00</span>
    </div>
  </div>

  <form method="post" action="/cinema/checkout" id="checkout-form">
    <input type="hidden" name="showtime_id" value="<?= $showtimeId ?>">
    <input type="hidden" name="seats" value="<?= htmlspecialchars($seatsParam) ?>">
    <input type="hidden" name="combos" value="<?= htmlspecialchars($combosParam) ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="promo_code" id="hidden-promo-code" value="">
    <input type="hidden" name="points_used" id="hidden-points" value="0">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- ─── Cột Form Trái (7 cols) ─── -->
      <div class="lg:col-span-7 space-y-6">
        <!-- 1. Thông tin người nhận -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
          <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5 text-amber-400"></i>
            Thông tin nhận vé điện tử
          </h2>
          <p class="text-xs text-gray-400 mb-5">Vé và mã QR check-in sẽ được lưu và liên kết với tài khoản/số điện thoại của bạn.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-300 mb-1.5">Họ và tên người nhận <span class="text-red-400">*</span></label>
              <input type="text" name="customer_name" required value="<?= htmlspecialchars($defaultName) ?>" placeholder="Ví dụ: Nguyễn Văn An"
                     class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-300 mb-1.5">Số điện thoại nhận vé <span class="text-red-400">*</span></label>
              <input type="tel" name="customer_phone" required value="<?= htmlspecialchars($defaultPhone) ?>" placeholder="0912 345 678"
                     class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-300 mb-1.5">Email nhận hóa đơn</label>
              <input type="email" name="customer_email" value="<?= htmlspecialchars($defaultEmail) ?>" placeholder="email@example.com"
                     class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500">
            </div>
          </div>
        </div>

        <!-- 2. Phương thức thanh toán -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
          <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-5 h-5 text-amber-400"></i>
            Phương thức thanh toán
          </h2>

          <div class="space-y-3">
            <label class="flex items-center justify-between p-4 rounded-2xl border border-pink-500/50 bg-pink-950/20 cursor-pointer">
              <div class="flex items-center gap-3">
                <input type="radio" name="payment_method" value="momo" checked class="accent-pink-500 w-4 h-4">
                <span class="w-9 h-9 rounded-xl bg-pink-600/20 border border-pink-500/30 flex items-center justify-center font-bold text-pink-400 text-xs">MoMo</span>
                <div>
                  <p class="text-xs font-bold text-white">Ví MoMo</p>
                  <p class="text-[11px] text-gray-400">Quét mã QR hoặc thanh toán một chạm</p>
                </div>
              </div>
              <span class="text-[10px] bg-pink-500/20 text-pink-400 px-2 py-0.5 rounded-full font-bold">Khuyên dùng</span>
            </label>

            <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-800 hover:border-gray-700 bg-gray-950/60 cursor-pointer">
              <div class="flex items-center gap-3">
                <input type="radio" name="payment_method" value="vnpay" class="accent-blue-500 w-4 h-4">
                <span class="w-9 h-9 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center font-bold text-blue-400 text-xs">VNPAY</span>
                <div>
                  <p class="text-xs font-bold text-white">Cổng VNPAY</p>
                  <p class="text-[11px] text-gray-400">Ứng dụng ngân hàng & VNPAY-QR</p>
                </div>
              </div>
            </label>

            <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-800 hover:border-gray-700 bg-gray-950/60 cursor-pointer">
              <div class="flex items-center gap-3">
                <input type="radio" name="payment_method" value="zalopay" class="accent-cyan-500 w-4 h-4">
                <span class="w-9 h-9 rounded-xl bg-cyan-600/20 border border-cyan-500/30 flex items-center justify-center font-bold text-cyan-400 text-[10px]">ZaloPay</span>
                <div>
                  <p class="text-xs font-bold text-white">Ví ZaloPay</p>
                  <p class="text-[11px] text-gray-400">Thanh toán nhanh qua ứng dụng Zalo / ZaloPay</p>
                </div>
              </div>
            </label>

            <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-800 hover:border-gray-700 bg-gray-950/60 cursor-pointer">
              <div class="flex items-center gap-3">
                <input type="radio" name="payment_method" value="card" class="accent-amber-500 w-4 h-4">
                <span class="w-9 h-9 rounded-xl bg-amber-600/20 border border-amber-500/30 flex items-center justify-center font-bold text-amber-400 text-xs">CARD</span>
                <div>
                  <p class="text-xs font-bold text-white">Thẻ Quốc Tế / Thẻ ATM</p>
                  <p class="text-[11px] text-gray-400">Visa, Mastercard, Napas</p>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- 3. Mã giảm giá / Voucher -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
          <h2 class="text-base font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="tag" class="w-5 h-5 text-amber-400"></i>
            Mã giảm giá / Voucher
          </h2>
          <div class="flex gap-2">
            <input type="text" id="promo-input" placeholder="Nhập mã giảm giá (VD: CINEMA20)" class="flex-1 bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white uppercase focus:outline-none focus:border-amber-500">
            <button type="button" onclick="applyPromo()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-amber-400 text-xs font-bold rounded-xl border border-gray-700">Áp dụng</button>
          </div>
          <p id="promo-feedback" class="text-xs mt-2 hidden"></p>

          <?php if (!empty($availablePromos)): ?>
            <div class="pt-3 mt-3 border-t border-gray-800">
              <span class="text-[11px] text-gray-400 block mb-2 font-medium">Chọn nhanh voucher đang có:</span>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <?php foreach ($availablePromos as $ap):
                  $dText = $ap['discount_type'] === 'percent' ? "Giảm {$ap['discount_value']}%" : "Giảm " . number_format($ap['discount_value'], 0, ',', '.') . "₫";
                ?>
                  <div onclick="selectPromoQuick('<?= htmlspecialchars($ap['code']) ?>')"
                       class="p-2.5 rounded-xl bg-gray-950 hover:bg-gray-800/80 border border-gray-800 hover:border-amber-500/50 cursor-pointer flex items-center justify-between transition-all">
                    <div>
                      <div class="flex items-center gap-1.5">
                        <span class="font-mono font-black text-amber-400 text-xs"><?= htmlspecialchars($ap['code']) ?></span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px] font-bold"><?= $dText ?></span>
                      </div>
                      <p class="text-[10px] text-gray-400 line-clamp-1 mt-0.5"><?= htmlspecialchars($ap['description'] ?: 'Áp dụng cho đơn đặt vé') ?></p>
                    </div>
                    <span class="text-[10px] text-amber-400 font-bold ml-2">Chọn</span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- 4. Điểm thưởng -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
          <h2 class="text-base font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="star" class="w-5 h-5 text-amber-400"></i>
            Điểm thưởng MovieGo
          </h2>
          <?php if ($loyalty): ?>
            <p class="text-xs text-gray-400 mb-3">
              Hạng <strong class="text-amber-400"><?= htmlspecialchars($loyalty['tier']) ?></strong> •
              Số dư: <strong class="text-white"><?= number_format($loyalty['balance'], 0, ',', '.') ?> điểm</strong>
              (1 điểm = <?= number_format(\App\Core\Loyalty::POINT_VALUE, 0, ',', '.') ?>₫)
            </p>
            <div class="flex gap-2">
              <input type="number" id="points-input" min="0" step="1" value="0" placeholder="Số điểm muốn dùng"
                     class="flex-1 bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500">
              <button type="button" onclick="useMaxPoints()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-amber-400 text-xs font-bold rounded-xl border border-gray-700">Dùng tối đa</button>
            </div>
            <p id="points-feedback" class="text-xs mt-2 text-gray-500"></p>
          <?php else: ?>
            <p class="text-xs text-gray-400">
              <a href="/login" class="text-amber-400 font-bold hover:underline">Đăng nhập</a> hoặc
              <a href="/register" class="text-amber-400 font-bold hover:underline">đăng ký</a>
              để tích điểm cho đơn này và dùng điểm giảm giá cho lần sau.
            </p>
          <?php endif; ?>
          <p class="text-xs mt-3 text-amber-300 hidden" id="earn-row">Đơn này sẽ tích: <strong id="earn-val">+0</strong> điểm</p>
        </div>
      </div>

      <!-- ─── Cột Tóm tắt Đơn hàng Phải (5 cols) ─── -->
      <div class="lg:col-span-5 space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl sticky top-24">
          <h2 class="text-base font-bold text-white mb-4 border-b border-gray-800 pb-3">Tóm tắt đơn hàng</h2>

          <!-- Movie card -->
          <div class="flex gap-4 mb-4">
            <img src="<?= htmlspecialchars($showtime['poster'] ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500') ?>" class="w-16 h-24 object-cover rounded-xl border border-gray-800 bg-gray-950">
            <div class="space-y-1 text-xs">
              <h3 class="font-bold text-white text-sm"><?= htmlspecialchars($showtime['movie_title'] ?? 'Phim') ?></h3>
              <p class="text-gray-400"><?= htmlspecialchars($showtime['cinema_name'] ?? '') ?> • <?= htmlspecialchars($showtime['screen_name'] ?? '') ?></p>
              <p class="text-amber-400 font-medium"><?= date('H:i - d/m/Y', strtotime($showtime['starts_at'] ?? 'now')) ?> (<?= htmlspecialchars($showtime['format'] ?? '2D') ?>)</p>
            </div>
          </div>

          <div class="py-3 border-t border-gray-800 text-xs">
            <div class="flex justify-between mb-1.5">
              <span class="text-gray-400">Ghế đã chọn (<?= count($seatList) ?> vé):</span>
              <span class="font-bold text-white"><?= number_format($ticketTotal, 0, ',', '.') ?>₫</span>
            </div>
            <div class="flex flex-wrap gap-1">
              <?php foreach ($seatList as $st): ?>
                <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold"><?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if (!empty($comboSummary)): ?>
            <div class="py-3 border-t border-gray-800 text-xs space-y-1.5">
              <div class="flex justify-between text-gray-400">
                <span>Bắp nước: <?= htmlspecialchars(implode(', ', $comboSummary)) ?></span>
                <span class="text-white"><?= number_format($comboTotal, 0, ',', '.') ?>₫</span>
              </div>
            </div>
          <?php endif; ?>

          <div class="py-3 border-t border-gray-800 text-xs space-y-1.5">
            <div class="flex justify-between text-emerald-400 font-medium hidden" id="discount-row">
              <span>Giảm giá Voucher:</span>
              <span id="discount-val">-0₫</span>
            </div>
            <div class="flex justify-between text-emerald-400 font-medium hidden" id="points-row">
              <span>Dùng điểm thưởng:</span>
              <span id="points-val">-0₫</span>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-800 mb-6">
            <div class="flex justify-between items-baseline">
              <span class="text-xs font-bold text-gray-300">Tổng thanh toán:</span>
              <span class="text-2xl font-black text-amber-400" id="final-total"><?= number_format($grandTotal, 0, ',', '.') ?>₫</span>
            </div>
          </div>

          <button type="submit" id="checkout-submit-btn" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-gray-950 font-extrabold text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer">
            <i data-lucide="lock" class="w-4 h-4"></i>
            Xác nhận thanh toán
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  let timeLeft = 600;
  const countdownTimer = setInterval(() => {
    if (timeLeft <= 0) {
      clearInterval(countdownTimer);
      document.getElementById('timeout-banner')?.classList.remove('hidden');
      const btn = document.getElementById('checkout-submit-btn');
      if (btn) btn.disabled = true;
      return;
    }
    timeLeft--;
    const mins = Math.floor(timeLeft / 60);
    const secs = timeLeft % 60;
    const el = document.getElementById('timer');
    if (el) {
      el.innerText = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }
    if (timeLeft === 0) {
      clearInterval(countdownTimer);
      document.getElementById('timeout-banner')?.classList.remove('hidden');
      const btn = document.getElementById('checkout-submit-btn');
      if (btn) btn.disabled = true;
    }
  }, 1000);

  const TICKET_TOTAL = <?= (int)$ticketTotal ?>;
  const COMBO_TOTAL  = <?= (int)$comboTotal ?>;
  const BASE_TOTAL   = TICKET_TOTAL + COMBO_TOTAL;
  const PROMOS       = <?= json_encode($promoMap, JSON_UNESCAPED_UNICODE) ?>;
  const LOGGED_IN    = <?= $loyalty ? 'true' : 'false' ?>;
  const BALANCE      = <?= (int)($loyalty['balance'] ?? 0) ?>;
  const POINT_VALUE  = <?= \App\Core\Loyalty::POINT_VALUE ?>;
  const MIN_REDEEM   = <?= \App\Core\Loyalty::MIN_REDEEM ?>;
  const MAX_PCT      = <?= \App\Core\Loyalty::MAX_REDEEM_PERCENT ?>;
  const EARN_TICKET  = <?= \App\Core\Loyalty::EARN_TICKET_PCT ?>;
  const EARN_COMBO   = <?= \App\Core\Loyalty::EARN_COMBO_PCT ?>;

  const vnd = n => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n);
  let promoCode = '';

  function promoDiscount() {
    const p = PROMOS[promoCode];
    if (!p) return 0;
    const d = p.type === 'percent' ? Math.round(BASE_TOTAL * p.value / 100) : p.value;
    return Math.min(d, BASE_TOTAL);
  }

  function maxPoints(afterPromo) {
    if (!LOGGED_IN) return 0;
    const cap = Math.floor(Math.floor(afterPromo * MAX_PCT / 100) / POINT_VALUE);
    const max = Math.min(BALANCE, cap);
    return max >= MIN_REDEEM ? max : 0;
  }

  function recalc() {
    const discount   = promoDiscount();
    const afterPromo = BASE_TOTAL - discount;
    const max        = maxPoints(afterPromo);

    let pts = 0;
    const input = document.getElementById('points-input');
    const pfb   = document.getElementById('points-feedback');
    if (input) {
      pts = Math.max(0, parseInt(input.value, 10) || 0);
      if (pts > max) { pts = max; input.value = max; }
      if (max === 0) {
        pfb.innerText = BALANCE < MIN_REDEEM
          ? 'Cần tối thiểu ' + MIN_REDEEM + ' điểm để sử dụng.'
          : 'Đơn hàng chưa đủ giá trị để dùng điểm.';
      } else {
        pfb.innerText = 'Dùng tối đa ' + max + ' điểm cho đơn này (không quá ' + MAX_PCT + '% giá trị đơn).';
      }
      if (pts > 0 && pts < MIN_REDEEM) { pts = 0; }
    }

    const pointsDiscount = pts * POINT_VALUE;
    const total = Math.max(0, afterPromo - pointsDiscount);

    document.getElementById('discount-row').classList.toggle('hidden', discount === 0);
    document.getElementById('discount-val').innerText = '-' + vnd(discount);
    document.getElementById('points-row').classList.toggle('hidden', pts === 0);
    document.getElementById('points-val').innerText = '-' + vnd(pointsDiscount);
    document.getElementById('final-total').innerText = vnd(total);
    document.getElementById('hidden-promo-code').value = promoCode;
    document.getElementById('hidden-points').value = pts;

    if (LOGGED_IN && BASE_TOTAL > 0 && total > 0) {
      const earn = Math.floor((TICKET_TOTAL * EARN_TICKET + COMBO_TOTAL * EARN_COMBO) * total / (BASE_TOTAL * 100 * POINT_VALUE));
      document.getElementById('earn-val').innerText = '+' + earn;
      document.getElementById('earn-row').classList.remove('hidden');
    } else {
      document.getElementById('earn-row').classList.add('hidden');
    }
  }

  function applyPromo() {
    const code = document.getElementById('promo-input').value.trim().toUpperCase();
    const fb = document.getElementById('promo-feedback');
    if (!code) {
      promoCode = '';
      fb.classList.add('hidden');
      recalc();
      return;
    }
    fb.classList.remove('hidden');
    const p = PROMOS[code];
    if (p) {
      promoCode = code;
      fb.className = 'text-xs mt-2 text-emerald-400';
      fb.innerText = 'Áp dụng thành công: ' + (p.type === 'percent' ? 'giảm ' + p.value + '%' : 'giảm ' + vnd(p.value)) + '!' + (p.desc ? ' (' + p.desc + ')' : '');
    } else {
      promoCode = '';
      fb.className = 'text-xs mt-2 text-red-400';
      fb.innerText = 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.';
    }
    recalc();
  }

  function selectPromoQuick(code) {
    document.getElementById('promo-input').value = code;
    applyPromo();
  }

  function useMaxPoints() {
    const input = document.getElementById('points-input');
    if (!input) return;
    input.value = maxPoints(BASE_TOTAL - promoDiscount());
    recalc();
  }

  const pointsInput = document.getElementById('points-input');
  if (pointsInput) pointsInput.addEventListener('input', recalc);
  recalc();
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

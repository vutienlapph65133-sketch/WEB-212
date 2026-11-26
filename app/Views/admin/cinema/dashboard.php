<?php
/**
 * app/Views/admin/cinema/dashboard.php - Thống kê Dashboard MovieGo kết nối CSDL thật
 */
$pageTitle = 'Dashboard Thống Kê - MovieGo Admin';
$activeMenu = 'dashboard';
include __DIR__ . '/layouts/sidebar.php';

$totalRevenue = $totalRevenue ?? 0;
$totalTickets = $totalTickets ?? 0;
$totalOrders  = $totalOrders ?? 0;
$totalUsers   = $totalUsers ?? 0;
$totalMovies  = $totalMovies ?? 0;
$totalCinemas = $totalCinemas ?? 0;
$totalScreens = $totalScreens ?? 0;
$topPayMethod = $topPayMethod ?? 'MOMO';
$topPayPct    = $topPayPct ?? 0;
$topMovies    = $topMovies ?? [];
$recentOrders = $recentOrders ?? [];
$chartLabels  = $chartLabels ?? ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
$chartData    = $chartData ?? [0, 0, 0, 0, 0, 0, 0];
?>

<div class="space-y-6">
  <!-- Page Header -->
  <div>
    <h1 class="text-xl sm:text-2xl font-black text-gray-900">Tổng quan Hoạt động</h1>
    <p class="text-xs text-gray-500 mt-0.5">Dữ liệu doanh thu & lượng vé bán ra cập nhật theo thời gian thực từ CSDL.</p>
  </div>

  <!-- ─── 4 KPI Cards ─── -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1 -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-400">Tổng doanh thu</p>
        <p class="text-xl font-black text-gray-900 mt-1"><?= number_format($totalRevenue, 0, ',', '.') ?>₫</p>
        <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-1">
          <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Đơn đã hoàn thành
        </span>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
        <i data-lucide="dollar-sign" class="w-6 h-6"></i>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-400">Vé đã bán ra</p>
        <p class="text-xl font-black text-gray-900 mt-1"><?= number_format($totalTickets) ?> vé</p>
        <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-1">
          <i data-lucide="ticket" class="w-3.5 h-3.5"></i> <?= $totalOrders ?> đơn hàng
        </span>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
        <i data-lucide="ticket" class="w-6 h-6"></i>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-400">Phim đang chiếu</p>
        <p class="text-xl font-black text-gray-900 mt-1"><?= $totalMovies ?> phim</p>
        <span class="text-[11px] text-gray-500 flex items-center gap-1 mt-1">
          Đang phục vụ khán giả
        </span>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
        <i data-lucide="film" class="w-6 h-6"></i>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-400">Khách hàng đăng ký</p>
        <p class="text-xl font-black text-gray-900 mt-1"><?= $totalUsers ?> thành viên</p>
        <span class="text-[11px] text-blue-600 font-semibold flex items-center gap-1 mt-1">
          Tài khoản khách hàng
        </span>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
    </div>
  </div>

  <!-- ─── Biểu đồ thống kê ─── -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Doanh thu 7 ngày (8 cols) -->
    <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="font-bold text-sm text-gray-900">Biểu đồ doanh thu 7 ngày qua</h3>
        <span class="text-xs text-blue-600 font-semibold bg-blue-50 px-2.5 py-1 rounded-lg">Đơn vị: VNĐ</span>
      </div>
      <div class="h-64">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Top Phim (4 cols) -->
    <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-sm text-gray-900">Trạng thái Hệ Thống</h3>
      </div>
      <div class="space-y-4 text-xs pt-2">
        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
          <span class="font-semibold text-gray-700">Máy chủ CSDL</span>
          <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px]">Đang kết nối</span>
        </div>
        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
          <span class="font-semibold text-gray-700">Tổng số rạp</span>
          <span class="font-bold text-gray-900"><?= (int)$totalCinemas ?> cụm rạp</span>
        </div>
        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
          <span class="font-semibold text-gray-700">Phòng chiếu kích hoạt</span>
          <span class="font-bold text-gray-900"><?= (int)$totalScreens ?> phòng</span>
        </div>
        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
          <span class="font-semibold text-gray-700">Thanh toán phổ biến (<?= htmlspecialchars($topPayMethod) ?>)</span>
          <span class="font-bold text-pink-600"><?= (int)$topPayPct ?>%</span>
        </div>
      </div>

      <div class="border-t border-gray-100 pt-4 space-y-2.5">
        <h4 class="font-bold text-xs text-gray-900 uppercase tracking-wider">Top Phim bán chạy</h4>
        <?php if (empty($topMovies)): ?>
          <p class="text-xs text-gray-400">Chưa có dữ liệu vé bán.</p>
        <?php else: ?>
          <?php foreach ($topMovies as $tm): ?>
            <div class="flex items-center justify-between text-xs py-1">
              <span class="font-semibold text-gray-700 truncate pr-2"><?= htmlspecialchars($tm['title']) ?></span>
              <span class="shrink-0 font-bold text-blue-600"><?= (int)$tm['tickets_sold'] ?> vé</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ─── Bảng đơn hàng gần đây ─── -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
      <h3 class="font-bold text-sm text-gray-900">Đơn đặt vé gần đây</h3>
      <a href="/admin/cinema/orders" class="text-xs text-blue-600 font-semibold hover:underline">Xem tất cả &rarr;</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Mã đơn</th>
            <th class="py-3 px-4">Khách hàng</th>
            <th class="py-3 px-4">Phim & Rạp</th>
            <th class="py-3 px-4">Tổng tiền</th>
            <th class="py-3 px-4">Phương thức</th>
            <th class="py-3 px-4">Trạng thái</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="6" class="py-6 text-center text-gray-400">Chưa có đơn hàng nào.</td></tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $od): ?>
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3 px-4 font-mono font-bold text-blue-600"><?= htmlspecialchars($od['code']) ?></td>
                <td class="py-3 px-4 font-semibold text-gray-800"><?= htmlspecialchars($od['customer_name']) ?></td>
                <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($od['movie_title']) ?> (<?= htmlspecialchars($od['cinema_name']) ?>)</td>
                <td class="py-3 px-4 font-bold text-gray-900"><?= number_format($od['total'], 0, ',', '.') ?>₫</td>
                <td class="py-3 px-4 uppercase text-gray-500 font-medium"><?= htmlspecialchars($od['payment_method']) ?></td>
                <td class="py-3 px-4">
                  <?php if ($od['status'] === 'completed'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Thành công</span>
                  <?php elseif ($od['status'] === 'pending'): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Chờ xử lý</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Đã hủy</span>
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

<!-- Chart.js initialization -->
<script>
  const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
  new Chart(ctxRevenue, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels) ?>,
      datasets: [{
        label: 'Doanh thu (₫)',
        data: <?= json_encode($chartData) ?>,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.08)',
        fill: true,
        tension: 0.35,
        borderWidth: 2,
        pointBackgroundColor: '#2563eb',
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { 
          grid: { color: '#f3f4f6' },
          ticks: {
            callback: function(value) {
              return new Intl.NumberFormat('vi-VN').format(value) + '₫';
            }
          }
        },
        x: { grid: { display: false } }
      }
    }
  });
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>

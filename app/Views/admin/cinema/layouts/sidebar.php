<?php
defined('APP_ENTRY') or exit('Forbidden');
/**
 * app/Views/admin/cinema/layouts/sidebar.php
 * Sidebar LIGHT MODE cho trang quản trị MovieGo Admin.
 */
$pageTitle = $pageTitle ?? 'Quản Trị Hệ Thống - MovieGo';
$activeMenu = $activeMenu ?? 'dashboard';
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$menuItems = [
  'dashboard'  => ['Dashboard Thống kê', '/admin/cinema',           'layout-dashboard'],
  'movies'     => ['Quản lý Phim',        '/admin/cinema/movies',    'film'],
  'cinemas'    => ['Quản lý Cụm Rạp',     '/admin/cinema/cinemas',   'building-2'],
  'showtimes'  => ['Quản lý Suất chiếu',  '/admin/cinema/showtimes',  'calendar'],
  'combos'     => ['Bắp nước & Thể loại', '/admin/cinema/combos',     'popcorn'],
  'orders'     => ['Quản lý Đơn hàng',    '/admin/cinema/orders',     'shopping-cart'],
  'promotions' => ['Khuyến Mãi & Voucher','/admin/cinema/promotions', 'tag'],
  'users'      => ['Quản lý Tài khoản',   '/admin/cinema/users',      'users'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $h($pageTitle) ?></title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://cdn.jsdelivr.net/npm/lucide@0.469.0/dist/umd/lucide.min.js"></script>
  <!-- Chart.js CDN for Admin Analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen">

<div class="flex h-screen overflow-hidden">
  <!-- ─── Sidebar Trái (Light Mode) ─── -->
  <aside id="admin-sidebar" class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0 transition-transform duration-200 fixed lg:static inset-y-0 left-0 z-30 -translate-x-full lg:translate-x-0">
    <!-- Logo -->
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
      <a href="/admin/cinema" class="flex items-center gap-2 font-black text-lg text-gray-900">
        <i data-lucide="film" class="w-6 h-6 text-blue-600"></i>
        <span>MovieGo <span class="text-xs text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">Admin</span></span>
      </a>
      <button type="button" onclick="toggleSidebar(false)" class="lg:hidden p-1 text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
      <?php foreach ($menuItems as $key => [$label, $url, $icon]): 
        $isActive = ($activeMenu === $key);
      ?>
        <a href="<?= $url ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $isActive ? 'bg-blue-50 text-blue-600 shadow-sm border border-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
          <i data-lucide="<?= $icon ?>" class="w-4 h-4 <?= $isActive ? 'text-blue-600' : 'text-gray-400' ?>"></i>
          <span><?= $label ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- Sidebar footer -->
    <div class="p-4 border-t border-gray-100 text-[11px] text-gray-400">
      <p>© <?= date('Y') ?> MovieGo Cinema System</p>
    </div>
  </aside>

  <!-- Sidebar Overlay for Mobile -->
  <div id="sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 bg-black/30 z-20 hidden lg:hidden"></div>

  <!-- ─── Main Content Area ─── -->
  <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <!-- Header Topbar -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 shrink-0 sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <button type="button" onclick="toggleSidebar(true)" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
          <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
        <span class="text-xs font-semibold text-gray-400 hidden sm:inline">Hệ thống quản lý rạp chiếu phim</span>
      </div>

      <div class="flex items-center gap-3">
        <!-- Chuyển sang Client Dark Mode -->
        <a href="/cinema" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold shadow-sm transition-colors">
          <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-400"></i>
          <span>Xem Giao diện Khách</span>
        </a>

        <!-- Avatar Admin + Đăng xuất -->
        <?php $__a = $_SESSION['user'] ?? null; $__name = $__a['full_name'] ?? 'Quản Trị Viên'; ?>
        <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
          <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs">
            <?= $h(function_exists('first_letter_upper') ? first_letter_upper($__name) : 'A') ?>
          </div>
          <span class="text-xs font-bold text-gray-800 hidden sm:inline"><?= $h($__name) ?></span>
          <form method="post" action="/admin/logout" class="inline">
            <input type="hidden" name="csrf" value="<?= $h(function_exists('csrf_token') ? csrf_token() : '') ?>">
            <button type="submit" class="ml-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 hover:text-red-600 transition-colors">Đăng xuất</button>
          </form>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="p-4 sm:p-6 lg:p-8 flex-1">

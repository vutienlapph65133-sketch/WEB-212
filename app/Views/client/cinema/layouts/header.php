<?php
/**
 * app/Views/client/cinema/layouts/header.php
 * Layout Header DARK MODE cho giao diện khách hàng MovieGo.
 */
$pageTitle = $pageTitle ?? 'MovieGo - Hệ Thống Rạp Chiếu Phim Hiện Đại';
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$__u = function_exists('currentUser') ? currentUser() : ($_SESSION['user'] ?? null);
?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $h($pageTitle) ?></title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              DEFAULT: '#f59e0b',
              light: '#fbbf24',
              dark: '#d97706',
            }
          }
        }
      }
    }
  </script>
  
  <script src="https://cdn.jsdelivr.net/npm/lucide@0.469.0/dist/umd/lucide.min.js"></script>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }
  </style>
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col antialiased selection:bg-amber-500 selection:text-gray-950">

  <!-- Navbar Sticky Glassmorphism -->
  <header class="sticky top-0 z-50 bg-gray-950/90 backdrop-blur-md border-b border-gray-800 print:hidden">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
      <!-- Logo -->
      <a href="/cinema" class="flex items-center gap-2.5 text-xl font-extrabold tracking-wide">
        <i data-lucide="film" class="w-7 h-7 text-amber-400"></i>
        <span class="bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-500 bg-clip-text text-transparent">
          MovieGo
        </span>
      </a>

      <!-- Desktop Nav -->
      <nav class="hidden md:flex items-center gap-7 text-sm font-medium">
        <a href="/cinema" class="text-amber-400 hover:text-amber-300 transition-colors">Trang chủ</a>
        <a href="/cinema#now-showing" class="text-gray-300 hover:text-amber-400 transition-colors">Phim đang chiếu</a>
        <a href="/cinema#coming-soon" class="text-gray-300 hover:text-amber-400 transition-colors">Phim sắp chiếu</a>
        <a href="/cinema/promotions" class="text-gray-300 hover:text-amber-400 transition-colors">Khuyến mãi</a>
        <a href="/cinema/ticket" class="text-gray-300 hover:text-amber-400 transition-colors">Tra cứu vé</a>
        <?php if ($__u): ?>
          <a href="/cinema/my-tickets" class="text-gray-300 hover:text-amber-400 transition-colors">Vé của tôi</a>
          <a href="/cinema/points" class="text-gray-300 hover:text-amber-400 transition-colors">Điểm thưởng</a>
        <?php endif; ?>
      </nav>

      <!-- Right actions -->
      <div class="flex items-center gap-3">
        <!-- Chuyển sang Admin (chỉ hiện cho admin) -->
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <a href="/admin/cinema" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-900 border border-gray-700 hover:border-amber-500/50 text-xs font-medium text-gray-300 hover:text-amber-400 transition-all shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Trang Quản Trị</span>
        </a>
        <?php endif; ?>

        <!-- Tài khoản: đăng nhập / đăng ký hoặc tên + điểm thưởng + đăng xuất -->
        <?php if ($__u): ?>
          <a href="/cinema/points" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 hover:border-amber-400 text-amber-400 text-xs font-bold transition-colors" title="Xem điểm thưởng & lịch sử giao dịch (1 điểm = 1.000₫)">
            <span>⭐</span>
            <span><?= number_format((int)($__u['points_balance'] ?? 0), 0, ',', '.') ?> điểm</span>
          </a>
          <a href="/cinema/profile" class="hidden sm:inline text-xs font-semibold text-gray-300 hover:text-amber-400 max-w-[120px] truncate transition-colors" title="Hồ sơ cá nhân (<?= $h($__u['email']) ?>)">
            <?= $h($__u['full_name']) ?>
          </a>
          <form method="post" action="/logout" class="inline">
            <input type="hidden" name="csrf" value="<?= $h(function_exists('csrf_token') ? csrf_token() : '') ?>">
            <button type="submit" class="px-3 py-1.5 rounded-xl border border-gray-700 hover:border-amber-500/50 text-xs font-medium text-gray-300 hover:text-amber-400 transition-colors">Đăng xuất</button>
          </form>
        <?php else: ?>
          <a href="/login" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-300 hover:text-amber-400 transition-colors">Đăng nhập</a>
          <a href="/register" class="hidden sm:inline-flex px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-bold transition-colors">Đăng ký</a>
        <?php endif; ?>

        <!-- Mobile hamburger -->
        <button type="button" id="mobile-menu-btn" aria-label="Menu" class="md:hidden p-2 rounded-lg hover:bg-gray-800 text-gray-300">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-800 bg-gray-950/95 backdrop-blur px-4 py-4 space-y-2">
      <a href="/cinema" class="block px-3 py-2 rounded-lg text-sm font-semibold text-amber-400 bg-amber-500/10">Trang chủ</a>
      <a href="/cinema#now-showing" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Phim đang chiếu</a>
      <a href="/cinema#coming-soon" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Phim sắp chiếu</a>
      <a href="/cinema/promotions" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Khuyến mãi</a>
      <a href="/cinema/ticket" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Tra cứu vé</a>
      <?php if ($__u): ?>
        <a href="/cinema/my-tickets" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Vé của tôi</a>
        <a href="/cinema/profile" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Tài khoản (<?= $h($__u['full_name']) ?>)</a>
        <a href="/cinema/points" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold text-amber-400 bg-amber-500/10">
          <span>⭐ Điểm thưởng của bạn</span>
          <span><?= number_format((int)($__u['points_balance'] ?? 0), 0, ',', '.') ?> điểm</span>
        </a>
      <?php else: ?>
        <a href="/login" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Đăng nhập</a>
        <a href="/register" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">Đăng ký</a>
      <?php endif; ?>
      <?php if (function_exists('isAdmin') && isAdmin()): ?>
      <a href="/admin/cinema" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-amber-400 border border-amber-500/30 bg-amber-500/10 mt-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        Vào Trang Quản Trị (Light Mode)
      </a>
      <?php endif; ?>
    </div>
  </header>

  <script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('hidden');
    });
  </script>

  <!-- Main Content Wrapper -->
  <main class="flex-1">

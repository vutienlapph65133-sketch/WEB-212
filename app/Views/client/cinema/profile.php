<?php
/**
 * app/Views/client/cinema/profile.php - Hồ sơ cá nhân & Đổi mật khẩu
 */
$pageTitle = 'Tài Khoản Cá Nhân - MovieGo';
include __DIR__ . '/layouts/header.php';

$user    = $user ?? [];
$errors  = $errors ?? [];
$success = $success ?? '';
$err = fn($k) => !empty($errors[$k]) ? '<p class="text-[11px] text-red-400 mt-1">' . $h($errors[$k]) . '</p>' : '';
$inputCls = 'w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 space-y-6">
  <div>
    <h1 class="text-2xl font-black text-white">Tài Khoản Cá Nhân</h1>
    <p class="text-xs text-gray-400 mt-1">Cập nhật thông tin liên hệ nhận vé và bảo mật mật khẩu.</p>
  </div>

  <?php if ($success !== ''): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs font-semibold">
      <?= $h($success) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors['form'])): ?>
    <div class="p-4 rounded-2xl bg-red-950/60 border border-red-500/40 text-red-300 text-xs font-semibold">
      <?= $h($errors['form']) ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Thông tin cơ bản -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
      <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
        <i data-lucide="user" class="w-4 h-4 text-amber-400"></i>
        Thông tin cá nhân
      </h2>
      <form method="post" action="/cinema/profile" class="space-y-4 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_info">

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Email</label>
          <input type="email" value="<?= $h($user['email'] ?? '') ?>" disabled class="w-full bg-gray-950/60 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-500 cursor-not-allowed">
        </div>

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Họ và tên</label>
          <input type="text" name="full_name" required value="<?= $h($user['full_name'] ?? '') ?>" class="<?= $inputCls ?>">
          <?= $err('full_name') ?>
        </div>

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Số điện thoại</label>
          <input type="tel" name="phone" value="<?= $h($user['phone'] ?? '') ?>" placeholder="0912345678" class="<?= $inputCls ?>">
          <?= $err('phone') ?>
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs transition-colors">
          Lưu thông tin
        </button>
      </form>
    </div>

    <!-- Đổi mật khẩu -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
      <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
        <i data-lucide="lock" class="w-4 h-4 text-amber-400"></i>
        Đổi mật khẩu
      </h2>
      <form method="post" action="/cinema/profile" class="space-y-4 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Mật khẩu hiện tại</label>
          <input type="password" name="current_password" required class="<?= $inputCls ?>">
          <?= $err('current_password') ?>
        </div>

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Mật khẩu mới</label>
          <input type="password" name="new_password" required class="<?= $inputCls ?>">
          <?= $err('new_password') ?>
        </div>

        <div>
          <label class="block font-semibold text-gray-300 mb-1.5">Nhập lại mật khẩu mới</label>
          <input type="password" name="confirm_password" required class="<?= $inputCls ?>">
          <?= $err('confirm_password') ?>
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-amber-400 font-bold text-xs transition-colors">
          Cập nhật mật khẩu
        </button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

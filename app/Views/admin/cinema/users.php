<?php
/**
 * app/Views/admin/cinema/users.php - Quản lý Người dùng, Hạng & Điểm thưởng
 */
$pageTitle = 'Quản Lý Người Dùng - MovieGo Admin';
$activeMenu = 'users';
include __DIR__ . '/layouts/sidebar.php';

use App\Core\Loyalty;

$users = $users ?? [];
$msg   = $msg ?? '';
$error = $error ?? '';
$myId  = (int)(currentUser()['id'] ?? 0);
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
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Quản lý Tài Khoản & Điểm Thưởng (<?= count($users) ?>)</h1>
      <p class="text-xs text-gray-500 mt-0.5">Danh sách tài khoản khách hàng, phân quyền, hạng thành viên và số dư điểm thưởng (1 điểm = 1.000₫).</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
          <tr>
            <th class="py-3 px-4">Họ và tên</th>
            <th class="py-3 px-4">Email / SĐT</th>
            <th class="py-3 px-4">Hạng / Chi tiêu</th>
            <th class="py-3 px-4">Điểm thưởng</th>
            <th class="py-3 px-4">Vai trò</th>
            <th class="py-3 px-4 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($users as $u):
            $spent = (int)($u['total_spent'] ?? 0);
            $tier  = Loyalty::tier($spent)['name'];
          ?>
            <tr class="hover:bg-gray-50/80 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px]">
                  <?= htmlspecialchars(first_letter_upper($u['full_name'])) ?>
                </div>
                <div>
                  <span class="block"><?= htmlspecialchars($u['full_name']) ?></span>
                  <span class="text-[10px] font-normal text-gray-400"><?= date('d/m/Y', strtotime($u['created_at'])) ?></span>
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span class="block text-gray-700"><?= htmlspecialchars($u['email']) ?></span>
                <span class="font-mono text-[11px] text-gray-400"><?= htmlspecialchars($u['phone'] ?: 'Chưa cập nhật') ?></span>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tier === 'VVIP' ? 'bg-purple-100 text-purple-700' : ($tier === 'VIP' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') ?>">
                  <?= htmlspecialchars($tier) ?>
                </span>
                <span class="block text-[11px] text-gray-500 mt-1"><?= number_format($spent, 0, ',', '.') ?>₫</span>
              </td>
              <td class="py-3.5 px-4 font-bold text-amber-600">
                <form method="post" action="/admin/cinema/users" class="inline-flex items-center gap-1">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_points">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="number" min="0" step="1" name="points_balance" value="<?= (int)($u['points_balance'] ?? 0) ?>" class="w-20 bg-gray-50 border border-gray-200 rounded px-2 py-0.5 text-xs text-amber-600 font-bold focus:outline-none focus:border-blue-600">
                  <button type="submit" class="px-2 py-0.5 rounded text-blue-600 hover:bg-blue-50 text-[10px] font-bold" title="Lưu điểm thưởng">Lưu</button>
                </form>
              </td>
              <td class="py-3.5 px-4">
                <?php if ($u['role'] === 'admin'): ?>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Quản trị viên (Admin)</span>
                <?php else: ?>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Khách hàng (Customer)</span>
                <?php endif; ?>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <?php if ((int)$u['id'] === $myId): ?>
                    <span class="text-[11px] text-gray-400 italic">Tài khoản hiện tại</span>
                  <?php else: ?>
                    <form method="post" action="/admin/cinema/users" class="inline-flex items-center">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="change_role">
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                      <select name="role" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-[11px] text-gray-700 focus:outline-none focus:border-blue-600">
                        <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                      </select>
                    </form>

                    <form method="post" action="/admin/cinema/users" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                      <button type="submit" class="p-1 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa tài khoản">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>

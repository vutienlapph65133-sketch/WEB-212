<?php
/**
 * app/Core/helpers.php - Hàm dùng chung cho đăng nhập / đăng ký (session, CSRF, chuyển trang)
 * Yêu cầu: biến toàn cục $base (thư mục con của site) đã được khai báo trong public/index.php
 */

// Tạo URL kèm thư mục con (vd: /cinema_project_ready/public + /login)
function url(string $path = ''): string
{
    global $base;
    return ($base ?? '') . $path;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function currentUser(): ?array
{
    if (empty($_SESSION['user']['id'])) {
        return null;
    }
    try {
        $st = \App\Core\Database::pdo()->prepare('SELECT id, full_name, email, phone, role, points_balance, total_spent FROM users WHERE id = ? LIMIT 1');
        $st->execute([$_SESSION['user']['id']]);
        $u = $st->fetch();
        if ($u) {
            $_SESSION['user'] = $u;
            return $u;
        }
    } catch (\Throwable $e) {}
    return $_SESSION['user'] ?? null;
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'admin';
}

function loginUser(array $user): void
{
    session_regenerate_id(true);          // chống session fixation
    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'role'      => $user['role'],
    ];
    unset($_SESSION['login_fail']);
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ---- CSRF ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/** Tạo đủ 96 ghế mặc định (A-H x 1-12) cho một phòng chiếu mới */
function seedScreenSeats(\PDO $pdo, int $screenId): void
{
    $st = $pdo->prepare('INSERT IGNORE INTO seats (screen_id, row_label, seat_number, seat_type) VALUES (?, ?, ?, ?)');
    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $row) {
        $type = in_array($row, ['D', 'E', 'F'], true) ? 'vip' : ($row === 'H' ? 'couple' : 'standard');
        for ($col = 1; $col <= 12; $col++) {
            $st->execute([$screenId, $row, $col, $type]);
        }
    }
}

// ---- Chống dò mật khẩu: sai 5 lần thì khóa 60 giây ----
function loginLockedSeconds(): int
{
    $until = $_SESSION['login_fail']['until'] ?? 0;
    return max(0, $until - time());
}

function loginFailed(): void
{
    $f = $_SESSION['login_fail'] ?? ['count' => 0, 'until' => 0];
    $f['count']++;
    if ($f['count'] >= 5) {
        $f = ['count' => 0, 'until' => time() + 60];
    }
    $_SESSION['login_fail'] = $f;
}

// Không phụ thuộc extension mbstring (một số cấu hình PHP chưa bật)
function str_len(string $s): int
{
    return (int) preg_match_all('/./us', $s);
}

function first_letter_upper(string $s): string
{
    if (!preg_match('/^./us', $s, $m)) {
        return '?';
    }
    return function_exists('mb_strtoupper') ? mb_strtoupper($m[0]) : strtoupper($m[0]);
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

// Chuẩn hóa/kiểm tra user từ DB; trả về mảng user hoặc null. Ném PDOException nếu lỗi DB.
function findUserByEmail(string $email): ?array
{
    $st = \App\Core\Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    return $st->fetch() ?: null;
}

const DB_ERROR_MSG = 'Không kết nối được CSDL. Hãy import database/cinema_db.sql và kiểm tra app/Config/database.php.';

<?php
/**
 * public/index.php - Router tối giản CHỈ ĐỂ XEM THỬ giao diện bằng PHP built-in server.
 * Chạy:  php -S localhost:8000 -t public public/index.php
 * Khi ghép vào CodeIgniter 4 thì KHÔNG dùng file này (dùng app/Config/Routes.php).
 */

// Thư mục con khi chạy qua Apache/Laragon, vd: /cinema_project_ready/public
// (chạy bằng `php -S` thì $base = '' nên không ảnh hưởng gì)
$base = '';
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php') {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

// Bỏ phần thư mục con khỏi đường dẫn để khớp với switch bên dưới
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($base !== '' && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base)) ?: '/';
}

// File tĩnh có thật (css, js, ảnh) thì để server tự trả về
if (is_file(__DIR__ . $path)) {
    return false;
}


if ($base !== '') {
    ob_start(function ($html) use ($base) {
        return preg_replace('~\b(href|src|action)=(["\'])/(?!/)~', '$1=$2' . $base . '/', $html);
    });
}

// Hàm view() giả lập giống CodeIgniter: view('client/cinema/home') -> app/Views/client/cinema/home.php
function view(string $name, array $data = []): string
{
    extract($data);
    ob_start();
    include __DIR__ . '/../app/Views/' . $name . '.php';
    return ob_get_clean();
}

define('APP_ENTRY', true);   // các view admin kiểm tra cờ này để không bị mở trực tiếp bằng URL
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../app/Core/Loyalty.php';
require __DIR__ . '/../app/Controllers/CinemaController.php';
require __DIR__ . '/../app/Controllers/AdminCinemaController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/AdminAuthController.php';

use App\Controllers\AdminAuthController;
use App\Controllers\AdminCinemaController;
use App\Controllers\AuthController;
use App\Controllers\CinemaController;

$client    = new CinemaController();
$admin     = new AdminCinemaController();
$auth      = new AuthController();
$adminAuth = new AdminAuthController();
$path      = rtrim($path, '/') ?: '/';

// Khu vực quản trị: chưa đăng nhập admin thì chuyển về trang đăng nhập admin
// Chặn TẤT CẢ /admin/* (trừ /admin/login và /admin/logout) cho người dùng thường
if (strpos($path, '/admin') === 0 && !in_array($path, ['/admin/login', '/admin/logout']) && !isAdmin()) {
    redirect('/admin/login');
}

switch ($path) {
    case '/':
    case '/cinema':                 echo $client->index();     break;
    case '/cinema/detail':          echo $client->detail();    break;
    case '/cinema/seats':           echo $client->seats();     break;
    case '/cinema/checkout':        echo $client->checkout();  break;
    case '/cinema/promotions':      echo $client->promotions();break;
    case '/cinema/points':          echo $client->points();    break;
    case '/cinema/ticket':          echo $client->ticket();    break;
    case '/cinema/spa':             echo $client->spa();       break;

    case '/login':                  echo $auth->login();       break;
    case '/register':               echo $auth->register();    break;
    case '/logout':                 $auth->logout();           break;

    case '/admin/login':            echo $adminAuth->login();  break;
    case '/admin/logout':           $adminAuth->logout();      break;

    case '/admin/cinema':           echo $admin->index();      break;
    case '/admin/cinema/movies':    echo $admin->movies();     break;
    case '/admin/cinema/cinemas':   echo $admin->cinemas();    break;
    case '/admin/cinema/showtimes': echo $admin->showtimes();  break;
    case '/admin/cinema/orders':    echo $admin->orders();     break;
    case '/admin/cinema/promotions':echo $admin->promotions(); break;
    case '/admin/cinema/users':     echo $admin->users();      break;

    default:
        http_response_code(404);
        echo '404 - Không tìm thấy trang';
}

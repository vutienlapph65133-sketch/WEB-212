<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Loyalty;
use PDO;

/**
 * app/Controllers/AdminCinemaController.php
 * Controller xử lý Quản trị Hệ thống MovieGo Admin kết nối CSDL MySQL thật.
 */
class AdminCinemaController
{
    /**
     * Dashboard Thống kê thời gian thực từ CSDL
     */
    public function index()
    {
        $pdo = Database::pdo();

        $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $totalOrders  = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalTickets = (int)$pdo->query(
            "SELECT COUNT(*) FROM order_seats os
             JOIN orders o ON o.id = os.order_id
             WHERE o.status = 'completed'"
        )->fetchColumn();
        $totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
        $totalMovies  = (int)$pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'now_showing'")->fetchColumn();
        $totalCinemas = (int)$pdo->query("SELECT COUNT(*) FROM cinemas")->fetchColumn();
        $totalScreens = (int)$pdo->query("SELECT COUNT(*) FROM screens")->fetchColumn();

        $payRows = $pdo->query(
            "SELECT payment_method, COUNT(*) AS cnt
             FROM orders
             WHERE status = 'completed'
             GROUP BY payment_method
             ORDER BY cnt DESC"
        )->fetchAll();
        $completedOrdersCnt = array_sum(array_column($payRows, 'cnt'));
        $topPayMethod = !empty($payRows) ? strtoupper($payRows[0]['payment_method']) : 'MOMO';
        $topPayPct    = $completedOrdersCnt > 0 ? (int)round(($payRows[0]['cnt'] / $completedOrdersCnt) * 100) : 0;

        $topMovies = $pdo->query(
            "SELECT m.id, m.title, m.poster,
                    COUNT(os.id) AS tickets_sold,
                    COALESCE(SUM(os.price), 0) AS ticket_revenue
             FROM movies m
             LEFT JOIN showtimes s ON s.movie_id = m.id
             LEFT JOIN orders o ON o.showtime_id = s.id AND o.status = 'completed'
             LEFT JOIN order_seats os ON os.order_id = o.id
             GROUP BY m.id
             ORDER BY tickets_sold DESC, m.rating DESC
             LIMIT 5"
        )->fetchAll();

        $recentOrders = $pdo->query(
            "SELECT o.*, m.title AS movie_title, s.starts_at, c.name AS cinema_name
             FROM orders o
             JOIN showtimes s ON s.id = o.showtime_id
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             ORDER BY o.created_at DESC LIMIT 6"
        )->fetchAll();

        $revenue7Days = $pdo->query(
            "SELECT DATE(created_at) AS order_date, COALESCE(SUM(total), 0) AS daily_total
             FROM orders
             WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        )->fetchAll();

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = date('d/m', strtotime($d));
            $found = false;
            foreach ($revenue7Days as $r) {
                if ($r['order_date'] === $d) {
                    $chartData[] = (int)$r['daily_total'];
                    $found = true;
                    break;
                }
            }
            if (!$found) $chartData[] = 0;
        }

        return view('admin/cinema/dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'totalTickets',
            'totalUsers',
            'totalMovies',
            'totalCinemas',
            'totalScreens',
            'topPayMethod',
            'topPayPct',
            'topMovies',
            'recentOrders',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Quản lý Phim: Xem danh sách, Thêm mới, Chỉnh sửa, Gán thể loại, Xóa phim
     */
    public function movies()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';

                if ($action === 'add' || $action === 'edit') {
                    $id          = (int)($_POST['id'] ?? 0);
                    $title       = trim($_POST['title'] ?? '');
                    $poster      = trim($_POST['poster'] ?? '');
                    $backdrop    = trim($_POST['backdrop'] ?? '');
                    $trailerUrl  = trim($_POST['trailer_url'] ?? '');
                    $releaseDate = !empty($_POST['release_date']) ? $_POST['release_date'] : null;
                    $duration    = max(1, (int)($_POST['duration'] ?? 120));
                    $rating      = min(10.0, max(0.0, (float)($_POST['rating'] ?? 8.0)));
                    $ageRating   = trim($_POST['age_rating'] ?? 'P');
                    $director    = trim($_POST['director'] ?? '');
                    $castList    = trim($_POST['cast_list'] ?? '');
                    $language    = trim($_POST['language'] ?? 'Tiếng Việt');
                    $status      = trim($_POST['status'] ?? 'now_showing');
                    $description = trim($_POST['description'] ?? '');
                    $genreIds    = array_values(array_unique(array_filter(array_map('intval', $_POST['genres'] ?? []))));

                    if (!in_array($ageRating, ['P', 'T13', 'T16', 'T18'], true)) $ageRating = 'P';
                    if (!in_array($status, ['now_showing', 'coming_soon', 'stopped'], true)) $status = 'now_showing';

                    if ($title === '') {
                        $error = 'Vui lòng nhập tên phim.';
                    } elseif ($action === 'add') {
                        $pdo->beginTransaction();
                        try {
                            $stmt = $pdo->prepare(
                                "INSERT INTO movies (title, poster, backdrop, trailer_url, release_date, duration, rating, age_rating, director, cast_list, language, status, description)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            $stmt->execute([
                                $title, $poster ?: null, $backdrop ?: null, $trailerUrl ?: null, $releaseDate,
                                $duration, $rating, $ageRating, $director ?: null, $castList ?: null,
                                $language ?: null, $status, $description
                            ]);
                            $newId = (int)$pdo->lastInsertId();
                            if (!empty($genreIds)) {
                                $gs = $pdo->prepare('INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)');
                                foreach ($genreIds as $gid) $gs->execute([$newId, $gid]);
                            }
                            $pdo->commit();
                            $msg = 'Đã thêm phim mới thành công!';
                        } catch (\Throwable $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            $error = 'Không thể thêm phim: ' . $e->getMessage();
                        }
                    } elseif ($id > 0) {
                        $pdo->beginTransaction();
                        try {
                            $stmt = $pdo->prepare(
                                "UPDATE movies
                                 SET title = ?, poster = ?, backdrop = ?, trailer_url = ?, release_date = ?,
                                     duration = ?, rating = ?, age_rating = ?, director = ?, cast_list = ?,
                                     language = ?, status = ?, description = ?
                                 WHERE id = ?"
                            );
                            $stmt->execute([
                                $title, $poster ?: null, $backdrop ?: null, $trailerUrl ?: null, $releaseDate,
                                $duration, $rating, $ageRating, $director ?: null, $castList ?: null,
                                $language ?: null, $status, $description, $id
                            ]);
                            $pdo->prepare('DELETE FROM movie_genres WHERE movie_id = ?')->execute([$id]);
                            if (!empty($genreIds)) {
                                $gs = $pdo->prepare('INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)');
                                foreach ($genreIds as $gid) $gs->execute([$id, $gid]);
                            }
                            $pdo->commit();
                            $msg = 'Đã cập nhật thông tin phim!';
                        } catch (\Throwable $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            $error = 'Không thể cập nhật phim: ' . $e->getMessage();
                        }
                    }
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
                            $stmt->execute([$id]);
                            $msg = 'Đã xóa phim thành công!';
                        } catch (\PDOException $e) {
                            $error = 'Không thể xóa phim vì đã có suất chiếu phát sinh đơn đặt vé. Hãy chuyển trạng thái sang "Ngừng chiếu".';
                        }
                    }
                }
            }
        }

        $movies = $pdo->query(
            "SELECT m.*, GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genre_names,
                    GROUP_CONCAT(DISTINCT g.id ORDER BY g.id SEPARATOR ',') AS genre_ids,
                    COUNT(DISTINCT s.id) AS showtimes_count
             FROM movies m
             LEFT JOIN movie_genres mg ON mg.movie_id = m.id
             LEFT JOIN genres g ON g.id = mg.genre_id
             LEFT JOIN showtimes s ON s.movie_id = m.id
             GROUP BY m.id
             ORDER BY m.id DESC"
        )->fetchAll();

        $genresList = $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();

        return view('admin/cinema/movies', compact('movies', 'genresList', 'msg', 'error'));
    }

    /**
     * Quản lý Cụm Rạp: Thêm (kèm phòng & ghế tự động), Sửa, Thêm phòng chiếu, Xóa
     */
    public function cinemas()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'add') {
                    $name    = trim($_POST['name'] ?? '');
                    $address = trim($_POST['address'] ?? '');
                    $city    = trim($_POST['city'] ?? '');
                    $phone   = trim($_POST['phone'] ?? '');

                    if ($name !== '' && $address !== '') {
                        $pdo->beginTransaction();
                        try {
                            $stmt = $pdo->prepare("INSERT INTO cinemas (name, address, city, phone) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$name, $address, $city, $phone ?: null]);
                            $cinemaId = (int)$pdo->lastInsertId();

                            $screenStmt = $pdo->prepare("INSERT INTO screens (cinema_id, name) VALUES (?, ?)");
                            foreach (['Phòng 1', 'Phòng 2'] as $sName) {
                                $screenStmt->execute([$cinemaId, $sName]);
                                seedScreenSeats($pdo, (int)$pdo->lastInsertId());
                            }
                            $pdo->commit();
                            $msg = 'Đã thêm cụm rạp mới (kèm 2 phòng chiếu & 192 ghế) thành công!';
                        } catch (\Throwable $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            $error = 'Không thể thêm cụm rạp: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Vui lòng nhập đầy đủ tên và địa chỉ cụm rạp.';
                    }
                } elseif ($action === 'edit') {
                    $id      = (int)($_POST['id'] ?? 0);
                    $name    = trim($_POST['name'] ?? '');
                    $address = trim($_POST['address'] ?? '');
                    $city    = trim($_POST['city'] ?? '');
                    $phone   = trim($_POST['phone'] ?? '');
                    if ($id && $name !== '' && $address !== '') {
                        $pdo->prepare("UPDATE cinemas SET name = ?, address = ?, city = ?, phone = ? WHERE id = ?")
                            ->execute([$name, $address, $city, $phone ?: null, $id]);
                        $msg = 'Đã cập nhật thông tin cụm rạp!';
                    }
                } elseif ($action === 'add_screen') {
                    $cinemaId = (int)($_POST['cinema_id'] ?? 0);
                    $screenName = trim($_POST['screen_name'] ?? '');
                    if ($cinemaId && $screenName !== '') {
                        $pdo->beginTransaction();
                        try {
                            $pdo->prepare("INSERT INTO screens (cinema_id, name) VALUES (?, ?)")
                                ->execute([$cinemaId, $screenName]);
                            seedScreenSeats($pdo, (int)$pdo->lastInsertId());
                            $pdo->commit();
                            $msg = "Đã thêm '{$screenName}' (96 ghế) thành công!";
                        } catch (\PDOException $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            $error = 'Phòng chiếu này đã tồn tại trong cụm rạp.';
                        }
                    }
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM cinemas WHERE id = ?");
                            $stmt->execute([$id]);
                            $msg = 'Đã xóa cụm rạp!';
                        } catch (\PDOException $e) {
                            $error = 'Không thể xóa cụm rạp vì đang có suất chiếu đã phát sinh đơn đặt vé.';
                        }
                    }
                }
            }
        }

        $cinemas = $pdo->query(
            "SELECT c.*, COUNT(DISTINCT sc.id) AS screens_count, COUNT(DISTINCT se.id) AS seats_count
             FROM cinemas c
             LEFT JOIN screens sc ON sc.cinema_id = c.id
             LEFT JOIN seats se ON se.screen_id = sc.id
             GROUP BY c.id
             ORDER BY c.id ASC"
        )->fetchAll();

        return view('admin/cinema/cinemas', compact('cinemas', 'msg', 'error'));
    }

    /**
     * Quản lý Lịch & Suất Chiếu (Thêm, Sửa, Xóa, Chống trùng giờ)
     */
    public function showtimes()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'add' || $action === 'edit') {
                    $id        = (int)($_POST['id'] ?? 0);
                    $movieId   = (int)($_POST['movie_id'] ?? 0);
                    $screenId  = (int)($_POST['screen_id'] ?? 0);
                    $startsAt  = str_replace('T', ' ', trim($_POST['starts_at'] ?? ''));
                    if (strlen($startsAt) === 16) $startsAt .= ':00';
                    $format    = trim($_POST['format'] ?? '2D');
                    $basePrice = max(0, (int)($_POST['base_price'] ?? 75000));
                    if (!in_array($format, ['2D', '3D', 'IMAX'], true)) $format = '2D';

                    if ($movieId && $screenId && !empty($startsAt)) {
                        $conflictStmt = $pdo->prepare(
                            "SELECT COUNT(*) FROM showtimes
                             WHERE screen_id = ? AND id != ?
                               AND ABS(TIMESTAMPDIFF(MINUTE, starts_at, ?)) < 120"
                        );
                        $conflictStmt->execute([$screenId, $action === 'edit' ? $id : 0, $startsAt]);
                        if ((int)$conflictStmt->fetchColumn() > 0) {
                            $error = 'Phòng chiếu này đã có suất chiếu khác trong vòng 2 giờ quanh khung giờ đã chọn.';
                        } elseif ($action === 'add') {
                            $stmt = $pdo->prepare(
                                "INSERT INTO showtimes (movie_id, screen_id, starts_at, format, base_price)
                                 VALUES (?, ?, ?, ?, ?)"
                            );
                            $stmt->execute([$movieId, $screenId, $startsAt, $format, $basePrice]);
                            $msg = 'Đã tạo suất chiếu mới thành công!';
                        } elseif ($id > 0) {
                            $stmt = $pdo->prepare(
                                "UPDATE showtimes SET movie_id = ?, screen_id = ?, starts_at = ?, format = ?, base_price = ? WHERE id = ?"
                            );
                            $stmt->execute([$movieId, $screenId, $startsAt, $format, $basePrice, $id]);
                            $msg = 'Đã cập nhật suất chiếu!';
                        }
                    }
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM showtimes WHERE id = ?");
                            $stmt->execute([$id]);
                            $msg = 'Đã xóa suất chiếu!';
                        } catch (\PDOException $e) {
                            $error = 'Không thể xóa suất chiếu vì đã có khách đặt vé cho suất này.';
                        }
                    }
                }
            }
        }

        $showtimes = $pdo->query(
            "SELECT s.*, m.title AS movie_title, c.name AS cinema_name, sc.name AS screen_name
             FROM showtimes s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             ORDER BY s.starts_at DESC"
        )->fetchAll();

        $moviesList = $pdo->query("SELECT id, title FROM movies ORDER BY title ASC")->fetchAll();
        $screensList = $pdo->query(
            "SELECT sc.id, sc.name AS screen_name, c.name AS cinema_name
             FROM screens sc
             JOIN cinemas c ON c.id = sc.cinema_id
             ORDER BY c.id, sc.name ASC"
        )->fetchAll();

        return view('admin/cinema/showtimes', compact('showtimes', 'moviesList', 'screensList', 'msg', 'error'));
    }

    /**
     * Quản lý Combo Bắp Nước & Thể Loại Phim
     */
    public function combos()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'add_combo' || $action === 'edit_combo') {
                    $id    = (int)($_POST['id'] ?? 0);
                    $name  = trim($_POST['name'] ?? '');
                    $desc  = trim($_POST['description'] ?? '');
                    $icon  = trim($_POST['icon'] ?? '🍿') ?: '🍿';
                    $price = max(0, (int)($_POST['price'] ?? 0));
                    if ($name !== '' && $price > 0) {
                        if ($action === 'add_combo') {
                            $pdo->prepare("INSERT INTO combos (name, description, icon, price, is_active) VALUES (?, ?, ?, ?, 1)")
                                ->execute([$name, $desc ?: null, $icon, $price]);
                            $msg = 'Đã thêm combo bắp nước mới!';
                        } elseif ($id > 0) {
                            $pdo->prepare("UPDATE combos SET name = ?, description = ?, icon = ?, price = ? WHERE id = ?")
                                ->execute([$name, $desc ?: null, $icon, $price, $id]);
                            $msg = 'Đã cập nhật combo bắp nước!';
                        }
                    } else {
                        $error = 'Vui lòng nhập tên combo và giá hợp lệ.';
                    }
                } elseif ($action === 'toggle_combo') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        $pdo->prepare("UPDATE combos SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
                        $msg = 'Đã cập nhật trạng thái combo!';
                    }
                } elseif ($action === 'delete_combo') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        try {
                            $pdo->prepare("DELETE FROM combos WHERE id = ?")->execute([$id]);
                            $msg = 'Đã xóa combo!';
                        } catch (\PDOException $e) {
                            $error = 'Không thể xóa combo đã có trong đơn hàng. Hãy tắt kích hoạt thay vì xóa.';
                        }
                    }
                } elseif ($action === 'add_genre') {
                    $gName = trim($_POST['genre_name'] ?? '');
                    if ($gName !== '') {
                        try {
                            $pdo->prepare("INSERT INTO genres (name) VALUES (?)")->execute([$gName]);
                            $msg = 'Đã thêm thể loại phim mới!';
                        } catch (\PDOException $e) {
                            $error = 'Thể loại này đã tồn tại.';
                        }
                    }
                } elseif ($action === 'delete_genre') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        $pdo->prepare("DELETE FROM genres WHERE id = ?")->execute([$id]);
                        $msg = 'Đã xóa thể loại phim!';
                    }
                }
            }
        }

        $combos = $pdo->query("SELECT * FROM combos ORDER BY id ASC")->fetchAll();
        $genres = $pdo->query(
            "SELECT g.*, COUNT(mg.movie_id) AS movie_count
             FROM genres g
             LEFT JOIN movie_genres mg ON mg.genre_id = g.id
             GROUP BY g.id
             ORDER BY g.name ASC"
        )->fetchAll();

        return view('admin/cinema/combos', compact('combos', 'genres', 'msg', 'error'));
    }

    /**
     * Quản lý Đơn hàng: Cập nhật trạng thái, Nhả ghế & Hoàn điểm khi hủy
     */
    public function orders()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'update_status') {
                    $id = (int)($_POST['id'] ?? 0);
                    $status = trim($_POST['status'] ?? 'completed');
                    if ($id && in_array($status, ['pending', 'completed', 'cancelled'], true)) {
                        $pdo->beginTransaction();
                        try {
                            $old = $pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
                            $old->execute([$id]);
                            $oldStatus = $old->fetchColumn();

                            if ($oldStatus === 'cancelled' && $status !== 'cancelled') {
                                $pdo->rollBack();
                                $error = 'Đơn hàng đã hủy (ghế đã được nhả và điểm đã hoàn), không thể khôi phục trạng thái.';
                            } else {
                                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                                $stmt->execute([$status, $id]);

                                if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                                    $pdo->prepare("DELETE FROM order_seats WHERE order_id = ?")->execute([$id]);
                                    if ($oldStatus === 'completed') {
                                        Loyalty::refundOrder($pdo, $id);
                                    }
                                }
                                $pdo->commit();
                                $msg = "Đã cập nhật trạng thái đơn sang '{$status}'!";
                            }
                        } catch (\Exception $e) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            $error = 'Không cập nhật được đơn: ' . $e->getMessage();
                        }
                    }
                }
            }
        }

        $orders = $pdo->query(
            "SELECT o.*, m.title AS movie_title, c.name AS cinema_name, s.starts_at,
                    GROUP_CONCAT(CONCAT(se.row_label, se.seat_number) SEPARATOR ', ') AS seats_list
             FROM orders o
             JOIN showtimes s ON s.id = o.showtime_id
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             LEFT JOIN order_seats os ON os.order_id = o.id
             LEFT JOIN seats se ON se.id = os.seat_id
             GROUP BY o.id
             ORDER BY o.created_at DESC"
        )->fetchAll();

        return view('admin/cinema/orders', compact('orders', 'msg', 'error'));
    }

    /**
     * Quản lý Người dùng, Phân quyền & Điều chỉnh điểm thưởng
     */
    public function users()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';
        $myId = (int)(currentUser()['id'] ?? 0);

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                $id = (int)($_POST['id'] ?? 0);

                if ($action === 'change_role' && $id) {
                    $role = trim($_POST['role'] ?? 'customer');
                    if ($id === $myId && $role !== 'admin') {
                        $error = 'Không thể tự hạ quyền tài khoản quản trị đang đăng nhập.';
                    } elseif (in_array($role, ['customer', 'admin'], true)) {
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt->execute([$role, $id]);
                        $msg = 'Đã cập nhật vai trò người dùng!';
                    }
                } elseif ($action === 'update_points' && $id) {
                    $points = max(0, (int)($_POST['points_balance'] ?? 0));
                    Loyalty::adjustPoints($pdo, $id, $points);
                    $msg = 'Đã cập nhật điểm thưởng & ghi lịch sử giao dịch!';
                } elseif ($action === 'delete' && $id) {
                    if ($id === $myId) {
                        $error = 'Không thể tự xóa tài khoản quản trị đang đăng nhập.';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        $msg = 'Đã xóa người dùng!';
                    }
                }
            }
        }

        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

        return view('admin/cinema/users', compact('users', 'msg', 'error'));
    }

    /**
     * Quản lý Mã Giảm Giá & Khuyến Mãi
     */
    public function promotions()
    {
        $pdo = Database::pdo();
        $msg = '';
        $error = '';

        if (isPost()) {
            if (!csrf_check()) {
                $error = 'Phiên làm việc đã hết hạn (CSRF), vui lòng tải lại trang.';
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'add' || $action === 'edit') {
                    $id            = (int)($_POST['id'] ?? 0);
                    $code          = strtoupper(trim($_POST['code'] ?? ''));
                    $description   = trim($_POST['description'] ?? '');
                    $discountType  = trim($_POST['discount_type'] ?? 'percent');
                    $discountValue = (int)($_POST['discount_value'] ?? 0);
                    $expiresAt     = !empty($_POST['expires_at']) ? str_replace('T', ' ', $_POST['expires_at']) : null;

                    if ($code === '' || $discountValue <= 0 || !in_array($discountType, ['percent', 'fixed'], true)) {
                        $error = 'Vui lòng nhập mã và mức giảm hợp lệ (> 0).';
                    } elseif ($discountType === 'percent' && $discountValue > 100) {
                        $error = 'Mức giảm theo phần trăm không được vượt quá 100%.';
                    } else {
                        try {
                            if ($action === 'add') {
                                $stmt = $pdo->prepare(
                                    "INSERT INTO promotions (code, description, discount_type, discount_value, expires_at, is_active)
                                     VALUES (?, ?, ?, ?, ?, 1)"
                                );
                                $stmt->execute([$code, $description, $discountType, $discountValue, $expiresAt]);
                                $msg = 'Đã tạo mã khuyến mãi mới thành công!';
                            } elseif ($id > 0) {
                                $stmt = $pdo->prepare(
                                    "UPDATE promotions SET code = ?, description = ?, discount_type = ?, discount_value = ?, expires_at = ? WHERE id = ?"
                                );
                                $stmt->execute([$code, $description, $discountType, $discountValue, $expiresAt, $id]);
                                $msg = 'Đã cập nhật mã khuyến mãi!';
                            }
                        } catch (\PDOException $e) {
                            $error = 'Lỗi: Mã khuyến mãi này đã tồn tại trong CSDL.';
                        }
                    }
                } elseif ($action === 'toggle') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        $stmt = $pdo->prepare("UPDATE promotions SET is_active = 1 - is_active WHERE id = ?");
                        $stmt->execute([$id]);
                        $msg = 'Đã thay đổi trạng thái kích hoạt của mã khuyến mãi!';
                    }
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id) {
                        $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
                        $stmt->execute([$id]);
                        $msg = 'Đã xóa mã khuyến mãi!';
                    }
                }
            }
        }

        $promotions = $pdo->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll();

        return view('admin/cinema/promotions', compact('promotions', 'msg', 'error'));
    }
}

<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Loyalty;
use PDO;

/**
 * app/Controllers/CinemaController.php
 * Controller xử lý toàn bộ logic cho giao diện Khách hàng MovieGo kết nối CSDL MySQL thật.
 */
class CinemaController
{
    /**
     * Trang chủ MovieGo: Lấy danh sách phim thật từ CSDL, thể loại, phim nổi bật
     */
    public function index()
    {
        $pdo = Database::pdo();
        $search = trim($_GET['q'] ?? '');
        $selectedGenre = trim($_GET['genre'] ?? '');

        $genresList = $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();

        $sql = "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names
                FROM movies m
                LEFT JOIN movie_genres mg ON mg.movie_id = m.id
                LEFT JOIN genres g ON g.id = mg.genre_id";

        $params = [];
        $where = [];

        if ($search !== '') {
            $where[] = "(m.title LIKE ? OR m.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($selectedGenre !== '') {
            $where[] = "g.name = ?";
            $params[] = $selectedGenre;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY m.id ORDER BY m.rating DESC, m.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rawMovies = $stmt->fetchAll();

        $movies = [];
        foreach ($rawMovies as $row) {
            $genres = !empty($row['genre_names']) ? explode(', ', $row['genre_names']) : ['Chưa phân loại'];
            $movies[] = [
                'id'          => (int)$row['id'],
                'title'       => $row['title'],
                'poster'      => $row['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500',
                'backdrop'    => $row['backdrop'] ?: $row['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200',
                'genre'       => $genres,
                'duration'    => (int)$row['duration'],
                'rating'      => (float)$row['rating'],
                'status'      => $row['status'],
                'ageRating'   => $row['age_rating'] ?: 'P',
                'description' => $row['description'] ?: 'Chưa có mô tả.',
            ];
        }

        $nowShowing = array_values(array_filter($movies, fn($m) => $m['status'] === 'now_showing'));
        $comingSoon = array_values(array_filter($movies, fn($m) => $m['status'] === 'coming_soon'));
        $featured = !empty($nowShowing) ? $nowShowing[0] : (!empty($movies) ? $movies[0] : null);
        $bannerMovies = array_slice(!empty($nowShowing) ? array_merge($nowShowing, $comingSoon) : $movies, 0, 6);

        return view('client/cinema/home', compact(
            'movies',
            'nowShowing',
            'comingSoon',
            'featured',
            'bannerMovies',
            'genresList',
            'search',
            'selectedGenre'
        ));
    }

    /**
     * Chi tiết phim & danh sách suất chiếu theo từng rạp
     */
    public function detail()
    {
        $pdo = Database::pdo();
        $movieId = (int)($_GET['id'] ?? 1);

        $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? LIMIT 1");
        $stmt->execute([$movieId]);
        $row = $stmt->fetch();

        if (!$row) {
            redirect('/cinema');
        }

        $genreStmt = $pdo->prepare("SELECT g.name FROM genres g JOIN movie_genres mg ON mg.genre_id = g.id WHERE mg.movie_id = ?");
        $genreStmt->execute([$movieId]);
        $genres = $genreStmt->fetchAll(PDO::FETCH_COLUMN) ?: ['Điện ảnh'];

        $movie = [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'poster'      => $row['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500',
            'backdrop'    => $row['backdrop'] ?: $row['poster'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200',
            'genre'       => $genres,
            'duration'    => (int)$row['duration'],
            'rating'      => (float)$row['rating'],
            'releaseDate' => $row['release_date'] ? date('d/m/Y', strtotime($row['release_date'])) : 'Đang cập nhật',
            'director'    => $row['director'] ?: 'Đang cập nhật',
            'cast'        => !empty($row['cast_list']) ? explode(', ', $row['cast_list']) : ['Đang cập nhật'],
            'description' => $row['description'] ?: 'Chưa có tóm tắt nội dung.',
            'status'      => $row['status'],
            'ageRating'   => $row['age_rating'] ?: 'P',
            'language'    => $row['language'] ?: 'Tiếng Việt - Phụ đề',
        ];

        $cinemas = $pdo->query("SELECT * FROM cinemas ORDER BY id ASC")->fetchAll();

        $showStmt = $pdo->prepare(
            "SELECT s.*, sc.name AS screen_name, c.id AS cinema_id, c.name AS cinema_name
             FROM showtimes s
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             WHERE s.movie_id = ?
             ORDER BY s.starts_at ASC"
        );
        $showStmt->execute([$movieId]);
        $rawShows = $showStmt->fetchAll();

        $showtimes = [];
        foreach ($rawShows as $s) {
            $showtimes[] = [
                'id'       => (int)$s['id'],
                'cinemaId' => (int)$s['cinema_id'],
                'screen'   => $s['screen_name'],
                'time'     => date('H:i', strtotime($s['starts_at'])),
                'date'     => date('d/m/Y', strtotime($s['starts_at'])),
                'format'   => $s['format'],
                'price'    => (int)$s['base_price'],
            ];
        }

        return view('client/cinema/detail', compact('movie', 'cinemas', 'showtimes'));
    }

    /**
     * Sơ đồ chọn ghế & combo bắp nước
     */
    public function seats()
    {
        $pdo = Database::pdo();
        $showtimeId = (int)($_GET['showtime_id'] ?? 1);

        $stmt = $pdo->prepare(
            "SELECT s.*, m.title AS movie_title, m.poster, m.age_rating,
                    sc.id AS screen_id, sc.name AS screen_name,
                    c.id AS cinema_id, c.name AS cinema_name
             FROM showtimes s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$showtimeId]);
        $showtime = $stmt->fetch();

        if (!$showtime) {
            redirect('/cinema');
        }

        $soldStmt = $pdo->prepare(
            "SELECT CONCAT(se.row_label, se.seat_number) AS code
             FROM order_seats os
             JOIN seats se ON se.id = os.seat_id
             JOIN orders o ON o.id = os.order_id
             WHERE os.showtime_id = ? AND o.status != 'cancelled'"
        );
        $soldStmt->execute([$showtimeId]);
        $soldSeats = $soldStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $combos = $pdo->query("SELECT id, name, description AS `desc`, icon, price FROM combos WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

        return view('client/cinema/seats', compact('showtime', 'showtimeId', 'soldSeats', 'combos'));
    }

    /**
     * Trang xem danh sách khuyến mãi / ưu đãi & chính sách điểm thưởng
     */
    public function promotions()
    {
        $pdo = Database::pdo();
        $promotions = $pdo->query(
            "SELECT * FROM promotions
             WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY id DESC"
        )->fetchAll();

        $user = currentUser();
        $loyalty = null;
        if ($user) {
            $loyalty = [
                'balance' => (int)($user['points_balance'] ?? 0),
                'spent'   => (int)($user['total_spent'] ?? 0),
                'tier'    => Loyalty::tier((int)($user['total_spent'] ?? 0))['name'],
            ];
        }

        return view('client/cinema/promotions', compact('promotions', 'user', 'loyalty'));
    }

    /**
     * Thanh toán & Xử lý đặt vé thật lưu vào CSDL (Voucher + Điểm thưởng Loyalty)
     */
    public function checkout()
    {
        $pdo = Database::pdo();
        $user = currentUser();

        // ── XỬ LÝ POST: KHÁCH HÀNG XÁC NHẬN ĐẶT VÉ & THANH TOÁN ──
        if (isPost()) {
            if (!csrf_check()) {
                $_SESSION['checkout_error'] = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
                redirect('/cinema');
            }

            $showtimeId     = (int)($_POST['showtime_id'] ?? 0);
            $seatsParam     = trim($_POST['seats'] ?? '');
            $combosJson     = trim($_POST['combos'] ?? '{}');
            $customerName   = trim($_POST['customer_name'] ?? '');
            $customerPhone  = trim($_POST['customer_phone'] ?? '');
            $customerEmail  = trim($_POST['customer_email'] ?? '');
            $paymentMethod  = trim($_POST['payment_method'] ?? 'momo');
            $promoCode      = strtoupper(trim($_POST['promo_code'] ?? ''));
            $pointsWanted   = max(0, (int)($_POST['points_used'] ?? 0));

            if (!$showtimeId || empty($seatsParam) || empty($customerName) || empty($customerPhone)) {
                redirect('/cinema');
            }

            $stmt = $pdo->prepare("SELECT s.*, sc.id as screen_id FROM showtimes s JOIN screens sc ON sc.id = s.screen_id WHERE s.id = ?");
            $stmt->execute([$showtimeId]);
            $showtime = $stmt->fetch();
            if (!$showtime) {
                redirect('/cinema');
            }

            $seatCodes = array_values(array_filter(array_map('trim', explode(',', $seatsParam))));
            if (empty($seatCodes)) {
                redirect('/cinema/seats?showtime_id=' . $showtimeId);
            }

            $placeholders = implode(',', array_fill(0, count($seatCodes), '?'));
            $seatQuery = $pdo->prepare(
                "SELECT se.id, se.row_label, se.seat_number, se.seat_type,
                        st.name AS type_name, st.surcharge
                 FROM seats se
                 JOIN seat_types st ON st.code = se.seat_type
                 WHERE se.screen_id = ? AND CONCAT(se.row_label, se.seat_number) IN ($placeholders)"
            );
            $seatQuery->execute(array_merge([$showtime['screen_id']], $seatCodes));
            $selectedSeats = $seatQuery->fetchAll();

            if (empty($selectedSeats)) {
                redirect('/cinema/seats?showtime_id=' . $showtimeId);
            }

            // Kiểm tra trùng ghế
            $seatIds = array_column($selectedSeats, 'id');
            $seatIdPlaceholders = implode(',', array_fill(0, count($seatIds), '?'));
            $checkSoldStmt = $pdo->prepare(
                "SELECT se.row_label, se.seat_number
                 FROM order_seats os
                 JOIN seats se ON se.id = os.seat_id
                 JOIN orders o ON o.id = os.order_id
                 WHERE os.showtime_id = ? AND os.seat_id IN ($seatIdPlaceholders) AND o.status != 'cancelled'"
            );
            $checkSoldStmt->execute(array_merge([$showtimeId], $seatIds));
            $conflictSeats = $checkSoldStmt->fetchAll();

            if (!empty($conflictSeats)) {
                $conflictList = implode(', ', array_map(fn($s) => $s['row_label'] . $s['seat_number'], $conflictSeats));
                $_SESSION['checkout_error'] = "Ghế ({$conflictList}) vừa có người đặt. Vui lòng chọn ghế khác.";
                redirect("/cinema/seats?showtime_id={$showtimeId}");
            }

            // Tính tiền vé
            $ticketsTotal = 0;
            $seatsWithPrice = [];
            foreach ($selectedSeats as $s) {
                $price = (int)$showtime['base_price'] + (int)$s['surcharge'];
                $ticketsTotal += $price;
                $seatsWithPrice[] = [
                    'seat_id' => $s['id'],
                    'price'   => $price,
                ];
            }

            // Tính tiền combo
            $combosTotal = 0;
            $combosData = json_decode($combosJson, true) ?: [];
            $combosToInsert = [];
            if (!empty($combosData)) {
                $activeCombos = $pdo->query("SELECT id, name, price FROM combos WHERE is_active = 1")->fetchAll();
                $combosById = array_column($activeCombos, null, 'id');

                foreach ($combosData as $cId => $item) {
                    $qty = (int)($item['qty'] ?? 0);
                    if ($qty > 0 && isset($combosById[$cId])) {
                        $price = (int)$combosById[$cId]['price'];
                        $combosTotal += $price * $qty;
                        $combosToInsert[] = [
                            'combo_id' => $cId,
                            'quantity' => $qty,
                            'price'    => $price,
                        ];
                    }
                }
            }

            // 1. Áp dụng Mã giảm giá (Voucher)
            $discount = 0;
            $promotionId = null;
            if ($promoCode !== '') {
                $promoStmt = $pdo->prepare("SELECT * FROM promotions WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1");
                $promoStmt->execute([$promoCode]);
                $promo = $promoStmt->fetch();
                if ($promo) {
                    $promotionId = $promo['id'];
                    if ($promo['discount_type'] === 'percent') {
                        $discount = (int)round(($ticketsTotal + $combosTotal) * ($promo['discount_value'] / 100));
                    } else {
                        $discount = (int)$promo['discount_value'];
                    }
                }
            }

            $discount   = min($discount, $ticketsTotal + $combosTotal);
            $afterPromo = $ticketsTotal + $combosTotal - $discount;

            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

            // Transaction lưu đơn + điểm thưởng
            $pdo->beginTransaction();
            try {
                $userId  = $user ? (int)$user['id'] : null;
                $balance = $userId ? Loyalty::lockBalance($pdo, $userId) : null;
                if ($balance === null) {
                    $userId  = null;
                    $balance = 0;
                }

                $pointsUsed = 0;
                if ($userId && $pointsWanted > 0) {
                    $pointsUsed = min($pointsWanted, Loyalty::maxRedeem($afterPromo, $balance));
                }
                $pointsDiscount = $pointsUsed * Loyalty::POINT_VALUE;
                $grandTotal     = max(0, $afterPromo - $pointsDiscount);
                $pointsEarned   = $userId ? Loyalty::calcEarn($ticketsTotal, $combosTotal, $grandTotal) : 0;

                $orderStmt = $pdo->prepare(
                    "INSERT INTO orders (code, user_id, showtime_id, customer_name, customer_phone, customer_email,
                                         promotion_id, tickets_total, combos_total, discount,
                                         points_used, points_discount, points_earned, total, payment_method, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')"
                );
                $orderStmt->execute([
                    $orderCode,
                    $userId,
                    $showtimeId,
                    $customerName,
                    $customerPhone,
                    $customerEmail ?: null,
                    $promotionId,
                    $ticketsTotal,
                    $combosTotal,
                    $discount,
                    $pointsUsed,
                    $pointsDiscount,
                    $pointsEarned,
                    $grandTotal,
                    $paymentMethod,
                ]);
                $orderId = (int)$pdo->lastInsertId();

                $seatInsert = $pdo->prepare("INSERT INTO order_seats (order_id, showtime_id, seat_id, price) VALUES (?, ?, ?, ?)");
                foreach ($seatsWithPrice as $swp) {
                    $seatInsert->execute([$orderId, $showtimeId, $swp['seat_id'], $swp['price']]);
                }

                if (!empty($combosToInsert)) {
                    $comboInsert = $pdo->prepare("INSERT INTO order_combos (order_id, combo_id, quantity, price) VALUES (?, ?, ?, ?)");
                    foreach ($combosToInsert as $cti) {
                        $comboInsert->execute([$orderId, $cti['combo_id'], $cti['quantity'], $cti['price']]);
                    }
                }

                if ($userId) {
                    Loyalty::settleOrder($pdo, $userId, $orderId, $orderCode, $balance, $pointsUsed, $pointsEarned, $grandTotal);
                }

                $pdo->commit();
            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $msg = ($e instanceof \PDOException && ($e->errorInfo[1] ?? 0) === 1062)
                    ? 'Ghế vừa có người đặt. Vui lòng chọn ghế khác.'
                    : 'Lỗi trong quá trình tạo đơn: ' . $e->getMessage();
                $_SESSION['checkout_error'] = $msg;
                redirect("/cinema/seats?showtime_id={$showtimeId}");
            }

            redirect('/cinema/ticket?code=' . urlencode($orderCode));
        }

        // ── XỬ LÝ GET: HIỂN THỊ TRANG XÁC NHẬN THANH TOÁN ──
        $showtimeId  = (int)($_GET['showtime_id'] ?? 1);
        $seatsParam  = $_GET['seats'] ?? '';
        $combosParam = $_GET['combos'] ?? '{}';

        $stmt = $pdo->prepare(
            "SELECT s.*, m.title AS movie_title, m.poster, m.age_rating,
                    sc.id AS screen_id, sc.name AS screen_name,
                    c.id AS cinema_id, c.name AS cinema_name, c.address AS cinema_address
             FROM showtimes s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             JOIN cinemas c ON c.id = sc.cinema_id
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$showtimeId]);
        $showtime = $stmt->fetch();

        if (!$showtime) {
            redirect('/cinema');
        }

        $seatList = array_values(array_filter(array_map('trim', explode(',', $seatsParam)), fn($s) => preg_match('/^[A-Z][0-9]{1,2}$/', $s)));
        if (empty($seatList)) {
            redirect('/cinema/seats?showtime_id=' . $showtimeId);
        }

        $ticketTotal = 0;
        $placeholders = implode(',', array_fill(0, count($seatList), '?'));
        $sQuery = $pdo->prepare("SELECT se.*, st.surcharge FROM seats se JOIN seat_types st ON st.code = se.seat_type WHERE se.screen_id = ? AND CONCAT(se.row_label, se.seat_number) IN ($placeholders)");
        $sQuery->execute(array_merge([$showtime['screen_id']], $seatList));
        $foundSeats = $sQuery->fetchAll();
        foreach ($foundSeats as $fs) {
            $ticketTotal += (int)$showtime['base_price'] + (int)$fs['surcharge'];
        }
        if ($ticketTotal === 0) {
            $ticketTotal = count($seatList) * (int)$showtime['base_price'];
        }

        $comboTotal = 0;
        $combosArray = json_decode($combosParam, true) ?: [];
        $comboSummary = [];
        if (!empty($combosArray)) {
            $cRows = $pdo->query("SELECT id, name, price FROM combos WHERE is_active = 1")->fetchAll();
            $cById = array_column($cRows, null, 'id');
            foreach ($combosArray as $cId => $item) {
                $qty = (int)($item['qty'] ?? 0);
                if ($qty > 0 && isset($cById[$cId])) {
                    $price = (int)$cById[$cId]['price'];
                    $comboTotal += $price * $qty;
                    $comboSummary[] = $cById[$cId]['name'] . " (x{$qty})";
                }
            }
        }

        $grandTotal = $ticketTotal + $comboTotal;

        // Danh sách khuyến mãi đang hoạt động (để chọn nhanh & JS tính đồng bộ với server)
        $availablePromos = $pdo->query(
            "SELECT * FROM promotions
             WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY id DESC"
        )->fetchAll();
        $promoMap = [];
        foreach ($availablePromos as $pr) {
            $promoMap[strtoupper($pr['code'])] = [
                'type'  => $pr['discount_type'],
                'value' => (int)$pr['discount_value'],
                'desc'  => $pr['description'] ?? '',
            ];
        }

        // Điểm thưởng hiện tại của khách
        $loyalty = null;
        if ($user) {
            $uStmt = $pdo->prepare('SELECT points_balance, total_spent FROM users WHERE id = ?');
            $uStmt->execute([$user['id']]);
            $uRow = $uStmt->fetch();
            if ($uRow) {
                $loyalty = [
                    'balance' => (int)$uRow['points_balance'],
                    'tier'    => Loyalty::tier((int)$uRow['total_spent'])['name'],
                ];
            }
        }

        return view('client/cinema/checkout', compact(
            'showtime',
            'showtimeId',
            'seatList',
            'seatsParam',
            'combosParam',
            'comboSummary',
            'ticketTotal',
            'comboTotal',
            'grandTotal',
            'user',
            'availablePromos',
            'promoMap',
            'loyalty'
        ));
    }

    /**
     * Trang Điểm thưởng: số dư, hạng thành viên, lịch sử cộng/trừ điểm
     */
    public function points()
    {
        $user = currentUser();
        if (!$user) {
            redirect('/login');
        }
        $pdo = Database::pdo();

        $st = $pdo->prepare('SELECT full_name, points_balance, total_spent FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        $row = $st->fetch();
        if (!$row) {
            redirect('/logout');
        }
        $balance = (int)$row['points_balance'];
        $spent   = (int)$row['total_spent'];
        $tier    = Loyalty::tier($spent);

        $hs = $pdo->prepare(
            'SELECT pt.*, o.code AS order_code FROM point_transactions pt
             LEFT JOIN orders o ON o.id = pt.order_id
             WHERE pt.user_id = ? ORDER BY pt.id DESC LIMIT 50'
        );
        $hs->execute([$user['id']]);
        $history = $hs->fetchAll();

        return view('client/cinema/points', compact('row', 'balance', 'spent', 'tier', 'history'));
    }

    /**
     * Vé điện tử & Tra cứu vé theo mã hoặc số điện thoại
     */
    public function ticket()
    {
        $pdo = Database::pdo();
        $code = trim($_GET['code'] ?? $_POST['search_code'] ?? '');
        $phone = trim($_POST['search_phone'] ?? '');

        $order = null;
        $seats = [];
        $combos = [];
        $searchResults = [];
        $searched = false;

        if ($phone !== '') {
            $searched = true;
            $stmt = $pdo->prepare(
                "SELECT o.*, m.title AS movie_title, s.starts_at, c.name AS cinema_name
                 FROM orders o
                 JOIN showtimes s ON s.id = o.showtime_id
                 JOIN movies m ON m.id = s.movie_id
                 JOIN screens sc ON sc.id = s.screen_id
                 JOIN cinemas c ON c.id = sc.cinema_id
                 WHERE o.customer_phone = ?
                 ORDER BY o.created_at DESC"
            );
            $stmt->execute([$phone]);
            $searchResults = $stmt->fetchAll();
            if (!empty($searchResults)) {
                $code = $searchResults[0]['code'];
            }
        }

        if ($code !== '') {
            $searched = true;
            $stmt = $pdo->prepare(
                "SELECT o.*, s.starts_at, s.format, m.title AS movie_title, m.poster, m.age_rating,
                        sc.name AS screen_name, c.name AS cinema_name, c.address AS cinema_address,
                        p.code AS promo_code
                 FROM orders o
                 JOIN showtimes s ON s.id = o.showtime_id
                 JOIN movies m ON m.id = s.movie_id
                 JOIN screens sc ON sc.id = s.screen_id
                 JOIN cinemas c ON c.id = sc.cinema_id
                 LEFT JOIN promotions p ON p.id = o.promotion_id
                 WHERE o.code = ? LIMIT 1"
            );
            $stmt->execute([$code]);
            $order = $stmt->fetch();

            if ($order) {
                $sStmt = $pdo->prepare(
                    "SELECT os.*, se.row_label, se.seat_number, se.seat_type
                     FROM order_seats os
                     JOIN seats se ON se.id = os.seat_id
                     WHERE os.order_id = ?
                     ORDER BY se.row_label, se.seat_number"
                );
                $sStmt->execute([$order['id']]);
                $seats = $sStmt->fetchAll();

                $cStmt = $pdo->prepare(
                    "SELECT oc.*, c.name, c.icon
                     FROM order_combos oc
                     JOIN combos c ON c.id = oc.combo_id
                     WHERE oc.order_id = ?"
                );
                $cStmt->execute([$order['id']]);
                $combos = $cStmt->fetchAll();
            }
        }

        return view('client/cinema/ticket', compact(
            'order',
            'seats',
            'combos',
            'code',
            'phone',
            'searchResults',
            'searched'
        ));
    }

    /**
     * Tùy chọn: Chạy toàn bộ ứng dụng bằng React SPA
     */
    public function spa()
    {
        return view('client/cinema/react_spa');
    }
}

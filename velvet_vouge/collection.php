<?php
session_start();
$host = 'localhost';
$dbname = 'velvet_vogue'; // Make sure this matches your phpMyAdmin database name!
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get filters from URL parameters
$category = $_GET['category'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'newest';
$price_min = $_GET['price_min'] ?? 0;
$price_max = $_GET['price_max'] ?? 1000;
$size_filter = $_GET['size'] ?? '';
$color_filter = $_GET['color'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;

// Build SQL query with filters
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category !== 'all') {
    $sql .= " AND category = :category";
    $params['category'] = $category;
}
if ($price_min > 0) {
    $sql .= " AND price >= :price_min";
    $params['price_min'] = $price_min;
}
if ($price_max < 1000) {
    $sql .= " AND price <= :price_max";
    $params['price_max'] = $price_max;
}
if ($size_filter) {
    $sql .= " AND sizes LIKE :size";
    $params['size'] = "%$size_filter%";
}
if ($color_filter) {
    $sql .= " AND colors LIKE :color";
    $params['color'] = "%$color_filter%";
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY name ASC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

// Add pagination
$offset = ($page - 1) * $per_page;
$sql .= " LIMIT :offset, :per_page";
$params['offset'] = $offset;
$params['per_page'] = $per_page;

// Fetch products
try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === 'offset' || $key === 'per_page') {
            $stmt->bindValue(':' . $key, (int)$value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':' . $key, $value);
        }
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM products WHERE 1=1";
$count_params = [];
if ($category !== 'all') {
    $count_sql .= " AND category = :category";
    $count_params['category'] = $category;
}
if ($price_min > 0) {
    $count_sql .= " AND price >= :price_min";
    $count_params['price_min'] = $price_min;
}
if ($price_max < 1000) {
    $count_sql .= " AND price <= :price_max";
    $count_params['price_max'] = $price_max;
}
if ($size_filter) {
    $count_sql .= " AND sizes LIKE :size";
    $count_params['size'] = "%$size_filter%";
}
if ($color_filter) {
    $count_sql .= " AND colors LIKE :color";
    $count_params['color'] = "%$color_filter%";
}
try {
    $count_stmt = $pdo->prepare($count_sql);
    foreach ($count_params as $key => $value) {
        $count_stmt->bindValue(':' . $key, $value);
    }
    $count_stmt->execute();
    $total_products = $count_stmt->fetchColumn();
} catch(PDOException $e) {
    $total_products = 0;
}
$total_pages = ceil($total_products / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection - Velvet Vouge</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #000;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #000;
        }

        /* Main Content */
        .main-content {
            display: flex;
            gap: 30px;
            padding: 30px 0;
        }

        /* Sidebar Filters */
        .sidebar {
            width: 280px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
            sticky: top: 120px;
        }

        .filter-section {
            margin-bottom: 30px;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #000;
        }

        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-option input {
            accent-color: #000;
        }

        .filter-option label {
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
        }

        .price-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .color-options {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .color-option {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }

        .color-option:hover {
            border-color: #000;
        }

        .color-option.selected {
            border-color: #000;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.2);
        }

        .apply-filters {
            background: #000;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .apply-filters:hover {
            background: #333;
        }

        /* Products Section */
        .products-section {
            flex: 1;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .products-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #000;
        }

        .products-count {
            color: #666;
            font-size: 0.9rem;
        }

        .sort-options {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .sort-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            background: #fff;
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .product-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            overflow: hidden;
            border-radius: 10px;
            background: #f8f8f8;
            position: relative;
        }

        .product-image img {
            transition: transform 0.4s cubic-bezier(.4,2,.3,1), box-shadow 0.4s;
            will-change: transform;
            display: block;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08) rotate(-2deg);
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            z-index: 2;
        }

        /* Fashion Sale Banner */
        .fashion-sale-banner {
            background: #ededed;
            padding: 40px 0 30px 0;
        }

        .fashion-sale-banner .banner-content {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            border-radius: 24px;
            background: #fff;
            overflow: hidden;
            position: relative;
        }

        .fashion-sale-banner .banner-text {
            flex: 1;
            padding: 60px 40px 60px 60px;
        }

        .fashion-sale-banner .brand-name {
            font-size: 1.3rem;
            color: #888;
            letter-spacing: 2px;
            margin-bottom: 18px;
        }

        .fashion-sale-banner .sale-title {
            font-size: 3.2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 18px;
            font-family: serif;
        }

        .fashion-sale-banner .discount {
            font-size: 1.5rem;
            color: #444;
            margin-bottom: 28px;
        }

        .fashion-sale-banner .discount span {
            color: #000;
        }

        .fashion-sale-banner .description {
            font-size: 1.15rem;
            color: #666;
            margin-bottom: 38px;
            max-width: 430px;
        }

        .fashion-sale-banner .shop-now {
            display: inline-block;
            padding: 18px 38px;
            background: #111;
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.25rem;
            letter-spacing: 1px;
            transition: background 0.2s;
        }

        .fashion-sale-banner .shop-now:hover {
            background: #333;
        }

        .fashion-sale-banner .banner-image {
            flex: 1;
            min-width: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            position: relative;
        }

        .fashion-sale-banner .image-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fashion-sale-banner img {
            width: 420px;
            max-width: 100%;
            border-radius: 24px;
            margin: 40px 0;
            object-fit: cover;
        }

        /* Decorative geometric line */
        .fashion-sale-banner svg {
            position: absolute;
            right: 30px;
            top: 30px;
            z-index: 1;
        }

        /* Footer */
        footer {
            background: #fff;
            color: #222;
            padding: 60px 0 0 0;
            border-top: 1px solid #eee;
            font-family: 'Arial', sans-serif;
        }

        .footer-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 40px 0;
            justify-content: space-between;
            padding: 0 40px;
        }

        /* Info Columns */
        .footer-info {
            flex: 1 1 180px;
            min-width: 180px;
            padding: 0 20px;
        }

        .footer-info .footer-title {
            font-weight: 700;
            margin-bottom: 18px;
        }

        .footer-info .footer-link {
            margin-bottom: 10px;
        }

        .footer-info .footer-link a {
            color: #222;
            text-decoration: none;
        }

        .footer-info .footer-link a:hover {
            text-decoration: underline;
        }

        /* Contact Form */
        .contact-form {
            flex: 1 1 340px;
            min-width: 340px;
            background: #fafafa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 40px;
        }

        .contact-form .form-title {
            font-weight: 700;
            margin-bottom: 18px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 1px solid #bbb;
            margin-bottom: 18px;
            background: transparent;
        }

        .contact-form button {
            width: 100%;
            padding: 12px 0;
            background: #555;
            color: #fff;
            font-weight: 600;
            letter-spacing: 2px;
            border: none;
            border-radius: 2px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        /* Payment Methods */
        .payment-methods {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px 40px 0 40px;
        }

        .payment-methods .payment-title {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .payment-methods img {
            height: 28px;
            margin-right: 10px;
        }

        /* Copyright */
        .copyright {
            background: #e30613;
            color: #fff;
            text-align: center;
            padding: 18px 0;
            margin-top: 40px;
            font-size: 1rem;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: relative;
                top: 0;
                padding: 20px;
                border-radius: 10px 10px 0 0;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }

            .products-section {
                flex: 1;
                padding: 0 20px;
            }

            .products-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .products-title {
                font-size: 1.6rem;
            }

            .products-count {
                font-size: 0.8rem;
            }

            .sort-options {
                flex-direction: column;
                width: 100%;
            }

            .sort-select {
                width: 100%;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .product-card {
                transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            }

            .product-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 3px 15px rgba(0,0,0,0.1);
                background-color: #f9f9f9;
            }

            /* Fashion Sale Banner */
            .fashion-sale-banner {
                padding: 30px 0 20px 0;
            }

            .fashion-sale-banner .banner-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .fashion-sale-banner .banner-text {
                padding: 40px 20px;
            }

            .fashion-sale-banner .brand-name {
                font-size: 1.1rem;
            }

            .fashion-sale-banner .sale-title {
                font-size: 2.5rem;
            }

            .fashion-sale-banner .discount {
                font-size: 1.2rem;
            }

            .fashion-sale-banner .description {
                font-size: 1rem;
                margin-bottom: 25px;
            }

            .fashion-sale-banner .shop-now {
                padding: 15px 30px;
                font-size: 1.1rem;
            }

            .fashion-sale-banner .banner-image {
                margin-top: 20px;
            }

            .fashion-sale-banner img {
                width: 100%;
                max-width: 350px;
                margin: 0 auto;
            }

            /* Footer */
            footer {
                padding: 20px 0;
            }

            .footer-content {
                gap: 20px;
            }

            .footer-logo {
                font-size: 1.5rem;
            }

            .footer-links {
                justify-content: center;
            }

            .social-icons {
                justify-content: center;
            }

            .copyright {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .products-title {
                font-size: 1.4rem;
            }

            .products-count {
                font-size: 0.7rem;
            }

            .sort-options {
                flex-direction: column;
                width: 100%;
            }

            .sort-select {
                width: 100%;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .product-card {
                transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            }

            .product-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                background-color: #f9f9f9;
            }

            /* Fashion Sale Banner */
            .fashion-sale-banner {
                padding: 20px 0 15px 0;
            }

            .fashion-sale-banner .banner-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .fashion-sale-banner .banner-text {
                padding: 30px 15px;
            }

            .fashion-sale-banner .brand-name {
                font-size: 1rem;
            }

            .fashion-sale-banner .sale-title {
                font-size: 2rem;
            }

            .fashion-sale-banner .discount {
                font-size: 1rem;
            }

            .fashion-sale-banner .description {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }

            .fashion-sale-banner .shop-now {
                padding: 12px 25px;
                font-size: 1rem;
            }

            .fashion-sale-banner .banner-image {
                margin-top: 15px;
            }

            .fashion-sale-banner img {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }

            /* Footer */
            footer {
                padding: 15px 0;
            }

            .footer-content {
                gap: 15px;
            }

            .footer-logo {
                font-size: 1.2rem;
            }

            .footer-links {
                justify-content: center;
            }

            .social-icons {
                justify-content: center;
            }

            .copyright {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <header>
    <div class="container">
        <div class="header-content" style="display:flex;justify-content:space-between;align-items:center;padding:15px 0;">
            <!-- Logo -->
            <div class="logo" style="font-size:2.2rem;font-weight:bold;color:#000;letter-spacing:2px;">
                <a href="index.php" style="color:#000;text-decoration:none;">Velvet Vogue</a>
            </div>
            <!-- Search Bar -->
            <form action="collection.php" method="get" style="flex:1;max-width:420px;margin:0 40px;">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:24px;font-size:1rem;">
            </form>
            <!-- Navigation -->
            <ul class="nav-links" style="display:flex;list-style:none;gap:30px;align-items:center;margin:0;">
                <li><a href="index.php" style="color:#333;text-decoration:none;font-weight:500;transition:color 0.3s;">Home</a></li>
                <li><a href="collection.php" style="color:#333;text-decoration:none;font-weight:500;transition:color 0.3s;">Collections</a></li>
                <li><a href="about.php" style="color:#333;text-decoration:none;font-weight:500;transition:color 0.3s;">About</a></li>
                <li><a href="contact.php" style="color:#333;text-decoration:none;font-weight:500;transition:color 0.3s;">Contact</a></li>
                <!-- Cart Icon -->
                <li>
                    <a href="cart.php" style="display:flex;align-items:center;gap:6px;position:relative;color:#333;text-decoration:none;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 20C6.447 20 6 20.448 6 21C6 21.552 6.447 22 7 22C7.553 22 8 21.552 8 21C8 20.448 7.553 20 7 20ZM17 20C16.447 20 16 20.448 16 21C16 21.552 16.447 22 17 22C17.553 22 18 21.552 18 21C18 20.448 17.553 20 17 20ZM7.16 17H17.5C18.328 17 19.042 16.438 19.226 15.629L21.197 7.629C21.356 6.976 20.864 6.333 20.186 6.333H6.21L5.27 2.926C5.111 2.324 4.557 1.917 3.934 2.003C3.312 2.09 2.857 2.652 2.995 3.261L4.295 8.901C4.354 9.166 4.591 9.333 4.86 9.333H19.5C19.776 9.333 20 9.557 20 9.833C20 10.109 19.776 10.333 19.5 10.333H7.16L6.25 13.333H18.5C18.776 13.333 19 13.557 19 13.833C19 14.109 18.776 14.333 18.5 14.333H6.16L7.16 17Z" fill="#333"/>
                        </svg>
                        <span id="cart-count" style="background:#ff4d4f;color:#fff;font-size:0.85rem;font-weight:bold;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;position:absolute;top:-7px;right:-12px;padding:0 5px;">
                            <?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?>
                        </span>
                        Cart
                    </a>
                </li>
                <!-- User/Profile Icon -->
                <li>
                    <a href="admin_panal.php" class="btn-signup" style="display:flex;align-items:center;padding:8px 0;background:transparent;color:#fff;border:none;text-decoration:none;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e11d48;">
                            <span style="color:#fff;font-weight:600;font-size:1.2rem;">
                                <?php
                                    if (isset($_SESSION['user']['username']) && $_SESSION['user']['username']) {
                                        echo strtoupper(substr($_SESSION['user']['username'], 0, 1));
                                    } else {
                                        echo 'A';
                                    }
                                ?>
                            </span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Optional: Add a secondary nav for quick links, offers, or language/currency switcher here -->
</header>

    <div class="container">
        <!-- Replace your current .fashion-sale-banner section with this for the new image and layout -->
<section class="fashion-sale-banner" style="background:#ededed; padding:40px 0 30px 0;">
    <div style="max-width:1100px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 24px rgba(0,0,0,0.07); border-radius:24px; background:#fff; overflow:hidden; position:relative;">
        <div style="flex:1; padding:60px 40px 60px 60px;">
            <div style="font-size:1.3rem; color:#888; letter-spacing:2px; margin-bottom:18px;">BRAND NAME</div>
            <div style="font-size:3.2rem; font-weight:700; color:#222; margin-bottom:18px; font-family:serif;">Fashion Sale</div>
            <div style="font-size:1.5rem; color:#444; margin-bottom:28px;">Save up to <span style="color:#000;">30% off</span></div>
            <div style="font-size:1.15rem; color:#666; margin-bottom:38px; max-width:430px;">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
            </div>
            <a href="#collections" style="display:inline-block; padding:18px 38px; background:#111; color:#fff; font-weight:700; border-radius:8px; text-decoration:none; font-size:1.25rem; letter-spacing:1px; transition:background 0.2s;">SHOP NOW</a>
        </div>
        <div style="flex:1; min-width:320px; display:flex; align-items:center; justify-content:center; background:transparent; position:relative;">
            <div style="position:relative; width:100%; display:flex; align-items:center; justify-content:center;">
                <img src="https://img.freepik.com/free-photo/white-sneakers-black-t-shirt-hanger_23-2149439967.jpg?w=900" alt="Fashion Sale" style="width:420px; max-width:100%; border-radius:24px; margin:40px 0 40px 0; object-fit:cover;">
                <!-- Decorative geometric line -->
                <svg width="180" height="180" style="position:absolute; right:30px; top:30px; z-index:1;" fill="none" stroke="#222" stroke-width="4">
                    <polygon points="30,10 170,30 150,170 10,150" />
                </svg>
            </div>
        </div>
    </div>
</section>

            <!-- Products Section -->
            <div class="products-section">
                <div class="products-header">
                    <div class="products-title">New Arrivals</div>
                    <div class="products-count"><?php echo $total_products; ?> products found</div>
                    <div class="sort-options">
                        <select class="sort-select" id="sort" name="sort">
                            <option value="newest" <?php if($sort_by == 'newest') echo 'selected'; ?>>Sort by: Newest</option>
                            <option value="price_low" <?php if($sort_by == 'price_low') echo 'selected'; ?>>Sort by: Price (Low to High)</option>
                            <option value="price_high" <?php if($sort_by == 'price_high') echo 'selected'; ?>>Sort by: Price (High to Low)</option>
                            <option value="name" <?php if($sort_by == 'name') echo 'selected'; ?>>Sort by: Name</option>
                        </select>
                    </div>
                </div>

                <div class="products-grid">
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php
                                // Use correct image path and fallback if missing
                                $img = !empty($product['image_url']) && file_exists($product['image_url'])
                                    ? $product['image_url']
                                    : 'uploads/products/default.png'; // Place a default.png in uploads/products/
                            ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:100%;height:320px;object-fit:cover;transition:transform 0.4s cubic-bezier(.4,2,.3,1), box-shadow 0.4s;">
                        </div>
                        <div class="product-info" style="padding:16px;">
                            <div class="product-name" style="font-weight:600;font-size:1.05rem;margin-bottom:8px;"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price" style="color:#e30613;font-weight:700;font-size:1.15rem;margin-bottom:6px;">
                                Rs.<?php echo number_format($product['price'], 2); ?>
                            </div>
                            <?php if (!empty($product['sizes'])): ?>
                                <div style="margin-bottom:8px;">
                                    <?php foreach (explode(',', $product['sizes']) as $size): ?>
                                        <span style="display:inline-block;background:#f5f5f5;border-radius:3px;padding:2px 8px;font-size:0.95rem;margin-right:4px;margin-bottom:2px;"><?php echo htmlspecialchars(trim($size)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn" style="display:inline-block;padding:8px 18px;background:#fff;border:1px solid #bbb;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;color:#222;margin-top:8px;">
                                <i class="fa fa-shopping-bag" style="margin-right:6px;"></i>
                                Select Options
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if($total_pages > 1): ?>
                    <div class="pagination-controls">
                        <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort_by; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&size=<?php echo $size_filter; ?>&color=<?php echo $color_filter; ?>" class="prev-page">« Previous</a>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort_by; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&size=<?php echo $size_filter; ?>&color=<?php echo $color_filter; ?>" class="page-number <?php if($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>

                        <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort_by; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&size=<?php echo $size_filter; ?>&color=<?php echo $color_filter; ?>" class="next-page">Next »</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background:#fff; color:#222; padding:60px 0 0 0; border-top:1px solid #eee; font-family:'Arial',sans-serif;">
    <div style="max-width:1600px; margin:0 auto; display:flex; flex-wrap:wrap; gap:40px 0; justify-content:space-between; padding:0 40px;">
        <!-- Info Columns -->
        <div style="flex:1 1 180px; min-width:180px; padding:0 20px;">
            <div style="font-weight:700; margin-bottom:18px;">INFORMATION</div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Home</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">About Us</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Awards</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Careers</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Contact Us</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Corporate Sale</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">CSR</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Launches</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">News & Events</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Our Branches</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Return & Exchange</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Size Guide</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Terms & Conditions</a></div>
        </div>
        <div style="flex:1 1 180px; min-width:180px; padding:0 20px;">
            <div style="font-weight:700; margin-bottom:18px;">QUICK LINKS</div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">My Account</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Wish List</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Size Guide</a></div>
        </div>
        <div style="flex:1 1 180px; min-width:180px; padding:0 20px;">
            <div style="font-weight:700; margin-bottom:18px;">CATEGORIES</div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Women</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Men</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Kids & Baby</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Baby Collection</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Boys</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">Home & Lifestyle</a></div>
            <div style="margin-bottom:10px;"><a href="#" style="color:#222; text-decoration:none;">New Arrivals</a></div>
        </div>
        <div style="flex:1 1 260px; min-width:260px; padding:0 20px;">
            <div style="font-weight:700; margin-bottom:18px;">ONLINE OFFICE OPENING HOURS</div>
            <div style="margin-bottom:10px;">Mon-Fri : 9.00 AM to 5.30 PM</div>
            <div style="margin-bottom:10px;">Saturday : 9.00 AM to 2.30 PM</div>
            <div style="margin-bottom:18px;">Sunday : Closed</div>
            <div style="font-weight:700; margin-bottom:10px;">CONTACT US</div>
            <div style="margin-bottom:10px;">📞 0765347444</div>
            <div style="margin-bottom:18px;">✉️ online@velvetvouge.lk</div>
            <div style="font-weight:700; margin-bottom:10px;">FOR FREE UPDATES</div>
            <div style="margin-bottom:10px;">📱 0765347444</div>
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <a href="#" style="color:#4267B2; font-size:1.5rem;">&#xf09a;</a>
                <a href="#" style="color:#E1306C; font-size:1.5rem;">&#xf16d;</a>
            </div>
        </div>
        <!-- Contact Form -->
        <div style="flex:1 1 340px; min-width:340px; background:#fafafa; padding:30px 30px 20px 30px; border-radius:10px; margin-bottom:40px;">
            <div style="font-weight:700; margin-bottom:18px;">GET IN TOUCH WITH US</div>
            <form>
                <input type="text" placeholder="Name" style="width:100%;padding:10px 0;border:none;border-bottom:1px solid #bbb;margin-bottom:18px;background:transparent;">
                <input type="text" placeholder="Phone Number" style="width:100%;padding:10px 0;border:none;border-bottom:1px solid #bbb;margin-bottom:18px;background:transparent;">
                <input type="email" placeholder="Email" style="width:100%;padding:10px 0;border:none;border-bottom:1px solid #bbb;margin-bottom:18px;background:transparent;">
                <textarea placeholder="Message" style="width:100%;padding:10px 0;border:none;border-bottom:1px solid #bbb;margin-bottom:18px;background:transparent;resize:none;height:60px;"></textarea>
                <button type="submit" style="width:100%;padding:12px 0;background:#555;color:#fff;font-weight:600;letter-spacing:2px;border:none;border-radius:2px;font-size:1.1rem;cursor:pointer;">SUBMIT</button>
            </form>
        </div>
    </div>
    <div style="max-width:1600px; margin:0 auto; padding:30px 40px 0 40px;">
        <div style="font-weight:700; margin-bottom:10px;">WE ACCEPT</div>
        <div style="display:flex; align-items:center; gap:18px; flex-wrap:wrap;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" alt="MasterCard" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Frimi_logo.png" alt="Frimi" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/UnionPay_logo.png" alt="UnionPay" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Cash_on_Delivery_logo.png" alt="Cash on Delivery" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/7/7e/Koko_logo.png" alt="Koko" style="height:28px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2e/Mintpay_logo.png" alt="Mintpay" style="height:28px;">
        </div>
    </div>
    <div style="background:#e30613; color:#fff; text-align:center; padding:18px 0; margin-top:40px; font-size:1rem;">
        Copyright © 2025 - Velvet Vogue - All Rights Reserved. Concept, Design & Development by Your Company
    </div>
</footer>
</body>
</html>
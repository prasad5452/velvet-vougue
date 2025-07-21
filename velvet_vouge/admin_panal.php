<?php
session_start();
require 'includes/db.php'; // Adjust path if necessary

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Fetch admin name
$adminName = 'Admin';
$stmt = $pdo->prepare("SELECT username FROM register WHERE id = ? AND is_admin = 1");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
if ($admin) {
    $adminName = $admin['username'];
}

// ✅ Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ✅ Dashboard statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM register")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('Completed', 'Paid')")->fetchColumn() ?? 0;

// ✅ Handle product form submissions (add/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        // Collect form values
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $discount = isset($_POST['discount']) ? intval($_POST['discount']) : 0;
        $gender = trim($_POST['gender']);
        $type = trim($_POST['type']);
        $size = trim($_POST['size']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

        // ✅ Validate category
        if ($category_id) {
            $cat_check = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
            $cat_check->execute([$category_id]);
            if (!$cat_check->fetch()) {
                die("Error: Invalid category selected.");
            }
        }

        // ✅ Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check && move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = $targetPath;
            }
        } elseif (!empty($_POST['existing_image'])) {
            $image_url = trim($_POST['existing_image']);
        }

        // ✅ Add or Update product
        if (isset($_POST['add_product'])) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, discount, category_id, gender, type, size, image_url)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $discount, $category_id, $gender, $type, $size, $image_url]);
            $_SESSION['success'] = "Product added successfully!";
        } elseif (isset($_POST['update_product'])) {
            $product_id = intval($_POST['product_id']);
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount = ?, category_id = ?, gender = ?, type = ?, size = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $discount, $category_id, $gender, $type, $size, $image_url, $product_id]);
            $_SESSION['success'] = "Product updated successfully!";
        }
        header("Location: admin_panal.php?action=manage_products");
        exit();
    }
    // ✅ Delete product
    elseif (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        // Delete image file
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if ($product && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        // Delete product record
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['success'] = "Product deleted successfully!";
        header("Location: admin_panal.php?action=manage_products");
        exit();
    }
}

// ✅ Fetch all products (with category name)
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

// ✅ Get recent 5 orders
$recentOrders = $pdo->query("
    SELECT o.*, r.email AS customer_email, r.username
    FROM orders o
    LEFT JOIN register r ON o.user_id = r.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Velvet Vogue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7d3aed;
            --primary-dark: #6d28d9;
            --primary-light: #8b5cf6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --error: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --sidebar: #111827;
            --sidebar-hover: #1f2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: var(--dark);
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--sidebar);
            color: white;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            color: var(--primary-light);
            font-size: 1.75rem;
        }

        .sidebar-nav {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: var(--gray-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .nav-link i {
            width: 1.5rem;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background-color: rgba(125, 58, 237, 0.03);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 5px 15px rgba(125, 58, 237, 0.3);
        }

        .btn-danger {
            background-color: var(--error);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #0d9b6c;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(125, 58, 237, 0.15);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .select-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            background-color: white;
            cursor: pointer;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert i {
            font-size: 1.25rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Dashboard Cards */
        .admin-greeting {
            font-size: 1.1rem;
            color: var(--primary);
            margin-top: 0.5rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .dashboard-card .icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(125, 58, 237, 0.1);
        }

        .dashboard-card.users .icon {
            color: var(--primary);
        }

        .dashboard-card.revenue .icon {
            color: var(--success);
            background-color: rgba(16, 185, 129, 0.1);
        }

        .dashboard-card.orders .icon {
            color: var(--primary-dark);
            background-color: rgba(109, 40, 217, 0.1);
        }

        .dashboard-card .value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .dashboard-card .label {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        /* Product Image */
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.25rem;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
                padding: 1.5rem 1rem;
            }
            
            .main-content {
                margin-left: 240px;
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            
            .admin-container {
                flex-direction: column;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <a href="index.php" class="logo">
                <i class="fas fa-tshirt logo-icon"></i>
                <span>Velvet Vogue</span>
            </a>
            
            <p style="color: var(--gray-light); margin-bottom: 2rem; font-size: 0.875rem;">Admin Panel</p>
            
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="admin_panal.php" class="nav-link <?php echo (!isset($_GET['action']) ? 'active' : ''); ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_panal.php?action=add_product" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'add_product') ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Product</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_panal.php?action=manage_products" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'manage_products') ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i>
                        <span>Manage Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_panal.php?action=manage_orders" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'manage_orders') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Manage Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link" style="display:flex;align-items:center;gap:10px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#64748b;">
                            <i class="fas fa-cog" style="color:#fff;font-size:1.2rem;"></i>
                        </span>
                        <span>Setting</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h1 class="page-title">Admin Dashboard</h1>
                    <p class="admin-greeting">Welcome back, <?php echo htmlspecialchars($adminName); ?> 👋</p>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Stats -->
            <div class="dashboard-cards">
                <div class="dashboard-card users">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="value"><?php echo $totalUsers; ?></div>
                    <div class="label">Total Users</div>
                </div>
                <div class="dashboard-card revenue">
                    <div class="icon"><i class="fas fa-coins"></i></div>
                    <div class="value">LKR <?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="label">Total Revenue</div>
                </div>
                <div class="dashboard-card orders">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="value"><?php echo $totalOrders; ?></div>
                    <div class="label">Total Orders</div>
                </div>
            </div>

            <?php if (!isset($_GET['action']) || $_GET['action'] === 'manage_products'): ?>
                <!-- Products Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Products</h2>
                        <a href="admin_panal.php?action=add_product" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if(!empty($product['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" class="product-image">
                                            <?php else: ?>
                                                <div class="product-image" style="background: #f1f1f1; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: var(--gray);"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td>LKR <?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo $product['discount']; ?>%</td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="admin_panal.php?action=edit_product&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" name="delete_product" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($products)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 2rem;">No products found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Orders</h2>
                        <a href="admin_panal.php?action=manage_orders" class="btn btn-primary">
                            View All Orders
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>
                                            <?php 
                                                if (!empty($order['username'])) {
                                                    echo htmlspecialchars($order['username']);
                                                } else {
                                                    echo htmlspecialchars($order['customer_email'] ?? 'Guest');
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>LKR <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin_panal.php?action=view_order&id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem;">No recent orders</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif($_GET['action'] === 'add_product' || $_GET['action'] === 'edit_product'): 
                $product = [
                    'id' => '',
                    'name' => '', 
                    'description' => '', 
                    'price' => '', 
                    'discount' => 0,
                    'category_id' => '',
                    'gender' => 'Male',
                    'type' => 'T-Shirt',
                    'size' => 'M',
                    'image_url' => ''
                ];
                $formAction = 'add_product';
                $formTitle = 'Add New Product';
                
                if ($_GET['action'] === 'edit_product' && isset($_GET['id'])) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $product = $stmt->fetch();
                    $formAction = 'update_product';
                    $formTitle = 'Edit Product';
                }
            ?>
                <!-- Product Form -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?php echo $formTitle; ?></h2>
                        <a href="admin_panal.php?action=manage_products" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Products
                        </a>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <?php if (!empty($product['image_url'])): ?>
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4" required><?php 
                                echo htmlspecialchars($product['description']); 
                            ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price" class="form-label">Price (LKR)</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="discount" class="form-label">Discount (%)</label>
                            <input type="number" id="discount" name="discount" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['discount']); ?>" min="0" max="100" step="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" name="category_id" class="select-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="select-control" required>
                                <option value="Male" <?php echo ($product['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($product['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Unisex" <?php echo ($product['gender'] === 'Unisex') ? 'selected' : ''; ?>>Unisex</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_type" class="form-label">Type</label>
                            <select id="product_type" name="type" class="select-control" required>
                                <option value="T-Shirt" <?php echo ($product['type'] === 'T-Shirt') ? 'selected' : ''; ?>>T-Shirt</option>
                                <option value="Shirt" <?php echo ($product['type'] === 'Shirt') ? 'selected' : ''; ?>>Shirt</option>
                                <option value="Pants" <?php echo ($product['type'] === 'Pants') ? 'selected' : ''; ?>>Pants</option>
                                <option value="Dress" <?php echo ($product['type'] === 'Dress') ? 'selected' : ''; ?>>Dress</option>
                                <option value="Jacket" <?php echo ($product['type'] === 'Jacket') ? 'selected' : ''; ?>>Jacket</option>
                                <option value="Jeans" <?php echo ($product['type'] === 'Jeans') ? 'selected' : ''; ?>>Jeans</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="size" class="form-label">Size</label>
                            <select id="size" name="size" class="select-control" required>
                                <option value="S" <?php echo ($product['size'] === 'S') ? 'selected' : ''; ?>>S</option>
                                <option value="M" <?php echo ($product['size'] === 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="L" <?php echo ($product['size'] === 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="XL" <?php echo ($product['size'] === 'XL') ? 'selected' : ''; ?>>XL</option>
                                <option value="XXL" <?php echo ($product['size'] === 'XXL') ? 'selected' : ''; ?>>XXL</option>
                                <option value="All size Available" <?php echo ($product['size'] === 'All size Available') ? 'selected' : ''; ?>>All size Available</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Product Image</label>
                            <?php if (!empty($product['image_url'])): ?>
                                <div style="margin-bottom: 1rem;">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current product image" style="max-width: 200px; display: block; margin-bottom: 0.5rem; border-radius: 0.5rem;">
                                    <small>Current image</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="image" name="image" class="form-control" <?php echo empty($product['image_url']) ? 'required' : ''; ?> accept="image/*">
                            <small class="text-muted" style="display: block; margin-top: 0.5rem; color: var(--gray);">
                                <?php echo empty($product['image_url']) ? 'Upload product image (JPEG, PNG, etc.)' : 'Upload new image to replace current one'; ?>
                            </small>
                        </div>
                        
                        <button type="submit" name="<?php echo $formAction; ?>" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            <?php echo $formTitle; ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php
// Insert default categories if not exist
$defaultCategories = ['Women', 'Men', 'Kids', 'Accessories', 'Home & Lifestyle'];
foreach ($defaultCategories as $category) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    $stmt->execute([$category]);
}
?>
<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$page_title = 'Dashboard';
include 'includes/header.php';
?>
<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Products</h3>
        <p>
            <?php 
            $stmt = $pdo->query("SELECT COUNT(*) FROM products");
            echo $stmt->fetchColumn();
            ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Total Orders</h3>
        <p>
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
            echo $stmt->fetchColumn();
            ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Revenue</h3>
        <p>
            $<?php
            $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status = 'completed'");
            echo number_format($stmt->fetchColumn(), 2);
            ?>
        </p>
    </div>
</div>
<div class="recent-orders">
    <h2>Recent Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT id, customer_name, created_at, total_amount, order_status
                                 FROM orders
                                 ORDER BY created_at DESC LIMIT 5");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                        <td>#{$row['id']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['created_at']}</td>
                        <td>\${$row['total_amount']}</td>
                        <td><span class='status {$row['order_status']}'>{$row['order_status']}</span></td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "velvet_vogue";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
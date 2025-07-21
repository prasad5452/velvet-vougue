<?php
require 'includes/db.php'; // adjust path if needed

// Get product id from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Debug: Show product id
// echo "Product ID: $product_id<br>";

// Fetch product from database
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<h2>Product not found.</h2>";
    exit;
}

// Prepare sizes array (if available)
$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = array_map('trim', explode(',', $product['sizes']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> | Offers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f8fa; margin: 0; }
        .product-container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.07); display: flex; gap: 40px; padding: 40px; }
        .product-images { flex: 1; display: flex; flex-direction: column; gap: 16px; }
        .product-images img { width: 100%; max-width: 400px; border-radius: 12px; object-fit: cover; }
        .product-details { flex: 2; }
        .product-title { font-size: 2rem; font-weight: 700; margin-bottom: 12px; }
        .product-price { color: #e30613; font-size: 1.7rem; font-weight: 700; margin-bottom: 10px; }
        .product-stock { color: #10b981; font-weight: 600; margin-bottom: 10px; }
        .product-label { font-weight: 600; margin-top: 18px; margin-bottom: 8px; }
        .sizes-list span { display: inline-block; background: #f5f5f5; border-radius: 3px; padding: 4px 12px; font-size: 1rem; margin-right: 6px; margin-bottom: 4px; }
        .qty-box { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; }
        .qty-btn { width: 32px; height: 32px; border: 1px solid #bbb; background: #fff; border-radius: 4px; font-size: 1.2rem; cursor: pointer; }
        .qty-input { width: 40px; text-align: center; border: 1px solid #bbb; border-radius: 4px; height: 32px; }
        .action-btns { display: flex; gap: 16px; margin-top: 18px; }
        .add-cart-btn, .wishlist-btn { padding: 12px 28px; border-radius: 6px; font-size: 1.1rem; font-weight: 600; border: none; cursor: pointer; }
        .add-cart-btn { background: #e30613; color: #fff; }
        .wishlist-btn { background: #fff; color: #e30613; border: 2px solid #e30613; }
    </style>
</head>
<body>
    <div class="product-container">
        <div class="product-images">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <!-- Add more images if you have them -->
        </div>
        <div class="product-details">
            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-price">Rs.<?php echo number_format($product['price'], 2); ?></div>
            <div class="product-stock">In Stock</div>
            <div class="product-label">Size</div>
            <div class="sizes-list">
                <?php foreach ($sizes as $size): ?>
                    <span><?php echo htmlspecialchars($size); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="product-label">QTY</div>
            <div class="qty-box">
                <button class="qty-btn" onclick="changeQty(-1)">-</button>
                <input type="text" id="qty" class="qty-input" value="1" readonly>
                <button class="qty-btn" onclick="changeQty(1)">+</button>
            </div>
            <div class="action-btns">
                <button class="add-cart-btn"><i class="fa fa-shopping-cart"></i> ADD TO CART</button>
                <button class="wishlist-btn"><i class="fa fa-heart"></i> WISHLIST</button>
            </div>
            <a href="offers.php?id=<?php echo $product['id']; ?>">Select Options</a>
        </div>
    </div>
    <script>
        function changeQty(val) {
            var qty = document.getElementById('qty');
            var newQty = Math.max(1, parseInt(qty.value) + val);
            qty.value = newQty;
        }
    </script>
</body>
</html>
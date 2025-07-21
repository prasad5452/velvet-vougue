<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sample products database
$products = [
    1 => [
        'name' => 'Wireless Bluetooth Headphones',
        'price' => 89.99,
        'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200&h=200&fit=crop',
        'size' => 'One Size',
        'color' => 'Black'
    ],
    2 => [
        'name' => 'Premium Cotton T-Shirt',
        'price' => 24.99,
        'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=200&h=200&fit=crop',
        'size' => 'Medium',
        'color' => 'Navy Blue'
    ],
    3 => [
        'name' => 'Leather Crossbody Bag',
        'price' => 149.99,
        'image' => 'velvet_vouge\velvet_vougue\7.jpg',
        'size' => 'One Size',
        'color' => 'Brown'
    ]
];

// Initialize cart with sample items if empty
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        1 => ['quantity' => 1],
        2 => ['quantity' => 2],
        3 => ['quantity' => 1]
    ];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $productId = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$productId]);
                } else {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                }
                break;
                
            case 'remove_item':
                $productId = (int)$_POST['product_id'];
                unset($_SESSION['cart'][$productId]);
                break;
                
            case 'apply_promo':
                $promoCode = strtoupper(trim($_POST['promo_code']));
                if ($promoCode === 'SAVE10') {
                    $_SESSION['promo'] = ['code' => 'SAVE10', 'discount' => 0.1, 'type' => 'percentage'];
                } elseif ($promoCode === 'FREESHIP') {
                    $_SESSION['promo'] = ['code' => 'FREESHIP', 'discount' => 8.99, 'type' => 'fixed'];
                } else {
                    $_SESSION['promo_error'] = 'Invalid promo code';
                }
                break;
                
            case 'clear_cart':
                $_SESSION['cart'] = [];
                unset($_SESSION['promo']);
                break;
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Calculate totals
function calculateCartTotals($cart, $products) {
    $subtotal = 0;
    foreach ($cart as $productId => $item) {
        if (isset($products[$productId])) {
            $subtotal += $products[$productId]['price'] * $item['quantity'];
        }
    }
    
    $shipping = 8.99;
    $tax = $subtotal * 0.08;
    
    $discount = 0;
    if (isset($_SESSION['promo'])) {
        $promo = $_SESSION['promo'];
        $discount = $promo['type'] === 'percentage' ? $subtotal * $promo['discount'] : $promo['discount'];
    }
    
    $total = $subtotal + $shipping + $tax - $discount;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'discount' => $discount,
        'total' => $total
    ];
}

$totals = calculateCartTotals($_SESSION['cart'], $products);
$cartItemCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #000000 0%, #2c2c2c 100%);
            color: #ffffff;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 20px 0;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #ffffff, #cccccc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 40px;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #cccccc;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ffffff;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border: 1px solid #ecf0f1;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .item-specs {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: #ecf0f1;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .quantity-btn:hover {
            background: #d5dbdb;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.5rem;
        }
        
        .item-price {
            text-align: right;
            margin-left: 1rem;
        }
        
        .price-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .price-each {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.3s;
        }
        
        .remove-btn:hover {
            background: #c0392b;
        }
        
        .order-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        
        .promo-section {
            margin-bottom: 2rem;
        }
        
        .promo-label {
            display: block;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .promo-form {
            display: flex;
            gap: 0.5rem;
        }
        
        .promo-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .promo-btn {
            background: #34495e;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .promo-btn:hover {
            background: #2c3e50;
        }
        
        .promo-success {
            color: #27ae60;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .promo-error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .price-breakdown {
            margin-bottom: 2rem;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
        }
        
        .price-row.total {
            border-top: 2px solid #ecf0f1;
            padding-top: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .discount-row {
            color: #27ae60;
        }
        
        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #3498db 0%, #9b59b6 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .continue-btn {
            width: 100%;
            background: transparent;
            color: #7f8c8d;
            border: 2px solid #ecf0f1;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .continue-btn:hover {
            background: #ecf0f1;
            border-color: #d5dbdb;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-cart h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .empty-cart p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .features {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .admin-actions {
            margin-top: 2rem;
            text-align: center;
        }
        
        .clear-cart-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .clear-cart-btn:hover {
            background: #c0392b;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .item-price {
                margin-left: 0;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
             <nav class="container">
             <h1>Shopping Cart</h1>
            <p><?php echo $cartItemCount; ?> item<?php echo $cartItemCount !== 1 ? 's' : ''; ?> in your cart</p>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="collection.php">Collections</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>

        </div>
        <br>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <button class="checkout-btn" onclick="window.history.back()">Continue Shopping</button>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                        <?php if (isset($products[$productId])): ?>
                            <?php $product = $products[$productId]; ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="item-image">
                                
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="item-specs">
                                        Size: <?php echo htmlspecialchars($product['size']); ?> • 
                                        Color: <?php echo htmlspecialchars($product['color']); ?>
                                    </div>
                                    
                                    <div class="quantity-controls">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                            <button type="submit" class="quantity-btn">-</button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" onchange="this.form.submit()">
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                            <button type="submit" class="quantity-btn">+</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="item-price">
                                    <div class="price-total">$<?php echo number_format($product['price'] * $item['quantity'], 2); ?></div>
                                    <div class="price-each">$<?php echo number_format($product['price'], 2); ?> each</div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <button type="submit" class="remove-btn" onclick="return confirm('Remove this item from cart?')">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    
                    <div class="promo-section">
                        <label class="promo-label">Promo Code</label>
                        <form method="POST" class="promo-form">
                            <input type="hidden" name="action" value="apply_promo">
                            <input type="text" name="promo_code" placeholder="Enter code" class="promo-input">
                            <button type="submit" class="promo-btn">Apply</button>
                        </form>
                        
                        <?php if (isset($_SESSION['promo'])): ?>
                            <div class="promo-success">✓ <?php echo $_SESSION['promo']['code']; ?> applied</div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['promo_error'])): ?>
                            <div class="promo-error"><?php echo $_SESSION['promo_error']; ?></div>
                            <?php unset($_SESSION['promo_error']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($totals['subtotal'], 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Shipping</span>
                            <span>$<?php echo number_format($totals['shipping'], 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Tax</span>
                            <span>$<?php echo number_format($totals['tax'], 2); ?></span>
                        </div>
                        
                        <?php if ($totals['discount'] > 0): ?>
                            <div class="price-row discount-row">
                                <span>Discount (<?php echo $_SESSION['promo']['code']; ?>)</span>
                                <span>-$<?php echo number_format($totals['discount'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="price-row total">
                            <span>Total</span>
                            <span>$<?php echo number_format($totals['total'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="features">
                        <div class="feature-item">
                            <span>🚚</span>
                            <span>Free returns within 30 days</span>
                        </div>
                        <div class="feature-item">
                            <span>🔒</span>
                            <span>Secure checkout</span>
                        </div>
                    </div>
                    
                    <button class="checkout-btn" onclick="alert('Proceeding to checkout...')">
                        Proceed to Checkout →
                    </button>
                    
                    <button class="continue-btn" onclick="window.history.back()">
                        Continue Shopping
                    </button>
                </div>
            </div>
            
            <div class="admin-actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="clear-cart-btn" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit quantity changes after a delay
        document.querySelectorAll('.quantity-input').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.form.submit();
                }, 1000);
            });
        });
        
        // Confirm before removing items
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Remove this item from cart?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
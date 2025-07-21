<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // TEMP: Remove this in production!
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process payment
    $errors = [];
    
    // Validate card details
    if (empty($_POST['cardholder'])) {
        $errors['cardholder'] = 'Cardholder name is required';
    }
    
    if (empty($_POST['cardnumber']) || !preg_match('/^\d{16}$/', str_replace(' ', '', $_POST['cardnumber']))) {
        $errors['cardnumber'] = 'Valid card number is required';
    }
    
    if (empty($_POST['expmonth']) || empty($_POST['expyear'])) {
        $errors['expdate'] = 'Expiration date is required';
    }
    
    // If no errors, process payment
    if (empty($errors)) {
        // Here you would typically connect to a payment gateway
        // For demo, we'll just store in session and redirect
        $_SESSION['payment_processed'] = true;
        header('Location: order_confirmation.php');
        exit;
    }
}

// Order summary data (would normally come from database/cart)
$subtotal = 715.00;
$shipping = 35.00;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
            background: #bfc5d6;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .card-number {
            letter-spacing: 2px;
        }
        .exp-date {
            display: flex;
            gap: 10px;
        }
        .exp-date input {
            width: 60px;
        }
        .order-summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .order-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-summary td {
            padding: 5px 0;
        }
        .order-summary td:last-child {
            text-align: right;
        }
        .total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background: #2980b9;
        }
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        form#paymentForm {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(44,62,80,0.08);
            padding: 24px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <a href="shipping.php" class="back-link">← BACK to shipping details</a>
    
    <h1>PAYMENT DETAILS</h1>
    
    <form id="paymentForm" method="POST">
        <div class="form-group">
            <label for="cardholder">Cardholder</label>
            <input type="text" id="cardholder" name="cardholder" value="<?= htmlspecialchars($_POST['cardholder'] ?? 'Dmitry Bowl') ?>" required>
            <?php if (isset($errors['cardholder'])): ?>
                <div class="error"><?= $errors['cardholder'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="cardnumber">Card number</label>
            <input type="text" id="cardnumber" name="cardnumber" class="card-number" 
                   value="<?= htmlspecialchars($_POST['cardnumber'] ?? '5173 4040 2871 9063') ?>" required>
            <?php if (isset($errors['cardnumber'])): ?>
                <div class="error"><?= $errors['cardnumber'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label>Expiration date</label>
            <div class="exp-date">
                <input type="text" id="expmonth" name="expmonth" placeholder="MM" 
                       value="<?= htmlspecialchars($_POST['expmonth'] ?? '07') ?>" required>
                <span>/</span>
                <input type="text" id="expyear" name="expyear" placeholder="YY" 
                       value="<?= htmlspecialchars($_POST['expyear'] ?? '23') ?>" required>
                <?php if (isset($errors['expdate'])): ?>
                    <div class="error"><?= $errors['expdate'] ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="order-summary">
            <h3>Order Summary</h3>
            <table>
                <tr>
                    <td>Price</td>
                    <td><?= number_format($subtotal, 2) ?> EUR</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td><?= number_format($shipping, 2) ?> EUR</td>
                </tr>
                <tr class="total">
                    <td>TOTAL</td>
                    <td><?= number_format($total, 2) ?> EUR</td>
                </tr>
            </table>
        </div>
        
        <button type="submit" class="btn">CHECK OUT</button>
    </form>

    <script>
        // Format card number with spaces
        document.getElementById('cardnumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });

        // Format expiration date
        document.getElementById('expmonth').addEventListener('input', function(e) {
            if (e.target.value.length > 2) {
                e.target.value = e.target.value.slice(0, 2);
            }
        });

        document.getElementById('expyear').addEventListener('input', function(e) {
            if (e.target.value.length > 2) {
                e.target.value = e.target.value.slice(0, 2);
            }
        });

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            let isValid = true;
            const cardNumber = document.getElementById('cardnumber').value.replace(/\s+/g, '');
            
            if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                alert('Please enter a valid 16-digit card number');
                isValid = false;
            if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                alert('Please enter a valid 16-digit card number');
                isValid = false;
            }ument.getElementById('expmonth').value;
            onst expYear = document.getElementById('expyear').value;
            const expMonth = document.getElementById('expmonth').value;
            const expYear = document.getElementById('expyear').value;
            ;
            if (expMonth.length !== 2 || expYear.length !== 2) {    isValid = false;
                alert('Please enter a valid expiration date (MM/YY)');
                isValid = false;
            }
               e.preventDefault();
            if (!isValid) {}
                e.preventDefault();
            }
        });
    </script></body></html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velvet Vogue - Luxury Fashion</title>
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

        /* Hero Section */
        .hero {
            background: url('velvet_vougue/5.jpg') center center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ffffff, #888888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            color: #cccccc;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(45deg, #333333, #000000);
            color: #ffffff;
            text-decoration: none;
            border: 2px solid #ffffff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-button:hover {
            background: linear-gradient(45deg, #ffffff, #cccccc);
            color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2);
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            width: 200px;
            height: 200px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        /* Featured Collections */
        .featured {
            padding: 100px 0;
            background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
        }

        .section-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 60px;
            color: #ffffff;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .collection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .collection-card {
            background: linear-gradient(135deg, #2c2c2c 0%, #000000 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .collection-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .collection-card h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .collection-card p {
            color: #cccccc;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .collection-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .collection-link:hover {
            border-bottom-color: #ffffff;
        }

        /* Newsletter */
        .newsletter {
            padding: 80px 0;
            background: linear-gradient(45deg, #000000 0%, #333333 100%);
            text-align: center;
        }

        .newsletter h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .newsletter p {
            color: #cccccc;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .newsletter-form {
            display: flex;
            justify-content: center;
            gap: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        .newsletter-form input {
            flex: 1;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 1rem;
        }

        .newsletter-form input::placeholder {
            color: #cccccc;
        }

        .newsletter-form button {
            padding: 15px 30px;
            background: #ffffff;
            color: #000000;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-form button:hover {
            background: #cccccc;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: #000000;
            padding: 40px 0;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-content {
            color: #cccccc;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .social-links a {
            color: #cccccc;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #ffffff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .newsletter-form {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">VELVET VOGUE</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="collection.php">Collections</a></li>
                <li><a href="payment.php">Payment</a></li>
                <li><a href="#contact">Contact</a></li>
                <li>
    <a href="cart.php" style="display:flex;align-items:center;gap:6px;position:relative;">
        <!-- Use an SVG cart icon for best compatibility -->
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 20C6.447 20 6 20.448 6 21C6 21.552 6.447 22 7 22C7.553 22 8 21.552 8 21C8 20.448 7.553 20 7 20ZM17 20C16.447 20 16 20.448 16 21C16 21.552 16.447 22 17 22C17.553 22 18 21.552 18 21C18 20.448 17.553 20 17 20ZM7.16 17H17.5C18.328 17 19.042 16.438 19.226 15.629L21.197 7.629C21.356 6.976 20.864 6.333 20.186 6.333H6.21L5.27 2.926C5.111 2.324 4.557 1.917 3.934 2.003C3.312 2.09 2.857 2.652 2.995 3.261L4.295 8.901C4.354 9.166 4.591 9.333 4.86 9.333H19.5C19.776 9.333 20 9.557 20 9.833C20 10.109 19.776 10.333 19.5 10.333H7.16L6.25 13.333H18.5C18.776 13.333 19 13.557 19 13.833C19 14.109 18.776 14.333 18.5 14.333H6.16L7.16 17Z" fill="#fff"/>
        </svg>
        <span id="cart-count" style="background:#ff4d4f;color:#fff;font-size:0.85rem;font-weight:bold;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;position:absolute;top:-7px;right:-12px;padding:0 5px;">2</span>
        Cart
    </a>
</li>
<li>
    <a href="admin_panal.php" class="btn-signup" style="display:flex;align-items:center;padding:8px 0;background:transparent;color:#fff;border:none;text-decoration:none;">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e11d48;">
            <span style="color:#fff;font-weight:600;font-size:1.2rem;">
                <?php
                    // Show first letter of username if logged in, else 'a'
                    if (isset($_SESSION['user']['username']) && $_SESSION['user']['username']) {
                        echo strtolower(substr($_SESSION['user']['username'], 0, 1));
                    } else {
                        echo 'a';
                    }
                ?>
            </span>
        </span>
    </a>
</li>
            </ul>
        </nav>
    </header>

    <section class="hero" id="home" style="background: url('velvet_vougue/5.jpg') center center/cover no-repeat; min-height: 100vh; display:flex; align-items: center; justify-content: center; text-align: center; position: relative;">
        <div class="hero-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:1;"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="hero-content" style="position:relative;z-index:2;">
            <h1>Luxury Redefined</h1>
            <p>Discover our exclusive collection of premium velvet fashion that embodies elegance, sophistication, and timeless style.</p>
            <a href="#collections" class="cta-button">EXPLORE COLLECTION</a>
        </div>
    </section>

    <!-- Add this banner section near the top of your index.php, just below <body> or below the header -->
<section class="fashion-sale-banner" style="background:#ededed; padding:40px 0 30px 0;">
    <div style="max-width:1100px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 24px rgba(0,0,0,0.07); border-radius:24px; background:#fff; overflow:hidden; position:relative;">
        <div style="flex:1; padding:60px 40px 60px 60px;">
            <div style="font-size:1.3rem; color:#888; letter-spacing:2px; margin-bottom:18px;">VELVET VOUGE</div>
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
                <svg width="180" height="180" style="position:absolute; right=30px; top=30px; z-index:1;" fill="none" stroke="#222" stroke-width="4">
                    <polygon points="30,10 170,30 150,170 10,150" />
                </svg>
            </div>
        </div>
    </div>
</section>

    <section class="featured" id="collections">
        <div class="container">
            <h2 class="section-title">Featured Collections</h2>
            <div class="collection-grid">
                <div class="collection-card">
                    <h3>MEN</h3>
                    <img src="velvet_vougue\1.jpeg" alt="Description" style="width:100%; border-radius:12px; margin-bottom:20px;">
                    <p>Modern, comfortable, and stylish clothing for every man</p>
                    <a href="collection.php?category=dresses" class="collection-link" target="_blank">Shop Now →</a>
                </div>
                <div class="collection-card">
                    <h3>Women</h3>
                    <img src="velvet_vougue\3.jpg" alt="Description" style="width:100%; border-radius:12px; margin-bottom:20px;">
                    <p>Chic, stylish, and comfortable clothing made for every woman.</p>
                    <a href="collection.php?category=pants" class="collection-link">Shop Now →</a>
                </div>
                <div class="collection-card">
                    <h3>Accessories</h3>
                    <img src="velvet_vougue\4.jpg" alt="Description" style="width:100%; border-radius:12px; margin-bottom:20px;">
                    <p>Stylish add-ons to elevate every outfit, from bags to jewelry.</p>
                    <a href="collection.php?category=jackets" class="collection-link">Shop Now →</a>
                </div>
            </div>
        </div>
    </section>
    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=velvet_vogue", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO email_subscribers (email) VALUES (:email)");
        $stmt->execute(['email' => $email]);

        echo "Subscribed successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

    <section class="newsletter">
        <div class="container">
            <h2>Stay in Vogue</h2>
            <p>Be the first to know about new arrivals, exclusive offers, and fashion insights.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">SUBSCRIBE</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2025 Velvet Vogue. All rights reserved.</p>
                <div class="social-links">
                    <a href="#">Instagram</a>
                    <a href="#">Facebook</a>
                    <a href="#">Twitter</a>
                    <a href="#">Pinterest</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(0, 0, 0, 0.95)';
                header.style.backdropFilter = 'blur(20px)';
            } else {
                header.style.background = 'rgba(0, 0, 0, 0.9)';
                header.style.backdropFilter = 'blur(10px)';
            }
        });

        // Newsletter form submission
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            alert('Thank you for subscribing! We\'ll keep you updated with our latest collections.');
            this.reset();
        });

        // Add subtle parallax effect to floating elements
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const floatingElements = document.querySelectorAll('.floating-element');
            
            floatingElements.forEach((element, index) => {
                const speed = 0.5 + (index * 0.1);
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    </script>


</html></body></body>
</html>
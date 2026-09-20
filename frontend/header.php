<?php
require_once __DIR__ . '/config.php';
session_start();
$is_logged_in = isset($_SESSION['user']);
$user_id = $is_logged_in ? $_SESSION['user']['id'] : null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>CUSAT Store</title>
    <!-- Google Fonts: Outfit for distinct CUSAT Store branding & Plus Jakarta Sans for UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script>
        window.API_BASE_URL = "<?php echo htmlspecialchars($API_BASE_URL); ?>";
    </script>
</head>
<body>
<div id="toast-container"></div>

<header class="main-header">
    <div class="header-container">
        
        <a href="index.php" class="logo-link">
            <img src="assets/cusat_logo_cropped.png" class="logo-img-file" alt="CUSAT Logo">
            <span class="logo-text">CUSAT Store</span>
        </a>

        <!-- Mobile header controls (Cart + Hamburger) -->
        <div class="mobile-header-controls">
            <a href="cart.php" class="cart-status-link mobile-cart-icon" aria-label="Shopping Cart">
                <span class="cart-icon">🛒</span>
                <span id="mobile-cart-badge" class="badge-count cart-badge-target" style="display: none;">0</span>
            </a>
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle navigation menu" onclick="toggleMobileMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>

        <!-- Desktop and Collapsible Mobile Navigation Wrapper -->
        <div class="nav-wrapper" id="nav-wrapper">
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">Products</a>
                <a href="cart.php" class="nav-item mobile-menu-cart-item">Cart</a>
                <?php if ($is_logged_in) { ?>
                    <a href="orders.php" class="nav-item">My Orders</a>
                <?php } ?>
                <?php if ($is_admin) { ?>
                    <a href="admin.php" class="nav-item admin-badge-link">Admin Panel</a>
                <?php } ?>
            </nav>

            <div class="header-actions">
                <a href="cart.php" class="cart-status-link desktop-cart-icon">
                    <span class="cart-icon">🛒</span>
                    <span id="cart-badge" class="badge-count cart-badge-target" style="display: none;">0</span>
                </a>

                <?php if ($is_logged_in) { ?>
                    <span class="user-greeting">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                    <a href="logout.php" class="nav-btn btn-logout">Logout</a>
                <?php } else { ?>
                    <a href="login.php" class="nav-btn btn-login">Login</a>
                    <a href="register.php" class="nav-btn btn-register">Register</a>
                <?php } ?>
            </div>
        </div>

    </div>
</header>
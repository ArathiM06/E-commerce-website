<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
    <script>
        // Clear any shared or guest cart on logout
        localStorage.removeItem('cusat_cart');
        localStorage.removeItem('cusat_cart_guest');
        window.location.href = 'index.php';
    </script>
</head>
<body>
</body>
</html>

<?php
include 'header.php';

// get the category and search from url
$selected_category = 'All';
if (isset($_GET['category'])) {
    $selected_category = $_GET['category'];
}

$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// backend endpoint link stuff
$api_url = $API_BASE_URL . "/products";
if ($selected_category != 'All') {
    $api_url = $api_url . "?category=" . urlencode($selected_category);
}

$products = array();
$backend_offline = false;

// fetching data from fastapi
try {
    $response = api_get($api_url);
    
    if ($response == false) {
        $backend_offline = true;
    } else {
        $products = json_decode($response, true);
        
        if ($products === null || !is_array($products) || isset($products['detail'])) {
            $products = array();
        } else {
            // filter array if user typed search query
            if ($search_query != '') {
                $filtered = array();
                foreach ($products as $p) {
                    if (isset($p['name']) && (stristr($p['name'], $search_query) == true || (isset($p['description']) && stristr($p['description'], $search_query) == true))) {
                        $filtered[] = $p;
                    }
                }
                $products = $filtered;
            }
        }
    }
} catch (Exception $e) {
    $backend_offline = true;
}

$categories = array('All', 'Apparel', 'Textbooks', 'Tech', 'Stationery');
?>

<main class="container page-main">
    
    <div class="hero">
        <div class="hero-bg-slide slide-1"></div>
        <div class="hero-bg-slide slide-2"></div>
        <div class="hero-container-inner">
            <div class="hero-text-side">
                <span class="hero-tag">OFFICIAL STORE</span>
                <h1 class="hero-title">Wear Your Pride, Learn in Style</h1>
                <p class="hero-subtitle">Get official Cochin University merchandise, textbooks, stationery, and lab essentials. Designed for CUSATians, by CUSATians.</p>
                <a href="#store-section" class="hero-btn">Shop Collection</a>
            </div>
            <div class="hero-image-side">
                <div class="admin-card">
                    <img src="assets/cusat_admin_cropped.png" alt="CUSAT Administrative Center" class="admin-card-img">
                </div>
            </div>
        </div>
    </div>

    <div id="store-section" class="store-filter-bar">
        <div class="filter-categories-group">
            <span class="filter-label">Categories:</span>
            <div class="filter-buttons-list">
                <?php foreach ($categories as $cat) { ?>
                    <a href="index.php?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search_query); ?>#store-section" 
                       class="category-link-btn <?php if($selected_category == $cat) { echo 'active-cat'; } ?>">
                        <?php echo $cat; ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        
        <div class="filter-search-group">
            <form method="GET" action="index.php#store-section" class="search-form-inline">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                <span class="filter-label">Find Item:</span> 
                <div class="search-input-wrapper">
                    <input type="text" name="search" placeholder="Type here to search..." value="<?php echo htmlspecialchars($search_query); ?>" class="search-input-field">
                    <button type="submit" class="search-submit-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
    <br><br>

    <?php if ($backend_offline == true) { ?>
        <div class="backend-error-box">
            <h2 class="error-heading">🛑 FastAPI Backend is Offline 🛑</h2>
            <p>CUSAT Store requires the FastAPI backend to load products and handle checkouts. Please make sure the backend server is running locally on port 8000.</p>
            <br>
            <b>Run this startup command in your terminal:</b><br><br>
            <textarea readonly class="error-terminal-code">uvicorn main:app --reload --port 8000</textarea>
        </div>
        <br><br>
    <?php } ?>

    <?php if ($backend_offline == false) { ?>
        
        <?php if (count($products) == 0) { ?>
            
            <div class="empty-products-view">
                <h3>🔍 No Products Found</h3>
                <p>We couldn't find any products matching your selection.</p>
                <br>
                <a href="index.php" class="clear-filters-link">[ Clear All Filters ]</a>
            </div>

        <?php } else { ?>
            
            <div class="products-grid">
                <?php foreach ($products as $prod) { ?>
                    <div class="product-card">
                        <div class="product-image-box">
                            <span class="product-card-category-badge">
                                <?php echo htmlspecialchars($prod['category']); ?>
                            </span>
                            <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="catalog-product-img">
                        </div>
                        
                        <div class="product-info-box">
                            <h3 class="catalog-product-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <p class="catalog-product-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                        </div>
                        
                        <div class="product-card-footer">
                            <b class="catalog-product-price">₹<?php echo number_format(floatval($prod['price'] ?? 0), 2); ?></b>
                            <button onclick="addToCart(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['name']); ?>', <?php echo $prod['price']; ?>, '<?php echo addslashes($prod['image_url']); ?>')" 
                                    class="add-to-cart-action-btn">
                                Add to Cart 🛒
                            </button>
                        </div>
                    </div>
                <?php } ?>
            </div>

        <?php } ?>
    <?php } ?>

</main>

<br><br>
<hr>

<footer class="global-page-footer">
    <center>
        <p class="footer-brand-text"><b>CUSAT Store Catalog View</b></p>
        <p class="footer-copyright-text">&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </center>
</footer>

<script src="app.js"></script>
</body>
</html>
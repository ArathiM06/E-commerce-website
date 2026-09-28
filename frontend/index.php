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

// Default fallback catalog items for resilience (e.g. during Render free-tier cold starts)
$fallback_products = array(
    array(
        "id" => 1,
        "name" => "CUSAT Premium Hoodie",
        "price" => 850.00,
        "category" => "Apparel",
        "description" => "Navy blue hoodie with the official CUSAT crest printed in white and gold. Standard fit.",
        "image_url" => "https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 2,
        "name" => "CUSAT Eco-Friendly Canvas Tote Bag",
        "price" => 220.00,
        "category" => "Apparel",
        "description" => "Durable natural cotton canvas tote bag with the official CUSAT crest. Perfect for carrying notebooks, laptops, and lab essentials around campus.",
        "image_url" => "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 3,
        "name" => "Engineering Physics Textbook",
        "price" => 520.00,
        "category" => "Textbooks",
        "description" => "Prescribed textbook for CUSAT B.Tech first-year syllabus. Fully updated edition.",
        "image_url" => "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 4,
        "name" => "CUSAT Official Polo T-Shirt",
        "price" => 499.00,
        "category" => "Apparel",
        "description" => "Premium navy blue polo t-shirt with official CUSAT crest embroidery and collar trim. Made of breathable cotton pique fabric.",
        "image_url" => "https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 5,
        "name" => "Maker Kit (Arduino Uno & Sensors)",
        "price" => 1250.00,
        "category" => "Tech",
        "description" => "Starter electronics kit containing an Arduino Uno board, breadboard, jumper wires, LEDs, and standard sensors.",
        "image_url" => "https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 6,
        "name" => "A2 Drawing Board & T-Square",
        "price" => 950.00,
        "category" => "Stationery",
        "description" => "Durable wooden engineering drawing board along with a precise 60cm T-Square rule. Essential for Engineering Graphics.",
        "image_url" => "https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 7,
        "name" => "CUSAT Insulated Stainless Steel Flask",
        "price" => 349.00,
        "category" => "Accessories",
        "description" => "Double-wall insulated 750ml stainless steel flask with laser-engraved CUSAT logo. Keeps beverages cold or hot for 12 hours.",
        "image_url" => "https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 8,
        "name" => "CUSAT Official Campus Backpack",
        "price" => 899.00,
        "category" => "Apparel",
        "description" => "Water-resistant navy blue backpack with padded laptop compartment, multiple organizers, and reflective CUSAT crest.",
        "image_url" => "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 9,
        "name" => "CUSAT Executive Pen & Notebook Set",
        "price" => 299.00,
        "category" => "Stationery",
        "description" => "Hardbound A5 notebook with gold-embossed CUSAT logo paired with a sleek metallic rollerball pen.",
        "image_url" => "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=400"
    ),
    array(
        "id" => 10,
        "name" => "Casio FX-991CW ClassWiz Scientific Calculator",
        "price" => 1295.00,
        "category" => "Tech",
        "description" => "Advanced non-programmable scientific calculator prescribed for CUSAT B.Tech & Engineering examinations.",
        "image_url" => "assets/casio_calc.png"
    ),
    array(
        "id" => 11,
        "name" => "CUSAT Varsity Baseball Cap",
        "price" => 275.00,
        "category" => "Apparel",
        "description" => "Adjustable cotton twill cap in deep navy blue with 3D embroidered CUSAT lettering.",
        "image_url" => "https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&q=80&w=400"
    )
);

// fetching data from fastapi
try {
    $response = api_get($api_url);
    
    if ($response == false) {
        $backend_offline = true;
        $products = $fallback_products;
    } else {
        $decoded = json_decode($response, true);
        
        if ($decoded === null || !is_array($decoded) || isset($decoded['detail']) || count($decoded) == 0) {
            $backend_offline = true;
            $products = $fallback_products;
        } else {
            $products = $decoded;
        }
    }
} catch (Exception $e) {
    $backend_offline = true;
    $products = $fallback_products;
}

// Category filter on array if needed
if ($selected_category != 'All') {
    $filtered_cat = array();
    foreach ($products as $p) {
        if (isset($p['category']) && strcasecmp($p['category'], $selected_category) == 0) {
            $filtered_cat[] = $p;
        }
    }
    $products = $filtered_cat;
}

// Search query filter
if ($search_query != '') {
    $filtered = array();
    foreach ($products as $p) {
        if (isset($p['name']) && (stristr($p['name'], $search_query) == true || (isset($p['description']) && stristr($p['description'], $search_query) == true))) {
            $filtered[] = $p;
        }
    }
    $products = $filtered;
}

$categories = array('All', 'Apparel', 'Textbooks', 'Tech', 'Stationery', 'Accessories');
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
        <div id="backend-status-banner" class="backend-error-box" style="background:#fff8e1; border:1px solid #ffe082; color:#856404; padding:15px; border-radius:8px; margin-bottom:20px;">
            <h3 style="margin:0 0 5px 0; font-size:1.1rem; color:#856404;">⚡ Connecting to live FastAPI backend...</h3>
            <p style="margin:0; font-size:0.9rem;">The backend service on Render is warming up. Default catalog items are displayed below and will update automatically once connected.</p>
        </div>
    <?php } ?>

    <?php if (count($products) == 0) { ?>
        
        <div class="empty-products-view">
            <h3>🔍 No Products Found</h3>
            <p>We couldn't find any products matching your selection.</p>
            <br>
            <a href="index.php" class="clear-filters-link">[ Clear All Filters ]</a>
        </div>

    <?php } else { ?>
        
        <div class="products-grid" id="main-products-grid">
            <?php foreach ($products as $prod) { 
                $safe_id = intval($prod['id']);
                $safe_name = htmlspecialchars(addslashes($prod['name']), ENT_QUOTES);
                $safe_price = floatval($prod['price'] ?? 0);
                $safe_cat = htmlspecialchars(addslashes($prod['category'] ?? 'General'), ENT_QUOTES);
                $safe_desc = htmlspecialchars(addslashes(preg_replace("/\r|\n/", " ", $prod['description'] ?? '')), ENT_QUOTES);
                $safe_img = htmlspecialchars(addslashes($prod['image_url'] ?? ''), ENT_QUOTES);
            ?>
                <div class="product-card" onclick="openProductModal(<?php echo $safe_id; ?>, '<?php echo $safe_name; ?>', <?php echo $safe_price; ?>, '<?php echo $safe_cat; ?>', '<?php echo $safe_desc; ?>', '<?php echo $safe_img; ?>')">
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
                        <button onclick="event.stopPropagation(); addToCart(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['name']); ?>', <?php echo $prod['price']; ?>, '<?php echo addslashes($prod['image_url']); ?>')" 
                                class="add-to-cart-action-btn">
                            Add to Cart 🛒
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>

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
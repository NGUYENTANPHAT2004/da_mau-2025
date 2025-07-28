<!-- views/search/index.php -->
<div class="container mt-4">
    <!-- Search Header -->
    <div class="search-header mb-4">
        <div class="row">
            <div class="col-md-8">
                <h2>
                    <?php if($query): ?>
                        Kết quả tìm kiếm cho: <span class="text-primary">"<?php echo htmlspecialchars($query); ?>"</span>
                    <?php else: ?>
                        Tìm kiếm sản phẩm
                    <?php endif; ?>
                </h2>
                <?php if($total_count > 0): ?>
                    <p class="text-muted">Tìm thấy <?php echo number_format($total_count); ?> sản phẩm</p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="search-sort">
                    <select class="form-select" onchange="updateSort(this.value)">
                        <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Liên quan nhất</option>
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Đánh giá cao</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Search Filters Sidebar -->
        <div class="col-lg-3">
            <div class="search-filters">
                <div class="filter-section">
                    <h5>Bộ lọc tìm kiếm</h5>
                    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h6>Danh mục</h6>
                        <div class="filter-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="" 
                                       <?php echo !$category_id ? 'checked' : ''; ?> onchange="updateFilters()">
                                <label class="form-check-label">Tất cả</label>
                            </div>
                            <?php foreach($categories as $cat): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" 
                                           value="<?php echo $cat['id']; ?>" 
                                           <?php echo $category_id == $cat['id'] ? 'checked' : ''; ?> 
                                           onchange="updateFilters()">
                                    <label class="form-check-label"><?php echo $cat['name']; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <h6>Khoảng giá</h6>
                        <div class="price-range">
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           placeholder="Từ" name="min_price" value="<?php echo $min_price; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           placeholder="Đến" name="max_price" value="<?php echo $max_price; ?>">
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="updateFilters()">
                                Áp dụng
                            </button>
                        </div>
                        
                        <!-- Quick price filters -->
                        <div class="quick-price-filters mt-2">
                            <button class="btn btn-sm btn-outline-secondary mb-1" onclick="setQuickPrice(0, 1000000)">
                                Dưới 1 triệu
                            </button>
                            <button class="btn btn-sm btn-outline-secondary mb-1" onclick="setQuickPrice(1000000, 5000000)">
                                1-5 triệu
                            </button>
                            <button class="btn btn-sm btn-outline-secondary mb-1" onclick="setQuickPrice(5000000, 10000000)">
                                5-10 triệu
                            </button>
                            <button class="btn btn-sm btn-outline-secondary mb-1" onclick="setQuickPrice(10000000, 0)">
                                Trên 10 triệu
                            </button>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div class="filter-group">
                        <h6>Đánh giá</h6>
                        <div class="rating-filters">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="<?php echo $i; ?>" 
                                           name="rating" onchange="updateFilters()">
                                    <label class="form-check-label">
                                        <?php for($j = 1; $j <= 5; $j++): ?>
                                            <i class="fas fa-star <?php echo $j <= $i ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                        <?php if($i < 5): ?>trở lên<?php endif; ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <button class="btn btn-outline-danger w-100" onclick="clearFilters()">
                        <i class="fas fa-times me-2"></i>Xóa bộ lọc
                    </button>
                </div>

                <!-- Popular Searches -->
                <?php if(!empty($popular_searches)): ?>
                    <div class="popular-searches mt-4">
                        <h6>Tìm kiếm phổ biến</h6>
                        <div class="popular-tags">
                            <?php foreach($popular_searches as $search): ?>
                                <a href="search?q=<?php echo urlencode($search['query']); ?>" 
                                   class="badge bg-light text-dark me-1 mb-1">
                                    <?php echo htmlspecialchars($search['query']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Results -->
        <div class="col-lg-9">
            <?php if(empty($results) && $query): ?>
                <!-- No Results -->
                <div class="no-results text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Không tìm thấy sản phẩm nào</h4>
                    <p class="text-muted">
                        Không có sản phẩm nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($query); ?></strong>"
                    </p>
                    
                    <!-- Search Suggestions -->
                    <?php if(!empty($suggestions)): ?>
                        <div class="search-suggestions mt-4">
                            <h6>Có thể bạn muốn tìm:</h6>
                            <?php foreach($suggestions as $suggestion): ?>
                                <a href="search?q=<?php echo urlencode($suggestion['suggestion']); ?>" 
                                   class="btn btn-outline-primary me-2 mb-2">
                                    <?php echo htmlspecialchars($suggestion['suggestion']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="products" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    </div>
                </div>
            <?php elseif(!empty($results)): ?>
                <!-- Results Grid -->
                <div class="search-results">
                    <div class="row">
                        <?php foreach($results as $product): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card product-card h-100">
                                    <div class="product-image-container">
                                        <img src="<?php echo $product['image']; ?>" 
                                             class="card-img-top" alt="<?php echo $product['name']; ?>">
                                        
                                        <!-- Quick Actions -->
                                        <div class="product-actions">
                                            <button class="btn btn-sm btn-light" 
                                                    onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light" 
                                                    onclick="quickView(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>

                                        <!-- Discount Badge -->
                                        <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                            <div class="discount-badge">
                                                -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="product-category">
                                            <small class="text-muted"><?php echo $product['category_name']; ?></small>
                                        </div>
                                        <h6 class="card-title">
                                            <a href="product-detail/<?php echo $product['id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo $product['name']; ?>
                                            </a>
                                        </h6>
                                        
                                        <!-- Rating -->
                                        <?php if($product['rating'] > 0): ?>
                                            <div class="product-rating mb-2">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= round($product['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted">(<?php echo $product['review_count']; ?>)</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="product-price mb-3">
                                            <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                <span class="current-price text-danger fw-bold">
                                                    <?php echo number_format($product['sale_price']); ?> VNĐ
                                                </span>
                                                <br>
                                                <small class="original-price text-muted text-decoration-line-through">
                                                    <?php echo number_format($product['price']); ?> VNĐ
                                                </small>
                                            <?php else: ?>
                                                <span class="current-price text-primary fw-bold">
                                                    <?php echo number_format($product['price']); ?> VNĐ
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="d-grid">
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <nav aria-label="Search pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildSearchUrl($page - 1); ?>">Trước</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildSearchUrl($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildSearchUrl($page + 1); ?>">Sau</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <!-- Default Search Page -->
                <div class="search-welcome text-center py-5">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h4>Tìm kiếm sản phẩm</h4>
                    <p class="text-muted">Nhập từ khóa để tìm kiếm sản phẩm bạn mong muốn</p>
                    
                    <div class="search-form mt-4">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" placeholder="Nhập từ khóa tìm kiếm..." 
                                   id="mainSearchInput">
                            <button class="btn btn-primary" onclick="performSearch()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if(!empty($popular_searches)): ?>
                        <div class="popular-searches-main mt-4">
                            <h6>Tìm kiếm phổ biến:</h6>
                            <?php foreach(array_slice($popular_searches, 0, 8) as $search): ?>
                                <a href="search?q=<?php echo urlencode($search['query']); ?>" 
                                   class="btn btn-outline-primary me-2 mb-2">
                                    <?php echo htmlspecialchars($search['query']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Search functionality
function updateSort(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
}

function updateFilters() {
    const url = new URL(window.location);
    
    // Category
    const category = document.querySelector('input[name="category"]:checked')?.value;
    if(category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    
    // Price range
    const minPrice = document.querySelector('input[name="min_price"]').value;
    const maxPrice = document.querySelector('input[name="max_price"]').value;
    
    if(minPrice) {
        url.searchParams.set('min_price', minPrice);
    } else {
        url.searchParams.delete('min_price');
    }
    
    if(maxPrice) {
        url.searchParams.set('max_price', maxPrice);
    } else {
        url.searchParams.delete('max_price');
    }
    
    // Rating
    const ratings = Array.from(document.querySelectorAll('input[name="rating"]:checked'))
                         .map(input => input.value);
    if(ratings.length > 0) {
        url.searchParams.set('rating', ratings.join(','));
    } else {
        url.searchParams.delete('rating');
    }
    
    window.location.href = url.toString();
}

function setQuickPrice(min, max) {
    document.querySelector('input[name="min_price"]').value = min || '';
    document.querySelector('input[name="max_price"]').value = max || '';
    updateFilters();
}

function clearFilters() {
    const url = new URL(window.location);
    const query = url.searchParams.get('q');
    
    // Keep only the search query
    const newUrl = new URL(window.location.origin + window.location.pathname);
    if(query) {
        newUrl.searchParams.set('q', query);
    }
    
    window.location.href = newUrl.toString();
}

function performSearch() {
    const query = document.getElementById('mainSearchInput').value.trim();
    if(query) {
        window.location.href = `search?q=${encodeURIComponent(query)}`;
    }
}

// PHP helper function for building search URLs
<?php
function buildSearchUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'search?' . http_build_query($params);
}
?>
</script>

<style>
/* Search Page Styles */
.search-filters {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    position: sticky;
    top: 2rem;
}

.filter-group {
    margin-bottom: 2rem;
}

.filter-group h6 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1rem;
}

.filter-options {
    max-height: 200px;
    overflow-y: auto;
}

.price-range .form-control {
    border-radius: 8px;
}

.quick-price-filters .btn {
    font-size: 0.75rem;
    border-radius: 15px;
    margin-right: 0.25rem;
}

.popular-tags .badge {
    text-decoration: none;
    transition: all 0.3s ease;
}

.popular-tags .badge:hover {
    background: var(--primary-color) !important;
    color: white !important;
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
}

.product-card:hover .product-actions {
    opacity: 1;
    transform: translateX(0);
}

.no-results i {
    color: #dee2e6;
}

.search-welcome i {
    color: var(--primary-color);
}

.search-suggestions .btn {
    margin: 0.25rem;
}

@media (max-width: 768px) {
    .search-filters {
        position: static;
        margin-bottom: 2rem;
    }
    
    .search-sort {
        margin-top: 1rem;
    }
}
</style>
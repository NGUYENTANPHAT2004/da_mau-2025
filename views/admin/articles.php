<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý bài viết</h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addArticleModal">
                    <i class="fas fa-plus"></i> Thêm bài viết mới
                </button>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tìm kiếm</label>
                                <input type="text" name="search" class="form-control" value="<?php echo $_GET['search'] ?? ''; ?>" placeholder="Tiêu đề bài viết...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Danh mục</label>
                                <select name="category" class="form-control">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="news" <?php echo ($_GET['category'] ?? '') == 'news' ? 'selected' : ''; ?>>Tin tức</option>
                                    <option value="guide" <?php echo ($_GET['category'] ?? '') == 'guide' ? 'selected' : ''; ?>>Hướng dẫn</option>
                                    <option value="review" <?php echo ($_GET['category'] ?? '') == 'review' ? 'selected' : ''; ?>>Đánh giá</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="published" <?php echo ($_GET['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Đã xuất bản</option>
                                    <option value="draft" <?php echo ($_GET['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng bài viết -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tiêu đề</th>
                                    <th>Danh mục</th>
                                    <th>Tác giả</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($articles)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Không có bài viết nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($articles as $article): ?>
                                        <tr>
                                            <td><?php echo $article['id']; ?></td>
                                            <td>
                                                <?php if($article['image']): ?>
                                                    <img src="<?php echo $article['image']; ?>" alt="<?php echo $article['title']; ?>" 
                                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo $article['title']; ?></strong>
                                                <br><small class="text-muted"><?php echo substr($article['excerpt'], 0, 100); ?>...</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?php echo ucfirst($article['category']); ?></span>
                                            </td>
                                            <td><?php echo $article['author_name']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $article['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo $article['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="editArticle(<?php echo $article['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteArticle(<?php echo $article['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa bài viết -->
<div class="modal fade" id="addArticleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm bài viết mới</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="articleForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="article_id" id="article_id">
                    
                    <div class="form-group">
                        <label>Tiêu đề *</label>
                        <input type="text" name="title" id="article_title" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Danh mục *</label>
                                <select name="category" id="article_category" class="form-control" required>
                                    <option value="">Chọn danh mục</option>
                                    <option value="news">Tin tức</option>
                                    <option value="guide">Hướng dẫn</option>
                                    <option value="review">Đánh giá</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" id="article_status" class="form-control">
                                    <option value="draft">Bản nháp</option>
                                    <option value="published">Xuất bản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tóm tắt</label>
                        <textarea name="excerpt" id="article_excerpt" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Nội dung *</label>
                        <textarea name="content" id="article_content" class="form-control" rows="10" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Hình ảnh</label>
                        <input type="file" name="image" id="article_image" class="form-control-file" accept="image/*">
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="featured" id="article_featured" class="form-check-input">
                        <label class="form-check-label" for="article_featured">
                            Bài viết nổi bật
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu bài viết</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editArticle(articleId) {
    // Load thông tin bài viết
    $.ajax({
        url: '<?php echo BASE_URL; ?>get-article/' + articleId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var article = response.article;
                $('#article_id').val(article.id);
                $('#article_title').val(article.title);
                $('#article_category').val(article.category);
                $('#article_status').val(article.status);
                $('#article_excerpt').val(article.excerpt);
                $('#article_content').val(article.content);
                $('#article_featured').prop('checked', article.featured == 1);
                
                $('#modalTitle').text('Sửa bài viết');
                $('#addArticleModal').modal('show');
            } else {
                alert('Không thể tải thông tin bài viết');
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
}

function deleteArticle(articleId) {
    if(confirm('Bạn có chắc muốn xóa bài viết này?')) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>delete-article',
            type: 'POST',
            data: {
                article_id: articleId
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Đã xóa bài viết thành công');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}

$('#articleForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>save-article',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                $('#addArticleModal').modal('hide');
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

$('#addArticleModal').on('hidden.bs.modal', function() {
    $('#articleForm')[0].reset();
    $('#article_id').val('');
    $('#modalTitle').text('Thêm bài viết mới');
});
</script> 
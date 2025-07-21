<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $slug = createSlug($name);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, parent_id, slug) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $parent_id, $slug]);
            $_SESSION['success'] = "Category added successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding category: " . $e->getMessage();
        }
    } 
    elseif (isset($_POST['update_category'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $slug = createSlug($name);
        
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, slug = ? WHERE category_id = ?");
            $stmt->execute([$name, $description, $parent_id, $slug, $id]);
            $_SESSION['success'] = "Category updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating category: " . $e->getMessage();
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            $_SESSION['error'] = "Cannot delete category with products. Please reassign or delete products first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Category deleted successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
    }
    
    header('Location: categories.php');
    exit;
}

// Helper function to create URL-friendly slugs
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Get all categories for display
$categories = $pdo->query("SELECT c.*, p.name as parent_name 
                          FROM categories c 
                          LEFT JOIN categories p ON c.parent_id = p.category_id
                          ORDER BY c.parent_id, c.name")->fetchAll(PDO::FETCH_ASSOC);

// Get categories for parent dropdown (excluding current category if editing)
$parentOptions = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Category Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="action-bar">
        <button id="addCategoryBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Parent Category</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['category_id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= htmlspecialchars($category['description']) ?></td>
                    <td><?= $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '—' ?></td>
                    <td><?= htmlspecialchars($category['slug']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-category" 
                                data-id="<?= $category['category_id'] ?>"
                                data-name="<?= htmlspecialchars($category['name']) ?>"
                                data-description="<?= htmlspecialchars($category['description']) ?>"
                                data-parent-id="<?= $category['parent_id'] ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="categories.php?delete=<?= $category['category_id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this category?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="categoryForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="parent_id">Parent Category</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="">— No Parent —</option>
                            <?php foreach ($parentOptions as $option): ?>
                            <option value="<?= $option['category_id'] ?>"><?= htmlspecialchars($option['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="add_category" id="submitBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Show add modal
    $('#addCategoryBtn').click(function() {
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#parent_id').val('');
        $('#categoryModalLabel').text('Add New Category');
        $('#submitBtn').attr('name', 'add_category').text('Save Category');
        $('#categoryModal').modal('show');
    });

    // Show edit modal
    $('.edit-category').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');
        const parentId = $(this).data('parent-id');

        $('#categoryId').val(id);
        $('#name').val(name);
        $('#description').val(description);
        $('#parent_id').val(parentId || '');
        $('#categoryModalLabel').text('Edit Category');
        $('#submitBtn').attr('name', 'update_category').text('Update Category');
        $('#categoryModal').modal('show');
    });

    // Form validation
    $('#categoryForm').submit(function(e) {
        const name = $('#name').val().trim();
        if (name.length < 2) {
            alert('Category name must be at least 2 characters long');
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>
<?php
require_once 'config/db.php';

// Get all categories with hierarchy
function buildCategoryTree($parent_id = null) {
    global $pdo;
    
    $categories = [];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id " . ($parent_id ? "= ?" : "IS NULL") . " ORDER BY name");
    $stmt->execute($parent_id ? [$parent_id] : []);
    
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category['children'] = buildCategoryTree($category['category_id']);
        $categories[] = $category;
    }
    
    return $categories;
}

$categoryTree = buildCategoryTree();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Velvet Vogue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .category-card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .category-img {
            height: 200px;
            object-fit: cover;
        }
        .category-title {
            font-weight: 600;
            color: #2c3e50;
        }
        .subcategories {
            margin-left: 20px;
            padding-left: 20px;
            border-left: 2px solid #eee;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/category-banner.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4">Our Collections</h1>
            <p class="lead">Browse through our carefully curated categories</p>
        </div>
    </div>

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categories</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">All Categories</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php 
                        function renderCategoryList($categories, $level = 0) {
                            foreach ($categories as $category) {
                                $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                                echo '<a href="category.php?id='.$category['category_id'].'" class="list-group-item list-group-item-action">';
                                echo $indent . htmlspecialchars($category['name']);
                                if (!empty($category['children'])) {
                                    echo ' <i class="fas fa-chevron-down float-end"></i>';
                                }
                                echo '</a>';
                                
                                if (!empty($category['children'])) {
                                    renderCategoryList($category['children'], $level + 1);
                                }
                            }
                        }
                        renderCategoryList($categoryTree);
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <h2 class="mb-4">Featured Categories</h2>
                
                <?php 
                function renderFeaturedCategories($categories) {
                    foreach ($categories as $category) {
                        if (empty($category['children'])) {
                            echo '<div class="col-lg-4 col-md-6 mb-4">';
                            echo '<div class="card category-card h-100">';
                            echo '<img src="'.getCategoryImage($category['category_id']).'" class="card-img-top category-img" alt="'.htmlspecialchars($category['name']).'">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title category-title">'.htmlspecialchars($category['name']).'</h5>';
                            echo '<p class="card-text">'.htmlspecialchars(truncateDescription($category['description'], 100)).'</p>';
                            echo '<a href="category.php?id='.$category['category_id'].'" class="btn btn-outline-dark">View Products</a>';
                            echo '</div></div></div>';
                        } else {
                            echo '<div class="col-12 mb-4">';
                            echo '<h4 class="mb-3">'.htmlspecialchars($category['name']).'</h4>';
                            echo '<div class="row">';
                            renderFeaturedCategories($category['children']);
                            echo '</div></div>';
                        }
                    }
                }
                
                function getCategoryImage($category_id) {
                    // In a real app, you would fetch this from your database
                    $images = [
                        'images/categories/1.jpg',
                        'images/categories/2.jpg',
                        'images/categories/3.jpg',
                        // Add more default images
                    ];
                    return $images[$category_id % count($images)];
                }
                
                function truncateDescription($text, $length = 60) {
                    if (!is_string($text)) return '';
                    $text = trim($text);
                    if (mb_strlen($text) <= $length) return $text;
                    // Ensure we don't cut off in the middle of a word
                    $truncated = mb_substr($text, 0, $length);
                    $lastSpace = mb_strrpos($truncated, ' ');
                    if ($lastSpace !== false) {
                        $truncated = mb_substr($truncated, 0, $lastSpace);
                    }
                    return $truncated . '...';
                }
                
                renderFeaturedCategories($categoryTree);
                ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>


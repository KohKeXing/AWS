<?php
include 'help.php';
session_start();

// Handle product deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = $_GET['delete'];

    // Get product image to delete file
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE productId = ?");
    $stmt->bind_param("s", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Delete the product from database
        $deleteStmt = $conn->prepare("DELETE FROM products WHERE productId = ?");
        $deleteStmt->bind_param("s", $productId);

        if ($deleteStmt->execute()) {
            // Delete image file if it exists
            if (!empty($product['image_url']) && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }

            // Redirect to refresh the page
            header("Location: admin_product.php?success=deleted");
            exit;
        }
    }
}

// Get dashboard stats
$totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$totalStock = $conn->query("SELECT SUM(stock) as total FROM products")->fetch_assoc()['total'] ?? 0;

// Fetch all products with category/subcategory names
$productsQuery = "SELECT p.*, c.name as categoryName, s.name as subcategoryName 
                 FROM products p
                 LEFT JOIN categories c ON p.categoryId = c.categoryId
                 LEFT JOIN subcategories s ON p.subcategoryId = s.subcategoryId
                 ORDER BY p.productId";
$productsResult = $conn->query($productsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            height: 100%;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 205px;
            background-color: #1e272e;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 205px;
            padding-left: 20px;
            width: calc(100% - 205px);
        }
        .dashboard {
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        mark.highlight-match {
            background-color: yellow;
            color: black;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <nav class="sidebar">
        <div class="logo">
            <h2>Admin Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="admindetails.php"><i class="fas fa-user-shield"></i> Admin</a></li>
            <li><a href="custdetails.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <header class="top-bar">
            <div class="search">
                <input type="text" id="productSearch" placeholder="Search products...">
            </div>
            <div class="user-info">
                <span>Admin User</span>
                <i class="fas fa-user-circle" style="font-size: 24px; color: #333;"></i>
            </div>
        </header>

        <div class="dashboard">
            <h1>Product Management</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert success">
                    <?php
                    echo $_GET['success'] === 'deleted' ? "Product deleted successfully!" : "Operation completed successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="quick-stats">
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tags"></i>
                    <h3>Categories</h3>
                    <p><?php echo $totalCategories; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-cubes"></i>
                    <h3>Total Stock</h3>
                    <p><?php echo $totalStock; ?></p>
                </div>
            </div>

            <div class="product-management">
                <div class="section-header">
                    <h2>Products List</h2>
                    <a href="admin_productDetail.php" class="add-product-btn"><i class="fas fa-plus"></i> Add New Product</a>
                </div>

                <table class="product-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Price (RM)</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="productTableBody">
                    <?php if ($productsResult->num_rows > 0): ?>
                        <?php while ($product = $productsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['productId']; ?></td>
                                <td>
                                    <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'images/placeholder.jpg'; ?>" alt="Product" width="60">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($product['subcategoryName'] ?? 'None'); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_productDetail.php?id=<?php echo $product['productId']; ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                        <a href="javascript:void(0);" onclick="confirmDelete('<?php echo $product['productId']; ?>')" class="delete-btn"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-products">No products found. Add your first product!</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    function confirmDelete(productId) {
        if (confirm("Are you sure you want to delete this product? This action cannot be undone.")) {
            window.location.href = "admin_product.php?delete=" + productId;
        }
    }

    document.getElementById('productSearch').addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase().trim();
        const tableRows = document.querySelectorAll('#productTableBody tr');

        tableRows.forEach(row => {
            let matchFound = false;
            const cells = row.querySelectorAll('td');

            cells.forEach(cell => {
                if (cell.querySelector('img')) return;

                const text = cell.textContent.toLowerCase();
                if (searchValue !== '' && text.includes(searchValue)) {
                    matchFound = true;

                    // Highlight the match
                    const regex = new RegExp(`(${searchValue})`, 'gi');
                    cell.innerHTML = cell.textContent.replace(regex, '<mark class="highlight-match">$1</mark>');
                } else {
                    cell.innerHTML = cell.textContent; // Remove highlights
                }
            });

            row.style.display = matchFound ? '' : 'none';
        });
    });
</script>
</body>
</html>

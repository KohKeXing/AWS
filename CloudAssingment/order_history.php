<?php
session_start();
if (!isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$dbname = 'graduation_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get customer info
$customerQuery = "SELECT * FROM customer WHERE customerid = ?";
$customerStmt = $pdo->prepare($customerQuery);
$customerStmt->execute([$_SESSION['customerid']]);
$customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

// Get all orders for this customer
$orderQuery = "SELECT o.*, p.PaymentMethod 
              FROM `order` o 
              LEFT JOIN payment p ON o.OrderID = p.OrderID 
              WHERE o.customerid = ? 
              ORDER BY o.OrderDate DESC";
$orderStmt = $pdo->prepare($orderQuery);
$orderStmt->execute([$_SESSION['customerid']]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

// View specific order details if order_id is provided
$orderDetails = [];
$selectedOrder = null;
if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $orderDetailQuery = "SELECT od.*, p.name 
                        FROM orderdetail od 
                        JOIN products p ON od.productId = p.productId 
                        WHERE od.OrderID = ?";
    $orderDetailStmt = $pdo->prepare($orderDetailQuery);
    $orderDetailStmt->execute([$_GET['order_id']]);
    $orderDetails = $orderDetailStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the selected order
    $selectedOrderQuery = "SELECT o.*, p.PaymentMethod, p.PaymentStatus
                          FROM `order` o 
                          LEFT JOIN payment p ON o.OrderID = p.OrderID 
                          WHERE o.OrderID = ? AND o.customerid = ?";
    $selectedOrderStmt = $pdo->prepare($selectedOrderQuery);
    $selectedOrderStmt->execute([$_GET['order_id'], $_SESSION['customerid']]);
    $selectedOrder = $selectedOrderStmt->fetch(PDO::FETCH_ASSOC);
    
    // Security check - make sure the order belongs to this customer
    if (!$selectedOrder) {
        header('Location: order_history.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add your existing CSS styles here */
    </style>
</head>
<style>

body {

font-family: "Segoe UI", sans-serif;

background: #f4f2fb;

color: #333;

margin: 0;

padding: 0;

}

:root {

--primary-color: #a759f5;

--primary-light: #c1b1f7;

--primary-dark: #8a4fd3;

--secondary-color: #353535;

--text-color: #353535;

--light-text: #666;

--white: #ffffff;

--light-bg: #f8f9fa;

--border-color: #eee;

--shadow: 0 4px 12px rgba(0,0,0,0.1);

--hover-shadow: 0 8px 20px rgba(0,0,0,0.15);

}

.main-header {

width: 100%;

background: #fff;

padding: 15px 30px;

display: flex;

justify-content: space-between;

align-items: center;

box-shadow: 0 4px 6px rgba(0,0,0,0.1);

position: fixed;

top: 0;

z-index: 1000;

}

.logo {
font-size: 24px;
font-weight: bold;
color: #a759f5;
}

.menu-icon {
display: none;
font-size: 24px;
cursor: pointer;
color: #a759f5;
}

#menu-toggle {
display: none;
}

.menu {
display: flex;
gap: 20px;
}

.menu a {
color: #353535;
text-decoration: none;
padding: 10px 15px;
border-radius: 5px;
transition: all 0.3s ease;
}

.menu a:hover,
.menu a.active {
background: linear-gradient(to right, #c1b1f7, #a890fe);
color: #fff;
}

.menu a i {
margin-right: 8px;
}

/* Responsive */
@media (max-width: 768px) {
.menu {
flex-direction: column;
position: absolute;
top: 70px;
left: 0;
width: 100%;
background: #fff;
padding: 10px 0;
display: none;
}

#menu-toggle:checked + .menu-icon + .menu {
display: flex;
}

.menu-icon {
display: block;
}
}

css

Copy
    header {
        background: #6b3fa0;
        color: white;
        padding: 15px 0;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    

    .content-area {
        flex: 1;
    }
    
    .page-title {
        color: #6b3fa0;
        margin-top: 0;
        margin-bottom: 20px;
    }
    
    .orders-list {
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .orders-table th {
        background: #6b3fa0;
        color: white;
        padding: 12px 15px;
        text-align: left;
    }
    
    .orders-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    
    .orders-table tr:last-child td {
        border-bottom: none;
    }
    
    .orders-table tr:hover {
        background: #f9f7fd;
    }
    
    .status-pill {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-processing {
        background: #cce5ff;
        color: #004085;
    }
    
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .view-btn {
        background: #6b3fa0;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
    }
    
    .view-btn:hover {
        background: #5a2e8a;
    }
    
    .order-details-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-top: 30px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }
    
    .order-id {
        font-size: 18px;
        font-weight: bold;
        color: #6b3fa0;
    }
    
    .order-date {
        color: #666;
    }
    
    .order-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .info-group {
        margin-bottom: 15px;
    }
    
    .info-label {
        font-weight: bold;
        color: #666;
        margin-bottom: 5px;
    }
    
    .info-value {
        color: #333;
    }
    
    .order-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .order-items th {
        background: #f1e9ff;
        color: #6b3fa0;
        padding: 10px;
        text-align: left;
    }
    
    .order-items td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .product-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    
    .product-name {
        font-weight: bold;
    }
    
    .order-summary {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .summary-card {
        width: 300px;
        background: #f9f7fd;
        padding: 15px;
        border-radius: 5px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .summary-item.total {
        padding-top: 10px;
        border-top: 1px solid #ddd;
        font-weight: bold;
        font-size: 18px;
    }
    
    .back-btn {
        display: inline-block;
        margin-bottom: 20px;
        color: #6b3fa0;
        text-decoration: none;
    }
    
    .back-btn i {
        margin-right: 5px;
    }
    
    .customer-info {
        margin-bottom: 15px;
    }
    
    .customer-name {
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 5px;
    }
    
    .no-orders {
        background: white;
        padding: 50px;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .print-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
        text-decoration: none;
    }

    .print-btn:hover {
        background: #2980b9;
    }
       /* Footer */
.main-footer {
background: #2C3E50;
color: white;
padding: 50px 0 0;
margin-top:100px;
}

.footer-content {
max-width: 1200px;
margin: 0 auto;
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 30px;
padding: 0 20px;
}

.footer-section h3 {
font-size: 18px;
margin-bottom: 20px;
position: relative;
}

.footer-section h3:after {
content: '';
display: block;
width: 50px;
height: 2px;
background: var(--primary-color);
margin-top: 10px;
}

.footer-section p {
margin-bottom: 10px;
opacity: 0.8;
}

.footer-section ul {
list-style: none;
}

.footer-section ul li {
margin-bottom: 10px;
}

.footer-section ul li a {
color: white;
text-decoration: none;
opacity: 0.8;
transition: all 0.3s;
}

.footer-section ul li a:hover {
opacity: 1;
padding-left: 5px;
color: var(--primary-light);
}

.social-icons {
display: flex;
gap: 15px;
margin-top: 15px;
}

.social-icons a {
color: white;
font-size: 18px;
transition: all 0.3s;
}

.social-icons a:hover {
color: var(--primary-color);
transform: translateY(-3px);
}

.footer-bottom {
background: #1a252f;
text-align: center;
padding: 20px 0;
margin-top: 40px;
}

.footer-bottom p {
font-size: 14px;
opacity: 0.7;
}

css

Copy
    @media print {
        header, .back-btn, .print-btn {
            display: none;
        }
        
        body, .container, .content-area {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        .order-details-card {
            box-shadow: none;
            margin: 0;
            padding: 0;
        }
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .main-content {
            flex-direction: column;
        }
     
        .order-info {
            grid-template-columns: 1fr;
        }
    }
</style>
<body>
<header class="main-header">
    <div class="logo">Golden Gown</div>
    <nav class="menu">
        <a href="Homepage.php" class="active"><i class="fas fa-qrcode"></i> Homepage</a>
        <a href="profile.php?customerid=<?= $_SESSION['customerid'] ?>"><i class="fas fa-stream"></i> Profile</a>
        <a href="customer_product.php"><i class="fas fa-graduation-cap"></i> Graduation Gift </a>
        <a href="shopping_cart.php"><i class="fas fa-shopping-cart"></i> Cart </a>
        <a href="customer_status.php"><i class="fa-solid fa-clipboard-list"></i> History </a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </nav>
</header>

<div class="container">
    <div class="main-content">
        <div class="sidebar">
            <div class="customer-info">
                <div class="customer-name"><?= htmlspecialchars($customer['name']) ?></div>
                <div><?= htmlspecialchars($customer['email']) ?></div>
            </div>
        </div>
        
        <div class="content-area">
            <?php if (isset($_GET['order_id']) && $selectedOrder): ?>
                <a href="order_history.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to all orders</a>
                
                <div class="order-details-card">
                    <div class="card-header">
                        <div class="order-id">Order #<?= htmlspecialchars($selectedOrder['OrderID']) ?></div>
                        <div class="order-date"><?= date('F j, Y', strtotime($selectedOrder['OrderDate'])) ?></div>
                    </div>
                    
                    <div class="order-info">
                        <div>
                            <div class="info-group">
                                <div class="info-label">Order Status</div>
                                <div class="status-pill status-<?= strtolower($selectedOrder['OrderStatus']) ?>">
                                    <?= htmlspecialchars($selectedOrder['OrderStatus']) ?>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Payment Method</div>
                                <div class="info-value"><?= htmlspecialchars($selectedOrder['PaymentMethod'] ?? 'N/A') ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Payment Status</div>
                                <div class="info-value"><?= htmlspecialchars($selectedOrder['PaymentStatus'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="info-group">
                                <div class="info-label">Customer Name</div>
                                <div class="info-value"><?= htmlspecialchars($customer['name']) ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Shipping Address</div>
                                <div class="info-value"><?= htmlspecialchars($customer['address']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <h3>Order Items</h3>
                    <table class="order-items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orderDetails as $item): ?>
                                <tr>
                                    <td class="product-name"><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['Quantity'] ?></td>
                                    <td>RM <?= number_format($item['UnitPrice'], 2) ?></td>
                                    <td>RM <?= number_format($item['TotalAmount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="order-summary">
                        <div class="summary-card">
                            <div class="summary-item">
                                <div>Subtotal</div>
                                <div>RM <?= number_format($selectedOrder['TotalAmount'], 2) ?></div>
                            </div>
                            
                            <div class="summary-item">
                                <div>Discount</div>
                                <div>RM <?= number_format($selectedOrder['DiscountAmount'], 2) ?></div>
                            </div>
                            
                            <div class="summary-item total">
                                <div>Total</div>
                                <div>RM <?= number_format($selectedOrder['FinalAmount'], 2) ?></div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button onclick="window.print()" class="print-btn"><i class="fas fa-print"></i> Print Receipt</button>
                    </div>
                </div>
            
            <?php else: ?>
                <h1 class="page-title">My Order History</h1>
                
                <?php if (count($orders) > 0): ?>
                    <div class="orders-list">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['OrderID']) ?></td>
                                        <td><?= date('M d, Y', strtotime($order['OrderDate'])) ?></td>
                                        <td>RM <?= number_format($order['FinalAmount'], 2) ?></td>
                                        <td><?= htmlspecialchars($order['PaymentMethod'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="status-pill status-<?= strtolower($order['OrderStatus']) ?>">
                                                <?= htmlspecialchars($order['OrderStatus']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_history.php?order_id=<?= $order['OrderID'] ?>" class="view-btn">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
                        <h2>No Orders Found</h2>
                        <p>You haven't placed any orders yet.</p>
                        <a href="customer_product.php" class="view-btn" style="margin-top: 15px; display: inline-block;">
                            Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About Us</h3>
            <p>TARUMT Golden Gown provides high-quality graduation essentials for all TARUMT graduates.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="Homepage.php">Home</a></li>
                <li><a href="customer_product.php">Product</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> TARUMT, Penang Branch </p>
            <p><i class="fas fa-phone"></i> +60 12-345-6789</p>
            <p><i class="fas fa-envelope"></i> info@goldengow.com</p>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2010 TARUMT Golden Gown. All rights reserved.</p>
    </div>
</footer>

<script>
    // Add any JavaScript functionality if needed
</script>
</body>
</html>

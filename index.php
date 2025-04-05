<?php
session_start();
include 'includes/db.php';

// Lấy danh mục và thương hiệu
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);

// Xử lý lọc sản phẩm
$whereClause = "1=1";
$params = [];

if (!empty($_GET['category'])) {
    $whereClause .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}
if (!empty($_GET['brand'])) {
    $whereClause .= " AND p.brand_id = ?";
    $params[] = $_GET['brand'];
}
if (!empty($_GET['search'])) {
    $whereClause .= " AND p.name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
}

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE $whereClause");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tìm kiếm sản phẩm
$search = "";
$search_condition = "1=1"; // Mặc định là luôn đúng
$params = [];

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_condition .= " AND (products.name LIKE ? OR categories.name LIKE ? OR brands.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $conn->prepare("
    SELECT products.*, categories.name AS category_name, brands.name AS brand_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    LEFT JOIN brands ON products.brand_id = brands.id
    WHERE $search_condition
");
$stmt->execute($params);

// Lấy số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart_items WHERE cart_id IN (SELECT id FROM carts WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user']['id']]);
    $cart_count = $stmt->fetchColumn() ?? 0;
}

// Xử lý thêm vào giỏ hàng
if (isset($_GET['add_to_cart']) && isset($_SESSION['user'])) {
    $product_id = $_GET['add_to_cart'];
    $user_id = $_SESSION['user']['id'];

    $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_id = $stmt->fetchColumn();

    if (!$cart_id) {
        $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $conn->lastInsertId();
    }

    $stmt = $conn->prepare("SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$cart_id, $product_id]);
    }

    echo "<script>alert('Product has been added to cart'); window.location.href='index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; background: #f8f9fa; color: #333; }
        .container { width: 90%; margin: auto; padding-top: 20px; display: flex; }
        .sidebar { width: 25%; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .content { width: 70%; margin-left: 5%; }
        .header { background: #007bff; color: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); }
        .header a { color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; }
        .header a:hover { background: rgba(255, 255, 255, 0.2); }
        .btn { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; text-align: center; }
        .btn:hover { background: #218838; }
        .product-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .product { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); text-align: center; transition: transform 0.3s ease; }
        .product:hover { transform: translateY(-10px); }
        .product img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
        .product-info { padding-top: 15px; }
        .product h3 { font-size: 1.2em; color: #333; font-weight: bold; }
        .product p { font-size: 1em; color: #777; margin: 10px 0; }
        .search-box {margin-bottom: 20px; position: relative; display: inline-block;}
        input[type="text"] {width: 100%;max-width: 500px; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 25px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); background: white; transition: all 0.3s;}
        input[type="text"]:focus { border-color: #007bff; outline: none; box-shadow: 0px 4px 15px rgba(0, 123, 255, 0.3); }
        button {position: absolute; left:300px ; top: 50%; transform: translateY(-50%); background:rgb(38, 180, 19); color: white; border: none; border-radius: 50px; padding: 8px 15px; cursor: pointer;font-size: 16px;box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); transition: background 0.3s; }
        button:hover {background: #0056b3; }
        .sidebar-item { font-size: 1em; color: #555; text-decoration: none; display: block; padding: 8px 0; transition: color 0.3s ease; }
        .sidebar-item:hover { color: #007bff; }
        .brands { margin-top: 15px; }
        .brand-item { display: inline-block; background: #007bff; color: white; padding: 10px; border-radius: 5px; text-decoration: none; margin: 5px 0; transition: background-color 0.3s ease; }
        .brand-item:hover { background: #0056b3; }
        .pagination a { padding: 10px 15px; margin: 5px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; }
        .pagination a:hover { background: #0056b3; }
        .footer { background: #343a40; color: white; text-align: center; padding: 15px 0; margin-top: 20px; }
        .footer a { color: #f8f9fa; text-decoration: none; margin: 0 10px; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <a href="home.php"><i class="fa fa-home"></i> Home </a>

        <form method="GET" class="search-box">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="🔍 Search by name, category, or brand...">
            <button type="submit">Search</button>
        </form>

        <div>
            <a href="cart.php" class="btn"><i class="fa fa-shopping-cart"></i> Cart (<?php echo $cart_count; ?>)</a>
            <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])): ?>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn">🔧 Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn">Log out</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>📌 Loại đồng hồ</h3>
            <?php foreach ($categories as $cat): ?>
                <p><a href="?category=<?php echo $cat['id']; ?>" class="sidebar-item">👉 <?php echo $cat['name']; ?></a></p>
            <?php endforeach; ?>
            
            <h3>🏷️ Thương hiệu</h3>
            <div class="brands">
                <?php foreach ($brands as $brand): ?>
                    <a href="?brand=<?php echo $brand['id']; ?>" class="brand-item"><?php echo $brand['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="content">
            <h1>Watch List</h1>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><strong>Price:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                            <a href="index.php?add_to_cart=<?php echo $product['id']; ?>" class="btn">Add to cart</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <!-- Pagination Links -->
            </div>
        </div>
    </div>

    <div class="footer">
        <p>© 2025 WatchStore. All rights reserved.</p>
    </div>
</body>
</html>

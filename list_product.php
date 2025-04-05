<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'includes/db.php';

// Xóa sản phẩm
$delete_msg = "";
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$_GET['delete_id']])) {
        $delete_msg = "Xóa đồng hồ thành công";
    } else {
        $delete_msg = "Lỗi khi xóa đồng hồ";
    }
}

// Tìm kiếm sản phẩm
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("
        SELECT products.*, categories.name AS category_name, brands.name AS brand_name
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        LEFT JOIN brands ON products.brand_id = brands.id
        WHERE products.name LIKE ?
    ");
    $stmt->execute(["%$search%"]);
} else {
    // Phân trang
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $limit;

    $stmt = $conn->prepare("
        SELECT products.*, categories.name AS category_name, brands.name AS brand_name
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        LEFT JOIN brands ON products.brand_id = brands.id
        LIMIT ?, ?
    ");
    $stmt->bindValue(1, $start, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    // Đếm tổng số sản phẩm
    $total_stmt = $conn->query("SELECT COUNT(*) FROM products");
    $total_rows = $total_stmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; text-align: center; padding: 20px; }
        .container { max-width: 900px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin: auto; }
        h1 { color: #333; }
        .message { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .search-box { margin-bottom: 15px; }
        input[type="text"] { width: 70%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #28a745; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        img { width: 80px; height: auto; border-radius: 5px; }
        .btn { text-decoration: none; padding: 8px 12px; border-radius: 5px; color: white; display: inline-block; }
        .edit { background: #007bff; }
        .delete { background: #dc3545; }
        .add { background: #28a745; margin-top: 10px; display: inline-block; }
        .pagination a { margin: 0 5px; padding: 8px 12px; background: #ddd; color: black; border-radius: 4px; text-decoration: none; }
        .pagination a.active { background: #4CAF50; color: white; }
        .admin-home { display: block; margin-bottom: 15px; font-size: 18px; font-weight: bold; color: black; }
        .admin-home {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .admin-home:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" class="admin-home" >🏠 Admin Home</a>
        <h1>Product Management</h1>

        <?php if ($delete_msg): ?>
            <p class="message"><?php echo $delete_msg; ?></p>
        <?php endif; ?>

        <form method="GET" class="search-box">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="🔍 Nhập tên sản phẩm...">
            <button type="submit">Search</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Tên đồng hồ</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Image</th>
                <th>Act</th>
            </tr>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format(floatval($product['price']), 0, ',', '.'); ?> VNĐ</td>
                    <td><img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                    <td>
                        <a href="product.php?edit_id=<?php echo $product['id']; ?>" class="btn edit">✏️ Edit</a>
                        <a href="#" onclick="confirmDelete(<?php echo $product['id']; ?>)" class="btn delete">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="product.php" class="btn add">➕ Add new product</a>

        <?php if (!isset($_GET['search'])): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this product?")) {
                window.location.href = "list_product.php?delete_id=" + id;
            }
        }
    </script>
</body>
</html>

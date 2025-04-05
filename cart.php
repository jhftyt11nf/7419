<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'includes/db.php';
$user_id = $_SESSION['user']['id'];

// Lấy ID giỏ hàng của user
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không có giỏ hàng, tạo giỏ hàng mới
if (!$cart) {
    $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $conn->lastInsertId();
} else {
    $cart_id = $cart['id'];
}

// Lấy danh sách sản phẩm trong giỏ hàng
$stmt = $conn->prepare("
    SELECT ci.id AS cart_item_id, p.id AS product_id, p.name, p.price, p.image, ci.quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove_from_cart'])) {
    $cart_item_id = $_GET['remove_from_cart'];

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$cart_item_id]);

    header('Location: cart.php');
    exit();
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    if ($total_price > 0) {
        // Tạo đơn hàng mới
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $total_price]);
        $order_id = $conn->lastInsertId();

        // Lưu chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Xóa giỏ hàng sau khi thanh toán
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);

        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #ccc;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        img {
            width: 80px;
            height: auto;
            border-radius: 5px;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        .checkout-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 15px;
        }

        .checkout-btn:hover {
            background: #218838;
        }

        .back-home {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .back-home:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Your cart</h1>
        <a href="index.php" class="back-home">🏠 Back to home page</a>

        <table>
            <tr>
                <th>Product Name</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Act</th>
            </tr>
            <?php if (count($cart_items) > 0): ?>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($item['image']); ?>"></td>
                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                        <td>
                            <button class="remove-btn" onclick="confirmDelete(<?php echo $item['cart_item_id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">Cart is empty.</td></tr>
            <?php endif; ?>
        </table>

        <?php if (count($cart_items) > 0): ?>
            <form method="POST" action="checkout.php">
                <button type="submit" name="checkout" class="checkout-btn">✅ Pay</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(cartItemId) {
            if (confirm("Are you sure you want to remove this product from your cart?")) {
                window.location.href = "?remove_from_cart=" + cartItemId;
            }
        }
    </script>
</body>
</html>

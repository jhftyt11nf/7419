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

// Nếu không có giỏ hàng, quay về giỏ hàng
if (!$cart) {
    header('Location: cart.php');
    exit();
}

$cart_id = $cart['id'];

// Lấy sản phẩm trong giỏ hàng
$stmt = $conn->prepare("
    SELECT p.id AS product_id, p.name, p.price, p.image, ci.quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Khởi tạo tổng tiền trước khi tính toán
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Xử lý thanh toán
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // ✅ Kiểm tra biến trước khi sử dụng
    $shipping_address = $_POST['shipping_address'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (empty($shipping_address) || empty($phone)) {
        $error = "Please enter complete shipping information.";
    } else {
        // ✅ Tạo đơn hàng mới
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, shipping_address, phone, status, created_at) 
                                VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$user_id, $total_price, $shipping_address, $phone]);
        $order_id = $conn->lastInsertId();

        // ✅ Lưu chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // ✅ Xóa giỏ hàng sau khi thanh toán
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);

        // ✅ Chuyển hướng đến trang thành công
        header('Location: success.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Pay</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { width: 50%; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; }
        button { margin-top: 15px; padding: 10px; background: green; color: white; border: none; cursor: pointer; width: 100%; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Pay</h1>

    <!-- Hiển thị lỗi nếu có -->
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Shipping address:</label>
        <input type="text" name="shipping_address" required>

        <label>Phone number:</label>
        <input type="text" name="phone" required>

        <h3>Total amount: <?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ</h3>

        <button type="submit" name="confirm_order">Payment Confirmation</button>
    </form>
</body>
</html>

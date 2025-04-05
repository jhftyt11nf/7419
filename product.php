<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'includes/db.php';

$name = $price = "";
$category_id = $brand_id = null;
$edit_mode = false;
$error = "";
$success = "";
$image = "";

// Lấy danh sách categories
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách brands
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra nếu có edit_id (Chỉnh sửa sản phẩm)
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $product_id = $_GET['edit_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
        $price = $product['price'];
        $category_id = $product['category_id'];
        $brand_id = $product['brand_id'];
        $image = $product['image'];
    }
}

// Xử lý thêm/sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $product_id = $_POST['product_id'] ?? null;

    if (empty($name) || empty($price) || empty($category_id) || empty($brand_id)) {
        $error = "Vui lòng điền đầy đủ thông tin";
    } elseif ($price <= 0) {
        $error = "Giá đồng hồ phải lớn hơn 0";
    } else {
        // Xử lý upload hình ảnh
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $image_name = time() . "_" . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file; // Cập nhật đường dẫn ảnh mới
            }
        }

        if ($product_id) {
            // Cập nhật sản phẩm
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category_id = ?, brand_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $price, $category_id, $brand_id, $image, $product_id]);
            $success = "Cập nhật đồng hồ thành công";
        } else {
            // Thêm sản phẩm mới
            $stmt = $conn->prepare("INSERT INTO products (name, price, category_id, brand_id, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $category_id, $brand_id, $image]);
            $success = "PThêm đồng hồ thành công";
        }

        if (!$edit_mode) {
            $name = $price = "";
            $category_id = $brand_id = null;
            $image = "";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $edit_mode ? "Edit product" : "Add product"; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #ccc;
            text-align: left;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .btn-back {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #0056b3;
        }

        .product-image {
            max-width: 100px;
            display: block;
            margin: 10px auto;
            border-radius: 5px;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $edit_mode ? "Edit product" : "Add product"; ?></h1>
        <a href="list_product.php" class="btn-back">⮜ Quay lại danh sách đồng hồ</a>

        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="message"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $edit_mode ? $product['id'] : ''; ?>">

            <label for="name">Tên đồng hồ:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="category_id">Loại đồng hồ:</label>
            <select name="category_id" required>
                <option value="">Chọn loại đồng hồ</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $category_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="brand_id">Thương hiệu:</label>
            <select name="brand_id" required>
                <option value="">Chọn thương hiệu</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['id']; ?>" <?php echo ($brand['id'] == $brand_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="price">Giá:</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="image">Hình ảnh:</label>
            <input type="file" name="image">
            <?php if (!empty($image)): ?>
                <p>Hình ảnh hiện tại:</p>
                <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
            <?php endif; ?>

            <button type="submit" class="btn-submit"><?php echo $edit_mode ? "Update product" : "Add product"; ?></button>
        </form>
    </div>
</body>
</html>

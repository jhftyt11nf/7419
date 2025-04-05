<?php
// Bắt đầu phiên làm việc
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa (có thể tùy chỉnh phần này tùy theo yêu cầu của bạn)
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="LoginDH.html">
    <title>Home Page</title>
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="list_product.php">Products List</a></li>
            <li><a href="product.php">Product Detail</a></li>
        </ul>
    </nav>

    <!-- Nhúng nội dung các file vào đây -->
    <div id="content">
        <?php
        // Điều kiện để hiển thị trang nào
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            if ($page == 'index') {
                include('index.php');
            } elseif ($page == 'list_product') {
                include('list_product.php');
            } elseif ($page == 'product') {
                include('product.php');
            }
        } else {
            include('index.php'); // Mặc định sẽ hiển thị trang chủ
        }
        ?>
    </div>

    <div class="wrapper">
        <div class="header">
            <div class="logo">
                <img id="logo" src="a.png" alt="" width="50">
            </div>
            <div class="form-search">
                <input type="text" placeholder="Tìm kiếm sản phẩm...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
            <div class="cart">
                <a href="cart.php"> <!-- Thêm liên kết đến trang giỏ hàng -->
                    <img src="cart.jpg" alt="Cart" width="30">
                    <span id="cart-count">0</span>
                </a>
            </div>
        </div>
        <div class="menu">
            <ul>
                <li><a href="home.php">Home page</a></li>
                <li><a href="add_product.php">Add product</a></li>
                <li><a href="my_account.php">My account</a></li>
                <li><a href="contact.php">Contact Page</a></li>
            </ul>
        </div>
        
        <!-- Slideshow tự động chạy -->
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></button>
            </div>
        </div>        

        <div class="content">
            <div class="left">
                <div class="category">
                    <ul>
                        <p>Sản phẩm</p> 
                        <li>Đồng hồ cơ</li>
                        <li>Đồng hồ tự động</li>
                        <li>Đồng hồ cổ điển</li>
                        <li>Đồng hồ thời trang</li>
                        <li>Đồng hồ thể thao</li>
                    </ul>
                </div>
                <div class="brand">
                    <ul>
                        <p>Thương hiệu</p>
                        <li><a href="">Tissot</a></li>
                        <li><a href="">Rolex</a></li>
                        <li><a href="">Casio</a></li>
                        <li><a href="">Garmin</a></li>
                    </ul>
                </div>
            </div>
            <div class="right">
                <div class="product">
                    <div class="single_product">
                        <h3>Đồng hồ Tissot</h3>
                        <img src="dh1.jpg" alt="" width="150">
                        <p>Price: $500.000</p>
                        <a href="chitietdh.php">details</a>
                        <button class="add-to-cart" data-product-id="1" data-product-name="Đồng hồ" data-product-price="500000">Add to cart</button>
                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ Rolex</h3>
                        <img src="dh2.jpg" alt="" width="150">
                        <p>Price: $200.000</p>
                        <a href="chitietdh2.php">details</a>
                        <button class="add-to-cart" data-product-id="2" data-product-name="Đồng hồ Rolex" data-product-price="200000">Add to cart</button>
                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ Casio</h3>
                        <img src="dh3.jpg" alt="" width="150">
                        <p>Price: $600.000</p>
                        <a href="chitietdh3.php">details</a>
                        <button class="add-to-cart" data-product-id="3" data-product-name="Đồng hồ Casio" data-product-price="600000">Add to cart</button>
                    </div>
                    <!-- Thêm các sản phẩm khác -->
                </div>
            </div>
        </div>
        <div class="footer"></div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="cart.js"></script>
</body>
</html>

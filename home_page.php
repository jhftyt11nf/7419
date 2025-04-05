<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.html">
    <link rel="stylesheet" href="home_page.css">
    <title>Document</title>
    
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="logo">
                <img id="logo" src="a.png" alt="" width="50">
            </div>
            <form action="search.php" method="GET" class="form-search">
                <input type="text" name="query" placeholder="Search product">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>

            <div class="cart">
                <a href="cart.php"> <!-- Thêm liên kết đến trang giỏ hàng -->
                    <img src="cart.jpg" alt="Cart" width="30">
                    <span id="cart-count">0</span>
                </a>
            </div>
            <a href="AHIHI/login/logout.php">Logout</a>
        </div>
        <div class="menu">
            <ul>
                <li><a href="">Home page</a></li>
                <li><a href="">Add product</a></li>
                <li><a href="">My account</a></li>
                <li><a href="">Contact Page</a></li>
            </ul>
        </div>
              

        <div class="content">
            <div class="left">
                <div class="category">
                    <ul>
                        <p >Sản phẩm </p> 
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
                        <li><a href="">Tissot </a></li>
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
                        <p>Price: $500,000</p>
                        <a href="chitietdh.html">details</a>
                        <button onclick="addToCart(this)" data-id="1">Add to cart</button>
                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ Rolex</h3>
                        <img src="dh2.jpg" alt="" width="150">
                        <p>Price: $200,000</p>
                        <a href="chitietdh2.html">details</a>
                        <button onclick="addToCart(this)" data-id="2">Add to cart</button>
                    </div>

                    <div class="single_product">
                        <h3>Đồng hồ</h3>
                        <img src="dh3.jpg" alt="" width="150">
                        <p>Price: $600,000</p>
                        <a href="">details</a>
                        <button onclick="addToCart(this)" data-id="1">Add to cart</button>

                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ</h3>
                        <img src="dh4.jpg" alt="" width="150">
                        <p>Price: $300,000</p>
                        <a href="">details</a>
                        <button onclick="addToCart(this)" data-id="1">Add to cart</button>

                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ</h3>
                        <img src="dh5.jpg" alt="" width="150">
                        <p>Price: $600,000</p>
                        <a href="">details</a>
                        <button onclick="addToCart(this)" data-id="1">Add to cart</button>

                    </div>
                    <div class="single_product">
                        <h3>Đồng hồ</h3>
                        <img src="dh6.jpg" alt="" width="150">
                        <p>Price: $400,000</p>
                        <a href="">details</a>
                        <button onclick="addToCart(this)" data-id="1">Add to cart</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer"></div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function addToCart(button) {
    const productId = button.getAttribute('data-id');
    const quantity = 1;  // Có thể thêm tính năng cho phép người dùng chọn số lượng

    // Gửi yêu cầu tới server để thêm sản phẩm vào giỏ hàng
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);  // Hiển thị thông báo
        if (data.status === 'success') {
            updateCartCount();  // Cập nhật số lượng giỏ hàng
        }
    });
    }

    function updateCartCount() {
        fetch('cart_count.php')
        .then(response => response.text())
        .then(count => {
            document.getElementById('cart-count').textContent = count;  // Cập nhật số lượng giỏ hàng
        });
    }

    // Gọi updateCartCount khi trang được tải
    document.addEventListener('DOMContentLoaded', updateCartCount);

    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        p {
            color: #555;
            margin-bottom: 20px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .nav a {
            display: block;
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .nav a:hover {
            background: #218838;
        }

        .logout {
            background: #dc3545 !important;
        }

        .logout:hover {
            background: #c82333 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome Admin</h1>
        <p>You have the right to manage products and users.</p>
        
        <div class="nav">
            <a href="index.php">🏠 Home</a>
            <a href="list_product.php">📦 Product Management</a>
            <a href="user.php">👥 User Management</a>
            <a href="logout.php" class="logout">🚪 Log out</a>
        </div>
    </div>
</body>
</html>

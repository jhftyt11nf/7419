<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'includes/db.php';

// Xử lý xóa người dùng
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: user.php");
    exit();
}

// Lấy danh sách người dùng
$users = $conn->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; text-align: center; padding: 20px; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin: auto; }
        h1 { color: #333; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #28a745; color: white; }
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .edit { background: #ffc107; color: black; }
        .delete { background: #dc3545; color: white; }
        .edit:hover { background: #e0a800; }
        .delete:hover { background: #c82333; }
        .back { display: block; margin-top: 15px; background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; }
        .back:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Management</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Name </th>
                <th>Email</th>
                <th>Role </th>
                <th>Act </th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn edit">✏️ Sửa</a>
                        <a href="user.php?delete_id=<?php echo $user['id']; ?>" class="btn delete" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">🗑️ Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="admin.php" class="back">🏠 Back to Admin</a>
    </div>
</body>
</html>

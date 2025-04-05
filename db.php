
<?php
$host = "localhost";
$dbname = "se07101asm";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Thiết lập UTF-8 đúng cách
    $conn->exec("SET NAMES utf8mb4");
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->exec("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>

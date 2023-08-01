<?php
session_start();

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Sử dụng Prepared Statements để tránh SQL Injection
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["user_id"]; // Lưu user_id vào session
        $_SESSION["fullname"] = $user["fullname"]; // Lưu user_id vào session
        header("Location: tocao.php");
        exit();
    } else {
        echo "Đăng nhập không thành công! Vui lòng kiểm tra lại username và password.";
    }

    $stmt->close();
}

$conn->close();
?>

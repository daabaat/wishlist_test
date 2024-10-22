<?php
session_start();
require_once 'db_connection.php';

// 회원가입 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        echo "회원가입이 완료되었습니다.";
    } else {
        echo "회원가입 중 오류가 발생했습니다.";
    }
    $stmt->close();
}

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: main.php");
            exit();
        } else {
            echo "비밀번호가 일치하지 않습니다.";
        }
    } else {
        echo "사용자를 찾을 수 없습니다.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 및 로그인</title>
</head>
<body>
    <h2>회원가입</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="사용자 이름" required>
        <input type="password" name="password" placeholder="비밀번호" required>
        <button type="submit" name="register">회원가입</button>
    </form>

    <h2>로그인</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="사용자 이름" required>
        <input type="password" name="password" placeholder="비밀번호" required>
        <button type="submit" name="login">로그인</button>
    </form>
</body>
</html>

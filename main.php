<?php
session_start();
require_once 'db_connection.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// JSON 파일에서 관광지 정보 불러오기
$tourism_data = json_decode(file_get_contents('response.json'), true);

// 찜하기 기능
if (isset($_POST['favorite'])) {
    $attraction_id = $_POST['attraction_id'];
    
    $sql = "INSERT INTO wishlist (user_id, place_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $attraction_id);
    $stmt->execute();
}

// 리뷰 작성 기능
if (isset($_POST['review'])) {
    $attraction_id = $_POST['attraction_id'];
    $review_text = $_POST['review_text'];
    
    $sql = "INSERT INTO reviews (user_id, place_id, review_text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $attraction_id, $review_text);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>대구 관광지</title>
</head>
<body>
<a href="mypage.php">마이페이지</a>
<a href="logout.php">로그아웃</a>
    <h1>대구 관광지 목록</h1>
    
    <?php foreach ($tourism_data['data'] as $attraction): ?>
        <div>
            <h2><?php echo htmlspecialchars($attraction['관광지']); ?></h2>
            <p><?php echo htmlspecialchars($attraction['코스설명']); ?></p>
            
            <form method="POST">
                <input type="hidden" name="attraction_id" value="<?php echo $attraction['관광지번호']; ?>">
                <button type="submit" name="favorite">찜하기</button>
            </form>
            
            <form method="POST">
                <input type="hidden" name="attraction_id" value="<?php echo $attraction['관광지번호']; ?>">
                <textarea name="review_text" placeholder="리뷰를 작성해주세요"></textarea>
                <button type="submit" name="review">리뷰 작성</button>
            </form>
        </div>
    <?php endforeach; ?>
    

</body>
</html>

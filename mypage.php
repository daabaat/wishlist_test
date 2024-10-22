<?php
session_start();

// 로그인 상태 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 데이터베이스 연결
$conn = new mysqli("localhost", "root", "", "social");

if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

// 사용자 정보 가져오기
$user_query = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// JSON 파일에서 관광지 정보 불러오기
$tourism_data = json_decode(file_get_contents('response.json'), true);

// 찜한 목록 가져오기
$wishlist_query = "SELECT place_id FROM wishlist WHERE user_id = ?";
$wishlist_stmt = $conn->prepare($wishlist_query);
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();

// 작성한 리뷰 가져오기
$reviews_query = "SELECT r.place_id, r.review_text, r.created_at FROM reviews r WHERE r.user_id = ? ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $user_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지</title>
</head>
<body>
    <h1>마이페이지</h1>
    
    <p><?php echo htmlspecialchars($user['username']); ?>님 환영합니다!</p>
    <a href="logout.php">로그아웃</a>
    
    <h2>찜한 목록</h2>
    <ul>
    <?php while ($wishlist_item = $wishlist_result->fetch_assoc()): 
        $place_id = $wishlist_item['place_id'];
        $place = array_filter($tourism_data['data'], function($item) use ($place_id) {
            return $item['관광지번호'] == $place_id;
        });
        $place = reset($place); // 첫 번째 (그리고 유일한) 요소를 가져옵니다
        if ($place): ?>
            <li><?php echo htmlspecialchars($place['관광지']); ?> - <?php echo htmlspecialchars($place['코스설명']); ?></li>
        <?php endif; ?>
    <?php endwhile; ?>
    </ul>

    
    <h2>작성한 리뷰</h2>
    <ul>
    <?php while ($review = $reviews_result->fetch_assoc()): 
        $place_id = $review['place_id'];
        $place = array_filter($tourism_data['data'], function($item) use ($place_id) {
            return $item['관광지번호'] == $place_id;
        });
        $place = reset($place); // 첫 번째 (그리고 유일한) 요소를 가져옵니다
        if ($place): 
            $created_at = new DateTime($review['created_at']);
            $formatted_date = $created_at->format('Y년 m월 d일 H:i');
        ?>
            <li>
                <strong><?php echo htmlspecialchars($place['관광지']); ?></strong><br>
                <?php echo htmlspecialchars($review['review_text']); ?><br>
                <small>작성일: <?php echo $formatted_date; ?></small>
            </li>
        <?php endif; ?>
    <?php endwhile; ?>
    </ul>

</body>
</html>

<?php
$user_stmt->close();
$wishlist_stmt->close();
$reviews_stmt->close();
$conn->close();
?>

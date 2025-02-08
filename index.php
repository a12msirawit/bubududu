<?php
// --- ส่วน PHP: เชื่อมต่อฐานข้อมูลและ API endpoints --- //

$host    = 'localhost';
$db      = 'game_leaderboard';
$user    = 'root';     // ค่าเริ่มต้นสำหรับ MAMP
$pass    = 'root';     // ค่าเริ่มต้นสำหรับ MAMP (สำหรับ macOS)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

// ตรวจสอบว่ามีการเรียก API ผ่านพารามิเตอร์ action หรือไม่
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    // API สำหรับบันทึกคะแนน (save_score)
    if ($action == 'save_score' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $game     = $_POST['game'] ?? '';
        $username = $_POST['username'] ?? '';
        $time     = intval($_POST['time'] ?? 0);
        $rounds   = intval($_POST['rounds'] ?? 0);
        if ($game && $username) {
            $stmt = $pdo->prepare("INSERT INTO leaderboard (game, username, time, rounds) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$game, $username, $time, $rounds])) {
                echo "บันทึกคะแนนสำเร็จ";
            } else {
                echo "เกิดข้อผิดพลาดในการบันทึกคะแนน";
            }
        } else {
            echo "ข้อมูลไม่ครบถ้วน";
        }
        exit();
    }
    // API สำหรับดึงข้อมูล Leaderboard (get_leaderboard)
    if ($action == 'get_leaderboard') {
        $game = $_GET['game'] ?? 'game1';
        $stmt = $pdo->prepare("SELECT username, time, rounds, created_at FROM leaderboard WHERE game = ? ORDER BY time ASC LIMIT 10");
        $stmt->execute([$game]);
        $leaderboard = $stmt->fetchAll();
        echo json_encode($leaderboard);
        exit();
    }
}
?>

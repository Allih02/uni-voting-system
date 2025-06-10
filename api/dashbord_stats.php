<?php
// api/dashboard_stats.php - API endpoint for dashboard statistics
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$host = 'localhost';
$db = 'university_voting_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Fetch dashboard statistics
    $stats = [];

    // Total active elections
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM elections WHERE is_active = 1");
    $stmt->execute();
    $stats['active_elections'] = $stmt->fetch()['count'] ?? 0;

    // Total candidates
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM candidates WHERE is_active = 1");
    $stmt->execute();
    $stats['total_candidates'] = $stmt->fetch()['count'] ?? 0;

    // Total votes cast
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM votes");
    $stmt->execute();
    $stats['total_votes'] = $stmt->fetch()['count'] ?? 0;

    // Registered voters
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stmt->execute();
    $stats['registered_voters'] = $stmt->fetch()['count'] ?? 0;

    // Current user's vote status
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM votes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['user_has_voted'] = $stmt->fetch()['count'] > 0;

    // Voter turnout percentage
    if ($stats['registered_voters'] > 0) {
        $stats['voter_turnout'] = round(($stats['total_votes'] / $stats['registered_voters']) * 100, 1);
    } else {
        $stats['voter_turnout'] = 0;
    }

    // Days until election ends (sample calculation)
    $stmt = $pdo->prepare("SELECT end_date FROM elections WHERE is_active = 1 ORDER BY end_date ASC LIMIT 1");
    $stmt->execute();
    $election = $stmt->fetch();
    
    if ($election) {
        $end_date = new DateTime($election['end_date']);
        $now = new DateTime();
        $diff = $now->diff($end_date);
        $stats['days_remaining'] = $diff->days;
        $stats['election_active'] = $end_date > $now;
    } else {
        $stats['days_remaining'] = 0;
        $stats['election_active'] = false;
    }

    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>
<?php
// api/check_vote_status.php - API to check if user has voted
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

    $user_id = $_SESSION['user_id'];
    
    // Check if user has voted in any active election
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(v.id) as vote_count,
            MAX(v.voted_at) as last_vote_date,
            c.name as candidate_name,
            c.position as candidate_position
        FROM votes v
        LEFT JOIN candidates c ON v.candidate_id = c.id
        WHERE v.user_id = ?
        GROUP BY v.user_id
    ");
    $stmt->execute([$user_id]);
    $vote_data = $stmt->fetch();
    
    $has_voted = $vote_data && $vote_data['vote_count'] > 0;
    $vote_count = $vote_data ? (int)$vote_data['vote_count'] : 0;
    
    // Get current active election info
    $stmt = $pdo->prepare("
        SELECT id, title, end_date, is_active 
        FROM elections 
        WHERE is_active = 1 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $election = $stmt->fetch();
    
    $election_active = $election && $election['is_active'] == 1;
    $election_ended = false;
    
    if ($election && $election['end_date']) {
        $end_date = new DateTime($election['end_date']);
        $now = new DateTime();
        $election_ended = $end_date < $now;
    }

    echo json_encode([
        'success' => true,
        'has_voted' => $has_voted,
        'vote_count' => $vote_count,
        'last_vote_date' => $vote_data['last_vote_date'] ?? null,
        'voted_candidate' => $vote_data['candidate_name'] ?? null,
        'voted_position' => $vote_data['candidate_position'] ?? null,
        'election_active' => $election_active,
        'election_ended' => $election_ended,
        'election_title' => $election['title'] ?? null,
        'can_vote' => $election_active && !$election_ended && !$has_voted,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    error_log("Vote status check error: " . $e->getMessage());
}
?>

<?php
// Enhanced vote.php with strict one-vote logic
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
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
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user has already voted
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_vote_data = $stmt->fetch();
$has_voted = $user_vote_data['vote_count'] > 0;

// If user has voted, redirect to dashboard
if ($has_voted) {
    header("Location: dashboard.php?status=already_voted");
    exit();
}

// Check if election is active
$stmt = $pdo->prepare("SELECT * FROM elections WHERE is_active = 1 AND end_date > NOW() ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$active_election = $stmt->fetch();

if (!$active_election) {
    header("Location: dashboard.php?error=no_active_election");
    exit();
}

// Get candidates for active election
try {
    $stmt = $pdo->prepare("
        SELECT id, name, position, platform, image_path, achievements, theme_color
        FROM candidates 
        WHERE is_active = 1 AND election_id = ?
        ORDER BY display_order, name
    ");
    $stmt->execute([$active_election['id']]);
    $candidates = $stmt->fetchAll();
    
    if (empty($candidates)) {
        header("Location: dashboard.php?error=no_candidates");
        exit();
    }
} catch (PDOException $e) {
    error_log("Candidates fetch error: " . $e->getMessage());
    header("Location: dashboard.php?error=system_error");
    exit();
}

// Process vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: vote.php?error=csrf_failed");
        exit();
    }
    
    $candidate_id = filter_input(INPUT_POST, 'candidateId', FILTER_VALIDATE_INT);
    
    if (!$candidate_id) {
        header("Location: vote.php?error=invalid_candidate");
        exit();
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Double-check user hasn't voted (race condition protection)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        
        if ($stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            header("Location: dashboard.php?error=already_voted");
            exit();
        }
        
        // Verify candidate exists and is active
        $stmt = $pdo->prepare("
            SELECT c.*, e.is_active as election_active 
            FROM candidates c 
            JOIN elections e ON c.election_id = e.id 
            WHERE c.id = ? AND c.is_active = 1 AND e.is_active = 1
        ");
        $stmt->execute([$candidate_id]);
        $candidate = $stmt->fetch();
        
        if (!$candidate || !$candidate['election_active']) {
            $pdo->rollBack();
            header("Location: vote.php?error=invalid_candidate");
            exit();
        }
        
        // Record the vote
        $stmt = $pdo->prepare("
            INSERT INTO votes (user_id, candidate_id, election_id, voted_at, ip_address, user_agent) 
            VALUES (?, ?, ?, NOW(), ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $candidate_id,
            $active_election['id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Update user vote count (for quick reference)
        $stmt = $pdo->prepare("
            UPDATE users 
            SET vote_count = vote_count + 1, last_vote_date = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        // Log successful vote
        error_log("Vote recorded - User: $user_id, Candidate: $candidate_id, Election: {$active_election['id']}");
        
        // Send vote confirmation to admin system (optional)
        $this->notifyAdminSystem($user_id, $candidate_id, $active_election['id']);
        
        // Redirect with success
        header("Location: dashboard.php?success=voted&candidate=" . urlencode($candidate['name']));
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Voting error: " . $e->getMessage());
        header("Location: vote.php?error=voting_failed");
        exit();
    }
}

// Helper function to notify admin system
function notifyAdminSystem($user_id, $candidate_id, $election_id) {
    try {
        // Log to admin monitoring table (create this table if needed)
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO admin_vote_log (user_id, candidate_id, election_id, logged_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $candidate_id, $election_id]);
        
        // Optional: Send real-time notification to admin dashboard
        // This could be via WebSocket, database trigger, or API call
        
    } catch (Exception $e) {
        error_log("Admin notification error: " . $e->getMessage());
        // Don't fail the vote if admin notification fails
    }
}

// Get user info
try {
    $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    $user_info = ['fullname' => 'Student'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - Student Elections</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Include all the existing CSS from vote.php with these additions */
        
        /* Vote Restriction Styles */
        .vote-restricted {
            opacity: 0.6;
            pointer-events: none;
            cursor: not-allowed;
        }
        
        .vote-confirmed {
            background: var(--secondary) !important;
            color: white !important;
        }
        
        .confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .confirmation-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .confirmation-modal {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .confirmation-overlay.show .confirmation-modal {
            transform: scale(1);
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .confirmation-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .confirmation-message {
            margin-bottom: 2rem;
            color: var(--gray);
            line-height: 1.6;
        }
        
        .confirmation-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn-confirm {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-confirm:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: transparent;
            color: var(--gray);
            border: 1px solid var(--light-gray);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: var(--light);
        }
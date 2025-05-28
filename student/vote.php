<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get election ID from URL
$election_id = isset($_GET["election_id"]) ? intval($_GET["election_id"]) : 0;
// Database connection
$host = 'localhost';
$db = 'university_voting_system';
$user = 'root';
$pass = ''; // Replace with your MySQL root password if any

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get candidates from database
try {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE is_active = 1 ORDER BY display_order");
    $stmt->execute();
    $candidates = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $candidates[$row['id']] = [
            'name' => $row['name'],
            'position' => $row['position'],
            'platform' => $row['platform'],
            'image' => $row['image_path'],
            'color' => $row['theme_color'] ?? '#4F46E5', // Default color if not specified
            'achievements' => $row['achievements']
        ];
    }
} catch (PDOException $e) {
    // If there's an error, use sample data as fallback
    $candidates = [
        1 => [
            'name' => 'Allih A. Abubakar',
            'position' => 'Student Body President',
            'platform' => 'Campus Sustainability & Student Wellness',
            'image' => '/project-folder/student/allih.jpg',
            'color' => '#4F46E5',
            'achievements' => 'Led Green Campus Initiative, Founded Mental Health Awareness Week'
        ],
        2 => [
            'name' => 'Michael Chen',
            'position' => 'Student Body President',
            'platform' => 'Academic Excellence & Campus Unity',
            'image' => '/project-folder/student/me.jpg',
            'color' => '#8B5CF6',
            'achievements' => 'Academic Senate Representative, Organized Diversity Summit 2024'
        ],
    ];
}

// Process vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $candidateId = $_POST['candidateId'];
        $studentId = $_SESSION['studentId'];
        
        // Check if student has already voted
        $checkStmt = $pdo->prepare("SELECT id FROM votes WHERE student_id = ?");
        $checkStmt->execute([$studentId]);
        if ($checkStmt->rowCount() > 0) {
            // Student has already voted - handle accordingly
            header('Location: dashboard.php?error=already_voted');
            exit;
        }
        
        // Record the vote
        $voteStmt = $pdo->prepare("INSERT INTO votes (student_id, candidate_id, voted_at) VALUES (?, ?, NOW())");
        $voteStmt->execute([$studentId, $candidateId]);
        
        // Redirect to dashboard
        header('Location: dashboard.php?success=1');
        exit;
    } catch (PDOException $e) {
        // Handle database error
        header('Location: dashboard.php?error=db_error');
        exit;
    }
}

// Get user info from database
try {
    $studentId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT username, full_name, program FROM users WHERE id = ?");
    $stmt->execute([$studentId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        // Fallback if user not found
        $userInfo = [
            'username' => 'User',
            'full_name' => 'Student',
            'program' => 'University Program'
        ];
    }
    
    $userInfo['id'] = $studentId;
    
    // Set studentInfo for backward compatibility with the rest of the code
    $studentInfo = [
        'name' => $userInfo['full_name'],
        'id' => $studentId,
        'program' => $userInfo['program']
    ];
} catch (PDOException $e) {
    // Fallback data if there's an error
    $userInfo = [
        'username' => 'User',
        'full_name' => 'Student',
        'program' => 'University Program'
    ];
    
    $studentInfo = [
        'name' => 'Student',
        'id' => $_SESSION['user_id'],
        'program' => 'University Program'
    ];
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
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #C7D2FE;
            --secondary: #8B5CF6;
            --accent: #EC4899;
            --background: #F9FAFB;
            --foreground: #111827;
            --muted: #6B7280;
            --card: #FFFFFF;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --heading-font: 'Poppins', sans-serif;
            --body-font: 'Inter', sans-serif;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--background);
            min-height: 100vh;
            font-family: var(--body-font);
            color: var(--foreground);
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-family: var(--heading-font);
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            color: var(--accent);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .user-details {
            display: none;
        }
        
        .user-details h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--foreground);
        }

        .user-details p {
            font-size: 0.75rem;
            color: var(--muted);
        }

        .hero-section {
            padding: 3rem 0;
            text-align: center;
            background: linear-gradient(135deg, #EEF2FF 0%, #F3E8FF 100%);
            border-radius: 20px;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0) 70%);
            animation: pulse 15s ease-in-out infinite alternate;
        }

        .hero-section h1 {
            font-family: var(--heading-font);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
            position: relative;
        }

        .hero-section p {
            color: var(--muted);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .timeline {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100px;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: var(--primary-light);
            z-index: 0;
        }

        .timeline-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .timeline-circle.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.2);
        }

        .timeline-text {
            font-size: 0.75rem;
            text-align: center;
            color: var(--muted);
        }

        .timeline-item.active .timeline-text {
            color: var(--primary);
            font-weight: 600;
        }

        .voting-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .candidate-card {
            background: var(--card);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .candidate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .candidate-card:hover::before {
            opacity: 1;
        }

        .candidate-card.selected {
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.1);
        }

        .candidate-card.selected::before {
            opacity: 1;
        }

        .candidate-card-inner {
            position: relative;
            z-index: 1;
        }

        .candidate-checkmark {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s ease;
        }

        .candidate-card.selected .candidate-checkmark {
            opacity: 1;
            transform: scale(1);
        }

        .candidate-image {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            overflow: hidden;
            border: 5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease;
        }

        .candidate-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
        }

        .candidate-card:hover .candidate-image img {
            transform: scale(1.05);
        }

        .candidate-info {
            text-align: center;
        }

        .candidate-info h2 {
            font-family: var(--heading-font);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .candidate-info .position {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .platform {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: var(--muted);
        }

        .candidate-stats {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            margin-bottom: 1rem;
            gap: 1rem;
            font-size: 0.85rem;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .stat i {
            font-size: 0.75rem;
        }

        .achievements {
            background: rgba(250, 250, 250, 0.5);
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .achievements strong {
            display: block;
            color: var(--foreground);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .vote-button-container {
            padding: 1.5rem 0;
            position: sticky;
            bottom: 0;
            background: linear-gradient(0deg, rgba(249, 250, 251, 1) 0%, rgba(249, 250, 251, 0.9) 90%, rgba(249, 250, 251, 0) 100%);
            z-index: 10;
        }

        .vote-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        }

        .vote-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .vote-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
        }

        .vote-btn:hover::before {
            left: 100%;
        }

        .vote-btn:disabled {
            background: #CBD5E1;
            box-shadow: none;
            cursor: not-allowed;
        }

        .vote-btn:disabled::before {
            display: none;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .back-btn:hover {
            color: var(--foreground);
            background: rgba(0, 0, 0, 0.03);
        }

        .footer {
            margin-top: 3rem;
            padding: 2rem 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .footer-link {
            color: var(--muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--primary);
        }

        /* Animations */
        @keyframes pulse {
            0% { transform: translate(0, 0) scale(1); opacity: 0.7; }
            50% { transform: translate(-2%, -2%) scale(1.05); opacity: 0.8; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.7; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .selected .candidate-image {
            animation: float 3s ease-in-out infinite;
            border-color: var(--primary-light);
        }

        /* Responsive styles */
        @media (min-width: 768px) {
            .user-details {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
            
            .voting-container {
                grid-template-columns: 1fr;
            }
            
            .timeline {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .timeline-item:not(:last-child)::after {
                display: none;
            }
        }

        /* Theme colors for each candidate */
        #candidate-1::before {
            background: #4F46E5;
        }
        
        #candidate-2::before {
            background: #8B5CF6;
        }
        
        #candidate-3::before {
            background: #EC4899;
        }
        
        #candidate-1 .position,
        #candidate-1.selected h2,
        #candidate-1 .candidate-checkmark {
            color: #4F46E5;
        }
        
        #candidate-2 .position,
        #candidate-2.selected h2,
        #candidate-2 .candidate-checkmark {
            color: #8B5CF6;
        }
        
        #candidate-3 .position,
        #candidate-3.selected h2,
        #candidate-3 .candidate-checkmark {
            color: #EC4899;
        }
        
        #candidate-1.selected .candidate-checkmark {
            background: #4F46E5;
            color: white;
        }
        
        #candidate-2.selected .candidate-checkmark {
            background: #8B5CF6;
            color: white;
        }
        
        #candidate-3.selected .candidate-checkmark {
            background: #EC4899;
            color: white;
        }
    </style>
</head>
<body>
        <div class="container">
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="hero-section">
            <h1>Student Body Elections</h1>
            <p>Your voice matters! Select your preferred candidate to shape the future of our campus community.</p>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-circle">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-text">Registration</div>
                </div>
                <div class="timeline-item active">
                    <div class="timeline-circle active">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="timeline-text">Vote</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-circle">
                        <i class="fas fa-poll"></i>
                    </div>
                    <div class="timeline-text">Results</div>
                </div>
            </div>
        </div>

        <form method="POST" action="vote.php" id="voteForm">
            <input type="hidden" name="candidateId" id="candidateId" required>
            
            <div class="voting-container">
                <?php foreach ($candidates as $id => $candidate): ?>
                <div class="candidate-card" onclick="selectCandidate(<?php echo $id; ?>)" id="candidate-<?php echo $id; ?>">
                    <div class="candidate-card-inner">
                        <div class="candidate-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="candidate-image">
                            <img src="<?php echo htmlspecialchars($candidate['image']); ?>" alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                        </div>
                        <div class="candidate-info">
                            <h2><?php echo htmlspecialchars($candidate['name']); ?></h2>
                            <div class="position"><?php echo htmlspecialchars($candidate['position']); ?></div>
                            <p class="platform"><?php echo htmlspecialchars($candidate['platform']); ?></p>
                            
                            <div class="achievements">
                                <strong>Achievements</strong>
                                <?php echo htmlspecialchars($candidate['achievements']); ?>
                            </div>
                            
                            <div class="candidate-stats">
                                <div class="stat">
                                    <i class="fas fa-star"></i>
                                    Experience
                                </div>
                                <div class="stat">
                                    <i class="fas fa-users"></i>
                                    Leadership
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="vote-button-container">
                <button type="submit" class="vote-btn" id="submitBtn" disabled>
                    <i class="fas fa-vote-yea"></i>
                    Submit Your Vote
                    <span id="selectedCandidateName"></span>
                </button>
            </div>
        </form>
    </div>

    <div class="footer">
        <div class="container">
            <div class="footer-links">
                <a href="election-guidelines.php" class="footer-link">Election Guidelines</a>
                <a href="privacy-policy.php" class="footer-link">Privacy Policy</a>
                <a href="contact.php" class="footer-link">Contact Support</a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> University Student Elections. All rights reserved.</p>
        </div>
    </div>

    <script>
        function selectCandidate(id) {
            // Remove selection from all cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            const selectedCard = document.getElementById('candidate-' + id);
            selectedCard.classList.add('selected');
            
            // Update hidden input and enable submit button
            document.getElementById('candidateId').value = id;
            document.getElementById('submitBtn').disabled = false;
            
            // Get candidate name for the button
            const candidateName = selectedCard.querySelector('h2').textContent;
            document.getElementById('selectedCandidateName').textContent = ' for ' + candidateName;
            
            // Smooth scroll to the vote button
            document.querySelector('.vote-button-container').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }
        
        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.candidate-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * (index + 1));
            });
        });
    </script>
</body>
<!-- Add this HTML code right before the closing </body> tag -->
<div id="voteNotification" class="notification-popup">
    <div class="notification-content">
        <div class="notification-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="notification-text">
            <h3>Success!</h3>
            <p>Your vote has been submitted successfully.</p>
        </div>
    </div>
</div>

<!-- Add this CSS code inside the existing <style> tag -->
<style>
    /* Notification Popup Styles */
    .notification-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-width: 400px;
        width: 90%;
        text-align: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }
    
    .notification-popup.show {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
    }
    
    .notification-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .notification-icon {
        font-size: 3rem;
        color: #10B981; /* Success green color */
        margin-bottom: 1rem;
        animation: scaleIn 0.5s ease forwards;
    }
    
    .notification-text h3 {
        font-family: var(--heading-font);
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--foreground);
    }
    
    .notification-text p {
        color: var(--muted);
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    /* Overlay behind the popup */
    .notification-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .notification-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    @keyframes scaleIn {
        0% { transform: scale(0); }
        70% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>

<!-- Modify the JavaScript code for form submission -->
<script>
    // Existing code for selecting candidates
    function selectCandidate(id) {
        // Remove selection from all cards
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selection to clicked card
        const selectedCard = document.getElementById('candidate-' + id);
        selectedCard.classList.add('selected');
        
        // Update hidden input and enable submit button
        document.getElementById('candidateId').value = id;
        document.getElementById('submitBtn').disabled = false;
        
        // Get candidate name for the button
        const candidateName = selectedCard.querySelector('h2').textContent;
        document.getElementById('selectedCandidateName').textContent = ' for ' + candidateName;
        
        // Smooth scroll to the vote button
        document.querySelector('.vote-button-container').scrollIntoView({ 
            behavior: 'smooth',
            block: 'center'
        });
    }
    
    // Add entrance animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.candidate-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * (index + 1));
        });
        
        // Add event listener for form submission
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'notification-overlay';
            document.body.appendChild(overlay);
            
            // Show notification and overlay
            setTimeout(() => {
                overlay.classList.add('show');
                document.getElementById('voteNotification').classList.add('show');
            }, 100);
            
            // Submit the form data via AJAX
            const formData = new FormData(this);
            
            fetch('vote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Wait for the notification to be visible for a moment
                setTimeout(() => {
                    // Then redirect to dashboard with success parameter
                    window.location.href = 'dashboard.php?success=1';
                }, 2000); // Wait 2 seconds before redirecting
            })
            .catch(error => {
                console.error('Error:', error);
                // If there's an error, still redirect but with error parameter
                setTimeout(() => {
                    window.location.href = 'dashboard.php?error=submission_failed';
                }, 2000);
            });
        });
    });
</script>
</html>
<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get current election data
$current_election = [
    "id" => 1,
    "title" => "Student Council Elections 2025",
    "end_date" => "2025-04-30",
    "description" => "Cast your vote to elect the next Student Council representatives who will lead initiatives for the upcoming academic year."
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Voting System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary: #10b981;
            --secondary-light: #34d399;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --light-gray: #e2e8f0;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gradient: linear-gradient(135deg, #6366f1, #8b5cf6);
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --button-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navigation Bar - Enhanced Responsive */
        .navbar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .logo h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .nav-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .logout-button {
            background-color: transparent;
            color: var(--dark);
            border: 1px solid var(--light-gray);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .logout-button:hover {
            background-color: var(--light-gray);
            color: var(--primary-dark);
        }
        
        /* Hero Section - Responsive */
        .hero {
            background: var(--gradient);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .hero h2 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: clamp(1rem, 2vw, 1.1rem);
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta-button {
            background-color: white;
            color: var(--primary);
            border: none;
            padding: 12px 28px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--button-shadow);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px -1px rgba(0, 0, 0, 0.2);
        }
        
        /* Main Content - Responsive Grid */
        .main-content {
            flex: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .greeting h2 {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .greeting p {
            color: var(--gray);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .user-badge {
            background-color: white;
            border-radius: 50px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 1.2rem;
        }
        
        .user-info {
            line-height: 1.3;
        }
        
        .user-info p {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        /* Enhanced Dashboard Cards with Digit Counters */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.15);
        }
        
        .card-icon {
            background-color: var(--primary-light);
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-size: 1.8rem;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        /* Digit Counter Animation */
        .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        .card-description {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .card-change {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .card-change.positive {
            color: var(--secondary);
        }
        
        .card-change.negative {
            color: var(--danger);
        }
        
        /* Loading Animation for Counters */
        .counter-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
            height: 3rem;
            width: 80px;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .counter-animated {
            animation: countUp 0.6s ease-out;
        }
        
        /* Responsive Election Card */
        .election-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .election-header {
            padding: 2rem;
            background: var(--gradient);
            color: white;
            position: relative;
        }
        
        .election-header h3 {
            font-size: clamp(1.3rem, 3vw, 1.6rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .election-header p {
            opacity: 0.9;
            font-size: clamp(0.9rem, 2vw, 1rem);
            max-width: 700px;
        }
        
        .deadline-badge {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 8px 16px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .election-body {
            padding: 2rem;
            text-align: center;
        }
        
        .vote-button {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--button-shadow);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        
        .vote-button:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(99, 102, 241, 0.4);
        }
        
        .vote-button:disabled {
            background: var(--secondary);
            cursor: not-allowed;
            transform: none;
        }
        
        .vote-button.voted {
            background: var(--secondary);
            cursor: default;
        }
        
        /* Responsive Media Queries */
        @media (max-width: 1200px) {
            .dashboard-cards {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        /* Footer */
        .footer {
            background-color: #30465c;
            color: white;
            padding: 3rem 2rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .footer-section h4 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .footer-section p {
            margin-bottom: 1rem;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.7;
        }

        @media (max-width: 992px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-badge {
                align-self: flex-start;
            }
            
            .deadline-badge {
                position: relative;
                top: auto;
                right: auto;
                margin-top: 1rem;
                width: fit-content;
            }
            
            .main-content {
                padding: 2rem 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                order: 1;
                width: 100%;
                justify-content: center;
            }
            
            .logout-button {
                order: 2;
            }
            
            .hero {
                padding: 3rem 1rem;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .election-header {
                padding: 1.5rem;
            }
            
            .election-body {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .card-value {
                font-size: 2rem;
            }
            
            .vote-button {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --dark: #f8fafc;
                --light: #1e293b;
                --gray: #94a3b8;
                --light-gray: #334155;
            }
            
            body {
                background-color: #0f172a;
                color: var(--dark);
            }
            
            .navbar {
                background-color: #1e293b;
            }
            
            .card, .election-card {
                background-color: #1e293b;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-vote-yea"></i>
                <h1>University Voting System</h1>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="contact.php" class="nav-link">Support</a>
            </div>
            <a href="logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Your Voice Matters</h2>
                <p>Participate in the university elections and help shape the future of our academic community. Every vote counts in building a stronger institution.</p>
                <a href="#" class="cta-button" id="heroVoteBtn">
                    <i class="fas fa-vote-yea"></i> Vote Now
                </a>
            </div>
        </section>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="greeting fade-in">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>!</h2>
                    <p>Access your voting dashboard to participate in active elections.</p>
                </div>
                <div class="user-badge fade-in">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($_SESSION["fullname"]); ?></strong>
                        <p><?php echo htmlspecialchars($_SESSION["registration_number"]); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Dashboard Cards with Live Statistics -->
            <div class="dashboard-cards">
                <div class="card fade-in-delay-1">
                    <div class="card-icon" style="background-color: rgba(99, 102, 241, 0.2); color: var(--primary);">
                        <i class="fas fa-poll-h"></i>
                    </div>
                    <h3 class="card-title">Active Elections</h3>
                    <div class="card-value counter-loading" id="activeElectionsCounter">-</div>
                    <p class="card-description">Current ongoing election campaigns</p>
                    <div class="card-change positive" id="electionsChange">+0 this week</div>
                </div>
                
                <div class="card fade-in-delay-2">
                    <div class="card-icon" style="background-color: rgba(16, 185, 129, 0.2); color: var(--secondary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Total Candidates</h3>
                    <div class="card-value counter-loading" id="candidatesCounter">-</div>
                    <p class="card-description">Candidates running for positions</p>
                    <div class="card-change positive" id="candidatesChange">Ready to serve</div>
                </div>
                
                <div class="card fade-in-delay-3">
                    <div class="card-icon" style="background-color: rgba(245, 158, 11, 0.2); color: var(--warning);">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="card-title">Total Votes Cast</h3>
                    <div class="card-value counter-loading" id="votesCounter">-</div>
                    <p class="card-description">Community participation count</p>
                    <div class="card-change" id="turnoutChange">
                        <span id="turnoutPercentage">0%</span> turnout
                    </div>
                </div>
                
                <div class="card fade-in-delay-4">
                    <div class="card-icon" style="background-color: rgba(239, 68, 68, 0.2); color: var(--danger);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="card-title">Days Remaining</h3>
                    <div class="card-value counter-loading" id="daysCounter">-</div>
                    <p class="card-description">Until current election closes</p>
                    <div class="card-change" id="daysChange">Time to vote!</div>
                </div>
            </div>
            
            <!-- Current Election with Vote Status -->
            <div class="election-card fade-in" id="electionCard">
                <div class="election-header">
                    <h3><?php echo htmlspecialchars($current_election["title"]); ?></h3>
                    <p>An opportunity to elect your student representatives for the upcoming academic year.</p>
                    
                    <div class="deadline-badge">
                        <i class="far fa-calendar-alt"></i>
                        Ends: <?php echo date('M d, Y', strtotime($current_election["end_date"])); ?>
                    </div>
                </div>
                
                <div class="election-body">
                    <p class="election-description">
                        <?php echo htmlspecialchars($current_election["description"]); ?>
                    </p>
                    
                    <button class="vote-button" id="mainVoteBtn">
                        <i class="fas fa-vote-yea"></i> 
                        <span id="voteButtonText">Vote Now</span>
                    </button>
                    
                    <div id="voteStatus" style="display: none; margin-top: 1rem;">
                        <p style="color: var(--secondary); font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Thank you for voting!
                        </p>
                    </div>
                </div>
            </div>
        </main>
            <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>About</h4>
                    <p>The University Voting System is a secure platform designed to facilitate fair and transparent elections across the university community.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="help.php"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        <li><a href="backupallih@gmail.co"><i class="fas fa-envelope"></i> support@university.edu</a></li>
                        <li><a href="tel:+11234567890"><i class="fas fa-phone"></i> +1 (123) 456-7890</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> University Campus, Building A</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> University Voting System. All rights reserved.</p>
            </div>
        </footer>
    </div>

    </div>
    
    <script>
        // Dashboard Statistics Manager
        class DashboardStats {
            constructor() {
                this.apiEndpoint = 'api/dashboard_stats.php';
                this.refreshInterval = 30000; // 30 seconds
                this.counters = {
                    activeElections: document.getElementById('activeElectionsCounter'),
                    candidates: document.getElementById('candidatesCounter'),
                    votes: document.getElementById('votesCounter'),
                    days: document.getElementById('daysCounter')
                };
                this.init();
            }
            
            async init() {
                await this.fetchStats();
                this.startAutoRefresh();
                this.checkVoteStatus();
            }
            
            async fetchStats() {
                try {
                    const response = await fetch(this.apiEndpoint);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.updateCounters(result.data);
                        this.updateVoteButton(result.data.user_has_voted);
                    } else {
                        console.error('Failed to fetch stats:', result.error);
                        this.showOfflineData();
                    }
                } catch (error) {
                    console.error('Error fetching stats:', error);
                    this.showOfflineData();
                }
            }
            
            updateCounters(data) {
                // Animate counter updates
                this.animateCounter(this.counters.activeElections, data.active_elections || 1);
                this.animateCounter(this.counters.candidates, data.total_candidates || 2);
                this.animateCounter(this.counters.votes, data.total_votes || 0);
                this.animateCounter(this.counters.days, data.days_remaining || 16);
                
                // Update turnout percentage
                const turnoutElement = document.getElementById('turnoutPercentage');
                if (turnoutElement) {
                    turnoutElement.textContent = `${data.voter_turnout || 0}%`;
                    
                    // Color code turnout
                    const turnoutChange = document.getElementById('turnoutChange');
                    if (data.voter_turnout >= 70) {
                        turnoutChange.className = 'card-change positive';
                    } else if (data.voter_turnout >= 40) {
                        turnoutChange.className = 'card-change';
                    } else {
                        turnoutChange.className = 'card-change negative';
                    }
                }
                
                // Update election status
                if (!data.election_active) {
                    document.getElementById('daysChange').textContent = 'Election ended';
                    document.getElementById('daysChange').className = 'card-change negative';
                }
            }
            
            animateCounter(element, targetValue) {
                if (!element) return;
                
                // Remove loading class
                element.classList.remove('counter-loading');
                element.classList.add('counter-animated');
                
                const startValue = parseInt(element.textContent) || 0;
                const duration = 1000; // 1 second
                const startTime = performance.now();
                
                const updateCounter = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Easing function for smooth animation
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const currentValue = Math.round(startValue + (targetValue - startValue) * easeOut);
                    
                    element.textContent = currentValue.toLocaleString();
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                };
                
                requestAnimationFrame(updateCounter);
            }
            
            updateVoteButton(hasVoted) {
                const mainVoteBtn = document.getElementById('mainVoteBtn');
                const heroVoteBtn = document.getElementById('heroVoteBtn');
                const voteButtonText = document.getElementById('voteButtonText');
                const voteStatus = document.getElementById('voteStatus');
                
                if (hasVoted) {
                    // User has already voted
                    mainVoteBtn.disabled = true;
                    mainVoteBtn.classList.add('voted');
                    voteButtonText.innerHTML = '<i class="fas fa-check-circle"></i> Vote Recorded';
                    voteStatus.style.display = 'block';
                    
                    heroVoteBtn.style.display = 'none';
                } else {
                    // User can vote
                    mainVoteBtn.disabled = false;
                    mainVoteBtn.classList.remove('voted');
                    mainVoteBtn.onclick = () => this.redirectToVote();
                    heroVoteBtn.onclick = () => this.redirectToVote();
                }
            }
            
            redirectToVote() {
                window.location.href = 'vote.php?election_id=1';
            }
            
            checkVoteStatus() {
                // Check if user just voted (from URL parameters)
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('success') === 'voted') {
                    this.showVoteSuccessNotification();
                    // Clean URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
            
            showVoteSuccessNotification() {
                const notification = document.createElement('div');
                notification.className = 'vote-success-notification';
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas fa-check-circle"></i>
                        <span>Your vote has been recorded successfully!</span>
                    </div>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 5000);
            }
            
            showOfflineData() {
                // Show fallback data when API fails
                this.animateCounter(this.counters.activeElections, 1);
                this.animateCounter(this.counters.candidates, 2);
                this.animateCounter(this.counters.votes, 0);
                this.animateCounter(this.counters.days, 16);
            }
            
            startAutoRefresh() {
                setInterval(() => {
                    this.fetchStats();
                }, this.refreshInterval);
            }
        }
        
        // Vote Status Manager
        class VoteManager {
            constructor() {
                this.voteCount = 0;
                this.hasVoted = false;
                this.init();
            }
            
            init() {
                this.loadVoteStatus();
            }
            
            async loadVoteStatus() {
                try {
                    const response = await fetch('api/check_vote_status.php');
                    const result = await response.json();
                    
                    if (result.success) {
                        this.hasVoted = result.has_voted;
                        this.voteCount = result.vote_count;
                        this.updateUI();
                    }
                } catch (error) {
                    console.error('Error checking vote status:', error);
                }
            }
            
            updateUI() {
                const voteButtons = document.querySelectorAll('.vote-button');
                voteButtons.forEach(btn => {
                    if (this.hasVoted) {
                        btn.disabled = true;
                        btn.classList.add('voted');
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Vote Recorded';
                    }
                });
            }
        }
        
        // Responsive Design Manager
        class ResponsiveManager {
            constructor() {
                this.init();
            }
            
            init() {
                this.handleResize();
                window.addEventListener('resize', () => this.handleResize());
            }
            
            handleResize() {
                const width = window.innerWidth;
                
                // Adjust navigation for mobile
                if (width <= 768) {
                    this.enableMobileNavigation();
                } else {
                    this.disableMobileNavigation();
                }
                
                // Adjust cards layout
                this.adjustCardsLayout(width);
            }
            
            enableMobileNavigation() {
                const navbar = document.querySelector('.navbar');
                navbar.classList.add('mobile-nav');
            }
            
            disableMobileNavigation() {
                const navbar = document.querySelector('.navbar');
                navbar.classList.remove('mobile-nav');
            }
            
            adjustCardsLayout(width) {
                const cards = document.querySelectorAll('.card');
                cards.forEach(card => {
                    if (width <= 576) {
                        card.classList.add('mobile-card');
                    } else {
                        card.classList.remove('mobile-card');
                    }
                });
            }
        }
        
        // Animation Manager
        class AnimationManager {
            constructor() {
                this.init();
            }
            
            init() {
                this.observeElements();
            }
            
            observeElements() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = 1;
                            entry.target.style.transform = 'translateY(0)';
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                
                // Observe all elements with fade-in classes
                document.querySelectorAll('.fade-in, .fade-in-delay-1, .fade-in-delay-2, .fade-in-delay-3, .fade-in-delay-4').forEach(element => {
                    observer.observe(element);
                });
            }
        }
        
        // Initialize all managers when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            new DashboardStats();
            new VoteManager();
            new ResponsiveManager();
            new AnimationManager();
        });
    </script>
    
    <style>
        /* Additional styles for notifications and mobile enhancements */
        .vote-success-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--secondary), var(--secondary-light));
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
            max-width: 300px;
        }
        
        .vote-success-notification.show {
            transform: translateX(0);
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .notification-content i {
            font-size: 1.2rem;
        }
        
        /* Mobile navigation enhancements */
        .navbar.mobile-nav {
            padding: 0.75rem 1rem;
        }
        
        .navbar.mobile-nav .logo h1 {
            font-size: 1.1rem;
        }
        
        .navbar.mobile-nav .nav-links {
            gap: 15px;
        }
        
        .navbar.mobile-nav .nav-link {
            font-size: 0.85rem;
        }
        
        /* Mobile card enhancements */
        .card.mobile-card {
            padding: 1.25rem;
        }
        
        .card.mobile-card .card-value {
            font-size: 2rem;
        }
        
        .card.mobile-card .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        
        /* Loading states */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Enhanced responsive grid for very small screens */
        @media (max-width: 480px) {
            .main-content {
                padding: 1rem 0.75rem;
            }
            
            .hero {
                padding: 2rem 1rem;
            }
            
            .dashboard-cards {
                gap: 0.75rem;
            }
            
            .card {
                padding: 1rem;
            }
            
            .election-header, .election-body {
                padding: 1rem;
            }
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .card {
                border: 2px solid var(--dark);
            }
            
            .vote-button {
                border: 2px solid white;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</body>
</html>
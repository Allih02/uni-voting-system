<?php
// dashboard.php - Admin Dashboard
require_once 'includes/session_check.php';
require_once 'config/database.php';

// Get admin details
$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

// You can add more dashboard data queries here
try {
    $conn = getDBConnection();
    
    // Example: Get total admins count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins WHERE status = 'active'");
    $total_admins = $stmt->fetch()['total'];
    
    // Add more dashboard statistics queries as needed
} catch(PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University Voting System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php echo getSessionScript($_SESSION['session_lifetime'], $_SESSION['login_time']); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #f43f5e;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            font-size: 1.75rem;
        }

        .sidebar-nav {
            padding: 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: var(--background);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: var(--primary);
            color: white;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
        }

        .top-bar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            width: 300px;
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            margin-left: 0.5rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.2s;
        }

        .user-info:hover {
            background: var(--background);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .page-content {
            padding: 2rem;
        }

        .welcome-section {
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card-icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .stat-card-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-card-icon.yellow {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-card-icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-card-change.positive {
            color: var(--success);
        }

        .stat-card-change.negative {
            color: var(--error);
        }

        .recent-activity {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            padding: 1.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .button-primary {
            background: var(--primary);
            color: white;
        }

        .button-primary:hover {
            background: var(--primary-dark);
        }

        .button-secondary {
            background: var(--background);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .button-secondary:hover {
            background: var(--border);
        }

        .activity-list {
            space-y: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .activity-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }

            .logo span,
            .nav-item span,
            .user-details {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }

            .search-bar {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 1rem;
            }

            .search-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-vote-yea"></i>
                    <span>VoteAdmin</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="elections.php" class="nav-item">
                    <i class="fas fa-poll"></i>
                    <span>Elections</span>
                </a>
                <a href="candidates.php" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Candidates</span>
                </a>
                <a href="voters.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Voters</span>
                </a>
                <a href="results.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Results</span>
                </a>
                <a href="reports.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-menu">
                    <div style="margin-right: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                        Session expires in: <span id="session-time-remaining" style="font-weight: 600; color: var(--primary);">15:00</span>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</h1>
                    <p class="welcome-subtitle">Here's what's happening with the voting system today.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Elections</div>
                            <div class="stat-card-icon blue">
                                <i class="fas fa-poll"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">12</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>2 this month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Active Voters</div>
                            <div class="stat-card-icon green">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">4,532</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>12% from last week</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Candidates</div>
                            <div class="stat-card-icon yellow">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">87</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>5 new candidates</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Admins</div>
                            <div class="stat-card-icon red">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $total_admins ?? '1'; ?></div>
                        <div class="stat-card-change">
                            <span>Active administrators</span>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <div class="section-header">
                        <h2 class="section-title">Recent Activity</h2>
                        <a href="activity.php" class="button button-secondary">View All</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">New Election Created</div>
                                <div class="activity-description">Student Council Elections 2024 has been scheduled for next month</div>
                            </div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">New Candidate Registered</div>
                                <div class="activity-description">John Doe registered as a candidate for President position</div>
                            </div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Voting Results Published</div>
                                <div class="activity-description">Results for Faculty Representative elections are now available</div>
                            </div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Security Update</div>
                                <div class="activity-description">System security protocols have been updated successfully</div>
                            </div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add any dashboard-specific JavaScript here
        document.querySelector('.user-info').addEventListener('click', function() {
            // Toggle user dropdown menu (if you want to add one)
        });
    </script>
</body>
</html>
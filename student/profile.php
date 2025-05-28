<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// In a real implementation, you would fetch this from your database
$user = [
    "id" => $_SESSION["user_id"],
    "fullname" => $_SESSION["fullname"],
    "registration_number" => $_SESSION["registration_number"],
    "email" => "student@university.edu", // Placeholder email
    "department" => "Computer Science", // Placeholder department
    "year_of_study" => "3rd Year", // Placeholder year
    "voting_history" => [
        [
            "election_id" => 1,
            "election_name" => "Student Council Elections 2024",
            "voted_on" => "2024-04-15",
            "status" => "Completed"
        ]
    ]
];

// Check if form was submitted for profile update
$success_message = null;
$error_message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    // In a real implementation, you would validate and update the database
    // This is just a simulation for the UI
    $success_message = "Profile updated successfully!";
    
    // Update session variables (in a real app, update DB first)
    $_SESSION["fullname"] = $_POST["fullname"];
    $user["fullname"] = $_POST["fullname"];
    $user["email"] = $_POST["email"];
    $user["department"] = $_POST["department"];
    $user["year_of_study"] = $_POST["year_of_study"];
}

// Check if password change form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    // In a real implementation, you would validate passwords and update the database
    // This is just a simulation for the UI
    if ($_POST["new_password"] !== $_POST["confirm_password"]) {
        $error_message = "New passwords do not match!";
    } else {
        $success_message = "Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - University Voting System</title>
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
        
        /* Navigation Bar */
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
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
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
        }
        
        .nav-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link.active {
            color: var(--primary);
            font-weight: 600;
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
        }
        
        .logout-button:hover {
            background-color: var(--light-gray);
            color: var(--primary-dark);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        .page-description {
            color: var(--gray);
            max-width: 700px;
        }
        
        /* Profile Content */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        /* Profile Sidebar */
        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .profile-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .profile-header {
            background: var(--gradient);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .profile-id {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .profile-body {
            padding: 1.5rem;
        }
        
        .profile-info-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .profile-info-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .profile-info-details h4 {
            font-size: 0.9rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 0.2rem;
        }
        
        .profile-info-details p {
            font-weight: 600;
        }
        
        .profile-navigation {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .profile-nav-title {
            padding: 1.2rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .profile-nav-links {
            list-style: none;
        }
        
        .profile-nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }
        
        .profile-nav-link:hover {
            background-color: rgba(99, 102, 241, 0.05);
            border-left-color: var(--primary-light);
        }
        
        .profile-nav-link.active {
            background-color: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary);
            font-weight: 600;
        }
        
        .profile-nav-link i {
            margin-right: 1rem;
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        /* Profile Main */
        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .profile-section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .profile-section-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .profile-section-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .profile-section-body {
            padding: 1.5rem;
        }
        
        /* Form Styles */
        .profile-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-control[readonly] {
            background-color: var(--light);
            cursor: not-allowed;
        }
        
        .form-submit {
            grid-column: span 2;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: var(--button-shadow);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(99, 102, 241, 0.4);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }
        
        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--secondary);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        /* Voting History */
        .voting-history-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .voting-history-table th, 
        .voting-history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .voting-history-table th {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .voting-status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .voting-status-completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--secondary);
        }
        
        .voting-status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        /* Password Section */
        .password-section .profile-form {
            grid-template-columns: 1fr;
            max-width: 500px;
        }
        
        .password-section .form-submit {
            grid-column: span 1;
        }
        
        /* Notifications Section */
        .notification-options {
            display: grid;
            gap: 1rem;
        }
        
        .notification-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .notification-option:hover {
            border-color: var(--primary-light);
        }
        
        .notification-checkbox {
            margin-right: 1rem;
            width: 20px;
            height: 20px;
        }
        
        .notification-details h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }
        
        .notification-details p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Tab Control */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Footer */
        .footer {
            background-color: var(--dark);
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
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .profile-card, .profile-navigation {
                flex: 1;
                min-width: 300px;
            }
            
            .profile-form {
                grid-template-columns: 1fr;
            }
            
            .form-submit {
                grid-column: span 1;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            
            .logo h1 {
                font-size: 1.1rem;
            }
            
            .main-content {
                padding: 2rem 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .profile-sidebar {
                flex-direction: column;
            }
            
            .profile-card, .profile-navigation {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .nav-links {
                display: none;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-avatar {
                width: 90px;
                height: 90px;
                font-size: 2.5rem;
            }
            
            .voting-history-table {
                display: block;
                overflow-x: auto;
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
                <a href="profile.php" class="nav-link active">Profile</a>
            </div>
            <a href="logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">My Profile</h1>
                <p class="page-description">Manage your account settings and view your voting history.</p>
            </div>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success_message; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error_message; ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2 class="profile-name"><?php echo $user["fullname"]; ?></h2>
                            <p class="profile-id"><?php echo $user["registration_number"]; ?></p>
                        </div>
                        <div class="profile-body">
                            <div class="profile-info-item">
                                <div class="profile-info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="profile-info-details">
                                    <h4>Email</h4>
                                    <p><?php echo $user["email"]; ?></p>
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="profile-info-details">
                                    <h4>Department</h4>
                                    <p><?php echo $user["department"]; ?></p>
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="profile-info-details">
                                    <h4>Year of Study</h4>
                                    <p><?php echo $user["year_of_study"]; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Navigation -->
                    <div class="profile-navigation">
                        <div class="profile-nav-title">
                            Settings
                        </div>
                        <ul class="profile-nav-links">
                            <li>
                                <a href="#" class="profile-nav-link active" data-tab="personal-info">
                                    <i class="fas fa-user"></i> Personal Information
                                </a>
                            </li>
                            <li>
                                <a href="#" class="profile-nav-link" data-tab="voting-history">
                                    <i class="fas fa-history"></i> Voting History
                                </a>
                            </li>
                            <li>
                                <a href="change_password.php" class="profile-nav-link" data-tab="password">
                                    <i class="fas fa-lock"></i> Change Password
                                </a>
                            </li>
                            <li>
                                <a href="#" class="profile-nav-link" data-tab="notifications">
                                    <i class="fas fa-bell"></i> Notifications
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Profile Main -->
                <div class="profile-main">
                    <!-- Personal Information Section -->
                    <div class="profile-section tab-content active" id="personal-info">
                        <div class="profile-section-header">
                            <h3 class="profile-section-title">Personal Information</h3>
                        </div>
                        <div class="profile-section-body">
                            <form action="" method="post" class="profile-form">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo $user["fullname"]; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="registration_number">Registration Number</label>
                                    <input type="text" id="registration_number" name="registration_number" class="form-control" value="<?php echo $user["registration_number"]; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $user["email"]; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department" name="department" class="form-control">
                                        <option value="Computer Science" <?php echo $user["department"] === "Computer Science" ? "selected" : ""; ?>>Computer Science</option>
                                        <option value="Electrical Engineering" <?php echo $user["department"] === "Electrical Engineering" ? "selected" : ""; ?>>Electrical Engineering</option>
                                        <option value="Business Administration" <?php echo $user["department"] === "Business Administration" ? "selected" : ""; ?>>Business Administration</option>
                                        <option value="Mathematics" <?php echo $user["department"] === "Mathematics" ? "selected" : ""; ?>>Mathematics</option>
                                        <option value="Physics" <?php echo $user["department"] === "Physics" ? "selected" : ""; ?>>Physics</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="year_of_study">Year of Study</label>
                                    <select id="year_of_study" name="year_of_study" class="form-control">
                                        <option value="1st Year" <?php echo $user["year_of_study"] === "1st Year" ? "selected" : ""; ?>>1st Year</option>
                                        <option value="2nd Year" <?php echo $user["year_of_study"] === "2nd Year" ? "selected" : ""; ?>>2nd Year</option>
                                        <option value="3rd Year" <?php echo $user["year_of_study"] === "3rd Year" ? "selected" : ""; ?>>3rd Year</option>
                                        <option value="4th Year" <?php echo $user["year_of_study"] === "4th Year" ? "selected" : ""; ?>>4th Year</option>
                                        <option value="5th Year" <?php echo $user["year_of_study"] === "5th Year" ? "selected" : ""; ?>>5th Year</option>
                                    </select>
                                </div>
                                <div class="form-submit">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Voting History Section -->
                    <div class="profile-section tab-content" id="voting-history">
                        <div class="profile-section-header">
                            <h3 class="profile-section-title">Voting History</h3>
                        </div>
                        <div class="profile-section-body">
                            <?php if (empty($user["voting_history"])): ?>
                                <p>You haven't participated in any elections yet.</p>
                            <?php else: ?>
                                <table class="voting-history-table">
                                    <thead>
                                        <tr>
                                            <th>Election</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user["voting_history"] as $history): ?>
                                            <tr>
                                                <td><?php echo $history["election_name"]; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($history["voted_on"])); ?></td>
                                                <td>
                                                    <span class="voting-status voting-status-<?php echo strtolower($history["status"]); ?>">
                                                        <?php echo $history["status"]; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Change Password Section -->
                    <div class="profile-section tab-content password-section" id="password">
                        <div class="profile-section-header">
                            <h3 class="profile-section-title">Change Password</h3>
                        </div>
                        <div class="profile-section-body">
                            <form action="" method="post" class="profile-form">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                <div class="form-submit">
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-lock"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notifications Section -->
                    <div class="profile-section tab-content" id="notifications">
                        <div class="profile-section-header">
                            <h3 class="profile-section-title">Notification Preferences</h3>
                        </div>
                        <div class="profile-section-body">
                            <form action="" method="post">
                                <div class="notification-options">
                                    <div class="notification-option">
                                        <input type="checkbox" id="notify_new_elections" name="notify_new_elections" class="notification-checkbox" checked>
                                        <div class="notification-details">
                                            <h4>New Elections</h4>
                                            <p>Get notified when new elections are announced</p>
                                        </div>
                                    </div>
                                    <div class="notification-option">
                                        <input type="checkbox" id="notify_reminders" name="notify_reminders" class="notification-checkbox" checked>
                                        <div class="notification-details">
                                            <h4>Voting Reminders</h4>
                                            <p>Receive reminders about approaching deadlines</p>
                                        </div>
                                    </div>
                                    <div class="notification-option">
                                        <input type="checkbox" id="notify_results" name="notify_results" class="notification-checkbox" checked>
                                        <div class="notification-details">
                                            <h4>Election Results</h4>
                                            <p>Get notified when results are published</p>
                                        </div>
                                    </div>
                                    <div class="notification-option">
                                        <input type="checkbox" id="notify_announcements" name="notify_announcements" class="notification-checkbox">
                                        <div class="notification-details">
                                            <h4>General Announcements</h4>
                                            <p>Receive updates about the voting system</p>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 1.5rem;">
                                    <button type="submit" name="update_notifications" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
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
                        <li><a href="mailto:support@university.edu"><i class="fas fa-envelope"></i> support@university.edu</a></li>
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
    
    <script>
        // Tab Switching
        const tabLinks = document.querySelectorAll('.profile-nav-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tab links
                tabLinks.forEach(item => item.classList.remove('active'));
                
                // Add active class to clicked tab link
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show the selected tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Password match validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (newPasswordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== newPasswordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            newPasswordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value !== '' && this.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }
        
        // Auto hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>
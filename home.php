<?php
session_start();
include 'includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    $user_id = $_SESSION['user_id'];
    $full_name = $_SESSION['full_name'];
    // Log the logout action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, 'logout', "User $full_name logged out"]);
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Rental Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
        }
        .header {
            background-color: #90EE90;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .logout-btn {
            background-color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .logout-btn:hover {
            background-color: #d3f9d3;
        }
        .apartment-btns {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        .apartment-btn {
            background-color: #90EE90;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        .apartment-btn:hover {
            background-color: #78d578;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
    </div>
    <div class="content">
        <form action="home.php?logout=1" method="POST" style="display:inline;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
        <div class="apartment-btns">
            <a href="apartment_dashboard.php?apartment_id=1" class="apartment-btn">Charis (Kasarani)</a>
            <a href="apartment_dashboard.php?apartment_id=2" class="apartment-btn">Peniel House (Ngumba)</a>
            <a href="apartment_dashboard.php?apartment_id=3" class="apartment-btn">Beniah Apartment (Umoja)</a>
            <a href="apartment_dashboard.php?apartment_id=4" class="apartment-btn">Eleazar Apartment (Umoja)</a>
        </div>
    </div>
</body>
</html>
<?php
include 'includes/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Management System</title>
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
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .image-container {
            margin: 10px;
            text-align: center;
        }
        .image-container img {
            width: 300px;
            height: auto;
            border: 2px solid #90EE90;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #90EE90;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Rental Management System</h1>
        <p>Manage your apartments and houses efficiently!</p>
    </div>
    <div class="content">
        <div class="image-container">
            <img src="images/apartment1.jpg" alt="Apartment Image 1">
            <p>Charis (Kasarani)</p>
        </div>
        <div class="image-container">
            <img src="images/apartment1.jpg" alt="Apartment Image 2">
            <p>Peniel House (Ngumba)</p>
        </div>
        <div class="image-container">
            <img src="images/apartment1.jpg" alt="Apartment Image 3">
            <p>Beniah Apartment (Umoja)</p>
        </div>
        <div class="image-container">
            <img src="images/apartment1.jpg" alt="Apartment Image 4">
            <p>Eleazar Apartment (Umoja)</p>
        </div>
    </div>
    <div class="login-link">
        <a href="login.php">Login</a>
    </div>
</body>
</html>
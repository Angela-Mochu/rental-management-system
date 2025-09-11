<?php
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($full_name) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->rowCount() > 0) {
            $error = "Phone number already registered.";
        } else {
            // Hash password and insert user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, phone, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$full_name, $phone, $password_hash]);

            // Log the registration action
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, 'register', "User $full_name registered with phone $phone"]);

            // Redirect to login page
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Rental Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-container {
            background-color: #90EE90;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: #ffffff;
        }
        .register-container h2 {
            margin-bottom: 20px;
        }
        .register-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        .register-container button {
            width: 100%;
            padding: 10px;
            background-color: #ffffff;
            border: none;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
        }
        .register-container button:hover {
            background-color: #d3f9d3;
        }
        .register-links a {
            color: #ffffff;
            text-decoration: none;
            margin-top: 10px;
            display: block;
        }
        .register-links a:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff0000;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form action="register.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="register-links">
            <a href="login.php">Already have an account?</a>
        </div>
    </div>
</body>
</html>
<?php
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($phone) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if phone exists
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Hash the new password and update
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = $user['id'];
            $full_name = $user['full_name'];
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);

            // Log the password reset action
            $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, 'password_reset', "Password reset for $full_name (phone: $phone)"]);

            $success = "Password successfully reset. Redirecting to login...";
            header("Refresh: 3; url=login.php"); // Redirect after 3 seconds
            exit;
        } else {
            $error = "No account found with that phone number.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Rental Management System</title>
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
        .forgot-password-container {
            background-color: #90EE90;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: #ffffff;
        }
        .forgot-password-container h2 {
            margin-bottom: 20px;
        }
        .forgot-password-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        .forgot-password-container button {
            width: 100%;
            padding: 10px;
            background-color: #ffffff;
            border: none;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
        }
        .forgot-password-container button:hover {
            background-color: #d3f9d3;
        }
        .forgot-password-container a {
            color: #ffffff;
            text-decoration: none;
            margin-top: 10px;
            display: block;
        }
        .forgot-password-container a:hover {
            text-decoration: underline;
        }
        .error, .success {
            margin-top: 10px;
        }
        .error {
            color: #ff0000;
        }
        .success {
            color: #00ff00;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <form action="forgot_password.php" method="POST">
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
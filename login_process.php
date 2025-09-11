<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($full_name) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT id, full_name, password_hash, role FROM users WHERE full_name = ?");
        $stmt->execute([$full_name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Log the login action
            $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], 'login', "User $full_name logged in"]);

            // Redirect to home page
            header("Location: home.php");
            exit;
        } else {
            $error = "Invalid full name or password.";
        }
    }
}

// If not a POST request or error, redirect back to login with error message
if (isset($error)) {
    session_start(); // Ensure session is started for flash message
    $_SESSION['error'] = $error;
    header("Location: login.php");
    exit;
}
?>
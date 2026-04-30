<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Connect to database
        $conn = new mysqli("localhost", "root", "", "media_archive");

        if ($conn->connect_error) {
            $error = "Database connection failed.";
        } else {
            // Query the user table
            $stmt = $conn->prepare("SELECT user_id, username, usertype, account_status, password_hash FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($user['account_status'] !== 'active') {
                    $error = "Your account is not active. Please contact an administrator.";
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Successful login — save to session
                    $_SESSION['user_id']  = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['usertype'] = $user['usertype'];

                    // Redirect based on role
                    if ($user['usertype'] === 'admin' || $user['usertype'] === 'moderator') {
                        header("Location: members.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "Invalid username or password.";
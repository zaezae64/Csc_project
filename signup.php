<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $hint     = trim($_POST['password_hint'] ?? '');

    if ($username === "" || $password === "" || $confirm === "") {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $conn = new mysqli("localhost", "root", "", "csc_project");

        if ($conn->connect_error) {
            $error = "Database connection failed.";
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "That username is already taken. Please choose another.";
            } else {
                $stmt->close();

                $hashed   = password_hash($password, PASSWORD_DEFAULT);
                $usertype = "standard";
                $status   = "active";
                $flair    = "";

                $stmt = $conn->prepare(
                    "INSERT INTO user (username, usertype, account_status, FlairTags, password_hash, hint_question)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssss", $username, $usertype, $status, $flair, $hashed, $hint);
                $stmt->execute();

                if ($stmt->affected_rows === 1) {
                    $success = "Account created! You can now log in.";
                } else {
                    $error = "Could not create account. Please try again.";
                }
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – Media Archive</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #16213e;
            border-radius: 12px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            border: 1px solid #0f3460;
        }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo h1 { color: #e94560; font-size: 26px; letter-spacing: 1px; }
        .logo p { color: #a8a8b3; font-size: 13px; margin-top: 4px; }
        label { display: block; color: #a8a8b3; font-size: 13px; margin-bottom: 6px; margin-top: 18px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 11px 14px; border-radius: 8px;
            border: 1px solid #0f3460; background: #1a1a2e;
            color: #eaeaea; font-size: 15px; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: #e94560; }
        .helper { color: #6b6b80; font-size: 12px; margin-top: 5px; }
        .alert { border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-top: 16px; }
        .alert.error { background: rgba(233,69,96,0.15); border: 1px solid #e94560; color: #e94560; }
        .alert.success { background: rgba(39,174,96,0.15); border: 1px solid #27ae60; color: #27ae60; }
        .btn { width: 100%; padding: 12px; margin-top: 26px; background: #e94560; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #c73652; }
        .links { margin-top: 20px; text-align: center; font-size: 13px; }
        .links a { color: #a8a8b3; text-decoration: none; transition: color 0.2s; }
        .links a:hover { color: #e94560; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <h1>Media Archive</h1>
        <p>Create an account</p>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($success === ""): ?>
    <form method="POST" action="signup.php">
        <label for="username">Username <span style="color:#e94560">*</span></label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Choose a username" maxlength="50" required>

        <label for="password">Password <span style="color:#e94560">*</span></label>
        <input type="password" id="password" name="password"
               placeholder="Min. 6 characters" required>

        <label for="confirm_password">Confirm Password <span style="color:#e94560">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Re-enter your password" required>

        <label for="password_hint">Password Hint <span style="color:#6b6b80">(optional)</span></label>
        <input type="text" id="password_hint" name="password_hint"
               value="<?= htmlspecialchars($_POST['password_hint'] ?? '') ?>"
               placeholder="e.g. My childhood pet's name" maxlength="100">
        <div class="helper">This helps you recover your account. Don't write your actual password.</div>

        <button type="submit" class="btn">Create Account</button>
    </form>
    <?php endif; ?>

    <div class="links" style="margin-top: 24px;">
        <a href="login.php">← Already have an account? Sign in</a>
    </div>
</div>
</body>
</html>
<?php
session_start();
require_once 'db_connect.php'; // Database connection file

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
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
                // Successful login
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['usertype'] = $user['usertype'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MediaArchive</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0d0f14;
            --surface:  #161a23;
            --border:   #2a2f3d;
            --accent:   #c8a96e;
            --accent2:  #e8c98a;
            --text:     #e8e4dc;
            --muted:    #8a8a9a;
            --error:    #e05555;
            --input-bg: #1c2030;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #c8a96e, transparent);
            top: -100px; left: -100px;
            animation: drift1 12s ease-in-out infinite alternate;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #4a6fa5, transparent);
            bottom: -80px; right: -80px;
            animation: drift2 10s ease-in-out infinite alternate;
        }
        @keyframes drift1 { from{transform:translate(0,0)} to{transform:translate(60px,40px)} }
        @keyframes drift2 { from{transform:translate(0,0)} to{transform:translate(-40px,-30px)} }

        .page-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 900px;
            width: 90%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6);
            animation: rise 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes rise {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* Left panel */
        .brand-panel {
            background: linear-gradient(145deg, #1a1f2e 0%, #0f1118 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                45deg, transparent, transparent 40px,
                rgba(200,169,110,0.03) 40px, rgba(200,169,110,0.03) 41px
            );
        }
        .logo-mark { display:flex; align-items:center; gap:12px; position:relative; }
        .logo-icon {
            width:40px; height:40px;
            border:2px solid var(--accent); border-radius:8px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .logo-text { font-family:'Playfair Display',serif; font-size:1.3rem; color:var(--accent); }
        .brand-copy { position:relative; }
        .brand-headline {
            font-family:'Playfair Display',serif;
            font-size:2rem; line-height:1.25;
            color:var(--text); margin-bottom:16px;
        }
        .brand-headline span { color:var(--accent); }
        .brand-sub { font-size:0.9rem; color:var(--muted); line-height:1.6; }
        .brand-dots { display:flex; gap:6px; }
        .brand-dots span { width:6px; height:6px; border-radius:50%; background:var(--border); }
        .brand-dots span:first-child { background:var(--accent); }

        /* Right panel */
        .form-panel {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 28px;
        }
        .form-header h1 {
            font-family:'Playfair Display',serif;
            font-size:1.75rem; color:var(--text); margin-bottom:6px;
        }
        .form-header p { font-size:0.875rem; color:var(--muted); }

        .error-box {
            background:rgba(224,85,85,0.12);
            border:1px solid rgba(224,85,85,0.3);
            border-radius:8px; padding:12px 16px;
            font-size:0.875rem; color:var(--error);
        }

        .field { display:flex; flex-direction:column; gap:8px; }
        .field label {
            font-size:0.8rem; font-weight:500;
            color:var(--muted); letter-spacing:0.08em; text-transform:uppercase;
        }
        .input-wrap { position:relative; }
        .input-wrap .icon {
            position:absolute; left:14px; top:50%;
            transform:translateY(-50%); color:var(--muted);
            font-size:1rem; pointer-events:none;
        }
        .field input {
            width:100%; padding:12px 14px 12px 42px;
            background:var(--input-bg); border:1px solid var(--border);
            border-radius:8px; color:var(--text);
            font-family:'DM Sans',sans-serif; font-size:0.95rem;
            outline:none; transition:border-color 0.2s, box-shadow 0.2s;
        }
        .field input:focus {
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(200,169,110,0.1);
        }
        .field input::placeholder { color:var(--muted); }
        .show-pw {
            position:absolute; right:14px; top:50%;
            transform:translateY(-50%);
            background:none; border:none; color:var(--muted);
            cursor:pointer; font-size:0.85rem;
            transition:color 0.2s;
        }
        .show-pw:hover { color:var(--text); }
        .form-links { display:flex; justify-content:flex-end; }
        .form-links a { font-size:0.8rem; color:var(--accent); text-decoration:none; transition:color 0.2s; }
        .form-links a:hover { color:var(--accent2); }
        .btn-login {
            width:100%; padding:13px;
            background:var(--accent); color:#0d0f14;
            font-family:'DM Sans',sans-serif; font-weight:500;
            font-size:1rem; border:none; border-radius:8px;
            cursor:pointer; transition:background 0.2s, transform 0.1s;
        }
        .btn-login:hover { background:var(--accent2); }
        .btn-login:active { transform:scale(0.99); }
        .fields { display:flex; flex-direction:column; gap:20px; }

        @media (max-width:640px) {
            .page-wrapper { grid-template-columns:1fr; }
            .brand-panel { display:none; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="brand-panel">
        <div class="logo-mark">
            <div class="logo-icon">🎬</div>
            <span class="logo-text">MediaArchive</span>
        </div>
        <div class="brand-copy">
            <h2 class="brand-headline">Your media,<br><span>preserved.</span></h2>
            <p class="brand-sub">A curated archive for creators and collectors. Sign in to access your submissions and explore the collection.</p>
        </div>
        <div class="brand-dots"><span></span><span></span><span></span></div>
    </div>

    <div class="form-panel">
        <div class="form-header">
            <h1>Welcome back</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="fields">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <span class="icon">👤</span>
                        <input type="text" id="username" name="username"
                               placeholder="Enter your username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               autocomplete="username" required>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="icon">🔒</span>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               autocomplete="current-password" required>
                        <button type="button" class="show-pw" onclick="togglePw()">Show</button>
                    </div>
                </div>

                <div class="form-links">
                    <a href="forgot_password.php">Forgot password or username?</a>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </div>
        </form>
    </div>
</div>
<script>
function togglePw() {
    const inp = document.getElementById('password');
    const btn = event.target;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
}
</script>
</body>
</html>

<?php
session_start();

$message = "";
$messageType = "";
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

// Step 1: User submits email or username to look up account
if ($_SERVER["REQUEST_METHOD"] == "POST" && $step === 1) {
    $lookup = trim($_POST['lookup']);

    if (empty($lookup)) {
        $message = "Please enter your username or email address.";
        $messageType = "error";
        $step = 1;
    } else {
        // Check by username or email (assuming email column exists; adjust if not)
        $stmt = $conn->prepare("SELECT user_id, username FROM user WHERE username = ?");
        $stmt->bind_param("s", $lookup);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Store user_id in session to verify on step 2
            $_SESSION['reset_user_id'] = $user['user_id'];
            $_SESSION['reset_username'] = $user['username'];
            $step = 2;
            $message = "Account found! Please answer your password hint to continue.";
            $messageType = "success";
        } else {
            $message = "No account found with that username. Please try again.";
            $messageType = "error";
            $step = 1;
        }
        $stmt->close();
    }
}

// Step 2: Verify password hint answer, then redirect to password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && $step === 2) {
    $hint_answer = trim($_POST['hint_answer']);
    $user_id = $_SESSION['reset_user_id'] ?? null;

    if (!$user_id) {
        $message = "Session expired. Please start over.";
        $messageType = "error";
        $step = 1;
    } elseif (empty($hint_answer)) {
        $message = "Please enter your hint answer.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("SELECT hint_answer FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && strtolower($row['hint_answer']) === strtolower($hint_answer)) {
            $_SESSION['can_reset_password'] = true;
            header("Location: password_hint.php");
            exit();
        } else {
            $message = "Incorrect answer. Please try again.";
            $messageType = "error";
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
    <title>Forgot Password/Username — MediaArchive</title>
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
            --success:  #5ea87a;
            --input-bg: #1c2030;
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'DM Sans',sans-serif;
            background:var(--bg); color:var(--text);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center;
        }
        body::before {
            content:''; position:fixed;
            width:500px; height:500px;
            border-radius:50%; filter:blur(80px); opacity:0.1;
            background:radial-gradient(circle, #c8a96e, transparent);
            top:-100px; right:-100px; pointer-events:none;
            animation:drift 14s ease-in-out infinite alternate;
        }
        @keyframes drift { from{transform:translate(0,0)} to{transform:translate(-50px,40px)} }

        .card {
            max-width: 480px; width: 90%;
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
        .card-header {
            background: linear-gradient(135deg, #1a1f2e, #0f1118);
            border-bottom: 1px solid var(--border);
            padding: 32px 36px 28px;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content:'';
            position:absolute; inset:0;
            background: repeating-linear-gradient(
                45deg, transparent, transparent 30px,
                rgba(200,169,110,0.03) 30px, rgba(200,169,110,0.03) 31px
            );
        }
        .back-link {
            display:inline-flex; align-items:center; gap:6px;
            font-size:0.8rem; color:var(--muted);
            text-decoration:none; margin-bottom:20px;
            transition:color 0.2s; position:relative;
        }
        .back-link:hover { color:var(--accent); }
        .logo-mark { display:flex; align-items:center; gap:10px; margin-bottom:20px; position:relative; }
        .logo-icon {
            width:34px; height:34px;
            border:2px solid var(--accent); border-radius:6px;
            display:flex; align-items:center; justify-content:center; font-size:15px;
        }
        .logo-text { font-family:'Playfair Display',serif; font-size:1.1rem; color:var(--accent); }
        .card-header h1 {
            font-family:'Playfair Display',serif;
            font-size:1.5rem; color:var(--text);
            margin-bottom:6px; position:relative;
        }
        .card-header p { font-size:0.875rem; color:var(--muted); position:relative; }

        /* Step indicator */
        .steps {
            display:flex; align-items:center; gap:0;
            margin-top:24px; position:relative;
        }
        .step-item {
            display:flex; align-items:center; gap:8px; flex:1;
        }
        .step-circle {
            width:28px; height:28px; border-radius:50%;
            border:2px solid var(--border);
            display:flex; align-items:center; justify-content:center;
            font-size:0.75rem; font-weight:500;
            color:var(--muted); flex-shrink:0;
            transition:all 0.3s;
        }
        .step-circle.active { border-color:var(--accent); color:var(--accent); background:rgba(200,169,110,0.1); }
        .step-circle.done   { border-color:var(--success); color:var(--surface); background:var(--success); }
        .step-label { font-size:0.75rem; color:var(--muted); }
        .step-label.active { color:var(--text); }
        .step-connector { flex:1; height:1px; background:var(--border); max-width:40px; margin:0 8px; }

        .card-body { padding:32px 36px; display:flex; flex-direction:column; gap:24px; }

        .msg-box {
            border-radius:8px; padding:12px 16px;
            font-size:0.875rem; display:flex; align-items:center; gap:8px;
        }
        .msg-box.error   { background:rgba(224,85,85,0.12); border:1px solid rgba(224,85,85,0.3); color:var(--error); }
        .msg-box.success { background:rgba(94,168,122,0.12); border:1px solid rgba(94,168,122,0.3); color:var(--success); }

        .field { display:flex; flex-direction:column; gap:8px; }
        .field label {
            font-size:0.8rem; font-weight:500;
            color:var(--muted); letter-spacing:0.08em; text-transform:uppercase;
        }
        .input-wrap { position:relative; }
        .input-wrap .icon {
            position:absolute; left:14px; top:50%;
            transform:translateY(-50%); font-size:1rem;
            color:var(--muted); pointer-events:none;
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

        .btn-primary {
            width:100%; padding:13px;
            background:var(--accent); color:#0d0f14;
            font-family:'DM Sans',sans-serif; font-weight:500;
            font-size:1rem; border:none; border-radius:8px;
            cursor:pointer; transition:background 0.2s, transform 0.1s;
        }
        .btn-primary:hover { background:var(--accent2); }
        .btn-primary:active { transform:scale(0.99); }

        .help-text { font-size:0.8rem; color:var(--muted); line-height:1.5; }
        .help-text strong { color:var(--text); }

        .username-display {
            background:var(--input-bg); border:1px solid var(--border);
            border-radius:8px; padding:12px 16px;
            font-size:0.95rem; color:var(--accent);
            font-weight:500; letter-spacing:0.02em;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <a href="login.php" class="back-link">← Back to Login</a>
        <div class="logo-mark">
            <div class="logo-icon">🎬</div>
            <span class="logo-text">MediaArchive</span>
        </div>
        <h1>Account Recovery</h1>
        <p>Recover your username or reset your password.</p>

        <!-- Step indicator -->
        <div class="steps">
            <div class="step-item">
                <div class="step-circle <?= $step === 1 ? 'active' : 'done' ?>">
                    <?= $step > 1 ? '✓' : '1' ?>
                </div>
                <span class="step-label <?= $step === 1 ? 'active' : '' ?>">Find Account</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item">
                <div class="step-circle <?= $step === 2 ? 'active' : '' ?>">2</div>
                <span class="step-label <?= $step === 2 ? 'active' : '' ?>">Verify</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item">
                <div class="step-circle">3</div>
                <span class="step-label">Reset</span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php if (!empty($message)): ?>
        <div class="msg-box <?= $messageType ?>">
            <?= $messageType === 'error' ? '⚠' : '✓' ?> <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <!-- Step 1: Find account -->
        <form method="POST" action="forgot_password.php" novalidate>
            <input type="hidden" name="step" value="1">
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div class="field">
                    <label for="lookup">Username</label>
                    <div class="input-wrap">
                        <span class="icon">👤</span>
                        <input type="text" id="lookup" name="lookup"
                               placeholder="Enter your username"
                               value="<?= htmlspecialchars($_POST['lookup'] ?? '') ?>"
                               autocomplete="username">
                    </div>
                </div>
                <p class="help-text">Enter your <strong>username</strong> to look up your account. You'll then verify your identity using your password hint.</p>
                <button type="submit" class="btn-primary">Find My Account</button>
            </div>
        </form>

        <?php elseif ($step === 2): ?>
        <!-- Step 2: Verify with hint -->
        <p class="help-text">Account found: <strong style="color:var(--accent)"><?= htmlspecialchars($_SESSION['reset_username'] ?? '') ?></strong><br>
        Answer your password hint question to continue.</p>

        <form method="POST" action="forgot_password.php" novalidate>
            <input type="hidden" name="step" value="2">
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div class="field">
                    <label for="hint_answer">Password Hint Answer</label>
                    <div class="input-wrap">
                        <span class="icon">💡</span>
                        <input type="text" id="hint_answer" name="hint_answer"
                               placeholder="Your hint answer"
                               autocomplete="off">
                    </div>
                </div>
                <p class="help-text">Don't remember your hint? <a href="login.php" style="color:var(--accent)">Contact an admin</a> for help.</p>
                <button type="submit" class="btn-primary">Verify & Continue</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

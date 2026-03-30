<?php
session_start();

// This page serves two purposes:
//   1. Logged-in users can VIEW/SET their password hint
//   2. Users coming from forgot_password.php (after hint verification) can RESET their password

$is_reset_mode = isset($_SESSION['can_reset_password']) && $_SESSION['can_reset_password'] === true;
$is_logged_in  = isset($_SESSION['user_id']);

// If neither, redirect away
if (!$is_reset_mode && !$is_logged_in) {
    header("Location: login.php");
    exit();
}

$message = "";
$messageType = "";
$user_id = $is_reset_mode ? ($_SESSION['reset_user_id'] ?? null) : $_SESSION['user_id'];

// Fetch current hint question for display
$hint_question = "";
if ($user_id) {
    $stmt = $conn->prepare("SELECT hint_question, hint_answer FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hint_question = $row['hint_question'] ?? "";
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // ---- Reset password (from forgot_password flow) ----
    if ($action === 'reset_password' && $is_reset_mode) {
        $new_password  = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($new_password) || empty($confirm_password)) {
            $message = "Please fill in both password fields.";
            $messageType = "error";
        } elseif (strlen($new_password) < 8) {
            $message = "Password must be at least 8 characters.";
            $messageType = "error";
        } elseif ($new_password !== $confirm_password) {
            $message = "Passwords do not match.";
            $messageType = "error";
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET password_hash = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hash, $user_id);
            if ($stmt->execute()) {
                // Clear reset session flags
                unset($_SESSION['can_reset_password'], $_SESSION['reset_user_id'], $_SESSION['reset_username']);
                $message = "Password reset successfully! You can now log in.";
                $messageType = "success";
                $is_reset_mode = false;
            } else {
                $message = "An error occurred. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        }
    }

    // ---- Save/update hint question & answer (logged-in users) ----
    if ($action === 'save_hint' && $is_logged_in) {
        $new_hint_q = trim($_POST['hint_question']);
        $new_hint_a = trim($_POST['hint_answer']);

        if (empty($new_hint_q) || empty($new_hint_a)) {
            $message = "Please provide both a hint question and answer.";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("UPDATE user SET hint_question = ?, hint_answer = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $new_hint_q, $new_hint_a, $user_id);
            if ($stmt->execute()) {
                $hint_question = $new_hint_q;
                $message = "Password hint updated successfully!";
                $messageType = "success";
            } else {
                $message = "An error occurred. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_reset_mode ? 'Reset Password' : 'Password Hint' ?> — MediaArchive</title>
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
            width:500px; height:500px; border-radius:50%;
            filter:blur(80px); opacity:0.1;
            background:radial-gradient(circle, #5ea87a, transparent);
            bottom:-100px; left:-100px; pointer-events:none;
            animation:drift 14s ease-in-out infinite alternate;
        }
        @keyframes drift { from{transform:translate(0,0)} to{transform:translate(40px,-40px)} }

        .card {
            max-width:520px; width:90%;
            background:var(--surface); border:1px solid var(--border);
            border-radius:16px; overflow:hidden;
            box-shadow:0 32px 80px rgba(0,0,0,0.6);
            animation:rise 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes rise {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .card-header {
            background:linear-gradient(135deg,#1a1f2e,#0f1118);
            border-bottom:1px solid var(--border);
            padding:28px 36px;
            position:relative; overflow:hidden;
        }
        .card-header::before {
            content:''; position:absolute; inset:0;
            background:repeating-linear-gradient(
                45deg, transparent, transparent 30px,
                rgba(200,169,110,0.03) 30px, rgba(200,169,110,0.03) 31px
            );
        }
        .top-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; position:relative; }
        .logo-mark { display:flex; align-items:center; gap:10px; }
        .logo-icon {
            width:34px; height:34px; border:2px solid var(--accent);
            border-radius:6px; display:flex; align-items:center;
            justify-content:center; font-size:15px;
        }
        .logo-text { font-family:'Playfair Display',serif; font-size:1.1rem; color:var(--accent); }
        .back-link {
            font-size:0.8rem; color:var(--muted); text-decoration:none; transition:color 0.2s;
        }
        .back-link:hover { color:var(--accent); }
        .card-header h1 {
            font-family:'Playfair Display',serif;
            font-size:1.5rem; color:var(--text); margin-bottom:6px; position:relative;
        }
        .card-header p { font-size:0.875rem; color:var(--muted); position:relative; }

        /* Mode badge */
        .mode-badge {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(200,169,110,0.1); border:1px solid rgba(200,169,110,0.2);
            border-radius:20px; padding:4px 12px;
            font-size:0.75rem; color:var(--accent); margin-bottom:16px; position:relative;
        }

        .card-body { padding:32px 36px; display:flex; flex-direction:column; gap:24px; }

        .msg-box {
            border-radius:8px; padding:12px 16px;
            font-size:0.875rem; display:flex; align-items:center; gap:8px;
        }
        .msg-box.error   { background:rgba(224,85,85,0.12); border:1px solid rgba(224,85,85,0.3); color:var(--error); }
        .msg-box.success { background:rgba(94,168,122,0.12); border:1px solid rgba(94,168,122,0.3); color:var(--success); }

        .field { display:flex; flex-direction:column; gap:8px; }
        .field label {
            font-size:0.8rem; font-weight:500; color:var(--muted);
            letter-spacing:0.08em; text-transform:uppercase;
        }
        .field select, .field input, .field textarea {
            width:100%; padding:12px 14px;
            background:var(--input-bg); border:1px solid var(--border);
            border-radius:8px; color:var(--text);
            font-family:'DM Sans',sans-serif; font-size:0.95rem;
            outline:none; transition:border-color 0.2s, box-shadow 0.2s;
            resize:none;
        }
        .field select:focus, .field input:focus, .field textarea:focus {
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(200,169,110,0.1);
        }
        .field select option { background:var(--surface); }
        .field input::placeholder, .field textarea::placeholder { color:var(--muted); }

        /* Password strength */
        .strength-bar {
            height:4px; border-radius:2px; background:var(--border);
            margin-top:8px; overflow:hidden; transition:all 0.3s;
        }
        .strength-fill {
            height:100%; border-radius:2px; width:0%;
            transition:width 0.3s, background 0.3s;
        }
        .strength-label { font-size:0.75rem; color:var(--muted); margin-top:4px; }

        /* Current hint display box */
        .hint-display {
            background:var(--input-bg); border:1px solid var(--border);
            border-radius:8px; padding:14px 16px;
            font-size:0.9rem; color:var(--muted); line-height:1.5;
        }
        .hint-display strong { color:var(--accent); display:block; font-size:0.75rem;
            letter-spacing:0.08em; text-transform:uppercase; margin-bottom:6px; }

        .section-divider {
            border:none; border-top:1px solid var(--border); margin:4px 0;
        }

        .btn-primary {
            width:100%; padding:13px;
            background:var(--accent); color:#0d0f14;
            font-family:'DM Sans',sans-serif; font-weight:500;
            font-size:1rem; border:none; border-radius:8px;
            cursor:pointer; transition:background 0.2s, transform 0.1s;
        }
        .btn-primary:hover { background:var(--accent2); }
        .btn-primary:active { transform:scale(0.99); }

        .btn-secondary {
            width:100%; padding:12px;
            background:transparent; color:var(--muted);
            font-family:'DM Sans',sans-serif; font-weight:400;
            font-size:0.9rem; border:1px solid var(--border); border-radius:8px;
            cursor:pointer; transition:all 0.2s; text-align:center; text-decoration:none;
            display:block;
        }
        .btn-secondary:hover { border-color:var(--accent); color:var(--accent); }

        .fields { display:flex; flex-direction:column; gap:20px; }
        .help-text { font-size:0.8rem; color:var(--muted); line-height:1.5; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div class="top-row">
            <div class="logo-mark">
                <div class="logo-icon">🎬</div>
                <span class="logo-text">MediaArchive</span>
            </div>
            <?php if ($is_logged_in && !$is_reset_mode): ?>
            <a href="index.php" class="back-link">← Back</a>
            <?php else: ?>
            <a href="forgot_password.php" class="back-link">← Back</a>
            <?php endif; ?>
        </div>

        <?php if ($is_reset_mode): ?>
        <div class="mode-badge">🔑 Password Reset</div>
        <h1>Set New Password</h1>
        <p>Choose a strong new password for your account.</p>
        <?php else: ?>
        <div class="mode-badge">💡 Security Settings</div>
        <h1>Password Hint</h1>
        <p>Set a hint question to help recover your account.</p>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if (!empty($message)): ?>
        <div class="msg-box <?= $messageType ?>">
            <?= $messageType === 'error' ? '⚠' : '✓' ?> <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($is_reset_mode): ?>
        <!-- ===== RESET PASSWORD FORM ===== -->
        <?php if ($messageType !== 'success'): ?>
        <form method="POST" action="password_hint.php" novalidate>
            <input type="hidden" name="action" value="reset_password">
            <div class="fields">
                <div class="field">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="At least 8 characters"
                           autocomplete="new-password"
                           oninput="checkStrength(this.value)">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat your new password"
                           autocomplete="new-password">
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </div>
        </form>
        <?php else: ?>
        <a href="login.php" class="btn-primary" style="display:block;text-align:center;text-decoration:none;padding:13px;border-radius:8px;background:var(--accent);color:#0d0f14;font-weight:500;">Go to Login</a>
        <?php endif; ?>

        <?php else: ?>
        <!-- ===== HINT MANAGEMENT (logged-in) ===== -->

        <?php if (!empty($hint_question)): ?>
        <div>
            <div class="hint-display">
                <strong>Current Hint Question</strong>
                <?= htmlspecialchars($hint_question) ?>
            </div>
        </div>
        <hr class="section-divider">
        <?php endif; ?>

        <form method="POST" action="password_hint.php" novalidate>
            <input type="hidden" name="action" value="save_hint">
            <div class="fields">
                <div class="field">
                    <label for="hint_question">Hint Question</label>
                    <select id="hint_question" name="hint_question">
                        <option value="" disabled <?= empty($hint_question) ? 'selected' : '' ?>>Select a question…</option>
                        <?php
                        $questions = [
                            "What was the name of your first pet?",
                            "What city were you born in?",
                            "What is your mother's maiden name?",
                            "What was the make of your first car?",
                            "What was the name of your elementary school?",
                            "What is the name of your favorite childhood friend?"
                        ];
                        foreach ($questions as $q): ?>
                        <option value="<?= htmlspecialchars($q) ?>"
                            <?= $hint_question === $q ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="hint_answer">Your Answer</label>
                    <input type="text" id="hint_answer" name="hint_answer"
                           placeholder="Your answer (case-insensitive)"
                           autocomplete="off">
                </div>
                <p class="help-text">This answer will be used to verify your identity if you forget your password. Keep it memorable but not obvious.</p>
                <button type="submit" class="btn-primary">
                    <?= empty($hint_question) ? 'Save Hint' : 'Update Hint' ?>
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function checkStrength(pw) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    if (!fill) return;
    let score = 0;
    if (pw.length >= 8)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { w:'0%',   color:'transparent', text:'' },
        { w:'25%',  color:'#e05555',     text:'Weak' },
        { w:'50%',  color:'#e09055',     text:'Fair' },
        { w:'75%',  color:'#c8a96e',     text:'Good' },
        { w:'100%', color:'#5ea87a',     text:'Strong' }
    ];
    const lvl = levels[score] || levels[0];
    fill.style.width = lvl.w;
    fill.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
}
</script>
</body>
</html>

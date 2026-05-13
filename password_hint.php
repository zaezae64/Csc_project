<?php
session_start();
require_once 'DBConnect.php';

// This page serves two purposes:
//   1. Logged-in users can VIEW/SET their password hint
//   2. Users coming from forgot_password.php can RESET their password

$is_reset_mode = isset($_SESSION['can_reset_password']) && $_SESSION['can_reset_password'] === true;
$is_logged_in  = isset($_SESSION['user_id']);

if (!$is_reset_mode && !$is_logged_in) {
    header("Location: login.php");
    exit();
}

$message = "";
$messageType = "";
$user_id = $is_reset_mode ? ($_SESSION['reset_user_id'] ?? null) : $_SESSION['user_id'];

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset_password' && $is_reset_mode) {
        $new_password = $_POST['new_password'];
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
    <title><?php echo $is_reset_mode ? 'Reset Password' : 'Password Hint'; ?> - Media Archive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<main class="container mt-5" style="max-width: 650px;">

    <div class="oc-callout w3-card-4">

        <h1 class="h3 mb-3 text-center">
            <?php echo $is_reset_mode ? 'Reset Password' : 'Password Hint'; ?>
        </h1>

        <p class="text-center mb-4">
            <?php if ($is_reset_mode): ?>
                Choose a new password for your account.
            <?php else: ?>
                Set or update your account recovery hint.
            <?php endif; ?>
        </p>

        <?php if (!empty($message)): ?>
            <?php if ($messageType === "success"): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($is_reset_mode): ?>

            <?php if ($messageType !== 'success'): ?>
                <form method="POST" action="password_hint.php">
                    <input type="hidden" name="action" value="reset_password">

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            New Password
                        </label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-control"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            oninput="checkStrength(this.value)"
                            required
                        >

                        <div class="progress mt-2" style="height: 6px;">
                            <div
                                id="strengthFill"
                                class="progress-bar"
                                role="progressbar"
                                style="width: 0%;"
                            ></div>
                        </div>

                        <div id="strengthLabel" class="form-text text-light"></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Repeat your new password"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn btn-warning w-100">
                    Go to Login
                </a>
            <?php endif; ?>

        <?php else: ?>

            <?php if (!empty($hint_question)): ?>
                <div class="alert alert-secondary">
                    <strong>Current Hint Question:</strong><br>
                    <?php echo htmlspecialchars($hint_question); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="password_hint.php">
                <input type="hidden" name="action" value="save_hint">

                <div class="mb-3">
                    <label for="hint_question" class="form-label">
                        Hint Question
                    </label>

                    <select id="hint_question" name="hint_question" class="form-select" required>
                        <option value="" disabled <?php echo empty($hint_question) ? 'selected' : ''; ?>>
                            Select a question...
                        </option>

                        <?php
                        $questions = [
                            "What was the name of your first pet?",
                            "What city were you born in?",
                            "What is your mother's maiden name?",
                            "What was the make of your first car?",
                            "What was the name of your elementary school?",
                            "What is the name of your favorite childhood friend?"
                        ];

                        foreach ($questions as $q):
                        ?>
                            <option
                                value="<?php echo htmlspecialchars($q); ?>"
                                <?php echo $hint_question === $q ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($q); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="hint_answer" class="form-label">
                        Your Answer
                    </label>

                    <input
                        type="text"
                        id="hint_answer"
                        name="hint_answer"
                        class="form-control"
                        placeholder="Your answer"
                        autocomplete="off"
                        required
                    >

                    <div class="form-text text-light">
                        This answer will be used to verify your identity if you forget your password.
                    </div>
                </div>

                <button type="submit" class="btn btn-warning w-100">
                    <?php echo empty($hint_question) ? 'Save Hint' : 'Update Hint'; ?>
                </button>
            </form>

        <?php endif; ?>

        <hr>

        <p class="text-center mb-0">
            <?php if ($is_logged_in && !$is_reset_mode): ?>
                <a href="profile.php" class="text-light">Back to Profile</a>
            <?php else: ?>
                <a href="forgot_password.php" class="text-light">Back to Account Recovery</a>
            <?php endif; ?>
        </p>

    </div>

</main>

<script>
function checkStrength(pw) {
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    if (!fill || !label) return;

    let score = 0;

    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { width: '0%', text: '' },
        { width: '25%', text: 'Weak' },
        { width: '50%', text: 'Fair' },
        { width: '75%', text: 'Good' },
        { width: '100%', text: 'Strong' }
    ];

    const level = levels[score] || levels[0];

    fill.style.width = level.width;
    label.textContent = level.text;
}
</script>

</body>
</html>
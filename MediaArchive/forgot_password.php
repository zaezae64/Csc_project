<?php
session_start();
require_once 'DBConnect.php';

$message = "";
$messageType = "";
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $step === 1) {
    $lookup = trim($_POST['lookup'] ?? '');

    if (empty($lookup)) {
        $message = "Please enter your username.";
        $messageType = "error";
        $step = 1;
    } else {
        $stmt = $conn->prepare("SELECT user_id, username FROM user WHERE username = ?");
        $stmt->bind_param("s", $lookup);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $_SESSION['reset_user_id'] = $user['user_id'];
            $_SESSION['reset_username'] = $user['username'];

            $step = 2;
            $message = "Account found. Please answer your password hint to continue.";
            $messageType = "success";
        } else {
            $message = "No account found with that username.";
            $messageType = "error";
            $step = 1;
        }

        $stmt->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $step === 2) {
    $hint_answer = trim($_POST['hint_answer'] ?? '');
    $user_id = $_SESSION['reset_user_id'] ?? null;

    if (!$user_id) {
        $message = "Session expired. Please start over.";
        $messageType = "error";
        $step = 1;
    } elseif (empty($hint_answer)) {
        $message = "Please enter your hint answer.";
        $messageType = "error";
        $step = 2;
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
            $step = 2;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Recovery - Media Archive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<main class="container mt-5" style="max-width: 620px;">

    <div class="oc-callout w3-card-4">

        <h1 class="h3 mb-3 text-center">Account Recovery</h1>

        <p class="text-center mb-4">
            Recover your account using your username and password hint.
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

        <div class="mb-4">
            <div class="d-flex justify-content-between text-center">
                <div class="flex-fill">
                    <span class="badge <?php echo $step === 1 ? 'bg-warning text-dark' : 'bg-success'; ?>">
                        1
                    </span>
                    <div class="small mt-1">Find Account</div>
                </div>

                <div class="flex-fill">
                    <span class="badge <?php echo $step === 2 ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                        2
                    </span>
                    <div class="small mt-1">Verify Hint</div>
                </div>

                <div class="flex-fill">
                    <span class="badge bg-secondary">
                        3
                    </span>
                    <div class="small mt-1">Reset Password</div>
                </div>
            </div>
        </div>

        <?php if ($step === 1): ?>

            <form method="POST" action="forgot_password.php">
                <input type="hidden" name="step" value="1">

                <div class="mb-3">
                    <label for="lookup" class="form-label">Username</label>
                    <input
                        type="text"
                        id="lookup"
                        name="lookup"
                        class="form-control"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['lookup'] ?? ''); ?>"
                        required
                    >
                </div>

                <p class="text-light small">
                    Enter your username to find your account. Then you will answer your password hint.
                </p>

                <button type="submit" class="btn btn-warning w-100">
                    Find My Account
                </button>
            </form>

        <?php elseif ($step === 2): ?>

            <p>
                Account found:
                <strong><?php echo htmlspecialchars($_SESSION['reset_username'] ?? ''); ?></strong>
            </p>

            <form method="POST" action="forgot_password.php">
                <input type="hidden" name="step" value="2">

                <div class="mb-3">
                    <label for="hint_answer" class="form-label">Password Hint Answer</label>
                    <input
                        type="text"
                        id="hint_answer"
                        name="hint_answer"
                        class="form-control"
                        placeholder="Enter your hint answer"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-warning w-100">
                    Verify & Continue
                </button>
            </form>

        <?php endif; ?>

        <hr>

        <p class="text-center mb-0">
            <a href="login.php" class="text-light">
                Back to Login
            </a>
        </p>

    </div>

</main>

</body>
</html>
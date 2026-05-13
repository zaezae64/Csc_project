<?php
session_start();
require_once 'DBConnect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $hint_question = trim($_POST['hint_question'] ?? '');
    $hint_answer   = trim($_POST['hint_answer'] ?? '');

    if ($username === "" || $password === "" || $confirm === "" || $hint_question === "" || $hint_answer === "") {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
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
                "INSERT INTO user (username, usertype, account_status, FlairTags, password_hash, hint_question, hint_answer)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssss",
                $username,
                $usertype,
                $status,
                $flair,
                $hashed,
                $hint_question,
                $hint_answer
            );

            $stmt->execute();

            if ($stmt->affected_rows === 1) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Could not create account. Please try again.";
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Media Archive</title>
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

        <h1 class="h3 mb-3 text-center">Create Account</h1>

        <p class="text-center mb-4">
            Join the Media Archive community.
        </p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($success === ""): ?>

        <form method="POST" action="signup.php">

            <div class="mb-3">
                <label for="username" class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    placeholder="Choose a username"
                    maxlength="50"
                    required
                >
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Minimum 6 characters"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Re-enter password"
                        required
                    >
                </div>

            </div>

            <hr class="my-4">

            <h2 class="h5 mb-3">Account Recovery</h2>

            <div class="mb-3">
                <label for="hint_question" class="form-label">
                    Password Hint Question
                </label>

                <input
                    type="text"
                    id="hint_question"
                    name="hint_question"
                    class="form-control"
                    value="<?php echo htmlspecialchars($_POST['hint_question'] ?? ''); ?>"
                    placeholder="Example: What was the name of your first pet?"
                    maxlength="255"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="hint_answer" class="form-label">
                    Password Hint Answer
                </label>

                <input
                    type="text"
                    id="hint_answer"
                    name="hint_answer"
                    class="form-control"
                    value="<?php echo htmlspecialchars($_POST['hint_answer'] ?? ''); ?>"
                    placeholder="Enter your answer"
                    maxlength="255"
                    required
                >

                <div class="form-text text-light">
                    This helps recover your account. Do not enter your actual password.
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 mt-3">
                Create Account
            </button>

        </form>

        <?php endif; ?>

        <hr>

        <p class="text-center mb-0">
            Already have an account?
            <a href="login.php" class="text-light">
                Sign in here
            </a>
        </p>

    </div>

</main>

</body>
</html>
<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_required.php");
    exit();
}

$usertype = $_SESSION['usertype'] ?? '';

if ($usertype !== 'admin' && $usertype !== 'moderator') {
    header("Location: login_required.php");
    exit();
}

$updateMsg = "";
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user_id']) && $usertype === 'admin') {
    $updateId = (int) $_POST['update_user_id'];
    $newStatus = $_POST['new_status'] ?? '';

    $allowed = ['active', 'banned', 'moderator', 'contributor'];

    if (in_array($newStatus, $allowed, true)) {
        if ($newStatus === 'moderator' || $newStatus === 'contributor') {
            $stmt = $conn->prepare("
                UPDATE user
                SET usertype = ?, account_status = 'active'
                WHERE user_id = ?
            ");
            $stmt->bind_param("si", $newStatus, $updateId);
        } else {
            $newType = 'standard';

            $stmt = $conn->prepare("
                UPDATE user
                SET account_status = ?, usertype = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssi", $newStatus, $newType, $updateId);
        }

        if ($stmt->execute()) {
            $updateMsg = "User updated successfully.";
        } else {
            $errorMsg = "Failed to update user.";
        }

        $stmt->close();
    }
}

$result = $conn->query("
    SELECT user_id, username, usertype, account_status
    FROM user
    ORDER BY user_id ASC
");

function getBadge($user) {
    if ($user['account_status'] === 'banned') {
        return ['Banned', 'danger'];
    }

    if ($user['usertype'] === 'admin') {
        return ['Admin', 'warning'];
    }

    if ($user['usertype'] === 'moderator') {
        return ['Moderator', 'info'];
    }

    if ($user['usertype'] === 'contributor') {
        return ['Contributor', 'success'];
    }

    return ['Registered', 'secondary'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Members - Media Archive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container mt-4">

    <div class="oc-callout w3-card-4">
        <h1 class="h3 mb-3">View Members</h1>
        <p class="mb-0">
            View registered users. Admins can ban users or promote them to moderator/contributor.
        </p>
    </div>

    <?php if ($updateMsg !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($updateMsg); ?></div>
    <?php endif; ?>

    <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <?php if ($usertype === 'moderator'): ?>
        <div class="alert alert-info">
            Moderators have view-only access. Only admins can change user permissions.
        </div>
    <?php endif; ?>

    <div class="oc-callout w3-card-4">
        <input
            type="text"
            class="form-control mb-3"
            id="searchInput"
            placeholder="Search by username..."
        >

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered align-middle" id="memberTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Profile</th>
                            <?php if ($usertype === 'admin'): ?>
                                <th>Change Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <?php [$label, $badgeClass] = getBadge($user); ?>

                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>

                                <td><?php echo htmlspecialchars($user['username']); ?></td>

                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars($badgeClass); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </span>
                                </td>

                                <td>
                                    <a class="btn btn-outline-light btn-sm"
                                       href="view_profile.php?id=<?php echo urlencode($user['user_id']); ?>">
                                        View Profile
                                    </a>
                                </td>

                                <?php if ($usertype === 'admin'): ?>
                                    <td>
                                        <form method="POST" action="members.php" class="d-flex gap-2 flex-wrap">
                                            <input type="hidden" name="update_user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">

                                            <select name="new_status" class="form-select form-select-sm" style="max-width: 180px;">
                                                <option value="active"
                                                    <?php echo ($user['account_status'] === 'active' && $user['usertype'] === 'standard') ? 'selected' : ''; ?>>
                                                    Registered
                                                </option>

                                                <option value="banned"
                                                    <?php echo ($user['account_status'] === 'banned') ? 'selected' : ''; ?>>
                                                    Banned
                                                </option>

                                                <option value="moderator"
                                                    <?php echo ($user['usertype'] === 'moderator') ? 'selected' : ''; ?>>
                                                    Moderator
                                                </option>

                                                <option value="contributor"
                                                    <?php echo ($user['usertype'] === 'contributor') ? 'selected' : ''; ?>>
                                                    Contributor
                                                </option>
                                            </select>

                                            <button type="submit" class="btn btn-warning btn-sm">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mb-0">No members found.</p>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#memberTable tbody tr');

    rows.forEach(row => {
        const username = row.cells[1].textContent.toLowerCase();
        row.style.display = username.includes(query) ? '' : 'none';
    });
});
</script>

</body>
</html>
<?php
session_start();
 
// Only admins and moderators can view this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$usertype = $_SESSION['usertype'] ?? '';
if ($usertype !== 'admin' && $usertype !== 'moderator') {
    header("Location: permission_denied.php");
    exit();
}
 
$conn = new mysqli("localhost", "root", "", "csc_project");
if ($conn->connect_error) {
    die("Database connection failed.");
}
 
// Handle status update if admin submits form
$updateMsg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user_id']) && $usertype === 'admin') {
    $updateId     = (int)$_POST['update_user_id'];
    $updateStatus = $_POST['new_status'] ?? '';
    $allowed      = ['active', 'banned', 'moderator', 'contributor'];
 
    if (in_array($updateStatus, $allowed)) {
        if ($updateStatus === 'moderator' || $updateStatus === 'contributor') {
            $stmt = $conn->prepare("UPDATE user SET usertype = ?, account_status = 'active' WHERE user_id = ?");
            $stmt->bind_param("si", $updateStatus, $updateId);
        } else {
            $newType = $updateStatus === 'banned' ? 'standard' : 'standard';
            $stmt = $conn->prepare("UPDATE user SET account_status = ?, usertype = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $updateStatus, $newType, $updateId);
        }
        $stmt->execute();
        $updateMsg = "User updated successfully.";
        $stmt->close();
    }
}
 
// Fetch all users
$result = $conn->query("SELECT user_id, username, usertype, account_status FROM user ORDER BY user_id ASC");
$users  = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
 
// Helper: determine display badge
function getBadge($user) {
    if ($user['account_status'] === 'banned') return ['banned', '#e94560'];
    if ($user['usertype'] === 'admin')        return ['admin', '#9b59b6'];
    if ($user['usertype'] === 'moderator')    return ['moderator', '#e67e22'];
    if ($user['usertype'] === 'contributor')  return ['contributor', '#27ae60'];
    return ['registered', '#3498db'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member List – Media Archive</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            padding: 30px 20px;
            color: #eaeaea;
        }
 
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
 
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
 
        .header h1 {
            color: #e94560;
            font-size: 24px;
        }
 
        .header span {
            color: #a8a8b3;
            font-size: 13px;
        }
 
        .alert {
            background: rgba(39,174,96,0.15);
            border: 1px solid #27ae60;
            color: #27ae60;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
 
        .search-bar {
            width: 100%;
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid #0f3460;
            background: #16213e;
            color: #eaeaea;
            font-size: 14px;
            margin-bottom: 20px;
        }
 
        .search-bar:focus {
            outline: none;
            border-color: #e94560;
        }
 
        table {
            width: 100%;
            border-collapse: collapse;
            background: #16213e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
 
        thead {
            background: #0f3460;
        }
 
        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            color: #a8a8b3;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
 
        tbody tr {
            border-bottom: 1px solid #0f3460;
            transition: background 0.15s;
        }
 
        tbody tr:last-child {
            border-bottom: none;
        }
 
        tbody tr:hover {
            background: rgba(15,52,96,0.4);
        }
 
        td {
            padding: 13px 16px;
            font-size: 14px;
        }
 
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            letter-spacing: 0.3px;
        }
 
        .actions select {
            padding: 5px 8px;
            border-radius: 6px;
            border: 1px solid #0f3460;
            background: #1a1a2e;
            color: #eaeaea;
            font-size: 13px;
            cursor: pointer;
        }
 
        .actions button {
            padding: 5px 12px;
            border-radius: 6px;
            border: none;
            background: #e94560;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            margin-left: 6px;
        }
 
        .actions button:hover {
            background: #c73652;
        }
 
        .total {
            margin-top: 14px;
            color: #6b6b80;
            font-size: 13px;
            text-align: right;
        }
 
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #a8a8b3;
            text-decoration: none;
            font-size: 13px;
        }
 
        .back-link:hover { color: #e94560; }
 
        .mod-note {
            background: rgba(15,52,96,0.4);
            border: 1px solid #0f3460;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #a8a8b3;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
 
<div class="container">
 
    <a href="dashboard.php" class="back-link">← Back to Home</a>
 
    <div class="header">
        <h1>Member List</h1>
        <span>Logged in as: <strong style="color:#e94560"><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= htmlspecialchars($usertype) ?>)</span>
    </div>
 
    <?php if ($updateMsg): ?>
        <div class="alert"><?= htmlspecialchars($updateMsg) ?></div>
    <?php endif; ?>
 
    <?php if ($usertype === 'moderator'): ?>
        <div class="mod-note">👁 You have view-only access. Only admins can change user statuses.</div>
    <?php endif; ?>
 
    <input type="text" class="search-bar" id="searchInput" placeholder="Search by username...">
 
    <table id="memberTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Status</th>
                <?php if ($usertype === 'admin'): ?>
                <th>Change Status</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user):
                [$label, $color] = getBadge($user);
            ?>
            <tr>
                <td style="color:#6b6b80"><?= $user['user_id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td>
                    <span class="badge" style="background: <?= $color ?>22; color: <?= $color ?>; border: 1px solid <?= $color ?>;">
                        <?= $label ?>
                    </span>
                </td>
                <?php if ($usertype === 'admin'): ?>
                <td class="actions">
                    <form method="POST" action="members.php" style="display:inline">
                        <input type="hidden" name="update_user_id" value="<?= $user['user_id'] ?>">
                        <select name="new_status">
                            <option value="active"      <?= $user['account_status'] === 'active' && $user['usertype'] === 'standard' ? 'selected' : '' ?>>Registered</option>
                            <option value="banned"      <?= $user['account_status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
                            <option value="moderator"   <?= $user['usertype'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                            <option value="contributor" <?= $user['usertype'] === 'contributor' ? 'selected' : '' ?>>Contributor</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
 
    <div class="total">Total members: <?= count($users) ?></div>
 
</div>
 
<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const query = this.value.toLowerCase();
        const rows  = document.querySelectorAll('#memberTable tbody tr');
        rows.forEach(row => {
            const username = row.cells[1].textContent.toLowerCase();
            row.style.display = username.includes(query) ? '' : 'none';
        });
    });
</script>
 
</body>
</html>
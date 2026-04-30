<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login_required.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_sub_id'])) {
    $subId = (int) $_POST['approve_sub_id'];

    $stmt = $conn->prepare("
        UPDATE submission
        SET AcceptStatus = 'Accepted'
        WHERE Sub_ID = ?
    ");
    $stmt->bind_param("i", $subId);
    $stmt->execute();
    $stmt->close();
}

/* Load submissions */
$result = $conn->query("
    SELECT Sub_ID, MediaName, User_ID, AcceptStatus
    FROM submission
    ORDER BY Sub_ID DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($profileUser['username']); ?> Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">

    <h2>Admin Dashboard</h2>

    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Media</th>
                <th>User</th>
                <th>Status</th>
                <th>Approve</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['Sub_ID']; ?></td>
                <td><?php echo htmlspecialchars($row['MediaName']); ?></td>

                <td>
                    <a href="view_profile.php?id=<?php echo $row['User_ID']; ?>">
                        User #<?php echo $row['User_ID']; ?>
                    </a>
                </td>

                <td><?php echo $row['AcceptStatus']; ?></td>

                <td>
                    <?php if ($row['AcceptStatus'] !== 'Accepted'): ?>
                        <form method="post">
                            <input type="hidden" name="approve_sub_id" value="<?php echo $row['Sub_ID']; ?>">
                            <button class="btn btn-warning btn-sm">Approve</button>
                        </form>
                    <?php else: ?>
                        ✔
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</div>

</body>
</html>
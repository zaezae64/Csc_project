<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$profileUserId = (int) $_GET['id'];

/* Load user */
$stmt = $conn->prepare("
    SELECT user_id, username, usertype, profile_image, bio
    FROM user
    WHERE user_id = ?
");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$result = $stmt->get_result();
$profileUser = $result->fetch_assoc();
$stmt->close();

if (!$profileUser) {
    die("Profile not found.");
}

/* FIX: Accepted submission count */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS accepted_count
    FROM submission
    WHERE User_ID = ? AND AcceptStatus = 'Accepted'
");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$acceptedCount = $row['accepted_count'] ?? 0;
$stmt->close();

/* Load submissions */
$stmt = $conn->prepare("
    SELECT Sub_ID, MediaName, AcceptStatus
    FROM submission
    WHERE User_ID = ?
    ORDER BY Sub_ID DESC
");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$submissionResult = $stmt->get_result();
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

    <div class="oc-callout w3-card-4">
        <div class="row">

            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($profileUser['username']); ?></h2>

                <div class="d-flex gap-4">

                    <div>
                        <?php if (!empty($profileUser['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($profileUser['profile_image']); ?>" class="profile-page-image">
                        <?php endif; ?>
                    </div>

                    <div>
                        <h5>Bio</h5>
                        <p><?php echo nl2br(htmlspecialchars($profileUser['bio'] ?? '')); ?></p>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <p><strong>Account Type:</strong> <?php echo $profileUser['usertype']; ?></p>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profileUser['user_id']): ?>
                    <a href="profile.php" class="btn btn-warning btn-sm">Edit Your Profile</a>
                <?php endif; ?>

                <div class="accepted-submissions-box mt-3">
                    <span class="accepted-star">★</span>
                    <span>
                        Accepted Submissions: <?php echo $acceptedCount; ?>
                    </span>
                </div>
            </div>

        </div>
    </div>

    <div class="oc-callout w3-card-4">
        <h3>Media Submissions</h3>

        <?php if ($submissionResult->num_rows > 0): ?>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Media</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $submissionResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['MediaName']); ?></td>
                            <td><?php echo $row['AcceptStatus']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No submissions yet.</p>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
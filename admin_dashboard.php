<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login_required.php");
    exit();
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_sub_id'])) {
    $subId = (int) $_POST['approve_sub_id'];

    if ($subId > 0) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                SELECT Sub_ID, MediaName, SubmitDesc, AcceptStatus
                FROM submission
                WHERE Sub_ID = ?
            ");
            $stmt->bind_param("i", $subId);
            $stmt->execute();
            $result = $stmt->get_result();
            $submission = $result->fetch_assoc();
            $stmt->close();

            if (!$submission) {
                throw new Exception("Submission not found.");
            }

            if ($submission['AcceptStatus'] !== 'Pending') {
                throw new Exception("This submission has already been processed.");
            }

            $stmt = $conn->prepare("
                UPDATE submission
                SET AcceptStatus = 'Accepted'
                WHERE Sub_ID = ?
            ");
            $stmt->bind_param("i", $subId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update submission status.");
            }
            $stmt->close();

            $publishedDesc = trim($submission['SubmitDesc'] ?? '');
            if ($publishedDesc === '') {
                $publishedDesc = "Description pending moderator update.";
            }

            $stmt = $conn->prepare("
                INSERT INTO media_page (Sub_ID, MediaDesc)
                VALUES (?, ?)
            ");
            $stmt->bind_param("is", $subId, $publishedDesc);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create media page.");
            }

            $pageId = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("
                SELECT Tag_ID
                FROM submission_tags
                WHERE Sub_ID = ?
            ");
            $stmt->bind_param("i", $subId);
            $stmt->execute();
            $tagResult = $stmt->get_result();
            $tagIds = [];

            while ($row = $tagResult->fetch_assoc()) {
                $tagIds[] = (int) $row['Tag_ID'];
            }
            $stmt->close();

            if (!empty($tagIds)) {
                $insertTagStmt = $conn->prepare("
                    INSERT INTO media_page_tags (Page_ID, Tag_ID)
                    VALUES (?, ?)
                ");

                foreach ($tagIds as $tagId) {
                    $insertTagStmt->bind_param("ii", $pageId, $tagId);
                    if (!$insertTagStmt->execute()) {
                        throw new Exception("Failed to copy tags to media page.");
                    }
                }

                $insertTagStmt->close();
            }

            $conn->commit();
            $successMessage = "Submission approved and published successfully.";

        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = "Approval failed: " . $e->getMessage();
        }
    }
}

$publishedResult = $conn->query("
    SELECT media_page.Page_ID, submission.MediaName, media_page.MediaDesc
    FROM media_page
    INNER JOIN submission ON media_page.Sub_ID = submission.Sub_ID
    ORDER BY submission.MediaName ASC
");

$pendingResult = $conn->query("
    SELECT Sub_ID, MediaName, User_ID, AcceptStatus
    FROM submission
    WHERE AcceptStatus = 'Pending'
    ORDER BY Sub_ID ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Media Archive</title>

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
        <h1 class="h3 mb-3">Moderation Dashboard</h1>
        <p class="mb-0">View published media and review pending submissions.</p>
    </div>

    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3">Published Media</h2>

        <?php if ($publishedResult && $publishedResult->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Page ID</th>
                            <th>Media Name</th>
                            <th>Description</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $publishedResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Page_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['MediaName']); ?></td>
                                <td><?php echo htmlspecialchars($row['MediaDesc']); ?></td>
                                <td>
                                    <a href="media_details.php?id=<?php echo urlencode($row['Page_ID']); ?>" class="btn btn-warning btn-sm">
                                        Open / Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mb-0">No published media found.</p>
        <?php endif; ?>
    </div>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3">Pending Submissions</h2>

        <?php if ($pendingResult && $pendingResult->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Submission ID</th>
                            <th>Media Name</th>
                            <th>User ID</th>
                            <th>Status</th>
                            <th>Approve</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pendingResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Sub_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['MediaName']); ?></td>
                                <td><?php echo htmlspecialchars($row['User_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['AcceptStatus']); ?></td>
                                <td>
                                    <form method="post" action="admin_dashboard.php" class="m-0">
                                        <input type="hidden" name="approve_sub_id" value="<?php echo htmlspecialchars($row['Sub_ID']); ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            Approve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mb-0">There are no pending submissions right now.</p>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
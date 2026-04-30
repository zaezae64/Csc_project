<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_required.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_bio'])) {
        $bio = trim($_POST['bio'] ?? '');

        $stmt = $conn->prepare("UPDATE user SET bio = ? WHERE user_id = ?");
        $stmt->bind_param("si", $bio, $userId);

        if ($stmt->execute()) {
            $message = "Profile bio updated successfully.";
        } else {
            $error = "Failed to update bio.";
        }

        $stmt->close();
    }

    if (isset($_POST['upload_profile_image'])) {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['profile_image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes, true)) {
                $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
            } else {
                $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;

                $uploadDir = __DIR__ . '/uploads/profile/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $targetPath = $uploadDir . $newFileName;
                $dbPath = 'uploads/profile/' . $newFileName;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                    $stmt = $conn->prepare("UPDATE user SET profile_image = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $dbPath, $userId);

                    if ($stmt->execute()) {
                        $message = "Profile picture uploaded successfully.";
                    } else {
                        $error = "Failed to save profile picture.";
                    }

                    $stmt->close();
                } else {
                    $error = "Failed to upload profile picture.";
                }
            }
        } else {
            $error = "Please choose an image to upload.";
        }
    }

    if (isset($_POST['remove_profile_image'])) {
        $stmt = $conn->prepare("SELECT profile_image FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $oldImage = $result->fetch_assoc()['profile_image'] ?? null;
        $stmt->close();

        if ($oldImage) {
            $fullPath = __DIR__ . '/' . $oldImage;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $stmt = $conn->prepare("UPDATE user SET profile_image = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $message = "Profile picture removed.";
        } else {
            $error = "Failed to remove profile picture.";
        }

        $stmt->close();
    }
}

$stmt = $conn->prepare("
    SELECT user_id, username, usertype, profile_image, bio
    FROM user
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$stmt = $conn->prepare("
    SELECT Sub_ID, MediaName, AcceptStatus
    FROM submission
    WHERE User_ID = ?
    ORDER BY Sub_ID DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$submissionResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Page - Media Archive</title>
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
        <h1 class="h3 mb-3">Profile Page</h1>
        <p class="mb-0">Manage your profile picture, bio, and submitted media.</p>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3"><?php echo htmlspecialchars($user['username']); ?></h2>

        <p><strong>Account Type:</strong> <?php echo htmlspecialchars($user['usertype']); ?></p>

        <p>
            <a class="btn btn-outline-light btn-sm" href="view_profile.php?id=<?php echo urlencode($user['user_id']); ?>">
                View Public Profile
            </a>
        </p>

        <div class="mb-4 text-center">
            <?php if (!empty($user['profile_image'])): ?>
                <img
                    src="<?php echo htmlspecialchars($user['profile_image']); ?>"
                    alt="Profile Picture"
                    class="img-fluid rounded media-detail-image mb-3"
                >

                <form method="post">
                    <button type="submit" name="remove_profile_image" value="1" class="btn btn-danger btn-sm">
                        Remove Profile Picture
                    </button>
                </form>
            <?php else: ?>
                <p class="text-light">No profile picture uploaded.</p>
            <?php endif; ?>
        </div>

        <form method="post" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="profile_image" class="form-label">Upload Profile Picture</label>
                <input
                    type="file"
                    class="form-control"
                    id="profile_image"
                    name="profile_image"
                    accept=".jpg,.jpeg,.png,.gif,.webp"
                >
            </div>

            <button type="submit" name="upload_profile_image" value="1" class="btn btn-outline-light">
                Upload Picture
            </button>
        </form>

        <hr>

        <form method="post">
            <div class="mb-3">
                <label for="bio" class="form-label">Bio / Description</label>
                <textarea
                    class="form-control"
                    id="bio"
                    name="bio"
                    rows="5"
                    placeholder="Write something about yourself..."
                ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <button type="submit" name="save_bio" value="1" class="btn btn-warning">
                Save Bio
            </button>
        </form>
    </div>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3">Your Media Submissions</h2>

        <?php if ($submissionResult && $submissionResult->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Submission ID</th>
                            <th>Media Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $submissionResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Sub_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['MediaName']); ?></td>
                                <td><?php echo htmlspecialchars($row['AcceptStatus']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mb-0">You have not submitted any media yet.</p>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
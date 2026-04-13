<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid media page ID.");
}

$pageId = (int) $_GET['id'];
$isAdmin = isset($_SESSION['user_id']) && isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'admin';

$message = "";
$error = "";

/*
    ADMIN TEXT UPDATE HANDLER
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $isAdmin &&
    !isset($_POST['upload_image']) &&
    !isset($_POST['remove_image_id'])
) {
    $newMediaName = trim($_POST['media_name'] ?? '');
    $newMediaDesc = trim($_POST['media_desc'] ?? '');

    if ($newMediaName === '' || $newMediaDesc === '') {
        $error = "Media name and description cannot be empty.";
    } else {
        $stmt = $conn->prepare("
            SELECT media_page.Page_ID, media_page.Sub_ID
            FROM media_page
            WHERE media_page.Page_ID = ?
        ");
        $stmt->bind_param("i", $pageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $pageRow = $result->fetch_assoc();
        $stmt->close();

        if (!$pageRow) {
            $error = "Media page not found.";
        } else {
            $subId = (int) $pageRow['Sub_ID'];

            $stmt1 = $conn->prepare("UPDATE submission SET MediaName = ? WHERE Sub_ID = ?");
            $stmt1->bind_param("si", $newMediaName, $subId);

            $stmt2 = $conn->prepare("UPDATE media_page SET MediaDesc = ? WHERE Page_ID = ?");
            $stmt2->bind_param("si", $newMediaDesc, $pageId);

            $ok1 = $stmt1->execute();
            $ok2 = $stmt2->execute();

            $stmt1->close();
            $stmt2->close();

            if ($ok1 && $ok2) {
                $message = "Media page updated successfully.";
            } else {
                $error = "Failed to update media page.";
            }
        }
    }
}

/*
    ADMIN IMAGE UPLOAD HANDLER
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && isset($_POST['upload_image'])) {
    if (isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['media_image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes, true)) {
            $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } else {
            $ext = pathinfo($_FILES['media_image']['name'], PATHINFO_EXTENSION);
            $newFileName = 'media_' . $pageId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $targetPath = __DIR__ . '/uploads/media/' . $newFileName;
            $dbPath = 'uploads/media/' . $newFileName;

            if (move_uploaded_file($_FILES['media_image']['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("
                    INSERT INTO media_images (Page_ID, ImagePath)
                    VALUES (?, ?)
                ");
                $stmt->bind_param("is", $pageId, $dbPath);

                if ($stmt->execute()) {
                    $message = "Image uploaded successfully.";
                } else {
                    $error = "Failed to save image record.";
                }

                $stmt->close();
            } else {
                $error = "Failed to upload image.";
            }
        }
    } else {
        $error = "Please choose an image to upload.";
    }
}

/*
    ADMIN IMAGE REMOVE HANDLER
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && isset($_POST['remove_image_id'])) {
    $imageId = (int) $_POST['remove_image_id'];

    $stmt = $conn->prepare("
        SELECT Image_ID, ImagePath
        FROM media_images
        WHERE Image_ID = ? AND Page_ID = ?
    ");
    $stmt->bind_param("ii", $imageId, $pageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $imageRow = $result->fetch_assoc();
    $stmt->close();

    if ($imageRow) {
        $stmt = $conn->prepare("
            DELETE FROM media_images
            WHERE Image_ID = ? AND Page_ID = ?
        ");
        $stmt->bind_param("ii", $imageId, $pageId);

        if ($stmt->execute()) {
            $fullPath = __DIR__ . '/' . $imageRow['ImagePath'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $message = "Image removed successfully.";
        } else {
            $error = "Failed to remove image.";
        }

        $stmt->close();
    } else {
        $error = "Image not found.";
    }
}

/*
    LOAD PAGE DATA
*/
$stmt = $conn->prepare("
    SELECT media_page.Page_ID, media_page.Sub_ID, media_page.MediaDesc, submission.MediaName
    FROM media_page
    INNER JOIN submission ON media_page.Sub_ID = submission.Sub_ID
    WHERE media_page.Page_ID = ?
");
$stmt->bind_param("i", $pageId);
$stmt->execute();
$result = $stmt->get_result();
$media = $result->fetch_assoc();
$stmt->close();

if (!$media) {
    die("Media page not found.");
}

/*
    LOAD IMAGES
*/
$images = [];
$stmt = $conn->prepare("
    SELECT Image_ID, ImagePath
    FROM media_images
    WHERE Page_ID = ?
    ORDER BY Image_ID ASC
");
$stmt->bind_param("i", $pageId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Details - Media Archive</title>

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
        <h1 class="h3 mb-3"><?php echo htmlspecialchars($media['MediaName']); ?></h1>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($images)): ?>
            <div class="mb-4">
                <h2 class="h5 mb-3">Images</h2>
                <div class="row">
                    <?php foreach ($images as $img): ?>
                        <div class="col-md-4 mb-4 text-center">
                            <img
                                src="<?php echo htmlspecialchars($img['ImagePath']); ?>"
                                alt="Media Image"
                                class="img-fluid rounded media-detail-image"
                            >

                            <?php if ($isAdmin): ?>
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="remove_image_id" value="<?php echo htmlspecialchars($img['Image_ID']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Remove Image
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <p><strong>Page ID:</strong> <?php echo htmlspecialchars($media['Page_ID']); ?></p>
        <p><strong>Description:</strong><br>
            <?php echo nl2br(htmlspecialchars($media['MediaDesc'])); ?>
        </p>

        <a href="media.php" class="btn btn-warning">Back to Media</a>
    </div>

    <?php if ($isAdmin): ?>
        <div class="oc-callout w3-card-4 mt-4">
            <h2 class="h4 mb-3">Admin Edit Panel</h2>

            <form method="post" action="media_details.php?id=<?php echo urlencode($media['Page_ID']); ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="media_name" class="form-label">Media Name</label>
                    <input
                        type="text"
                        class="form-control"
                        id="media_name"
                        name="media_name"
                        value="<?php echo htmlspecialchars($media['MediaName']); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="media_desc" class="form-label">Description</label>
                    <textarea
                        class="form-control"
                        id="media_desc"
                        name="media_desc"
                        rows="6"
                        required
                    ><?php echo htmlspecialchars($media['MediaDesc']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-warning me-2">Save Changes</button>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="media_image" class="form-label">Upload Image</label>
                    <input
                        type="file"
                        class="form-control"
                        id="media_image"
                        name="media_image"
                        accept=".jpg,.jpeg,.png,.gif,.webp"
                    >
                </div>

                <button type="submit" name="upload_image" value="1" class="btn btn-outline-light">
                    Upload Image
                </button>
            </form>
        </div>
    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
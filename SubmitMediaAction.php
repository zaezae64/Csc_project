<?php
session_start();
require_once 'DBConnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_required.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: submit_media.php");
    exit;
}

$userId      = $_SESSION['user_id'];
$mediaName   = trim($_POST['media_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$tagsInput   = trim($_POST['tags'] ?? '');

if ($mediaName === '') {
    die("Error: Media name is required.");
}

if ($tagsInput === '') {
    die("Error: At least one tag is required.");
}

$acceptStatus = 'Pending';

// Insert submission with original submitted description preserved
$sql = "INSERT INTO submission (MediaName, SubmitDesc, User_ID, AcceptStatus) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssis", $mediaName, $description, $userId, $acceptStatus);

if (!$stmt->execute()) {
    die("Submission insert failed: " . $stmt->error);
}

$subId = $stmt->insert_id;
$stmt->close();

// Process tags
$rawTags = explode(',', $tagsInput);
$cleanTags = [];

foreach ($rawTags as $tag) {
    $tag = trim($tag);
    if ($tag !== '') {
        $cleanTags[] = $tag;
    }
}

$cleanTags = array_unique($cleanTags);

foreach ($cleanTags as $tagName) {
    $checkSql = "SELECT Tag_ID FROM tags WHERE TagName = ?";
    $checkStmt = $conn->prepare($checkSql);

    if (!$checkStmt) {
        die("Prepare failed: " . $conn->error);
    }

    $checkStmt->bind_param("s", $tagName);
    $checkStmt->execute();
    $checkStmt->bind_result($tagId);

    if ($checkStmt->fetch()) {
        $checkStmt->close();
    } else {
        $checkStmt->close();

        $insertTagSql = "INSERT INTO tags (TagName) VALUES (?)";
        $insertTagStmt = $conn->prepare($insertTagSql);

        if (!$insertTagStmt) {
            die("Prepare failed: " . $conn->error);
        }

        $insertTagStmt->bind_param("s", $tagName);

        if (!$insertTagStmt->execute()) {
            die("Tag insert failed: " . $insertTagStmt->error);
        }

        $tagId = $insertTagStmt->insert_id;
        $insertTagStmt->close();
    }

    $linkSql = "INSERT INTO submission_tags (Sub_ID, Tag_ID) VALUES (?, ?)";
    $linkStmt = $conn->prepare($linkSql);

    if (!$linkStmt) {
        die("Prepare failed: " . $conn->error);
    }

    $linkStmt->bind_param("ii", $subId, $tagId);

    if (!$linkStmt->execute()) {
        die("Submission-tag link failed: " . $linkStmt->error);
    }

    $linkStmt->close();
}

$conn->close();

header("Location: submit_success.php");
exit;
?>
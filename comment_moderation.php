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
 
$message     = "";
$messageType = "";
 
// Handle delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_comment_id'])) {
    $deleteId = (int)$_POST['delete_comment_id'];
 
    $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
 
    if ($stmt->affected_rows === 1) {
        $message     = "Comment deleted successfully.";
        $messageType = "success";
    } else {
        $message     = "Could not delete comment. It may have already been removed.";
        $messageType = "error";
    }
    $stmt->close();
}
 
// Fetch all comments joined with username
// Adjust table/column names below if your schema differs
$search = trim($_GET['search'] ?? '');
if ($search !== "") {
    $like = "%" . $conn->real_escape_string($search) . "%";
    $result = $conn->query(
        "SELECT c.comment_id, c.comment_text, c.created_at, u.username, u.user_id
         FROM comments c
         JOIN user u ON c.user_id = u.user_id
         WHERE c.comment_text LIKE '$like' OR u.username LIKE '$like'
         ORDER BY c.created_at DESC"
    );
} else {
    $result = $conn->query(
        "SELECT c.comment_id, c.comment_text, c.created_at, u.username, u.user_id
         FROM comments c
         JOIN user u ON c.user_id = u.user_id
         ORDER BY c.created_at DESC"
    );
}
 
$comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Moderation – Media Archive</title>
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
            max-width: 960px;
            margin: 0 auto;
        }
 
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #a8a8b3;
            text-decoration: none;
            font-size: 13px;
        }
 
        .back-link:hover { color: #e94560; }
 
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
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
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
 
        .alert.success {
            background: rgba(39,174,96,0.15);
            border: 1px solid #27ae60;
            color: #27ae60;
        }
 
        .alert.error {
            background: rgba(233,69,96,0.15);
            border: 1px solid #e94560;
            color: #e94560;
        }
 
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
 
        .toolbar form {
            display: flex;
            gap: 8px;
            flex: 1;
        }
 
        .toolbar input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #0f3460;
            background: #16213e;
            color: #eaeaea;
            font-size: 14px;
        }
 
        .toolbar input:focus {
            outline: none;
            border-color: #e94560;
        }
 
        .btn-search {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: #0f3460;
            color: #eaeaea;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
 
        .btn-search:hover { background: #1a4a8a; }
 
        .btn-clear {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #0f3460;
            background: transparent;
            color: #a8a8b3;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
 
        .btn-clear:hover { color: #e94560; border-color: #e94560; }
 
        .stats {
            color: #6b6b80;
            font-size: 13px;
            margin-bottom: 16px;
        }
 
        .stats span {
            color: #a8a8b3;
            font-weight: 600;
        }
 
        .comment-card {
            background: #16213e;
            border: 1px solid #0f3460;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            transition: border-color 0.2s;
        }
 
        .comment-card:hover {
            border-color: #1a4a8a;
        }
 
        .comment-card.flagged {
            border-left: 3px solid #e94560;
        }
 
        .comment-left {
            flex: 1;
        }
 
        .comment-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
 
        .comment-meta .username {
            color: #e94560;
            font-weight: 600;
            font-size: 14px;
        }
 
        .comment-meta .date {
            color: #6b6b80;
            font-size: 12px;
        }
 
        .comment-meta .comment-id {
            color: #6b6b80;
            font-size: 11px;
            background: #0f3460;
            padding: 2px 7px;
            border-radius: 10px;
        }
 
        .comment-text {
            color: #d0d0d0;
            font-size: 14px;
            line-height: 1.6;
            word-break: break-word;
        }
 
        .comment-right {
            flex-shrink: 0;
        }
 
        .btn-delete {
            padding: 7px 14px;
            border-radius: 7px;
            border: 1px solid #e94560;
            background: transparent;
            color: #e94560;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
 
        .btn-delete:hover {
            background: #e94560;
            color: #fff;
        }
 
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b6b80;
        }
 
        .empty-state p {
            font-size: 15px;
            margin-top: 10px;
        }
 
        /* Confirm overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
 
        .overlay.active {
            display: flex;
        }
 
        .confirm-box {
            background: #16213e;
            border: 1px solid #0f3460;
            border-radius: 12px;
            padding: 30px 28px;
            max-width: 380px;
            width: 100%;
            text-align: center;
        }
 
        .confirm-box h3 {
            color: #eaeaea;
            margin-bottom: 10px;
            font-size: 18px;
        }
 
        .confirm-box p {
            color: #a8a8b3;
            font-size: 14px;
            margin-bottom: 24px;
        }
 
        .confirm-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
 
        .confirm-actions .btn-cancel {
            padding: 10px 22px;
            border-radius: 8px;
            border: 1px solid #0f3460;
            background: transparent;
            color: #a8a8b3;
            font-size: 14px;
            cursor: pointer;
        }
 
        .confirm-actions .btn-confirm-delete {
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            background: #e94560;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
 
        .confirm-actions .btn-confirm-delete:hover {
            background: #c73652;
        }
    </style>
</head>
<body>
 
<div class="container">
 
    <a href="index.php" class="back-link">← Back to Home</a>
 
    <div class="header">
        <h1>Comment Moderation</h1>
        <span>Logged in as <strong style="color:#e94560"><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= htmlspecialchars($usertype) ?>)</span>
    </div>
 
    <?php if ($message !== ""): ?>
        <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
 
    <!-- Search bar -->
    <div class="toolbar">
        <form method="GET" action="comment_moderation.php">
            <input type="text" name="search"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by username or comment content...">
            <button type="submit" class="btn-search">Search</button>
        </form>
        <?php if ($search !== ""): ?>
            <a href="comment_moderation.php" class="btn-clear">Clear</a>
        <?php endif; ?>
    </div>
 
    <div class="stats">
        Showing <span><?= count($comments) ?></span> comment<?= count($comments) !== 1 ? 's' : '' ?>
        <?= $search !== "" ? " for \"" . htmlspecialchars($search) . "\"" : "" ?>
    </div>
 
    <!-- Comment list -->
    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <div style="font-size:40px">💬</div>
            <p><?= $search !== "" ? "No comments matched your search." : "No comments found." ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
        <div class="comment-card">
            <div class="comment-left">
                <div class="comment-meta">
                    <span class="username"><?= htmlspecialchars($comment['username']) ?></span>
                    <span class="date">
                        <?= isset($comment['created_at']) ? date('M j, Y g:i A', strtotime($comment['created_at'])) : 'Unknown date' ?>
                    </span>
                    <span class="comment-id">ID #<?= $comment['comment_id'] ?></span>
                </div>
                <div class="comment-text"><?= htmlspecialchars($comment['comment_text']) ?></div>
            </div>
            <div class="comment-right">
                <button class="btn-delete"
                        onclick="confirmDelete(<?= $comment['comment_id'] ?>, '<?= htmlspecialchars(addslashes($comment['username'])) ?>')">
                    Delete
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
 
</div>
 
<!-- Confirm delete overlay -->
<div class="overlay" id="confirmOverlay">
    <div class="confirm-box">
        <h3>Delete Comment?</h3>
        <p id="confirmMsg">Are you sure you want to delete this comment? This cannot be undone.</p>
        <div class="confirm-actions">
            <button class="btn-cancel" onclick="closeOverlay()">Cancel</button>
            <form method="POST" action="comment_moderation.php" style="display:inline">
                <input type="hidden" name="delete_comment_id" id="deleteCommentId">
                <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>
 
<script>
    function confirmDelete(commentId, username) {
        document.getElementById('deleteCommentId').value = commentId;
        document.getElementById('confirmMsg').textContent =
            'Are you sure you want to delete this comment by ' + username + '? This cannot be undone.';
        document.getElementById('confirmOverlay').classList.add('active');
    }
 
    function closeOverlay() {
        document.getElementById('confirmOverlay').classList.remove('active');
    }
 
    // Close overlay if clicking outside the box
    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeOverlay();
    });
</script>
 
</body>
</html>
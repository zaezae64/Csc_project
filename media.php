<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment_text = trim($_POST['comment_text'] ?? '');
    if (empty($comment_text)) {
        $error = "Comment cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, username, comment_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $_SESSION['username'], $comment_text);
        $stmt->execute();
        $stmt->close();
        $success = "Comment posted!";
    }
}

// Fetch all comments
$comments = [];
$result = $conn->query("SELECT username, comment_text, created_at FROM comments ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media — MediaArchive</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f14;
            --surface: #161a23;
            --border: #2a2f3d;
            --accent: #c8a96e;
            --text: #e8e4dc;
            --muted: #8a8a9a;
            --error: #e05555;
            --success: #5ea87a;
            --input-bg: #1c2030;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--accent);
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--muted);
        }
        .nav-right a { color: var(--accent); text-decoration: none; }
        .nav-right a:hover { text-decoration: underline; }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 24px;
        }

        /* Media Card */
        .media-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }
        .media-thumbnail {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #1a1f2e, #0f1118);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
        }
        .media-info { padding: 24px; }
        .media-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--text);
            margin-bottom: 8px;
        }
        .media-meta {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .media-desc {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* Comments */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--text);
            margin-bottom: 20px;
        }
        .comment-form {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 12px 14px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 100px;
            outline: none;
            transition: border-color 0.2s;
        }
        .comment-form textarea:focus { border-color: var(--accent); }
        .comment-form textarea::placeholder { color: var(--muted); }
        .btn {
            margin-top: 12px;
            padding: 10px 24px;
            background: var(--accent);
            color: #0d0f14;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #e8c98a; }
        .alert {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }
        .alert.error { background: rgba(224,85,85,0.12); border: 1px solid rgba(224,85,85,0.3); color: var(--error); }
        .alert.success { background: rgba(94,168,122,0.12); border: 1px solid rgba(94,168,122,0.3); color: var(--success); }

        .comment-list { display: flex; flex-direction: column; gap: 16px; }
        .comment {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
        }
        .comment-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .comment-username { font-weight: 500; color: var(--accent); font-size: 0.9rem; }
        .comment-date { font-size: 0.8rem; color: var(--muted); }
        .comment-text { font-size: 0.95rem; color: var(--text); line-height: 1.5; }
        .no-comments { color: var(--muted); font-size: 0.9rem; text-align: center; padding: 32px; }
    </style>
</head>
<body>
<div class="navbar">
    <div class="logo">🎬 MediaArchive</div>
    <div class="nav-right">
        <a href="dashboard.php">← Dashboard</a>
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php">Log out</a>
    </div>
</div>

<div class="container">
    <!-- Media Item -->
    <div class="media-card">
        <div class="media-thumbnail">🎬</div>
        <div class="media-info">
            <h1 class="media-title">Sample Media Title</h1>
            <div class="media-meta">Added by Admin &bull; April 2026 &bull; Category: Film</div>
            <p class="media-desc">This is a sample media item in the MediaArchive collection. A full description of the media would appear here, including details about its origin, content, and significance to the archive.</p>
        </div>
    </div>

    <!-- Comment Form -->
    <h2 class="section-title">Comments</h2>

    <?php if (!empty($error)): ?>
        <div class="alert error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="comment-form">
        <form method="POST" action="media.php">
            <textarea name="comment_text" placeholder="Leave a comment as <?= htmlspecialchars($_SESSION['username']) ?>..."><?= htmlspecialchars($_POST['comment_text'] ?? '') ?></textarea>
            <button type="submit" class="btn">Post Comment</button>
        </form>
    </div>

    <!-- Comments List -->
    <div class="comment-list">
        <?php if (empty($comments)): ?>
            <div class="no-comments">No comments yet. Be the first to comment!</div>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="comment-header">
                    <span class="comment-username">👤 <?= htmlspecialchars($c['username']) ?></span>
                    <span class="comment-date"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></span>
                </div>
                <p class="comment-text"><?= htmlspecialchars($c['comment_text']) ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
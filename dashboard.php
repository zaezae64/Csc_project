<?php
session_start();
$conn = new mysqli("localhost", "root", "", "csc_project");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$usertype = $_SESSION['usertype'] ?? '';
$isAdminOrMod = ($usertype === 'admin' || $usertype === 'moderator');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — MediaArchive</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f14;
            --surface: #161a23;
            --border: #2a2f3d;
            --accent: #c8a96e;
            --text: #e8e4dc;
            --muted: #8a8a9a;
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
        .nav-right a {
            color: var(--accent);
            text-decoration: none;
        }
        .nav-right a:hover { text-decoration: underline; }
        .nav-admin-link {
            background: rgba(200,169,110,0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .nav-admin-link:hover {
            background: rgba(200,169,110,0.25);
            text-decoration: none !important;
        }
        .container {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 24px;
        }
        .welcome {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--text);
            margin-bottom: 8px;
        }
        .welcome span { color: var(--accent); }
        .subtitle {
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 48px;
        }
        .section-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 16px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
            text-decoration: none;
            display: block;
        }
        .card:hover { border-color: var(--accent); }
        .card .icon { font-size: 2rem; margin-bottom: 12px; }
        .card h3 { font-size: 1rem; color: var(--text); margin-bottom: 6px; }
        .card p { font-size: 0.85rem; color: var(--muted); }
        .card.admin-card {
            border-color: rgba(200,169,110,0.3);
            background: rgba(200,169,110,0.05);
        }
        .card.admin-card:hover { border-color: var(--accent); }
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
<div class="navbar">
    <div class="logo">🎬 MediaArchive</div>
    <div class="nav-right">
        <?php if ($isAdminOrMod): ?>
            <a href="members.php" class="nav-admin-link">👥 Members</a>
            <a href="comment_moderation.php" class="nav-admin-link">🗂 Moderation</a>
        <?php endif; ?>
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($usertype) ?>)</span>
        <a href="logout.php">Log out</a>
    </div>
</div>
 
<div class="container">
    <h1 class="welcome">Welcome back, <span><?= htmlspecialchars($_SESSION['username']) ?></span>!</h1>
    <p class="subtitle">You're logged in to MediaArchive. More features coming soon.</p>
 
    <!-- Regular cards visible to everyone -->
    <div class="section-label">General</div>
    <div class="cards">
        <a class="card" href="media.php">
            <div class="icon">🎬</div>
            <h3>Browse Media</h3>
            <p>Explore the archive collection.</p>
        </a>
        <a class="card" href="#">
            <div class="icon">📁</div>
            <h3>My Submissions</h3>
            <p>View and manage your uploads.</p>
        </a>
        <a class="card" href="#">
            <div class="icon">⚙️</div>
            <h3>Account Settings</h3>
            <p>Update your profile and preferences.</p>
        </a>
    </div>
 
    <!-- Admin/Mod only cards -->
    <?php if ($isAdminOrMod): ?>
    <hr class="divider">
    <div class="section-label">Admin Panel</div>
    <div class="cards">
        <a class="card admin-card" href="members.php">
            <div class="icon">👥</div>
            <h3>Members</h3>
            <p>View and manage all site members.</p>
        </a>
        <a class="card admin-card" href="comment_moderation.php">
            <div class="icon">🗂</div>
            <h3>Comment Moderation</h3>
            <p>Review and delete user comments.</p>
        </a>
    </div>
    <?php endif; ?>
 
</div>
</body>
</html>

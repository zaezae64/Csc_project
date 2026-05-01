<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permission Denied – Media Archive</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #eaeaea;
        }
 
        .card {
            background: #16213e;
            border: 1px solid #0f3460;
            border-radius: 12px;
            padding: 50px 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
 
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
 
        h1 {
            color: #e94560;
            font-size: 26px;
            margin-bottom: 12px;
        }
 
        p {
            color: #a8a8b3;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 30px;
        }
 
        .btn-home {
            display: inline-block;
            padding: 11px 28px;
            background: #e94560;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
            margin: 0 6px;
        }
 
        .btn-home:hover { background: #c73652; }
 
        .btn-back {
            display: inline-block;
            padding: 11px 28px;
            border: 1px solid #0f3460;
            color: #a8a8b3;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            margin: 0 6px;
        }
 
        .btn-back:hover {
            border-color: #e94560;
            color: #e94560;
        }
 
        .error-code {
            color: #0f3460;
            font-size: 80px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: 4px;
        }
    </style>
</head>
<body>
 
<div class="card">
    <div class="error-code">403</div>
    <div class="icon">🚫</div>
    <h1>Permission Denied</h1>
    <p>
        You do not have permission to access this page.<br>
        This area is restricted to administrators and moderators only.
    </p>
    <div>
        <a href="dashboard.php" class="btn-home">Go to Home</a>
        <a href="javascript:history.back()" class="btn-back">Go Back</a>
    </div>
</div>
 
</body>
</html>
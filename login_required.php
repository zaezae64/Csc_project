<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Required - Media Database</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="w3-content" style="max-width: 1100px; margin-top:40px;">

    <div class="oc-callout w3-card-4 text-center">

        <h2 class="w3-text-white mb-3">Login Required</h2>

        <p class="w3-text-light-grey w3-large mb-4">
            You must be logged in to access this page.
        </p>


    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
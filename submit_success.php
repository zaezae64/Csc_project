<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - Media Archive</title>

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container mt-4">
    <div class="oc-callout w3-card-4 text-center">
        <h1 class="h3 mb-3">Submission Successful</h1>
        <p class="mb-4">
            Your media suggestion has been submitted for moderator review.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="submit_media.php" class="btn btn-warning">Submit Another</a>
            <a href="media.php" class="btn btn-outline-light">Browse Media</a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_required.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$userId   = $_SESSION['user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Media - Media Database</title>
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

    <div class="oc-callout w3-card-4">
        <h2 class="w3-text-white mb-3">Submit a Media Suggestion</h2>

        <p class="w3-text-light-grey mb-4">
            Logged in as <strong><?php echo htmlspecialchars($username); ?></strong>.
            Submit a media suggestion for moderator review.
        </p>

        <form class="w3-container" action="submitMediaAction.php" method="post">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

            <p>
                <label class="w3-text-blue"><b>Media Name</b></label>
                <input class="w3-input w3-border" type="text" name="media_name" required>
            </p>

            <p>
                <label class="w3-text-blue"><b>Media Type</b></label>
                <select class="w3-select w3-border" name="media_type">
                    <option value="" disabled selected>Select media type</option>
                    <option>Book</option>
                    <option>Movie</option>
                    <option>Video Game</option>
                    <option>TV Show</option>
                    <option>Comic</option>
                    <option>Other</option>
                </select>
            </p>

            <p>
                <label class="w3-text-blue"><b>Genre</b></label>
                <input class="w3-input w3-border" type="text" name="genre"
                       placeholder="Example: Fantasy, Action, Drama">
            </p>

            <p>
                <label class="w3-text-blue"><b>Description</b></label>
                <textarea class="w3-input w3-border" name="description" rows="4"
                          placeholder="This field is for future schema support."></textarea>
            </p>

            <p>
                <label class="w3-text-blue"><b>Tags</b></label>
                <input class="w3-input w3-border" type="text" name="tags"
                       placeholder="Enter tags separated by commas" required>
            </p>

            <p class="w3-text-light-grey" style="font-size: 0.95em;">
                Note: with the current database, only the media name and tags will be stored with the submission.
            </p>

            <p>
                <button class="btn btn-warning" type="submit">Submit Media</button>
            </p>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
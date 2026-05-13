<?php
session_start();
require_once 'DBConnect.php';

$selectedCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : 'all';

$sql = "
    SELECT 
        media_page.Page_ID,
        media_page.MediaDesc,
        submission.MediaName,
        (
            SELECT ImagePath
            FROM media_images
            WHERE media_images.Page_ID = media_page.Page_ID
            ORDER BY Image_ID ASC
            LIMIT 1
        ) AS ImagePath
    FROM media_page
    INNER JOIN submission ON media_page.Sub_ID = submission.Sub_ID
    ORDER BY submission.MediaName ASC
";

$result = $conn->query($sql);

function guessCategory(string $mediaName): string
{
    $books = ['The Hobbit', 'To Kill a Mockingbird'];
    $movies = ['The Matrix', 'Spider-Man: Into the Spider-Verse'];
    $games = ['Portal 2', 'The Legend of Zelda: Breath of the Wild', 'F-Zero'];

    if (in_array($mediaName, $books, true)) {
        return 'books';
    }

    if (in_array($mediaName, $movies, true)) {
        return 'movies';
    }

    if (in_array($mediaName, $games, true)) {
        return 'games';
    }

    return 'other';
}

function displayCategory(string $category): string
{
    return match ($category) {
        'books' => 'Book',
        'movies' => 'Movie',
        'games' => 'Video Game',
        default => 'Media'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Media Catalog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .media-card-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #222;
        }

        .media-card-placeholder {
            width: 100%;
            height: 220px;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 1rem;
        }

        .media-desc-preview {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
            min-height: 4.5em;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container mt-4">

    <div class="oc-callout w3-card-4">
        <h1 class="h3 mb-3">Media Catalog</h1>
        <p class="mb-3">Browse media currently published in the database.</p>

        <div class="d-flex flex-wrap gap-2">
            <a href="media.php?category=all" class="btn btn-outline-light">All Media</a>
            <a href="media.php?category=books" class="btn btn-outline-light">Books</a>
            <a href="media.php?category=movies" class="btn btn-outline-light">Movies</a>
            <a href="media.php?category=games" class="btn btn-outline-light">Video Games</a>
        </div>
    </div>

    <div class="row mt-3">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $mediaName = $row['MediaName'];
                    $category = guessCategory($mediaName);

                    if ($selectedCategory !== 'all' && $selectedCategory !== $category) {
                        continue;
                    }

                    $imagePath = $row['ImagePath'] ?? '';
                ?>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="oc-callout w3-card-4 media-tile h-100 d-flex flex-column">

                        <?php if (!empty($imagePath)): ?>
                            <img
                                src="<?php echo htmlspecialchars($imagePath); ?>"
                                alt="<?php echo htmlspecialchars($mediaName); ?>"
                                class="media-card-image"
                            >
                        <?php else: ?>
                            <div class="media-card-placeholder">
                                No Image Uploaded
                            </div>
                        <?php endif; ?>

                        <h2 class="h5 mb-2">
                            <?php echo htmlspecialchars($mediaName); ?>
                        </h2>

                        <p class="mb-2">
                            <strong>Category:</strong>
                            <?php echo htmlspecialchars(displayCategory($category)); ?>
                        </p>

                        <p class="mb-3 media-desc-preview">
                            <?php echo htmlspecialchars($row['MediaDesc']); ?>
                        </p>

                        <a
                            href="media_details.php?id=<?php echo urlencode($row['Page_ID']); ?>"
                            class="btn btn-warning btn-sm mt-auto"
                        >
                            View Details
                        </a>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="oc-callout w3-card-4">
                    <p class="mb-0">No media found in the database.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
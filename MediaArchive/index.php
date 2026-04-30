<?php
session_start();
require_once 'DBConnect.php';

/*
    Current schema:
    - media_page.Page_ID, media_page.Sub_ID, media_page.MediaDesc
    - submission.Sub_ID, submission.MediaName

    So we join media_page to submission on Sub_ID.
*/

$result = $conn->query("
    SELECT media_page.Page_ID, media_page.MediaDesc, submission.MediaName
    FROM media_page
    INNER JOIN submission ON media_page.Sub_ID = submission.Sub_ID
    ORDER BY RAND()
    LIMIT 1
");

$featuredMedia = null;

if ($result && $result->num_rows > 0) {
    $featuredMedia = $result->fetch_assoc();
}

$imageMap = [
    'The Hobbit' => 'https://upload.wikimedia.org/wikipedia/en/4/4a/TheHobbit_FirstEdition.jpg',
    'The Matrix' => 'https://upload.wikimedia.org/wikipedia/en/c/c1/The_Matrix_Poster.jpg',
    'Portal 2' => 'https://upload.wikimedia.org/wikipedia/en/f/f9/Portal2cover.jpg',
    'Spider-Man: Into the Spider-Verse' => 'https://upload.wikimedia.org/wikipedia/en/f/f9/Spider-Man_Into_the_Spider-Verse_poster.jpg',
    'The Legend of Zelda: Breath of the Wild' => 'https://upload.wikimedia.org/wikipedia/en/a/a7/The_Legend_of_Zelda_Breath_of_the_Wild.jpg',
    'To Kill a Mockingbird' => 'https://upload.wikimedia.org/wikipedia/en/7/79/To_Kill_a_Mockingbird.JPG'
];

function guessCategory(string $mediaName): string
{
    $books = ['The Hobbit', 'To Kill a Mockingbird'];
    $movies = ['The Matrix', 'Spider-Man: Into the Spider-Verse'];
    $games = ['Portal 2', 'The Legend of Zelda: Breath of the Wild'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Database</title>

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
        <h1 class="h3 mb-3">Welcome to the Media Database</h1>
        <p class="mb-3">
            Browse a collection of vetted media including books, movies, and video games.
            View descriptions and details for each published media page.
        </p>
        <a href="media.php" class="btn btn-warning">Browse Media</a>
    </div>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3">Browse by Category</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="media.php?category=all" class="btn btn-outline-light">All Media</a>
            <a href="media.php?category=books" class="btn btn-outline-light">Books</a>
            <a href="media.php?category=movies" class="btn btn-outline-light">Movies</a>
            <a href="media.php?category=games" class="btn btn-outline-light">Video Games</a>
        </div>
    </div>

    <div class="oc-callout w3-card-4">
        <h2 class="h4 mb-3">Featured Media</h2>

        <?php if ($featuredMedia): ?>
            <?php
                $mediaName = $featuredMedia['MediaName'];
                $category = guessCategory($mediaName);
                $imageUrl = $imageMap[$mediaName] ?? '';
            ?>

            <div class="text-center">
                <?php if ($imageUrl !== ''): ?>
                    <a href="media_details.php?id=<?php echo urlencode($featuredMedia['Page_ID']); ?>">
                        <img
                            src="<?php echo htmlspecialchars($imageUrl); ?>"
                            alt="<?php echo htmlspecialchars($mediaName); ?>"
                            class="img-fluid rounded media-detail-image mb-3"
                        >
                    </a>
                <?php endif; ?>

                <h3 class="h5"><?php echo htmlspecialchars($mediaName); ?></h3>

                <p>
                    <strong>Category:</strong>
                    <?php echo htmlspecialchars(displayCategory($category)); ?>
                </p>

                <p class="mb-3">
                    <?php echo htmlspecialchars($featuredMedia['MediaDesc']); ?>
                </p>

                <a href="media_details.php?id=<?php echo urlencode($featuredMedia['Page_ID']); ?>" class="btn btn-warning">
                    View Details
                </a>
            </div>
        <?php else: ?>
            <p class="mb-0">No featured media is available yet.</p>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
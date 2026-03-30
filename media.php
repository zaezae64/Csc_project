<?php
$selectedCategory = isset($_GET['category']) ? strtolower($_GET['category']) : 'all';

$mediaItems = [
    [
        'title' => 'The Hobbit',
        'category' => 'books',
        'display_category' => 'Book',
        'description' => 'A fantasy adventure following Bilbo Baggins on an unexpected journey.',
        'tags' => ['Fantasy', 'Adventure', 'Classic']
    ],
    [
        'title' => 'The Matrix',
        'category' => 'movies',
        'display_category' => 'Movie',
        'description' => 'A science fiction story about reality, control, and rebellion.',
        'tags' => ['Sci-Fi', 'Action', 'Classic']
    ],
    [
        'title' => 'Portal 2',
        'category' => 'games',
        'display_category' => 'Video Game',
        'description' => 'A puzzle game built around portals, experimentation, and problem solving.',
        'tags' => ['Puzzle', 'Sci-Fi', 'Co-op']
    ],
    [
        'title' => 'Spider-Man: Into the Spider-Verse',
        'category' => 'movies',
        'display_category' => 'Movie',
        'description' => 'An animated superhero film centered on Miles Morales and the multiverse.',
        'tags' => ['Animation', 'Superhero', 'Action']
    ],
    [
        'title' => 'The Legend of Zelda: Breath of the Wild',
        'category' => 'games',
        'display_category' => 'Video Game',
        'description' => 'An open-world adventure game focused on exploration, combat, and discovery.',
        'tags' => ['Adventure', 'Open World', 'Fantasy']
    ],
    [
        'title' => 'To Kill a Mockingbird',
        'category' => 'books',
        'display_category' => 'Book',
        'description' => 'A classic novel exploring justice, morality, and human behavior.',
        'tags' => ['Classic', 'Drama', 'Literature']
    ]
];

$filteredItems = [];

if ($selectedCategory === 'all') {
    $filteredItems = $mediaItems;
} else {
    foreach ($mediaItems as $item) {
        if ($item['category'] === $selectedCategory) {
            $filteredItems[] = $item;
        }
    }
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Catalog</title>

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
        <h1 class="h3 mb-3">Media Catalog</h1>
        <p class="mb-3">
            Browse media by category and explore descriptions and tags for each item.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="media.php?category=all" class="btn btn-outline-light">All Media</a>
            <a href="media.php?category=books" class="btn btn-outline-light">Books</a>
            <a href="media.php?category=movies" class="btn btn-outline-light">Movies</a>
            <a href="media.php?category=games" class="btn btn-outline-light">Video Games</a>
        </div>
    </div>

    <div class="row mt-3">
        <?php if (count($filteredItems) > 0): ?>
            <?php foreach ($filteredItems as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="oc-callout w3-card-4 media-tile h-100">
                        <h2 class="h5 mb-2"><?php echo htmlspecialchars($item['title']); ?></h2>

                        <p class="mb-2">
                            <strong>Category:</strong>
                            <?php echo htmlspecialchars($item['display_category']); ?>
                        </p>

                        <p class="mb-3">
                            <?php echo htmlspecialchars($item['description']); ?>
                        </p>

                        <p class="mb-2"><strong>Tags:</strong></p>
                        <div class="mb-3">
                            <?php foreach ($item['tags'] as $tag): ?>
                                <span class="badge bg-secondary me-1 mb-1">
                                    <?php echo htmlspecialchars($tag); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <a href="media_details.php?title=<?php echo urlencode($item['title']); ?>" class="btn btn-warning btn-sm mt-auto">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="oc-callout w3-card-4">
                    <p class="mb-0">No media items were found for this category.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
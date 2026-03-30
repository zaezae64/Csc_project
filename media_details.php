<?php
$title = isset($_GET['title']) ? $_GET['title'] : 'Unknown Media';

$mediaItems = [
    [
        'title' => 'The Hobbit',
        'category' => 'Book',
        'image' => 'https://upload.wikimedia.org/wikipedia/en/4/4a/TheHobbit_FirstEdition.jpg',
        'source' => 'https://en.wikipedia.org/wiki/The_Hobbit',
        'description' => 'A fantasy adventure following Bilbo Baggins on an unexpected journey.',
        'tags' => ['Fantasy', 'Adventure', 'Classic'],
        'details' => 'Bilbo Baggins is swept into an epic quest to reclaim a lost dwarf kingdom from the dragon Smaug.'
    ],
    [
        'title' => 'The Matrix',
        'category' => 'Movie',
        'image' => 'https://upload.wikimedia.org/wikipedia/en/d/db/The_Matrix.png',
        'source' => 'https://en.wikipedia.org/wiki/The_Matrix',
        'description' => 'A science fiction story about reality, control, and rebellion.',
        'tags' => ['Sci-Fi', 'Action', 'Classic'],
        'details' => 'Neo discovers the truth about reality and his role in the war against machines.'
    ],
    [
        'title' => 'Portal 2',
        'category' => 'Video Game',
        'image' => 'https://upload.wikimedia.org/wikipedia/en/f/f9/Portal2cover.jpg',
        'source' => 'https://en.wikipedia.org/wiki/Portal_2',
        'description' => 'A puzzle game built around portals, experimentation, and problem solving.',
        'tags' => ['Puzzle', 'Sci-Fi', 'Co-op'],
        'details' => 'Use a portal gun to solve increasingly complex test chambers in a mysterious facility.'
    ],
    [
        'title' => 'Spider-Man: Into the Spider-Verse',
        'category' => 'Movie',
        'image' => 'https://upload.wikimedia.org/wikipedia/en/f/fa/Spider-Man_Into_the_Spider-Verse_poster.png',
        'source' => 'https://en.wikipedia.org/wiki/Spider-Man:_Into_the_Spider-Verse',
        'description' => 'An animated superhero film centered on Miles Morales and the multiverse.',
        'tags' => ['Animation', 'Superhero', 'Action'],
        'details' => 'Miles Morales becomes Spider-Man and teams up with alternate Spider-People from across the multiverse.'
    ],
    [
        'title' => 'The Legend of Zelda: Breath of the Wild',
        'category' => 'Video Game',
        'image' => 'https://upload.wikimedia.org/wikipedia/en/c/c6/The_Legend_of_Zelda_Breath_of_the_Wild.jpg',
        'source' => 'https://en.wikipedia.org/wiki/The_Legend_of_Zelda:_Breath_of_the_Wild',
        'description' => 'An open-world adventure game focused on exploration, combat, and discovery.',
        'tags' => ['Adventure', 'Open World', 'Fantasy'],
        'details' => 'Link awakens in Hyrule and explores a vast world to recover his memories and defeat Calamity Ganon.'
    ],
    [
        'title' => 'To Kill a Mockingbird',
        'category' => 'Book',
        'image' => 'https://upload.wikimedia.org/wikipedia/commons/4/4f/To_Kill_a_Mockingbird_%28first_edition_cover%29.jpg',
        'source' => 'https://en.wikipedia.org/wiki/To_Kill_a_Mockingbird',
        'description' => 'A classic novel exploring justice, morality, and human behavior.',
        'tags' => ['Classic', 'Drama', 'Literature'],
        'details' => 'Set in the American South, the story follows Scout Finch as she learns about prejudice, justice, and empathy.'
    ]
];

$selectedItem = null;

foreach ($mediaItems as $item) {
    if ($item['title'] === $title) {
        $selectedItem = $item;
        break;
    }
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Details</title>

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mystyles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container mt-4">

    <?php if ($selectedItem): ?>
        <div class="oc-callout w3-card-4">
            <h1 class="h3 mb-3"><?php echo htmlspecialchars($selectedItem['title']); ?></h1>

            <p><strong>Category:</strong> <?php echo htmlspecialchars($selectedItem['category']); ?></p>

            <!-- IMAGE + SOURCE -->
            <div class="mb-4 text-center">
                <a href="<?php echo htmlspecialchars($selectedItem['source']); ?>" target="_blank">
                    <img
                        src="<?php echo htmlspecialchars($selectedItem['image']); ?>"
                        alt="<?php echo htmlspecialchars($selectedItem['title']); ?>"
                        class="img-fluid rounded media-detail-image"
                    >
                </a>

                <div class="mt-2">
                    <a href="<?php echo htmlspecialchars($selectedItem['source']); ?>" target="_blank" class="text-light">
                        View Source
                    </a>
                </div>
            </div>

            <p><strong>Description:</strong><br>
                <?php echo htmlspecialchars($selectedItem['description']); ?>
            </p>

            <p><strong>Details:</strong><br>
                <?php echo htmlspecialchars($selectedItem['details']); ?>
            </p>

            <p><strong>Tags:</strong></p>
            <div class="mb-3">
                <?php foreach ($selectedItem['tags'] as $tag): ?>
                    <span class="badge bg-secondary me-1 mb-1">
                        <?php echo htmlspecialchars($tag); ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <a href="media.php" class="btn btn-warning">Back to Media</a>
        </div>

    <?php else: ?>
        <div class="oc-callout w3-card-4">
            <h2 class="h4">Media Not Found</h2>
            <p>The requested media item could not be found.</p>
            <a href="media.php" class="btn btn-warning">Back to Media</a>
        </div>
    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
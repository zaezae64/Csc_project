<?php
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

$randomItem = $mediaItems[array_rand($mediaItems)];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            View descriptions, tags, and other details for each item.
        </p>
        <a href="media.php" class="btn btn-warning">Browse Media</a>
    </div>

<div class="oc-callout w3-card-4">
    <h2 class="h4 mb-3">Featured Media</h2>

    <div class="text-center">

        <a href="media_details.php?title=<?php echo urlencode($randomItem['title']); ?>" target="_self">
            <img
                src="<?php echo htmlspecialchars($randomItem['image']); ?>"
                alt="<?php echo htmlspecialchars($randomItem['title']); ?>"
                class="img-fluid rounded media-detail-image mb-3"
            >
        </a>

        <h3 class="h5"><?php echo htmlspecialchars($randomItem['title']); ?></h3>

        <p><strong>Category:</strong> <?php echo htmlspecialchars($randomItem['category']); ?></p>

        <p class="mb-3">
            <?php echo htmlspecialchars($randomItem['description']); ?>
        </p>

        <a href="media_details.php?title=<?php echo urlencode($randomItem['title']); ?>" class="btn btn-warning">
            View Details
        </a>

    </div>
</div>

</main>

<?php include 'footer.php'; ?>

</body>
</html>
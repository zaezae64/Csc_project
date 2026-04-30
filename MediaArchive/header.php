<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">

        <!-- Logo / Title -->
        <a class="navbar-brand" href="index.php">Media Database</a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- LEFT SIDE NAV -->
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="media.php">Media</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="media.php?category=books">Books</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="media.php?category=movies">Movies</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="media.php?category=games">Video Games</a>
                </li>

                <!-- Logged-in only: Submit Media -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_media.php">Submit Media</a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- RIGHT SIDE NAV -->
            <ul class="navbar-nav ms-auto">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <!-- Logged-in user info -->
                    <li class="nav-item">
                        <span class="nav-link">
                            Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </li>

                    <!-- View Profile (ALL users including admins) -->
                    <li class="nav-item">
                        <a class="nav-link" href="view_profile.php?id=<?php echo urlencode($_SESSION['user_id']); ?>">
                            View Profile
                        </a>
                    </li>

                    <!-- Edit Profile -->
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            Edit Profile
                        </a>
                    </li>

                    <!-- Admin-only link -->
                    <?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                Admin Page
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <!-- Guest links -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">
                            Sign Up
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>
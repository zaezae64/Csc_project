<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="index.php">
            Media Archive
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="media.php">
                        Media
                    </a>
                </li>


                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="submit_media.php">
                            Submit Media
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

            <ul class="navbar-nav ms-auto">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <span class="nav-link">
                            Logged in as
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="view_profile.php?id=<?php echo urlencode($_SESSION['user_id']); ?>"
                        >
                            View Profile
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            Edit Profile
                        </a>
                    </li>

                    <?php if (
                        isset($_SESSION['usertype']) &&
                        (
                            $_SESSION['usertype'] === 'admin' ||
                            $_SESSION['usertype'] === 'moderator'
                        )
                    ): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="members.php">
                                View Members
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (
                        isset($_SESSION['usertype']) &&
                        $_SESSION['usertype'] === 'admin'
                    ): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                Admin Page
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

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
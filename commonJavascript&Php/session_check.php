<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session only if one isn't already started
}
// Check if the session variable for last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate the session lifetime since the last activity
    $session_lifetime = time() - $_SESSION['last_activity'];

    // Specifying the time limit for inactivity
    $max_inactivity = 2 * 60 * 60; // 2 hours in seconds

    // If the session lifetime exceeds the maximum inactivity limit
    if ($session_lifetime > $max_inactivity) {
        // Destroy the session and clear the session variables
        session_unset(); // Unset $_SESSION variable for the runtime
        session_destroy(); // Destroy session data in storage

        // Redirect to login page with a timeout parameter
        header("Location: login.php?timeout=1");
        exit();
    }
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();
?>

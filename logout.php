<?php
    // Initialize the session
    session_start();

    // Unset all of the session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Redirect to the login page (adjust the location as needed)
    header("location: welcome.php");
    exit;
?>

<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'silah_sitesi_db');

/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli === false){
    // die() is not user-friendly for a production site, but okay for now during development.
    // We can implement a better error handling page later.
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// Set UTF-8 character set (try utf8mb4 first, then fallback to utf8)
if (!$mysqli->set_charset("utf8mb4")) {
    // printf("Error loading character set utf8mb4: %s\n", $mysqli->error);
    // echo "Falling back to utf8 character set.<br>"; // Optional: for debugging
    if (!$mysqli->set_charset("utf8")) {
        // printf("Error loading character set utf8: %s\n", $mysqli->error);
        // Optional: log error if both charsets fail
        // error_log("Error loading character set utf8mb4 or utf8: " . $mysqli->error);
    }
}
?> 
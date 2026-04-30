<?php
$dbserver = "localhost";
$dbuser   = "root";
$dbpswd   = "";
$dbname   = "media_archive";

$conn = new mysqli($dbserver, $dbuser, $dbpswd, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

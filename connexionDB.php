<?php
$hs = "localhost";
$us = "root";
$ps = "";
$dbname = "web";

// Establish the connection
$mysqlconnect = mysqli_connect($hs, $us, $ps, $dbname);

// Check if the connection was successful
if ($mysqlconnect === false) {
    die("MySQL connection failed: " . mysqli_connect_error());
}
?>

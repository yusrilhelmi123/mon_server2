<?php
/**
 * SensoLab - Database Connection Configuration
 * Edit this file to match your server credentials (e.g. for InfinityFree)
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sensor";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Initial Settings
date_default_timezone_set('Asia/Jakarta');
?>

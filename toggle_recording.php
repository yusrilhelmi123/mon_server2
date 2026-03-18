<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die("Fail");

if (isset($_POST['status'])) {
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE tb_settings SET value = '$status' WHERE name = 'recording_service'");
    echo "Success";
}
$conn->close();
?>
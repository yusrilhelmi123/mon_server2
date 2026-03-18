<?php
require_once 'db_connect.php';

if (isset($_POST['status'])) {
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE tb_settings SET value = '$status' WHERE name = 'recording_service'");
    echo "Success";
}
$conn->close();
?>
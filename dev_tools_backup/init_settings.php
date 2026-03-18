<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die($conn->connect_error);

$conn->query("CREATE TABLE IF NOT EXISTS tb_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    value VARCHAR(255)
)");

$conn->query("INSERT IGNORE INTO tb_settings (name, value) VALUES ('recording_service', '1')");

echo "Table tb_settings created and initialized.";
$conn->close();
?>
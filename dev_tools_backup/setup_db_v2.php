<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die("Connection failed");

// Create labeling table if not exists
$sql = "CREATE TABLE IF NOT EXISTS tb_reliability_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monitoring_id INT NOT NULL,
    is_valid TINYINT(1) DEFAULT 1,
    reliability_score FLOAT,
    analysis_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (monitoring_id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table tb_reliability_labels created/checked.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
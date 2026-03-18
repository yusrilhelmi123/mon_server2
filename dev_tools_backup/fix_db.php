<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->query("ALTER TABLE tb_monitoring ADD COLUMN ldr VARCHAR(20) DEFAULT '-' AFTER status_gas")) {
    echo "Column 'ldr' added successfully.";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
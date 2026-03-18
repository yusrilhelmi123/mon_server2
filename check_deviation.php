<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
$ids = [125, 126, 127, 128, 129];
$in_ids = implode(',', $ids);
$res = $conn->query("SELECT * FROM tb_monitoring WHERE id IN ($in_ids)");
echo "Monitoring Data for IDs 125-129:\n";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

// Also check surrounding data to see deviation
$res_context = $conn->query("SELECT * FROM tb_monitoring WHERE id BETWEEN 120 AND 135 ORDER BY id ASC");
echo "\nSurrounding Data Context (IDs 120-135):\n";
while ($row = $res_context->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Cahaya: " . $row['cahaya'] . "\n";
}
$conn->close();
?>
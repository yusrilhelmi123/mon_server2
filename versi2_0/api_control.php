<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB Error']);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'toggle_recording') {
    // Ambil status saat ini
    $res = $conn->query("SELECT value FROM tb_settings WHERE name='recording_service'");
    $current = $res ? $res->fetch_assoc()['value'] : '0';

    // Switch status
    $newVal = ($current === '1') ? '0' : '1';

    $stmt = $conn->prepare("UPDATE tb_settings SET value = ? WHERE name = 'recording_service'");
    $stmt->bind_param("s", $newVal);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'new_status' => $newVal]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
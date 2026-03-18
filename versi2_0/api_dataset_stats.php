<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$total_mon = $conn->query("SELECT COUNT(*) as t FROM tb_monitoring")->fetch_assoc()['t'];
$labeled = $conn->query("SELECT COUNT(*) as t FROM tb_reliability_labels")->fetch_assoc()['t'];
$valid = $conn->query("SELECT COUNT(*) as t FROM tb_reliability_labels WHERE is_valid = 1")->fetch_assoc()['t'];
$invalid = $conn->query("SELECT COUNT(*) as t FROM tb_reliability_labels WHERE is_valid = 0")->fetch_assoc()['t'];

echo json_encode([
    'total_monitoring' => (int) $total_mon,
    'total_labeled' => (int) $labeled,
    'total_valid' => (int) $valid,
    'total_invalid' => (int) $invalid,
    'pending' => (int) ($total_mon - $labeled)
]);

$conn->close();
?>
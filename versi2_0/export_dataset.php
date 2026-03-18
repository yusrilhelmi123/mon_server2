<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die("Connection failed");

$filename = "Sensolab_Valid_Dataset_" . date('Ymd_His') . ".csv";

// Bersihkan output buffer untuk mencegah karakter tak diinginkan
if (ob_get_length())
    ob_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, ['ID', 'Waktu', 'Temperatur(C)', 'Kelembaban(%)', 'Gas(PPM)', 'LDR_Status', 'Valid_Status']);

// Ambil hanya data yang valid
$query = "SELECT m.id, m.waktu, m.suhu, m.kelembaban, m.cahaya as gas, m.ldr, l.is_valid 
          FROM tb_monitoring m 
          JOIN tb_reliability_labels l ON m.id = l.monitoring_id 
          WHERE l.is_valid = 1 
          ORDER BY m.id ASC";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['waktu'],
        $row['suhu'],
        $row['kelembaban'],
        $row['gas'],
        $row['ldr'],
        'VALID'
    ]);
}

fclose($output);
$conn->close();
?>
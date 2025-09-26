<?php
header('Content-Type: application/json');
require 'db.php';

$today = date("Y-m-d");
$weekStart = date("Y-m-d", strtotime("monday this week"));
$monthStart = date("Y-m-01");

function getTotal($conn, $from, $to) {
    $sql = "SELECT SUM(total) as total FROM orders 
            WHERE created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'
            AND status='Paid'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    return $row['total'] ? floatval($row['total']) : 0;
}

$summary = [
    "today" => getTotal($conn, $today, $today),
    "week"  => getTotal($conn, $weekStart, date("Y-m-d")),
    "month" => getTotal($conn, $monthStart, date("Y-m-d"))
];

echo json_encode(["success" => true, "summary" => $summary]);
?>

<?php
include 'db_connect.php';

$type = $_GET['type'];
$date = date('Y-m-d');

if ($type == 'daily_waiting') {
    $result = $conn->query("SELECT * FROM WaitingList WHERE DateAdded = '$date'");
} elseif ($type == 'weekly_bus') {
    $result = $conn->query("SELECT BusID, COUNT(*) AS Total FROM Application JOIN Bus ON Application.RouteID = Bus.RouteID GROUP BY BusID");
}

while ($row = $result->fetch_assoc()) {
    echo json_encode($row);
}
?>

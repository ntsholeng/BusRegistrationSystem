
<?php
$host = "localhost";
$user = "jr";
$password = "@Ntsholeng101";
$database = "BusRegistrationSystem";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

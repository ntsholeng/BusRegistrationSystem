<?php
include 'db_connect.php';

$fullName = $_POST['fullName'];
$email = $_POST['email'];
$phone = $_POST['phone'];

$sql = "INSERT INTO Parent (FullName, Email, PhoneNumber) VALUES ('$fullName', '$email', '$phone')";
$conn->query($sql);
echo "Parent registered successfully.";
?>

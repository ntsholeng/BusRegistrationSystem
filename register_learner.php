<?php
include 'db_connect.php';

$fullName = $_POST['fullName'];
$grade = $_POST['grade'];
$email = $_POST['email'];
$parentID = $_POST['parentID'];

$sql = "INSERT INTO Learner (FullName, Grade, Email, ParentID) VALUES ('$fullName', $grade, '$email', $parentID)";
$conn->query($sql);
echo "Learner registered successfully.";
?>

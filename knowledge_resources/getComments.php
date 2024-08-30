<?php
include "../Database_config/database-connect.php";
// Check if session is logged in
session_start();
if (!isset($_SESSION["loggedin"])) {
  $_SESSION["loggedin"] = false;
}
if ($_SESSION["loggedin"] != true) {
  header("location: login.php");
} else {
  $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
  $rows = [[]];
  
  $clickedPostID = $_GET['clickedPostID'];

  //sets an sql statement that gets comments with their date, author, and commentID for the selected postID
  $stmt = $conn->prepare("SELECT comment, time, firstName, surname, commentID from Comments join Users on creatorID = userID where 
  postID = ?");
  $stmt->bind_param("i", $clickedPostID);
  $stmt->execute();
  $stmt->bind_result($comment, $time, $firstName, $surname, $commentID);
  while ($stmt->fetch()) {
    $rows[] = [$comment  . " | " . $firstName . " " . $surname . " | " . $time, $commentID];
  }
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>
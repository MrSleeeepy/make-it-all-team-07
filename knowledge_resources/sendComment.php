<?php
include "../Database_config/database-connect.php";
$success = "false";
session_start();
if (!isset($_SESSION["loggedin"])) {
  $_SESSION["loggedin"] = false;
}
if ($_SESSION["loggedin"] != true) {
  header("location: login.php");
} else {
  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $commentText = $_GET['commentText'];
    $currentPost = $_GET['currentPost'];
    $date = date("Y-m-d");
 
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $rows = [];
    if (strlen($commentText) > 0){
      $userID = $_SESSION['userID'];  
      //set sql to add new comment to the database
      $stmt = $conn->prepare("INSERT INTO Comments (creatorID, postID, comment, time, replyToID) 
      VALUES (?, ?, ?, ?, 0)");
      $stmt->bind_param("iiss", $userID, $currentPost, $commentText, $date);
      if ($stmt->execute() === TRUE) {
        $success = "true";
      } else {
        $success = "false";
      }
    }
  }
}
$rows[0] = $success;
echo json_encode($rows);
?>
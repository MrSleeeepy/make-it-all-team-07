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

    $postName = $_GET['postName'];
    $postText = $_GET['postText'];
    $currentTopic = $_GET['currentTopic'];
    $date = date("Y-m-d");

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $rows = [];
    if (strlen($postName) > 0){
      $userID = $_SESSION['userID'];  
      //set sql to add a new post into the database
      $stmt = $conn->prepare("INSERT INTO Posts (name, description, creatorID, time, limitedVisibility, topicID) 
      VALUES (?, ?, ?, ?, 0, ?)");
      $stmt->bind_param("ssisi", $postName, $postText, $userID, $date, $currentTopic);
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
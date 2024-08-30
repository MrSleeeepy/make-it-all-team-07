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
    $topic_name = $_GET['topicName'];
 
    // Variables for database connection
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $rows = [];
    if (strlen($topic_name) > 0){
      $userID = $_SESSION['userID'];
      //set sql to add a new topic into the database
      $stmt = $conn->prepare("INSERT INTO Topics (name, creatorID, limitedVisibility) 
      VALUES (?, ?, 0)");
      $stmt->bind_param("si", $topic_name, $userID);
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
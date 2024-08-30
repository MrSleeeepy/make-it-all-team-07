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

  $topic = $_GET['topic'];

  //sql to remove all data in the row containing the deleted topic
  $stmt = $conn->prepare("DELETE from Topics where topicID = ?");
  $stmt->bind_param("i", $topic);
  if ($stmt->execute()) {
    $success = "true";
  } else {
    $success = "false";
  }
}

//return true or false as to whether it worked or not
echo json_encode($success);
?>
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

  $postMessage = $_GET['postMessage'];
  $editText = $_GET['editText'];

  //sql to change the post message to a new input
  $stmt = $conn->prepare("UPDATE Posts SET description = ? WHERE postID = ?");
  $stmt->bind_param("si", $editText, $postMessage);
  if ($stmt->execute()) {
    $success = "true";
  } else {
    $success = "false";
  }
}

//return true or false as to whether it worked or not
echo json_encode($success);
?>
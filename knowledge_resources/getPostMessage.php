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

  //set sql to an sql statement that gets the post message from the postID
  $stmt = $conn->prepare("SELECT description, postID from Posts where postID = ?");
  $stmt->bind_param("i", $clickedPostID);
  $stmt->execute();
  $stmt->bind_result($description, $postID);
  while ($stmt->fetch()) {
    $rows[] = [$description, $postID];
  }
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>
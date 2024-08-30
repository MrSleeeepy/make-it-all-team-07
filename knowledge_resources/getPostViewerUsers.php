<?php
ini_set("display_errors",1);
error_reporting(-1);
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
  $rows = [];
  $postID = $_GET['postID'];

  //set sql to an sql statement that gets all users who can see a post
  $stmt = $conn->prepare("SELECT username from Users where
                        userID in (select userID from PostViewerUsers where
                        postID = '$postID')");
  $stmt->execute();
  $stmt->bind_result($result);
  while ($stmt->fetch()) {
    $rows[] = $result;
  }
}
echo json_encode($rows);
?>

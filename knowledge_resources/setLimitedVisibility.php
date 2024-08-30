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
  $rows = [];
  
  $postID = $_GET['postID'];
  $topicID = $_GET['topicID'];
  $userID = $_SESSION['userID'];

  if ($postID != -1){
    //set the limitedVisibility of the post to 1
    $stmt = $conn->prepare("UPDATE Posts set limitedVisibility = 1 where postID = ? AND
    ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
    $stmt->bind_param("iii", $postID, $userID, $userID);
    if ($stmt->execute() === TRUE) {
        $success = "true";
      } else {
        $success = "false";
    }
  } else {
    //no post clicked so set the limited visibility off the topic to 1
    $stmt2 = $conn->prepare("UPDATE Topics set limitedVisibility = 1 where topicID = ? AND
    ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
    $stmt2->bind_param("iii", $topicID, $userID, $userID);
    if ($stmt2->execute() === TRUE) {
        $success = "true";
      } else {
        $success = "false";
    }
  } 
  echo json_encode($success);
}
?>
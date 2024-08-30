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
    //gets the limitedVisibility of a post and the user must be a knowledgeAdmin or manager to run it
    $stmt = $conn->prepare("SELECT limitedVisibility from Posts where postID = ? AND
    ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
    $stmt->bind_param("iii", $postID, $userID, $userID);
    $stmt->execute();
    $stmt->bind_result($limitedVisibility);
    while ($stmt->fetch()) {
      $rows[] = $limitedVisibility;
    }
  } else {
    //gets the limitedVisibility of a topic and the user must be a knowledgeAdmin or manager to run it
    $stmt2 = $conn->prepare("SELECT limitedVisibility from Topics where topicID = ? AND
    ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
    $stmt2->bind_param("iii", $topicID, $userID, $userID);
    $stmt2->execute();
    $stmt2->bind_result($limitedVisibility);
    while ($stmt2->fetch()) {
      $rows[] = $limitedVisibility;
    }
  } 
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>
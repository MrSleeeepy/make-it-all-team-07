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

  $return = false;
  // Set userID to the userID in the session
  $userID = $_SESSION['userID'];
  $postID = $_GET['postID'];
  $groupID = $_GET['groupID'];

  // Set SQL to determine whether a user is a manager, a knowledge admin, or neither
  $stmt = $conn->prepare("SELECT manager, knowledgeAdmin FROM Users WHERE userID = ?;");
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $stmt->bind_result($result, $result2);
  while ($stmt->fetch()) {
    $rows[] = $result;
    $rows[] = $result2;
  }
  for ($i = 0; $i < count($rows); $i++) {
    if ($rows[$i] == 1) {
      $return = true;
    }
  }
  $conn -> close();

  if ($return == true) {
    $conn2 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    //set sql to delete the row containing the postID and groupID
    $stmt2 = $conn2->prepare("DELETE FROM PostViewerGroups WHERE (postID = ? AND groupID = ?)");
    $stmt2->bind_param("ii", $postID, $groupID);
    $stmt2->execute(); 
    if ($stmt2 -> affected_rows > 0){
      $success = "true";
    } else {
      $success = "false";
    }
  } else {$success = "false";}
}
echo json_encode($success);
?>
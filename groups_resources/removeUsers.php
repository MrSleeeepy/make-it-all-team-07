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
  $groupName = $_GET['groupName'];
  $username = $_GET['username'];
  // Set SQL to select the manager and knowledgeAdmin for a given user
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
    //set sql to an sql statement that removes a user from a group
    $stmt2 = $conn2->prepare("Delete from UsersGroups where 
    userID = (select userID from Users where username = ?) AND 
    groupID = (select groupID from `Groups` where name = ?);");
    $stmt2->bind_param("ss", $username, $groupName);
    if ($stmt2->execute()) {
      $success = "true";
    } else {
      $success = "false";
    }
  } else $success = "false";
}
echo json_encode($success);
?>
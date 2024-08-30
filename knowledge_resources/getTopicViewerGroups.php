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
  
  $topicID = $_GET['topicID'];
  $userID = $_SESSION['userID'];

  //set sql to get the groups that relate to a topic
  $stmt = $conn->prepare("SELECT groupID, name from `Groups` where
  (groupID in (select groupID from GroupsTopics where topicID = ?)) AND
  ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");

  $stmt->bind_param("iii", $topicID, $userID, $userID);
  $stmt->execute();
  $stmt->bind_result($groupID, $groupName);
  while ($stmt->fetch()) {
    $rows[] = [$topicID, $groupID, $groupName];
  }
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>
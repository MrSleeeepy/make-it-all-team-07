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
  $rows2 = [[]];
  $return = false;
  // Set userID to the userID in the session
  $userID = $_SESSION['userID'];
  // Set SQL to determine whether a user is a manager, a knowledge admin, or neither
  $stmt = $conn->prepare("SELECT manager, knowledgeAdmin FROM Users WHERE userID = ?;");
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $stmt->bind_result($result,$result2);
  while ($stmt->fetch()) {
    $rows[] = $result;
    $rows[] = $result2;
  }
  for ($i = 0 ;$i < count($rows); $i++){
      if ($rows[$i] == 1){
          $return = true;
      }
  }

  $conn -> close();

  if ($return == true){
    $conn2 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    
    //set sql to an sql statement that gets all topics as the user is a manager or knowledgeAdmin so can see everything
    $stmt2 = $conn2->prepare("SELECT name, topicID FROM Topics;");
    $stmt2->execute();
    $stmt2->bind_result($topicName, $topicID);
    while ($stmt2->fetch()) {
      $rows2[] = [$topicName, $topicID];
    }
    $conn2 -> close();
  } else{
    $conn3 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    //set sql to an sql statement that gets the topics specific to a user
    $stmt3 = $conn3->prepare("SELECT name, topicID FROM Topics WHERE topicID IN (SELECT topicID FROM GroupsTopics WHERE groupID IN (
      SELECT groupID FROM `Groups` WHERE groupID IN (SELECT groupID FROM UsersGroups WHERE userID = ?))) OR
      limitedVisibility = 0");
    $stmt3->bind_param("i", $userID);
    $stmt3->execute();
    $stmt3->bind_result($topicName, $topicID);
    while ($stmt3->fetch()) {
      $rows2[] = [$topicName, $topicID];
    }
    $conn3 -> close();
  }

}
//encode the return array and return it to the javascript
echo json_encode($rows2);
?>
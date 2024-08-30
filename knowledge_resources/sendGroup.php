<?php
include "../Database_config/database-connect.php";
$success = "false";
session_start();
if (!isset($_SESSION["loggedin"])) {
  $_SESSION["loggedin"] = false;
}
if ($_SESSION["loggedin"] != true) {
  header("location: login.php");
} else {
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $topicName = $_GET['topicName'];
    $groupName = $_GET['groupName'];
    $postName = $_GET['postName'];
    $userID = $_SESSION['userID'];

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $rows = [];

    //check the request has come from a manager or knowledgeAdmin
    $return = false;
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
    if ($return = true) {
      $conn2 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
      if ($postName == "") {
        //set sql to an sql statement that inserts the group and topic selected into GroupsTopics
        $stmt2 = $conn2->prepare("INSERT INTO GroupsTopics (groupID, topicID) 
        VALUES ((select groupID from `Groups` where name = ?), (select topicID from Topics where name = ?))");
        $stmt2->bind_param("ss", $groupName, $topicName);
        if ($stmt2->execute() === TRUE) {
          $success = "true";
        } else {
          $success = "false";
        }
      } else {
        //set sql to an sql statement that inserts the post and group into postViewerGroup
        $stmt3 = $conn2->prepare("INSERT INTO PostViewerGroups (postID, groupID) 
        VALUES ((select postID from Posts where name = ? and topicID in (select topicID from Topics where name = ?)), (select groupID from `Groups` where name = ?))");
        $stmt3->bind_param("sss", $postName, $topicName, $groupName);
        if ($stmt3->execute() === TRUE) {
          $success = "true";
        } else {
          $success = "false";
        }
      }
    } else{
      $success = "false";
    } 
  }
}

$returnArray[0] = $success;
echo json_encode($returnArray);
?>
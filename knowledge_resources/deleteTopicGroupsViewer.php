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
  $topicID = $_GET['topicID'];
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
    //set sql statements handle deleting a groups access to a topic by deleting them from groupsTopics
    //then removing that groups users from postViewerGroups and postVieweruser if they dont have access through another group
    $stmt2 = $conn2->prepare("DELETE FROM GroupsTopics WHERE (topicID = ? AND groupID = ?)");
    $stmt2->bind_param("ii", $topicID, $groupID);
    $stmt2->execute(); 


    $stmt3 = $conn2->prepare("DELETE FROM PostViewerUsers 
    WHERE userID IN (
        SELECT userID 
        FROM UsersGroups 
        WHERE groupID = ? 
        AND userID NOT IN (
            SELECT userID 
            FROM UsersGroups 
            WHERE groupID != ? 
            AND groupID IN (
                SELECT groupID 
                FROM GroupsTopics 
                WHERE topicID = ?
            )
        )
    )
    AND postID IN (
        SELECT postID 
        FROM Posts 
        WHERE topicID = ?
    );");
    $stmt3->bind_param("iiii", $groupID, $groupID, $topicID, $topicID);
    $stmt3->execute();


    $stmt4 = $conn2->prepare("DELETE FROM PostViewerGroups
    WHERE groupID = ?
      AND postID IN (
          SELECT postID
          FROM Posts
          WHERE topicID = ?
    );");
    $stmt4->bind_param("ii", $groupID, $topicID);
    $stmt4->execute();

    $conn2->close();

    if ($stmt2 -> affected_rows > 0){
      $success = "true";
    } else {
      $success = "false";
    }
  } else {$success = "false";}
}
echo json_encode($success);
?>
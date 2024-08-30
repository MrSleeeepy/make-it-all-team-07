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
  $groupID = $_GET['groupID'];
  $user_array = [];
  // Set SQL to select topics that can be viewed by the groups that the userID is in
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
    //set sql to an sql statement that gets all groups
    $stmt2 = $conn2->prepare("SELECT DISTINCT userID 
    FROM PostViewerUsers 
    WHERE postID IN (
        SELECT postID 
        FROM Posts 
        WHERE topicID IN (
            SELECT DISTINCT GroupsTopics.topicID 
            FROM GroupsTopics 
            JOIN Topics ON GroupsTopics.topicID = Topics.topicID 
            WHERE GroupsTopics.groupID = ? OR Topics.limitedVisibility = 0
        )
    ) 
    AND userID NOT IN (
        SELECT userID 
        FROM UsersGroups 
        WHERE groupID IN (
            SELECT groupID 
            FROM GroupsTopics 
            WHERE topicID IN (
                SELECT DISTINCT GroupsTopics.topicID 
                FROM GroupsTopics 
                JOIN Topics ON GroupsTopics.topicID = Topics.topicID 
                WHERE GroupsTopics.groupID = ?
            ) 
            AND groupID != ?
            
            UNION			
                   
            SELECT userID  
            FROM UsersGroups
            WHERE groupID IN (
                SELECT groupID
                FROM PostViewerGroups 
                WHERE postID IN (
                    SELECT postID 
                    FROM Posts 
                    WHERE topicID IN (
                        SELECT DISTINCT GroupsTopics.topicID 
                        FROM GroupsTopics 
                        JOIN Topics ON GroupsTopics.topicID = Topics.topicID 
                        WHERE GroupsTopics.groupID = ?
                    )
                )
            ) OR (
                    SELECT COUNT(*) 
                    FROM PostViewerGroups 
                    WHERE postID IN (
                        SELECT postID 
                        FROM Posts 
                        WHERE topicID IN (
                            SELECT DISTINCT GroupsTopics.topicID 
                            FROM GroupsTopics 
                            JOIN Topics ON GroupsTopics.topicID = Topics.topicID 
                            WHERE GroupsTopics.groupID = ? OR Topics.limitedVisibility = 0
                        )
                    )
            ) IS NULL
        )
    );");
    $stmt2->bind_param("iiiii", $groupID, $groupID, $groupID, $groupID, $groupID);
    
    $stmt2->execute();
    $stmt2->bind_result($oneUser);
    while ($stmt2->fetch()) {
      $user_array[] = $oneUser;
    }
    $stmt2->close();


    for ($i = 0; $i < count($user_array); $i++){
      $conn3 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

      $stmt3 = $conn3->prepare("DELETE FROM PostViewerUsers 
      WHERE userID IN (
          ?
      )   
      AND postID IN (
          SELECT postID 
          FROM Posts 
          WHERE topicID IN (
              SELECT DISTINCT GroupsTopics.topicID 
              FROM GroupsTopics 
              JOIN Topics ON GroupsTopics.topicID = Topics.topicID 
              WHERE GroupsTopics.groupID = ? OR Topics.limitedVisibility = 0
          )
      );
      ");
      $stmt3->bind_param("ii", $user_array[$i], $groupID);
      $stmt3->execute();
      $stmt3->close();
    }


    $conn4 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    $stmt4 = $conn4->prepare("DELETE FROM UsersGroups WHERE groupID = ?");
    $stmt4->bind_param("i", $groupID);
    $stmt4->execute();
    $stmt4->close();


    $conn1 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    $stmt1 = $conn1->prepare("DELETE FROM `Groups` WHERE groupID = ?");
    $stmt1->bind_param("i", $groupID);
    $stmt1->execute();
    $stmt1->close();



    $success = "true";

  } else $success = false;
}
echo json_encode([$success]);
?>
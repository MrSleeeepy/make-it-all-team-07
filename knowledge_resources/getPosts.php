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
  $rows2 = [[]];
  $return = false;
  // Set userID to the userID in the session
  $userID = $_SESSION['userID'];
  $topicID = $_GET['topicID'];
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
  //if a user is a knowledge admin or a manager
  if ($return == true){
    $conn2 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    
    //set sql to an sql statement that gets all posts as the user is a manager or knowledgeAdmin so can see everything
    $stmt2 = $conn2->prepare("SELECT name, postID FROM Posts where topicID = ?;");
    $stmt2->bind_param("i", $topicID);
    $stmt2->execute();
    $stmt2->bind_result($postName, $postID);
    while ($stmt2->fetch()) {
      $rows2[] = [$postName, $postID];
    }
    $conn2 -> close();
  } else{
    $conn3 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    //set sql to an sql statement that gets the posts that have no limited visibility, 
    //or where the user is in the group that can see the limited visibility post or the user has access through postViewerUsers
    $stmt3 = $conn3->prepare("SELECT name, postID 
    FROM Posts 
    WHERE ((topicID = ?) 
           AND limitedVisibility = 0
          ) 
           OR (
               postID IN (SELECT postID FROM PostViewerGroups 
               WHERE groupID in (SELECT groupID FROM UsersGroups 
                WHERE userID = ?
                   )
               ) 
                          AND topicID = ?          
           ) OR (
               limitedVisibility = 1 
               AND (postID IN (
                   SELECT PostViewerUsers.postID 
                   FROM PostViewerUsers
                   JOIN Posts
                   WHERE PostViewerUsers.userID = ? 
                   AND Posts.topicID = ?
               )
                   )
           );");
    $stmt3->bind_param("iiiii", $topicID, $userID, $topicID, $userID, $topicID);
    $stmt3->execute();
    $stmt3->bind_result($postName, $postID);
    while ($stmt3->fetch()) {
      $rows2[] = [$postName, $postID];
    }
    $conn3 -> close();
  }

}
//encode the return array and return it to the javascript
echo json_encode($rows2);
?>
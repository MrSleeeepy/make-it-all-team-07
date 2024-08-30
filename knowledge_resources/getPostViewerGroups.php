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
  
  $postID = $_GET['postID'];
  $userID = $_SESSION['userID'];


  //set sql to gets the postID, groupID, groupname where the groups have acces to that post 
  $stmt = $conn->prepare("SELECT p.postID, p.groupID, g.name from PostViewerGroups as p left join `Groups` as g ON p.groupID = g.groupID where p.postID = ? AND
  ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
  $stmt->bind_param("iii", $postID, $userID, $userID);
  $stmt->execute();
  $stmt->bind_result($postID, $groupID, $groupName);
  while ($stmt->fetch()) {
    $rows[] = [$postID, $groupID, $groupName];
  }
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>


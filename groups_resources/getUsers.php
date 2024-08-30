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

  $group = $_GET['groupName'];
  $rows = [];


//set sql to an sql statement that gets all topics that can be viewed by the groups that the userID is in
$stmt = $conn->prepare("SELECT username from Users where
UserID in (select userID from UsersGroups where groupID in 
(SELECT groupID from `Groups` where name = ?));");
$stmt->bind_param("s", $group);
$stmt->execute();
$stmt->bind_result($result);
while ($stmt->fetch()) {
    $rows[] = $result;
}


}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>

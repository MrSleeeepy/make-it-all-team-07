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

  //set sql to an sql statement that gets all groups
  $stmt = $conn->prepare("SELECT name, groupID from `Groups`");
  $stmt->execute();
  $stmt->bind_result($groupName, $groupID);
  while ($stmt->fetch()) {
    $rows[] = [$groupName, $groupID];
  }
}
echo json_encode($rows);
?>
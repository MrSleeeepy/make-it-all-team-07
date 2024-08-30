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
// Set SQL to determine whether a user is a manager, knowledge admin, both, or neither
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
}
echo json_encode($return);
?>

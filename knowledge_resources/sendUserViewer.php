<?php
ini_set("display_errors",1);
error_reporting(-1);
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
    $newUser = $_GET['newUser'];
    $postID = $_GET['postID'];
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

    $conn2 = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($return == true) {
      //Insert the postID and the newUsers ID into PostViewerUsers
        $stmt2 = $conn2->prepare("INSERT INTO PostViewerUsers (postID, userID) 
        VALUES (?, (select userID from Users where username = ?));");
        $stmt2->bind_param("is", $postID, $newUser);
        if ($stmt2->execute() === TRUE) {
          $success = "true";
        } else {
          $success = "false";
        }
    } else{
      $success = "false";
    } 
  }
}

$returnArray[0] = $success;
echo json_encode($returnArray);
?>
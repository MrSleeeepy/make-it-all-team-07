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
  
    $groupName = $_GET['groupName'];
 
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    if (strlen($group_name) > 0){
        $userID = $_SESSION['userID'];
        
        $stmt = $conn->prepare("INSERT INTO `Groups` (name) VALUES (?) where exists ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1);");
        $stmt->bind_param("sii", $groupName, $userID, $userID);
        if ($stmt->execute()) {
          $success = "true";
        } else {
          $success = "false";
        }
    }
}
}
$return = [];
$return[0] = $success;
echo json_encode($return);
?>
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
    $username = $_GET['username'];
    $groupName = $_GET['groupName'];
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    if (strlen($groupName) > 0){
        $userID = $_SESSION['userID'];
        //set sql to an sql statement that inserts the user into the group given a username and groupname
        $sql = "INSERT INTO UsersGroups (userID, groupID)
          SELECT u.userID, g.groupID
          FROM Users u,`Groups` g
          WHERE g.name = '$groupName'
          AND u.username = '$username';";
        if ($conn->query($sql) === TRUE) {
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
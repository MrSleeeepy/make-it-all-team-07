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
    //set sql to an sql statement that gets all usernames
    $stmt = $conn->prepare("SELECT username from Users");
    $stmt->execute();
    $stmt->bind_result($result);
    while ($stmt->fetch()) {
        $rows[] = $result;
    } 
}
//encode the return array and return it to the javascript
echo json_encode($rows);
?>
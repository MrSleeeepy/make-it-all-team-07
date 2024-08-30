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
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    $rows = [];
    $topicName = $_GET['topicName'];
    $userID = $_SESSION['userID'];

    //set sql to get viewers with access to the topic either via a group or via the topic having unlimited visibility
    $stmt = $conn->prepare("SELECT username from `Users` where userID in (select userID from UsersGroups where groupID in (select groupID from `Groups` where groupID in (select groupID from `GroupsTopics` where topicID in (select topicID from Topics where name = ?)))) 
        AND ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1) UNION 
        SELECT username from `Users` where ((select limitedVisibility from Topics where name = ?) = 0)
        AND ((select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1)
        ");
    $stmt->bind_param("siisii",$topicName, $userID, $userID, $topicName, $userID, $userID);
    $stmt->execute();
    $stmt->bind_result($result);
    while ($stmt->fetch()) {
        $rows[] = $result;
    }
}

echo json_encode($rows);
?>
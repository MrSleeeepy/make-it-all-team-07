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
    $postName = $_GET['postName'];
    $topicName = $_GET['topicName'];
    $userID = $_SESSION['userID'];
    if ($postName == "") {
        //set sql to an sql statement that gets all groups as to fill the viewer modal topic dropdown and check the user running it is an admin
        $stmt = $conn->prepare("SELECT name from `Groups` where (select manager from Users where userID = ?) = 1 or (select knowledgeAdmin from Users where userID = ?) = 1");
        $stmt->bind_param("ii", $userID, $userID);
        $stmt->execute();
        $stmt->bind_result($result);
        while ($stmt->fetch()) {
            $rows[] = $result;
        }
    } else {
        //set sql to an sql statement that gets the groups who can view a topic with limited visibility or all groups if the topic has unlimited visibility
        $stmt = $conn->prepare("SELECT name from `Groups` where groupID in 
        (select groupID from GroupsTopics where (topicID = (select topicID from Topics where name = ?))) AND
        (((select manager from Users where userID = ?) = 1) or ((select knowledgeAdmin from Users where userID = ?) = 1))
        UNION
        SELECT name from `Groups` where ((select limitedVisibility from Topics where name = ?) = 0) AND
        (((select manager from Users where userID = ?) = 1) or ((select knowledgeAdmin from Users where userID = ?) = 1));");
        $stmt->bind_param("siisii", $topicName, $userID, $userID, $topicName, $userID, $userID);
        $stmt->execute();
        $stmt->bind_result($result);
        while ($stmt->fetch()) {
            $rows[] = $result;
        }
    }


}
echo json_encode($rows);
?>
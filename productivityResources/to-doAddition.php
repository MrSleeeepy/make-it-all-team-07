<?php
include "../Database_config/database-connect.php";
session_start();
$userID=$_SESSION["userID"];
$detail=$_POST["detail"];
$taskID=$_POST["taskID"];

if($_POST["taskID"]=="none") {
    /*if there is no task associated with the to-do to be added, set the
    below variable to false*/
    $hasAssociatedTask=false;
}
else {
    $hasAssociatedTask=true;
}

$hasAccessToTask=false;
/*The below variable is used for the userID associated with the task that the to-do is to
be associated to, if the to-do is to be associated to a task*/
$resultUserID=0;
try {
    if($hasAssociatedTask) {
        $connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
    if(!$connection) {
        die("Connection failed: ".mysqli_connect_error());
    }
        /*statement to find the userID associated with the task
        that the to-do is to be associated to*/
        $taskCheck=mysqli_prepare($connection,"SELECT userID FROM Tasks
        WHERE taskID=?");
        mysqli_stmt_bind_param($taskCheck,"i",$taskID);
        mysqli_stmt_execute($taskCheck);
        $result=mysqli_stmt_bind_result($taskCheck,$resultUserID);
        mysqli_stmt_fetch($taskCheck);
        /*if the userID associated with the task that the to-do is to be
        associated to is that of the currently logged in user,
        the currently logged in user has access to the task as it is one of
        theirs*/
        if($resultUserID==$userID) {
            $hasAccessToTask=true;
        }
        mysqli_close($connection);
        //connect to database
        $newConnection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);
        if(!$newConnection) {
            die("Connection failed: ".mysqli_connect_error());
        }
        if($hasAccessToTask) {
            /*statement to insert the to-do item into the database with the given detail, taskID
            and the ID of the currently logged in user*/
            $statement=mysqli_prepare($newConnection,"INSERT INTO `ToDos` (detail,taskID,userID)
            VALUES(?,?,?);");
            mysqli_stmt_bind_param($statement,"sii",$detail,$taskID,$userID);
            mysqli_stmt_execute($statement);
            echo("true");
        }
        else {
            //do nothing
        }
        mysqli_close($newConnection);
    
    }
    else {//if the to-do item to be added doesn't have a task associated with it
        //connect to database
        $connectionNoAssociatedTask=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);
        if(!$connectionNoAssociatedTask) {
            die("Connection failed: ".mysqli_connect_error());
        }
        /*statement to insert the to-do item into the database with the given detail, taskID
        and the ID of the currently logged in user*/
        $statementNoAssociatedTask=mysqli_prepare($connectionNoAssociatedTask,"INSERT INTO `ToDos` (detail,userID)
        VALUES(?,?);");
        mysqli_stmt_bind_param($statementNoAssociatedTask,"si",$detail,$userID);
        mysqli_stmt_execute($statementNoAssociatedTask);
        echo("true");
        mysqli_close($connectionNoAssociatedTask);
    }
}
catch (Exception $e) {
    echo("false");
}


    
  
    
    
?>
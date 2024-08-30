<?php
include "../Database_config/database-connect.php";
session_start();

$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}
/*get ID of the to-do item to be marked as incomplete 
and the ID of the currently logged in user*/
$IDofToDoToUncomplete=$_POST["to-doID"];
$userID=$_SESSION["userID"];
try {
    //statement to mark the to-do item as incomplete
    /*set completionDate to NULL as to-do is being marked
    as incomplete*/
    $statement=mysqli_prepare($connection,"UPDATE ToDos
    SET isCompleted=0, completionDate=NULL
    WHERE toDoID=?
    AND userID=?");
    mysqli_stmt_bind_param($statement,"ii",$IDofToDoToUncomplete,$userID);
    mysqli_stmt_execute($statement);
    $result=mysqli_stmt_get_result($statement);
    echo("true");
}
catch (Exception $e) {
    echo("false");
}
finally {
    mysqli_close($connection);
}
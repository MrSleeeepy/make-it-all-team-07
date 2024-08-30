<?php
include "../Database_config/database-connect.php";
session_start();

$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}
/*get ID of the to-do item to be completed and the ID
of the currently logged in user*/
$IDofToDoToComplete=$_POST["to-doID"];
$userID=$_SESSION["userID"];
try {
    //statement to mark the to-do item as complete
    $statement=mysqli_prepare($connection,"UPDATE ToDos
    SET isCompleted=1,completionDate=CURRENT_DATE()
    WHERE toDoID=?
    AND userID=?");
    mysqli_stmt_bind_param($statement,"ii",$IDofToDoToComplete,$userID);
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


?>
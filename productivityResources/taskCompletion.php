<?php
include "../Database_config/database-connect.php";
session_start();

$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}
$IDofTaskToComplete=$_POST["taskID"];
try {
    $statement=mysqli_prepare($connection,"UPDATE Tasks
    SET isCompleted=1
    WHERE taskID=?
    AND userID=?");
    mysqli_stmt_bind_param($statement,"ii",$IDofTaskToComplete,$_SESSION['userID']);
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
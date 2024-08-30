<?php
include "../Database_config/database-connect.php";
session_start();


$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}


$reasonForRetrieval=$_POST["reason"]; //see what needs to be retrieved from the database

$statement=mysqli_stmt_init($connection);
$userID = $_SESSION['userID'];

switch($reasonForRetrieval) {//retrieve the required task data from the required columns
    case "displayTaskListAllProjects":
        mysqli_stmt_prepare($statement,"SELECT taskID AS ID,name,description,highPriority,isCompleted 
        FROM Tasks 
        WHERE userID=?
        AND isCompleted=0
        ORDER BY name;");
        mysqli_stmt_bind_param($statement,"i",$userID);
        break;
    case "displayCompletedTaskListAllProjects":
        mysqli_stmt_prepare($statement,"SELECT taskID AS ID,name 
        FROM Tasks 
        WHERE userID=?
        AND isCompleted=1
        ORDER BY name;");
        mysqli_stmt_bind_param($statement,"i",$userID);
        break;
    case "displayTaskList":
        $selectedProjectID=$_POST['projectID'];
        mysqli_stmt_prepare($statement,"SELECT taskID AS ID,name,description,highPriority,isCompleted,projectID
        FROM Tasks
        WHERE userID=?
        AND projectID=?
        AND isCompleted=0
        ORDER BY name;");
        mysqli_stmt_bind_param($statement,"ii",$userID,$selectedProjectID);
        break;
    case "displayTaskNames": //for the dropdown when adding a To-do item
        mysqli_stmt_prepare($statement,"SELECT taskID AS ID,name
        FROM Tasks
        WHERE userID=?
        AND isCompleted=0
        ORDER BY name;");
        mysqli_stmt_bind_param($statement,"i",$userID);
        break;
    case "displayTaskDetails": 
        $selectedTaskID=$_POST['taskID'];
        mysqli_stmt_prepare($statement,"SELECT taskID AS ID,name,highPriority,isCompleted,deadline,timeEstimate,description,dateCreated
        FROM Tasks
        WHERE taskID=?
        AND userID=?;");
        mysqli_stmt_bind_param($statement,"ii",$selectedTaskID,$userID);
        break;

}
mysqli_stmt_execute($statement);


$result=mysqli_stmt_get_result($statement);


if(mysqli_num_rows($result)>0) {//put each received row as an associative array into a 2D array
    while($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
        $retrievedData[]=$row;
    }
}
else{
    $retrievedData=[];
}
echo json_encode($retrievedData);//convert this 2D array into JSON so it can be read in the JS
mysqli_close($connection);

?>


<?php
include "../Database_config/database-connect.php";
session_start();
$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database with credentials
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}
$reasonForRetrieval=$_POST["reason"];/*see what data needs to be queried from the database based on the Fetch request
from the JS*/

$statement=mysqli_stmt_init($connection);

$userID = $_SESSION['userID'];

switch($reasonForRetrieval) {
    /*Query the database for the relevant data, depending on what project data is needed*/
    case "displayProjectList":
        mysqli_stmt_prepare($statement,"SELECT DISTINCT Projects.projectID AS ID,Projects.name 
        FROM Projects, Tasks
        WHERE Tasks.userID=? 
        AND Projects.projectID=Tasks.projectID
        AND Tasks.isCompleted=0;");
        mysqli_stmt_bind_param($statement,"i",$userID);
        break;
    case "displayProjectDetails":
        $selectedProjectID=$_POST['projectID'];
        mysqli_stmt_prepare($statement,"SELECT DISTINCT Projects.projectID AS ID,Projects.name, Projects.description,Projects.dateCreated,Projects.deadline,Users.firstName,Users.surname
        FROM Projects, Tasks, Users 
        WHERE Tasks.userID=? 
        AND Projects.projectID=?
        AND Users.userID=Projects.teamLeader
        AND Projects.projectID=Tasks.projectID;");
        mysqli_stmt_bind_param($statement,"ii",$userID,$selectedProjectID);
        break;
}
mysqli_stmt_execute($statement);
$result=mysqli_stmt_get_result($statement);


if(mysqli_num_rows($result)>0) {//put each received row (as an associative array) from the database into an indexed array
    while($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
        $retrievedData[]=$row;
    }
}
else{
    $retrievedData=[];
}
echo json_encode($retrievedData);//convert this 2D array into a JSON array of JSON objects so it can be read in the JS
mysqli_close($connection);
?>
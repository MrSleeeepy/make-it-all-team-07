<?php
include "../Database_config/database-connect.php";
session_start();

$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database with credentials
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}

$statement=mysqli_stmt_init($connection);

$userID = $_SESSION['userID'];
//retrieve the required to-do data from the required columns
/*use a union to retrieve all incomplete to-do items and
all completed ones which have been completed for less than
7 days*/
mysqli_stmt_prepare($statement,"SELECT toDoID AS ID, detail, taskID, isCompleted
FROM ToDos
WHERE userID=?
AND isCompleted=0
UNION
SELECT toDoID AS ID, detail, taskID, isCompleted
FROM ToDos
WHERE userID=?
AND isCompleted=1
AND DATEDIFF(CURRENT_DATE(),completionDate)<7;");
mysqli_stmt_bind_param($statement,"ii",$userID,$userID);
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

echo(json_encode($retrievedData)); //convert this 2D array into JSON so it can be read in the JS
mysqli_close($connection);
?>
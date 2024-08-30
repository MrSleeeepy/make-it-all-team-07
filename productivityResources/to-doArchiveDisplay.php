<?php
include "../Database_config/database-connect.php";
session_start();
$connection=mysqli_connect($dbservername,$dbusername,$dbpassword,$dbname);//connect to database
if(!$connection) {
    die("Connection failed: ".mysqli_connect_error());
}

$userID=$_SESSION["userID"];

try {
    /*retrieve the detail and completion date of any of the user's
    completed to-do items that were completed 7 or more days ago, showing the
    most recently completed first */
    $statement=mysqli_prepare($connection,"SELECT detail,completionDate
    FROM ToDos 
    WHERE userID=?
    AND isCompleted=1
    AND DATEDIFF(CURRENT_DATE(),completionDate)>=7
    ORDER BY completionDate DESC;");
    mysqli_stmt_bind_param($statement,"i",$userID);
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
    echo(json_encode($retrievedData));
}
catch (Exception $e) {
    echo("false");
}
finally {
    mysqli_close($connection);
}
?>
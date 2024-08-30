<?php 
include "../Database_config/database-connect.php";
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$type = $_POST['type'];

session_start();
$current_user_id = $_SESSION["userID"];

$stmt = $conn->prepare("SELECT manager FROM Users WHERE userID = ?");
$stmt->bind_param("i", $current_user_id);
$isManager = runPermissionQuery($stmt)[0]['manager'];

$stmt = $conn->prepare("SELECT projectID FROM Projects WHERE teamLeader = ?");
$stmt->bind_param("i", $current_user_id);
$teamLeaderOfProjectsObjectArray = runPermissionQuery($stmt);
$teamLeaderProjectIDS = array();
foreach($teamLeaderOfProjectsObjectArray as &$teamLeaderRow) {
    array_push($teamLeaderProjectIDS, $teamLeaderRow['projectID']);
}

switch ($type) {
    case 'taskCompletionChange': // Changes the isCompleted field of a given task to given value
        $taskID = $_POST['taskID'];
        $changeTo = $_POST['changeTo'];
        if (!is_null($taskID) && !is_null($changeTo)) {
            $stmt = $conn->prepare("UPDATE Tasks 
            SET isCompleted = ? 
            WHERE taskID = ? 
            AND EXISTS(
                SELECT 1 
                FROM Users 
                WHERE userID = ? 
                AND manager = 1
                
                OR 
                
                projectID IN (
                    SELECT projectID 
                    FROM Projects 
                    WHERE teamLeader = ?
                )
            );");
            $stmt->bind_param("iiii", $changeTo, $taskID, $current_user_id, $current_user_id);
            runQuery($stmt);

        } else {
            echo json_encode("Error: 'taskID' or 'changeTo' is null");
        }
        break;

    case 'deleteTask':
        $taskID = $_POST['taskID'];
        if (!is_null($taskID)) {
            $stmt = $conn->prepare("DELETE FROM Tasks 
            WHERE Tasks.taskID = ? 
            AND EXISTS(
                SELECT 1 
                FROM Users 
                WHERE userID = ? 
                AND manager = 1
                
                OR 
                
                projectID IN (
                    SELECT projectID 
                    FROM Projects 
                    WHERE teamLeader = ?
                )
            );");
            $stmt->bind_param("iii", $taskID, $current_user_id, $current_user_id);
            runQuery($stmt);

        } else {
            echo json_encode("Error: 'taskID' is null");
        }
        break; 

    case 'taskPriorityChange':
        $taskID = $_POST['taskID'];
        $changeTo = $_POST['changeTo'];
        if (!is_null($taskID)) {
            $stmt = $conn->prepare("UPDATE Tasks 
            SET highPriority = ? 
            WHERE Tasks.taskID = ? 
            AND EXISTS(
                SELECT 1 
                FROM Users 
                WHERE userID = ? 
                AND manager = 1
                
                OR 
                
                projectID IN (
                    SELECT projectID 
                    FROM Projects 
                    WHERE teamLeader = ?
                )
            );");
            $stmt->bind_param("iiii",$changeTo, $taskID, $current_user_id, $current_user_id);
            runQuery($stmt);

        } else {
            echo json_encode("Error: 'taskID' is null");
        }
        break; 

    case 'createTask':
        $taskName = $_POST['taskName'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $deadline = $_POST['deadline'];
        $highPriority = $_POST['highPriority'];
        $projectID = $_POST['projectID'];
        $userID = $_POST['userID'];

        if (!is_null($taskName)) {
            $stmt = $conn->prepare("INSERT INTO Tasks 
            (taskID, name, description, timeEstimate, dateCreated, deadline, highPriority, isCompleted, projectID, userID) 
            SELECT NULL, ?, ?, ?, CURRENT_DATE(), ?, ?, 0, ?, ?
            WHERE EXISTS(
                SELECT 1 
                FROM Users 
                WHERE userID = ? 
                AND manager = 1
                
                OR (
                    SELECT teamLeader
                    FROM Projects
                    WHERE projectID = ?
                ) = ?
            );");
            $stmt->bind_param("ssisiiiiii",$taskName, $description, $duration, $deadline, $highPriority, $projectID, $userID, $current_user_id, $projectID, $current_user_id);
            runQuery($stmt);

        } else {
            echo json_encode("Error: 'taskID' is null");
        }
        break;
        
    case 'create_new_project': // insert a new project with the given data
        $name = $_POST['name'];
        $description = $_POST['description'];
        $deadline = $_POST['deadline'];
        $teamLeader = $_POST['teamLeader'];
        if ($description == ""){
            $stmt = $conn->prepare("INSERT INTO Projects (projectID, name, description, dateCreated, deadline, teamLeader)
            SELECT NULL, ?, 'Waiting to add description', CURRENT_DATE(), ?, ? 
            WHERE EXISTS (
                SELECT 1 FROM Users
                WHERE userID = ?
                AND manager = 1);");
            $stmt->bind_param("ssii", $name, $deadline, $teamLeader, $current_user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO Projects (projectID, name, description, dateCreated, deadline, teamLeader)
            SELECT NULL, ?, ?, CURRENT_DATE(), ?, ? 
            WHERE EXISTS (
                SELECT 1 FROM Users
                WHERE userID = ?
                AND manager = 1);");
            $stmt->bind_param("sssii", $name, $description, $deadline, $teamLeader, $current_user_id);
        }
        
        runQuery($stmt);
            
        break;

    case 'toggleManagerRole':
        $userID = $_POST['userID'];
        $changeTo = $_POST['changeTo'];
        if (!is_null($userID) && !is_null($changeTo)) {
            if ($isManager == 1) {
                $stmt = $conn->prepare("UPDATE Users SET manager = ? WHERE Users.userID = ?; ");
                $stmt->bind_param("ii", $changeTo, $userID);
                runQuery($stmt);
            } else {
                echo json_encode("Error: user isn't a manager");
            }
                     
        } else {
            echo json_encode("Error: 'taskID' or 'changeTo' is null");
        }
        break; 

    case 'toggleKnowledgeAdmin':
        $userID = $_POST['userID'];
        $changeTo = $_POST['changeTo'];
        if (!is_null($userID) && !is_null($changeTo)) {
            if ($isManager == 1) {
                $stmt = $conn->prepare("UPDATE Users SET knowledgeAdmin = ? WHERE Users.userID = ?; ");
                $stmt->bind_param("ii", $changeTo, $userID);
                runQuery($stmt);
            } else {
                echo json_encode("Error: user isn't a manager");
            }
            
        } else {
            echo json_encode("Error: 'taskID' or 'changeTo' is null");
        }
        break; 

    case 'deleteProject':
        $projectID = $_POST['projectID'];
        if (!is_null($projectID)) {
            $stmt = $conn->prepare("DELETE FROM Projects WHERE Projects.projectID = ? AND EXISTS( SELECT 1 FROM Users WHERE userID = ? AND manager = 1);");
            $stmt->bind_param("ii", $projectID, $current_user_id);
            runQuery($stmt);
            
        } else {
            echo json_encode("Error: 'projectID' is null");
        }
        break; 

    case 'changeTeamLeader':
        $projectID = $_POST['projectID'];
        $userID = $_POST['userID'];
        if (!is_null($projectID) && !is_null($userID)) {

            if ($isManager == 1 || in_array($projectID, $teamLeaderProjectIDS)) {
                $stmt = $conn->prepare("UPDATE Projects SET teamLeader = ? WHERE Projects.projectID = ? ");
                $stmt->bind_param("ii", $userID, $projectID);
                runQuery($stmt);
            } else {
                echo json_encode("Error: user isn't a manager or teamleader of this project");
            }
            
        } else {
            echo json_encode("Error: 'projectID' is null");
        }
        break; 

    case 'deleteUser': // Removes a user from the database, associated tasks also removed from cascade rule in database
        $userID = $_POST['userID'];
        if (!is_null($userID)) {
            if ($isManager == 1) {
                $stmt = $conn->prepare("DELETE FROM Users WHERE Users.userID = ?");
                $stmt->bind_param("i", $userID);
                runQuery($stmt);
            } else {
                echo json_encode("Error: user isn't a manager");
            }
            

        } else {
            echo json_encode("Error: 'projectID' is null");
        }
        break; 

    default:
        echo json_encode("Error: Paramater 'type' invalid");
        break;
}

function runQuery($stmt){
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode("Success");

    } else {
        echo json_encode("Error: No fields in table updated");
    }
    $stmt->close();
}

function runPermissionQuery($stmt){
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;   
}

$conn->close();
?>

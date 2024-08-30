<?php 
include "../Database_config/database-connect.php";
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

$type = $_GET['type'];

session_start();
$current_user_id = $_SESSION["userID"];


if (true) {
    switch ($type) {
        case 'projName': 
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT name FROM Projects WHERE projectID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1)");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            break; 

        case 'projTaskCount': 
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT COUNT(taskID) AS tasks FROM Tasks WHERE projectID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1);");
            $stmt->bind_param("i", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            break; 
    
        case 'tasks_overdue': // Returns the number of on track and overdue tasks for a given project (via comparison to deadline date)
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT Projects.projectID, Projects.name, COUNT(CASE WHEN isCompleted = 1 THEN taskID END) as completed, COUNT(CASE WHEN Tasks.deadline >= CAST(NOW() AS date) AND isCompleted = 0 THEN taskID END) AS onTrack, COUNT(CASE WHEN Tasks.deadline < CAST(NOW() AS date) AND isCompleted = 0 THEN taskID END) AS overdue FROM Projects LEFT JOIN Tasks ON Tasks.projectID = Projects.projectID WHERE Projects.projectID = ? AND (Projects.projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1) GROUP BY Projects.projectID");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            
            break; 
    
        case 'emp_task_breakdown_proj': // Returns the number of on track and overdue tasks for each employee of a given project
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT Users.userID, Users.firstName, Users.surname, COUNT(CASE WHEN isCompleted = 1 THEN taskID END) AS completed,
                                    COUNT(CASE WHEN deadline >= CAST(NOW() AS DATE) AND isCompleted = 0 THEN taskID END) AS onTrack,
                                    COUNT(CASE WHEN deadline < CAST(NOW() AS DATE) AND isCompleted = 0 THEN taskID END) AS overdue
                                    FROM Users LEFT JOIN Tasks ON Users.userID = Tasks.userID WHERE projectID = ?
                                    AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1) GROUP BY Users.userID;");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            
            break;
    
        case 'proj_summ':
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT ROUND(IFNULL(SUM(CASE WHEN isCompleted = 1 THEN timeEstimate ELSE 0 END), 0), 2) AS hoursCompleted, 
                                    ROUND(IFNULL(SUM(timeEstimate), 0), 2) AS totalHours, COUNT(CASE WHEN isCompleted = 1 THEN taskID END) AS completed, 
                                    COUNT(CASE WHEN deadline < CAST(NOW() AS DATE) AND isCompleted = 0 THEN taskID END) AS overdue 
                                    FROM Tasks WHERE projectID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1);");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            
            break;
            
        case 'proj_leader':
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT teamLeader, firstName, surname FROM Projects LEFT JOIN Users ON Projects.teamLeader = Users.userID WHERE projectID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1);");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            
            break;
    
        case 'proj_all_task_info':
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT taskID, name, description, timeEstimate, deadline, isCompleted, highPriority, Tasks.userID, firstName, surname 
                                    FROM Tasks LEFT JOIN Users ON Tasks.userID = Users.userID WHERE projectID = ? AND ( projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1);");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
            
            break;
    
        case 'high_priority': // Returns a list of the 5 highest priority tasks for a given project, based on high priority value and deadline
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT taskID, name, description, deadline, highPriority FROM 
                                    Tasks WHERE isCompleted = 0 AND projectID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1) ORDER BY highPriority DESC, deadline ASC LIMIT 3;");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);
        
            break;
        
        case 'emp_list': // Returns a list of all employee on a project
            $stmt = $conn->prepare("SELECT userID, firstName, surname FROM Users;");
            runQuery($stmt);
        
            break;
        
        case 'all_projects_data': // returns a array of all projects ID, name, number of employees in the project, number of tasks in the project completed, number of tasks in the project incompleted, project creation date and project deadline
            $stmt = $conn->prepare("SELECT p.ProjectID, p.name AS ProjectName, COUNT(DISTINCT t.userID) AS NumberOfEmployees, SUM(CASE WHEN t.isCompleted = 1 THEN 1 ELSE 0 END) AS NumberOfTasksCompleted, p.dateCreated AS ProjectCreatedDate, p.deadline AS ProjectDueDate FROM Projects p LEFT JOIN Tasks t ON p.projectID = t.projectID 
            WHERE (SELECT manager FROM Users WHERE userID = ?) = 1
            GROUP BY p.projectID;");
            $stmt->bind_param("i", $current_user_id);
            runQuery($stmt);
        
            break;
        
        case 'all_projects_name': // returns all the project names
            $stmt = $conn->prepare("SELECT name AS ProjectName FROM Projects WHERE (SELECT manager FROM Users WHERE userID = ?) = 1;");
            $stmt->bind_param("i", $current_user_id);
            runQuery($stmt);

            break;

        case 'all_employees_data': // returns all employees userId, first name, surname, all the task names they have, number of tasks they have completed and number of tasks they have not complete yet
            $stmt = $conn->prepare("SELECT u.userID, u.firstName, u.surname, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS taskNames, SUM(CASE WHEN t.isCompleted = 1 THEN 1 ELSE 0 END) AS completedTasksCount, SUM(CASE WHEN t.isCompleted = 0 THEN 1 ELSE 0 END) AS notCompletedTasksCount FROM Users u LEFT JOIN Tasks t ON u.userID = t.userID 
            WHERE (SELECT manager FROM Users WHERE userID = ?) = 1 
            GROUP BY u.userID;");
            $stmt->bind_param("i", $current_user_id);
            runQuery($stmt);
        
            break;

        case 'all_employees_name_and_id': // returns all employees userID, their first name and surname
            $stmt = $conn->prepare("SELECT userID, firstName, surname FROM Users");
            runQuery($stmt);

            break;

        case 'emp_name': // Returns important user information about an employee
            $userID = $_GET['userID'];
            $stmt = $conn->prepare("SELECT Users.userID, firstName, surname, manager, knowledgeAdmin FROM Users LEFT JOIN Tasks ON Users.userID = Tasks.userID WHERE Users.userID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1)");
            $stmt->bind_param("iii", $userID, $current_user_id, $current_user_id);
            runQuery($stmt);
            break;

        case 'emp_summary':
            $userID = $_GET['userID'];
            $stmt = $conn->prepare("SELECT Users.userID, COUNT(CASE WHEN isCompleted = 1 THEN taskID END) AS completed, 
                                    COUNT(CASE WHEN deadline >= CAST(NOW() AS DATE) AND isCompleted = 0 THEN taskID END) AS onTrack,
                                    COUNT(CASE WHEN deadline < CAST(NOW() AS DATE) AND isCompleted = 0 THEN taskID END) AS overdue, 
                                    COUNT(DISTINCT(projectID)) AS partOf, registrationDate, manager
                                    FROM Users LEFT JOIN Tasks ON Tasks.userID = Users.userID WHERE Users.userID = ? 
                                    AND ( projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1) GROUP BY Users.userID;");
            $stmt->bind_param("iii", $userID, $current_user_id, $current_user_id);
            runQuery($stmt); 
            break;

        case 'high_priority_emp':
            $userID = $_GET['userID'];
            $stmt = $conn->prepare("SELECT taskID, name, description, deadline, highPriority FROM 
                                    Tasks WHERE isCompleted = 0 AND userID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1) ORDER BY highPriority DESC, deadline ASC LIMIT 3;");
            $stmt->bind_param("iii", $userID, $current_user_id, $current_user_id);
            runQuery($stmt); 
            break;

        case 'emp_all_task_info': // Returns all tasks with their info that a particular employee has
            $userID = $_GET['userID'];
            $stmt = $conn->prepare("SELECT taskID, name, timeEstimate, deadline, highPriority, isCompleted FROM Tasks WHERE userID = ? AND (projectID IN (SELECT projectID FROM Projects WHERE teamLeader = ?) OR (SELECT manager FROM Users WHERE userID = ?) = 1);");
            $stmt->bind_param("iii", $userID, $current_user_id, $current_user_id);
            runQuery($stmt); 
            break;

        case 'projects_data_for_teamLeader': // returns the projects ID, projects name, number of employees in the project, number of tasks in the project completed, number of tasks in the project incompleted, project creation date and project deadline for one team leader
            $teamLeaderID = $_GET['teamLeaderID'];
            if ($teamLeaderID == $current_user_id){
                $stmt = $conn->prepare("SELECT p.ProjectID, p.name AS ProjectName, COUNT(DISTINCT t.userID) AS NumberOfEmployees, SUM(CASE WHEN t.isCompleted = 1 THEN 1 ELSE 0 END) AS NumberOfTasksCompleted, p.dateCreated AS ProjectCreatedDate, p.deadline AS ProjectDueDate FROM Projects p LEFT JOIN Tasks t ON p.projectID = t.projectID WHERE teamLeader = ? GROUP BY p.projectID;");
                $stmt->bind_param("i", $teamLeaderID);
                runQuery($stmt);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Projects WHERE projectID = 0");
                runQuery($stmt);
            }
            

            break;

        case 'employees_data_for_teamLeader': // returns all employees userId, first name, surname, all the task names they have, number of tasks they have completed and number of tasks they have not complete yet for one team leader
            $teamLeaderID = $_GET['teamLeaderID'];
            if ($teamLeaderID == $current_user_id){
                $stmt = $conn->prepare("SELECT u.userID, u.firstName, u.surname, GROUP_CONCAT(t.name) AS taskNames, SUM(CASE WHEN t.isCompleted = 1 THEN 1 ELSE 0 END) AS completedTasksCount, SUM(CASE WHEN t.isCompleted = 0 THEN 1 ELSE 0 END) AS notCompletedTasksCount FROM Users u JOIN Tasks t ON u.userID = t.userID JOIN Projects p ON t.projectID = p.projectID WHERE p.teamLeader = ? GROUP BY u.userID, u.firstName, u.surname;");
                $stmt->bind_param("i", $teamLeaderID);
                runQuery($stmt);
            } else {
                $stmt = $conn->prepare("SELECT * FROM Projects WHERE projectID = 0");
                runQuery($stmt);
            }

            break;

        case 'tasks_on_cards': // returns up to 3 tasks with their task name and deadline with given project ID
            $projectID = $_GET['projectID'];
            $stmt = $conn->prepare("SELECT DISTINCT t.name, t.deadline FROM Tasks t JOIN Projects p WHERE t.projectID = ? AND t.isCompleted = 0 AND (p.teamLeader = ? OR (SELECT manager FROM Users WHERE userID = ?) = 1) ORDER BY t.deadline LIMIT 3;");
            $stmt->bind_param("iii", $projectID, $current_user_id, $current_user_id);
            runQuery($stmt);

            break;
            
        default:
            echo json_encode("Error: Paramater 'type' invalid");
            break;
    }
}


function runQuery($stmt){
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    echo json_encode($data);
}


$conn->close();
?>
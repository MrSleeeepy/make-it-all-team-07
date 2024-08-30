<?php
include "Database_config/database-connect.php";
// Start a new session or resume the existing one
session_start();

include('commonJavascript&Php/session_check.php'); // Includes session timeout and activity check

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Establish a new database connection
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

// Check the database connection for errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$manager = 0; // Default to non-manager

$teamLeader_array = []; // Default teamLeader array is empty ---- New
$role = "manager"; // Default to manager for role ---- New
$teamLeader_ID = 0; //Default teamLeader ID to 0

// Check if the user ID is set in the session
if (isset($_SESSION["userID"])) {
    $userID = $_SESSION["userID"];
    // Prepare a statement to select the manager status from the database
    $query = "SELECT manager FROM Users WHERE userID = ?";
    if ($stmt = $conn->prepare($query)) {
        // Bind the user ID to the prepared statement
        $stmt->bind_param("i", $userID);
        // Execute the statement
        $stmt->execute();
        // Bind the result to the manager variable
        $stmt->bind_result($manager);
        // Fetch the result
        $stmt->fetch();
        // Close the statement
        $stmt->close();
    }

    // Check if a user is a team leader ---- New
    $query = "SELECT DISTINCT teamLeader FROM Projects";
    if ($stmt = $conn->prepare($query)) {
        // Execute the statement
        $stmt->execute();
        // Bind the result to the manager variable
        $stmt->bind_result($oneTeamLeader);
        // Fetch the result
        while ($stmt->fetch()) {
            $teamLeader_array[] = $oneTeamLeader;
        }
        // Close the statement
        $stmt->close();
    }
}

for ($i = 0; $i < count($teamLeader_array); $i++){
    if ($_SESSION["userID"] == $teamLeader_array[$i]){
        $teamLeader_ID = $_SESSION["userID"];
        break;
    }
}

// Redirect the user to the index page if they are not a manager
if ($manager != 1) {
    if ($teamLeader_ID == 0){ // Check if a user is a teamLeader ---- New
        header("location: index.php");
        exit;
    } else {
        $role = "teamLeader";
    }
} 

$json_role = json_encode($role);
$json_teamLeader_ID = json_encode($teamLeader_ID);

?>


<!DOCTYPE html>
<!--
    Manager Dashboard Page
    This page provides a comprehensive dashboard for managers, featuring navigation to productivity, knowledge, and management sections.
-->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title of the page -->
    <title>Manager Dashboard</title>
    <!-- Bootstrap CSS for responsive and modern UI components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Bundle JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Main stylesheet for custom styles -->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="manager_resources/manager.css">
    <!-- This is the companys logo-->
    <link rel="shortcut icon" href="makeItAllIcon.png" />
    <!-- Font Awesome for scalable vector icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>

    <div class="container-fluid" id="container">

        <!--HTML for the navbar-->
        <div class="row">
            <nav class="col navbar navbar-expand-sm navbar-dark bg-dark">
                <!-- Brand logo and link -->
                <a class="navbar-brand" href="#">
                    <img src="images/MakeItAll.png" width="auto" height="80" class="d-inline-block align-top" style="max-height: 8vh" alt="MakeItAll Logo"/>
                </a>
                <!-- Responsive navigation toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation links -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link nav-link-underlined" href="productivity.php">Productivity</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="knowledge.php">Knowledge</a>
                        </li>
                        <!-- Conditional display based on manager status -->
                        <?php if ($manager == 1 || $teamLeader_ID != 0): ?>
                           <li class="nav-item">
                              <a class="nav-link" href="manager.php">Manager</a>
                           </li>
                       <?php endif; ?>
                    </ul>
                </div>

                <!-- User account dropdown -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown user-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i>
                            <!-- Display current logged-in user's username -->
                            <?php if(isset($_SESSION['username'])): ?>
                                <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <?php endif; ?>
                        </a>
                        <!-- Dropdown menu items -->
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if(isset($_SESSION['username'])): ?>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user-circle"></i> Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a class="dropdown-item" href="change-password.php"><i class="fas fa-key"></i> Change Password</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#invitationModal"><i class="fas fa-envelope"></i> Invitation</a>
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Log Out</a>

                        </div>
                    </li>
                </ul>
                
                <!-- Theme switch for dark and light mode -->
                <div class="theme-switch-wrapper">
                    <em class="fas fa-sun fa-lg"></em>
                    <div class="theme-switch" id="darkSwitch">
                        <div class="theme-switch-handle"></div>
                    </div>
                    <em class="fas fa-moon fa-lg"></em>
                </div>
            </nav>
        </div>

        <div class="pane overflow-auto">

            <!-- HTML for the taskbar and its contents -->
            <div class="taskbar container" id="taskbar">
                <div class="align-items-right">
                    <div class="row taskbar-div mx-auto">

                        <!-- Creating the button thats swaps between the employee and project dashboard -->
                        <div class="col mb-1 mt-1">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="btn_toggle_emp_proj" id="btnradioproj" autocomplete="off" value="1" checked>
                                <label class="btn btn-outline-primary rounded-left" for="btnradioproj">Projects</label>

                                <input type="radio" class="btn-check" name="btn_toggle_emp_proj" id="btnradioemp" autocomplete="off" value="2">
                                <label class="btn btn-outline-primary rounded-right" for="btnradioemp">Employees</label>
                            </div>
                        </div>

                        <!-- Search box for filtering displayed cards -->
                        <div class="col mb-1 mt-1">
                            <input type="text" id="searchbar" onkeyup="search_sort_process()" class="form-control search_box" placeholder="Search...">
                        </div>

                        <!-- Options to appear for the employee section -->
                        <!-- These are part of the class toggleVis which enables / disables them from displaying when appropriate -->
                        <div class="col mb-1 mt-1 toggleVis hidden">
                            <div class="sort">
                                <div>
                                    <label for="sort">Sort by:</label>
                                </div>
                                <select id="empSort" class="form-control select">
                                    <option value="emp_fname">First Name</option>
                                    <option value="emp_lname">Last Name</option>
                                </select>
                            </div>
                        </div>


                        <!-- Options to appear for the projects section -->
                        <!-- These are also part of the toggleVis class but aren't hidden initially and so will alternate with the employee taskbar divs -->
                        <div class="col mb-1 mt-1 toggleVis">
                            <div class="sort">
                                <label for="sort">Sort by:</label>
                                <select id="projectSort" class="form-control select">
                                    <option value="project_name">Project name</option>
                                    <option value="employee_count">Number of employees</option>
                                    <option value="project_tasks_completed_ascending">Tasks Completed - Ascending</option>
                                    <option value="project_tasks_completed_descending">Tasks Completed - Descending</option>
                                </select>
                            </div>
                        </div>

                        <div class="col mb-1 mt-1 toggleVis" id="button_create_project">
                            <button type="button" id="btn_open_modal_create" class="btn btn-primary btn-line" data-bs-toggle="modal" data-bs-target="#modal_add_project" style="border-radius: 34px">Create Project</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Container for the dashboards to go -->
            <div class="row">
                <div id="data_display_parent">
                    
                        <h1 id="title_dashboard_container">Projects</h1>
                        <div class="flex-container" id="card-container">
                        </div>
                    
                </div>
            </div>
        </div>


        <!-- Modal template for the project overview -->
        <div class="modal fade" id="project-modal-template" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="projectModalTitle"></h5>
                        <div id="projectModalTeamLeader"></div>
                        <button type="button" class="btn btn-danger" id="delete_current_project_btn">Delete project</button>
                    </div>
                    <!-- The main body of the modal-->
                    <div class="modal-body my-modal-body" id="projectModalBody">
                        <div class="row">
                            <div class="col mb-1 mt-1">
                                <input type="text" id="projectModalSearch" class="form-control search_box" placeholder="Search...">
                            </div>
                            <div class="col mb-3 mt-1">
                                <div class="sort">
                                    <label for="sort">Search by:</label>
                                    <select id="projTaskTableSearchBy" class="form-control select">
                                        <option value="taskName">Task Name</option>
                                        <option value="empName">Employee Name</option>
                                        <option value="taskHoursAbove">Time to Complete ></option>
                                        <option value="taskHoursBelow">Time to Complete <</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- The div that is filled with the table on load / search -->
                        <div id="projectModalTable"></div>
                    </div>
                    <!-- Elements for adding new task and closing modal -->
                    <div class="modal-footer" id="project-modal-footer">
                        <input type="text" class="form-control search_box" id="text_new_task" style="width:200px" placeholder="New task">
                        <!-- Creating the dropdown menu to select employees. It is filled with data through a javascript function when a card is clicked -->
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">Select Employee</button>
                            <div class="dropdown-menu">
                                <input type="search" class="form-control search_box" id="search_employee_projects" style="width: 200px" placeholder="Search..." autocomplete="off">                                
                                <div id="dropdown_menu_employees" class="dropdown-menu-scroll"></div>
                            </div>
                        </div>
                        <input type="hidden" id="taskSelectedEmployeeId" name="employeeId">
                        <input type="text" class="form-control search_box" id="text_new_task_time" style="width: 100px" placeholder="Hours">
                        <div id="addNewTaskButtonContainer"></div>
                        <button type="button" id="close_current_project" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal template for the employee overview -->
        <div class="modal fade" id="employee-modal-template" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- Employee name will be placed in the header as modal is opened -->
                        <h5 class="modal-title" id="employeeModalTitle"></h5>
                        <button type="button" class="btn btn-danger" id="delete_current_employee_btn">Delete employee</button>
                    </div>
                    <div class="modal-body my-modal-body" id="employeeModalBody">
                        <div class="row">
                            <div class="col mb-1 mt-1">
                                <input type="text" id="empTaskSearch" class="form-control search_box" placeholder="Search...">
                            </div>
                            <div class="col mb-3 mt-1">
                                <div class="sort">
                                    <label for="sort">Search by:</label>
                                    <select id="empTaskTableSearchBy" class="form-control select">
                                        <option value="taskName">Task Name</option>
                                        <option value="taskHoursAbove">Time to Complete ></option>
                                        <option value="taskHoursBelow">Time to Complete <</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="employeeModalTable"></div>
                    </div>
                    <div class="modal-footer" id="project-modal-footer">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="switchManagerRole" onchange="toggleManagerRole()">
                            <label class="form-check-label" for="flexSwitchCheckDefault">Manager</label>
                        </div>
                        <button type="button" id="close_current_employee" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal for inviting an employee -->
        <div class="modal fade" id="invite_modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="invite_modal_label">Invite An Emplyoee</h1>
                        <button type="button" id="btn-close-invite-emp-window" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="employee_email_address" class="col-form-label">Email Address:</label>
                                <input type="text" class="form-control" id="employee_email_address">
                            </div>
                            <div id="potential_error_message_for_invite_emp" class="error_msg"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="invite_new_employee()">Invite Employee</button>
                    </div>
                </div>
            </div>
        </div>


        

        <!-- Modal for creating a new project -->
        <div class="modal fade" id="modal_add_project" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Create New Project</h1>
                        <button type="button" id="btn-close-create-project-window" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="project_name" class="col-form-label">Project Name:</label>
                                <input type="text" class="form-control" id="project_name">
                                <label for="project_description" class="col-form-label">Project Description:</label>
                                <textarea class="form-control" id="project_description" style="height: 120px;"></textarea>
                                <label for="project_deadline" class="col-form-label">Project Deadline:</label>
                                <input type="date" class="form-control" id="project_deadline">
                            </div>
                            <div class="mb-3">
                                <label for="project_team_leader" class="col-form-label">Team Leader:</label></br>
                                <!-- Creating the dropdown menu to select employees. It is filled with data through a javascript function when a card is clicked -->
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button" id="dropdown_button_team_leader" data-bs-toggle="dropdown">Select Employee</button>
                                    <div class="dropdown-menu">
                                        <input type="search" class="form-control search_box" id="search_employee_team_leader" style="width: 200px" placeholder="Search..." autocomplete="off">                                
                                        <div id="dropdown_menu_employees_add_proj" class="dropdown-menu-scroll"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="project_selected_team_leader_id" name="employeeId">
                            </div>
                            <div id="potential_error_message_for_new_project" class="error_msg"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Creating a button to call the function to create a project -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="create_new_project()">Create Project</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Declare the variables for JavaScript by echoing them
        let role = <?php echo $json_role; ?>;
        let teamLeaderID = <?php echo $json_teamLeader_ID; ?>;
    </script>

     <script src="manager_resources/manager.js"></script>
     <!--Importing javascript for the email, dark light mode and the active tab-->
     <script src="commonJavascript&Php/theme-and-navigation.js"></script>

    

<!-- Modal for inviting people -->
<div class="modal fade" id="invitationModal" tabindex="-1" aria-labelledby="invitationModalLabel" aria-hidden="true">
    <!-- Defines a modal dialog box that fades into view, identified by "invitationModal", accessible through tab navigation, and hidden from screen readers when not in view -->
    <div class="modal-dialog">
        <!-- Container for the modal content -->
        <div class="modal-content">
            <!-- The content of the modal -->
            <div class="modal-header">
                <!-- Header section of the modal containing the title and close button -->
                <h5 class="modal-title" id="invitationModalLabel">Send Invitation</h5>
                <!-- Modal title with an ID for accessibility linking -->
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- Close button with an attribute to dismiss the modal -->
                    <span aria-hidden="true">&times;</span>
                    <!-- The X character indicating the close operation -->
                </button>
            </div>
            <div class="modal-body">
                <!-- Body section of the modal -->
                <form>
                    <!-- Form inside the modal for user input -->
                    <div class="mb-3">
                        <!-- Form group with bottom margin -->
                        <label for="userEmail" class="form-label">Enter the email of the user you want to invite:</label>
                        <!-- Label for the email input field -->
                        <input type="email" class="form-control" id="userEmail">
                        <!-- Email input field with Bootstrap styling -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <!-- Footer section of the modal -->
                <button type="button" class="btn btn-primary">Send Invitation</button>
                <!-- A button styled with Bootstrap's primary color to submit the form -->
            </div>
        </div>
    </div>
</div>

</body>

</html>
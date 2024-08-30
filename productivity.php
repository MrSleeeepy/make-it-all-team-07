<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "Database_config/database-connect.php";
include('commonJavascript&Php/session_check.php'); // Includes session timeout and activity check

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit; // Prwevent further script execution after redirect
}

// Create a new database connection
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

// Check the database connection for errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variable to check if user is a manager
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

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Character set, viewport and IE compatibility meta tags -->
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Bootstrap CSS for responsive design -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"/>
        <!-- Custom CSS for additional styling -->
        <link rel="stylesheet" href="index.css" />
        <link rel="stylesheet" href="productivityResources/productivity.css" />
        <!-- Webpage favicon -->
        <link rel="shortcut icon" href="makeItAllIcon.png" />
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <!-- Title name -->
        <title>Productivity</title>
    </head>
    <body>
        <!-- Modal for project details, centered and scrollable for extensive content -->
        <div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div id="projectModalContent" class="modal-content">
                    <!-- Modal header with a close button -->
                    <div class="modal-header border-0">
                        <h1 class="modal-title fs-5" id="projectModalLabel">Project</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Modal body for dynamic project information -->
                    <div id="projectModalBody" class="modal-body">
                        ...
                    </div>
                    <!-- Modal footer with a button to close the modal -->
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for task details, centered and scrollable for extensive content -->
        <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div id="taskModalContent" class="modal-content">
                    <!-- Modal header with a close button -->
                    <div class="modal-header border-0">
                        <h1 class="modal-title fs-5" id="taskModalLabel">Task</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Modal body for dynamic task information -->
                    <div id="taskModalBody" class="modal-body">
                        ...
                    </div>
                    <!-- Modal footer with a button to close the modal and button to complete task -->
                    <div class="modal-footer border-0">
                        <button type="button" id='removeTask' class='btn btn-danger' data-bs-dismiss="modal">Complete Task</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal for archived to-do item details, centered and scrollable for extensive content -->
        <div class="modal fade" id="to-doArchiveModal" tabindex="-1" aria-labelledby="to-doArchiveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div id="to-doArchiveModalContent" class="modal-content">
                    <!-- Modal header with a close button -->
                    <div class="modal-header border-0" style="padding-bottom:0">
                        <h1 class="modal-title fs-5" id="to-doArchiveModalLabel">Archived To-do items</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-header border-0" style="padding-top:0;padding-bottom:0">
                        <p class="fs-6" id="to-doArchiveModalInfo" style="margin:0">Showing all completed items that are 7 or more days old: </p>
                    </div>
                    
                    <!-- Modal body for dynamic to-do information -->
                    <!--Prevent the modal from becoming too tall -->
                    <div id="to-doArchiveModalBody" class="modal-body" style="max-height:150px; padding-top:0; margin-top:10px">
                        ...
                    </div>
                    <!-- Modal footer with a button to close the modal-->
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
         <!-- Main container for page content -->
        <div class="container-fluid text-wrap text-break bg-dark">
            <div class="row">
                <!-- Responsive navbar with dynamic PHP content for user-specific navigation -->
                <nav class="col navbar navbar-expand-sm navbar-dark bg-dark">
                    <a class="navbar-brand" href="#">
                        <img src="images/MakeItAll.png" alt="MakeItAll Logo" style="max-height: 8vh" />
                    </a>
                    <!-- Navbar toggler for collapsible menu on smaller screens -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <!-- Collapsible navbar content with conditional display based on user role -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link nav-link-underlined" href="#">Productivity</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="knowledge.php">Knowledge</a>
                            </li>
                            <!-- PHP conditional to display manager link for manager users -->
                            <?php if ($manager == 1 || $teamLeader_ID != 0): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="manager.php">Manager</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <!-- User dropdown for account management, leveraging Font Awesome for icons -->
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown user-dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i>
                                    <?php if(isset($_SESSION['username'])): ?>
                                        <!-- Display username, hiding on small screens for responsiveness -->
                                        <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <?php if(isset($_SESSION['username'])): ?>
                                        <a class="dropdown-item" href="#">
                                            <i class="fas fa-user-circle"></i> Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="change-password.php"><i class="fas fa-key"></i> Change Password</a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#invitationModal"><i class="fas fa-envelope"></i> Invitation</a>
                                    <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>

                                </div>
                            </li>
                        </ul>


                        <!-- dark/ligt mode changing -->
                        <div class="theme-switch-wrapper">
                            <em id="sunIcon" class="fas fa-sun fa-lg"></em>
                            <div class="theme-switch" id="darkSwitch">
                                <div class="theme-switch-handle"></div>
                            </div>
                            <em id="moonIcon" class="fas fa-moon fa-lg"></em>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Row with all panes -->
            <div id="main" class="row">
                <!-- List of projects -->
                <div
                    id="toDoProjectPane"
                    class="cos-xs-12 col-sm-6 col-md-3 pane overflow-auto"
                >
                    <h2>Projects:</h2>
                    <ul id="projectList"></ul>
                </div>
                <!-- List of tasks in project -->
                <div
                    id="toDoTaskPane"
                    class="cos-xs-12 col-sm-6 col-md-3 pane overflow-auto"
                >
                    <h2>Tasks:</h2>
                    <input type="text" placeholder="Search..." id="taskSearch" class="form-control">
                    <ul id="taskList"></ul>
                    <div id="completedTasks">
                        <h2>Finished Tasks:</h2>
                        <ul id="completedTaskList"></ul>
                    </div>
                </div>
                <!-- Form to add new item to to-do list -->
                <div id="toDoAddPane" class="cos-xs-12 col-sm-6 col-md-2 pane">
                    <h2>Add To-do item:</h2>
                    <div class="form-group">
                        <label for="newToDoDetail">To-do Details:</label>
                        <textarea
                            id="newToDoDetail"
                            class="form-control"
                            maxlength="500"
                        ></textarea>
                        <label for="associatedTask">Associated Task:</label>
                        <select
                            id="associatedTask"
                            class="form-control"
                        ></select>
                        <button id="addToDo" class="btn btn-primary">
                            Add To-do item
                        </button>
                    </div>
                </div>
                <!-- List of to-do items for current task -->

                <!-- List of to-do items for current task -->
                <div
                    id="toDoListPane"
                    class="cos-xs-12 col-sm-6 col-md-4 pane overflow-auto"
                >
                    <h2>To-do:</h2>
                    
                    <input type="text" placeholder="Search..." id="toDoSearch" class="form-control">
                    <ul id="toDoList"></ul>
                    <h2>Finished To-dos:</h2>
                    <ul id="tickedToDoList"></ul>
                    <!-- Button to open the archived to-do modal-->
                    <button id="to-doArchiveModalButton" type='button' class='btn btn-primary btn-sm' style='margin-bottom:20px;' data-bs-toggle='modal' data-bs-target='#to-doArchiveModal'>
                            View items that were completed 7 or more days ago
                            </button>
                </div>
            </div>
        </div>

        <!-- Project Popup -->
        <div id="projectPopup" class="container-fluid">
            
        </div>
        <!-- Invitation Modal Start -->
        <div class="modal fade" id="invitationModal" tabindex="-1" aria-labelledby="invitationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <!-- Modal Content Wrapper -->
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="invitationModalLabel">Send Invitation</h5>
                        <!-- Close Button -->
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Form for Email Input -->
                        <form>
                            <div class="mb-3">
                                <label for="userEmail" class="form-label">Enter the email of the user you want to invite:</label>
                                <input type="email" class="form-control" id="userEmail" />
                            </div>
                        </form>
                    </div>
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <!-- Send Invitation Button -->
                        <button type="button" class="btn btn-primary">Send Invitation</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Invitation Modal End -->

        <!-- Import productivity.js -->
        <script src="productivityResources/productivity.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"crossorigin="anonymous"></script>
        <!--Importing javascript for the email, dark light mode and the active tab-->
        <script src="commonJavascript&Php/theme-and-navigation.js"></script>

    </body>
</html>

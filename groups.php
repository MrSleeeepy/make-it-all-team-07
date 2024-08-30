<?php

include "Database_config/database-connect.php";
// Check if a session is not already started, if not, start the session.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('commonJavascript&Php/session_check.php'); // Include session timeout and activity check

// Redirect user to login page if not logged in.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}



// Attempt to connect to the database.
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);


// Check if database connection was successful.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variable to check if user is a manager
$manager = 0; // Default to non-manager

$teamLeader_array = []; // Default teamLeader array is empty ---- New
$knowledge_admin_array = [];
$role = "manager"; // Default to manager for role ---- New
$teamLeader_ID = 0; //Default teamLeader ID to 0
$knowledge_admin_id = 0;

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

    // Check if a user is a team leader ---- New
    $query = "SELECT userID FROM Users WHERE knowledgeAdmin = 1";
    if ($stmt = $conn->prepare($query)) {
        // Execute the statement
        $stmt->execute();
        // Bind the result to the manager variable
        $stmt->bind_result($oneKnowledgeAdmin);
        // Fetch the result
        while ($stmt->fetch()) {
            $knowledge_admin_array[] = $oneKnowledgeAdmin;
        }
        // Close the statement
        $stmt->close();
    }
}

for ($i = 0; $i < count($teamLeader_array); $i++) {
    if ($_SESSION["userID"] == $teamLeader_array[$i]) {
        $teamLeader_ID = $_SESSION["userID"];
        break;
    }
}

for ($i = 0; $i < count($knowledge_admin_array); $i++) {
    if ($_SESSION["userID"] == $knowledge_admin_array[$i]) {
        $knowledge_admin_id = $_SESSION["userID"];
        break;
    }
}

// Redirect the user to the index page if they are not a manager
if ($manager != 1) {
    if ($knowledge_admin_id == 0) { // Check if a user is a knowledge admin ---- New
        header("location: index.php");
        exit;
    }
}


$conn->close();
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="makeItAllIcon.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="groups_resources/groups.css" />
    <link rel="stylesheet" href="index.css">
    <title>Groups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- jQuery (Necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>


</head>

<body>
    <div class="container-fluid" id="container">
        <!--HTML for the navbar-->

        <div class="row">
            <nav class="col navbar navbar-expand-sm navbar-dark bg-dark">
                <!-- Navbar brand/logo -->
                <a class="navbar-brand" href="#">
                    <img src="images/MakeItAll.png" width="auto" height="80" class="d-inline-block align-top"
                        alt="MakeItAll Logo" style="max-height: 8vh" />
                </a>
                <!-- Toggler button for mobile navigation -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Navbar links -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="nav">
                        <!-- Dynamic PHP condition to highlight the current page's nav item -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-underlined" href="productivity.php">Productivity</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="knowledge.php">Knowledge</a>
                        </li>
                        <!-- Conditional display for manager link if the user is a manager -->
                        <?php if ($manager == 1 || $teamLeader_ID != 0): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="manager.php">Manager</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- User account and settings dropdown -->
                <div class="d-flex align-items-center justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown user-dropdown">
                            <!-- Dropdown toggle -->
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <!-- Display username if set in session -->
                                <?php if (isset($_SESSION['username'])): ?>
                                    <span class="ms-2 d-none d-md-inline">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown menu items -->
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if (isset($_SESSION['username'])): ?>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user-circle"></i> Logged in as <strong>
                                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                                        </strong>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>
                                <a class="dropdown-item" href="change-password.php"><i class="fas fa-key"></i> Change
                                    Password</a>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                    data-bs-target="#invitationModal"><i class="fas fa-envelope"></i> Invitation</a>
                                <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Log
                                    Out</a>

                            </div>
                        </li>
                    </ul>

                    <!-- Theme switch for dark/light mode -->
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


        <!--div for the main content-->
        <div class="row" .bg-red>
            <!--div for the left sidebar-->
            <div class="col pane overflow-auto" id="leftColumn">
                <h2 id="groupHeading"></h2>
                <div class="addNewGroup">
                    <div class="addNewGroupForm">
                        <!-- form to get a new group -->
                        <form id="NewGroupForm">
                            <input name="AddNewGroup" type="text" placeholder="Add New Group" id="AddNewGroup"
                                class="form-control" style="display:inline; max-width:250px;">
                            <input name="submit" type="submit" class="btn btn-primary" value="Add"
                                style="display: inline;">
                        </form>
                    </div>
                </div>
                <!--form for the group searchbar-->
                <form action="#" id="newGroupSearch">
                    <input type="text" placeholder="Search for Groups" id="groupSearchBar" class="form-control"
                        style="max-width: 250px;">
                </form>
                <div style="max-height: 100vh">
                    <ul id="Group-List"></ul>
                </div>
            </div>

            <!--div for the right side-->
            <div class="col-sm-8 pane col-xs-12 overflow-auto" style="max-height: 100vx" id="rightColumn">
                <h2 id="userListHeading" class="userHeadingClass">Select a group to display users</h1>
                    <!--form for the user searchbar-->
                    <form action="#" id="newUserSearch">
                        <input type="text" placeholder="Search for Users" id="userSearchBar" class="form-control"
                            style="max-width: 400px;">
                    </form>
                    <div class="addNewUser">
                        <!-- Creating the dropdown menu to select employees. It is filled with data through a javascript function -->
                        <form action="#" id="NewUserForm">
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button"
                                    id="dropdownMenuButton" data-bs-toggle="dropdown">Select Employee</button>
                                <div class="dropdown-menu">
                                    <input type="search" class="form-control search_box" id="search_users"
                                        style="width: 200px" placeholder="Search..." autocomplete="off" size="50">
                                    <div id="dropdown_menu_users" class="dropdown-menu-scroll"></div>
                                </div>
                                <input type="hidden" id="usernameSelected" name="Username" size="80">
                                <input name="submit" type="submit" class="btn btn-primary" value="AddUser">
                            </div>
                        </form>
                    </div>
                    <ul id="userList"></ul>
                    <p id="userText"></p>
            </div>
        </div>

        <!-- link to include bootstrap -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
            </script>


        <!-- Import groups.js -->
        <script src="groups_resources/groups.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
        <!--Importing javascript for the email, dark light mode and the active tab-->
        <script src="commonJavascript&Php/theme-and-navigation.js"></script>

        <div class="modal fade" id="invitationModal" tabindex="-1" aria-labelledby="invitationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="invitationModalLabel">Send Invitation</h5>

                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Modal Body with Form -->
                    <div class="modal-body">
                        <form>
                            <!-- Email input field -->
                            <div class="mb-3">
                                <label for="userEmail" class="form-label">Enter the email of the user you want to
                                    invite:</label>
                                <input type="email" class="form-control" id="userEmail">
                            </div>
                        </form>
                    </div>

                    <!-- Modal Footer with Send Invitation Button -->
                    <div class="modal-footer">

                        <button type="button" class="btn btn-primary">Send Invitation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
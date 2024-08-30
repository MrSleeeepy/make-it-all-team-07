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


$conn->close();
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="makeItAllIcon.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="knowledge_resources/knowledge.css" />
    <link rel="stylesheet" href="index.css">
    <title>Knowledge</title>
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
                                <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Log Out</a>

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


        <!-- manageViewer Modal -->
        <div class="modal fade" id="viewerModal" tabindex="-1" aria-labelledby="viewerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h1 class="modal-title fs-5" id="viewerModalLabel">Viewers</h1>
                        <div style="float: right; margin-right: 10px">
                            <input type="checkbox" id="limitedVisibilityCheckbox">
                            <p style="display: inline;">Limit Visibilty</p>
                        </div>
                    </div>
                    <div class="container-fluid" id="viewerContainer" class="body">
                        <div class="row" id="manageViewerModal">
                            <div class="col" id="viewerLeftColumn">
                                <h2 class="modal-title fs-6" id="viewerModalGroups">Groups</h2>
                                <ul id="viewerModalGroupList"></ul>
                            </div>
                            <div class="col" id="viewerRightColumn">
                                <h2 class="modal-title fs-6" id="viewerModalUsers">Users</h2>
                                <ul id="viewerModalUserList"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <!-- Creating the dropdown menu to select groups. It is filled with data through a javascript function -->
                        <form action="#" id="NewViewerForm" style="position: left; left: 10px;">
                            <div class="dropup">
                                <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button"
                                    id="dropdownMenuButton" data-bs-toggle="dropdown">Select Group to add</button>
                                <div class="dropdown-menu">
                                    <input type="search" class="form-control search_box" id="searchAddViewer"
                                        style="width: 200px" placeholder="Search..." autocomplete="off" size="50">
                                    <div id="dropdown_menu_viewers" class="dropdown-menu-scroll"></div>
                                </div>
                                <input type="hidden" id="viewerSelected" name="ViewerSelected" size="80">
                                <input name="submit" type="submit" class="btn btn-primary" value="Add">
                            </div>
                        </form>

                        <div id="newViewerUserDiv" style="display: inline-block">
                            <!-- Creating the dropdown menu to select users. It is filled with data through a javascript function -->
                            <form action="#" id="NewViewerUserForm">
                                <div class="dropup">
                                    <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button"
                                        id="dropdownUserMenuButton" data-bs-toggle="dropdown">Select User to
                                        add</button>
                                    <div class="dropdown-menu">
                                        <input type="search" class="form-control search_box" id="searchAddUserViewer"
                                            style="width: 200px" placeholder="Search..." autocomplete="off" size="50">
                                        <div id="dropdown_menu_user_viewers" class="dropdown-menu-scroll"></div>
                                    </div>
                                    <input type="hidden" id="userViewerSelected" name="UserViewerSelected" size="80">
                                    <input name="submit" type="submit" class="btn btn-primary" value="Add">
                                </div>
                            </form>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>




        <!--div for the main content-->
        <div class="row" .bg-red>
            <!--div for the left sidebar-->
            <div class="col pane overflow-auto" id="leftColumn">
                <h2 id="topicHeading"></h2>
                <!-- form to get a new topic -->
                <form id="NewTopicForm" class="addNewTopic">
                    <input name="AddNewTopic" type="text" placeholder="Add New Topic" id="AddNewTopic"
                        class="form-control" style="max-width: 250px; display: inline">
                    <input name="submit" type="submit" class="btn btn-primary" value="Add" class="form-control"
                        style="max-width: 250px; display: inline">
                </form>
                <!--form for the topic searchbar-->
                <form action="#" id="newTopicSearch">
                    <input type="text" placeholder="Search for Topics" id="topicSearchBar" class="form-control"
                        style="max-width: 250px; display: inline-block">
                </form>
                <div style="max-height: 100vh">
                    <ul id="Topic-List"></ul>
                </div>
            </div>

            <!--div for the right side-->
            <div class="col-sm-8 pane col-xs-12 overflow-auto" id="rightColumn">
                <div>
                    <h2 id="postListHeading" class="postHeadingClass">Select a topic to display posts</h1>
                        <button class="btn btn-primary" id="manageViewers" data-bs-toggle="modal"
                            data-bs-target='#viewerModal'>Manage viewers</button>
                        <a href="groups.php" id="groupsBtn" class="btn btn-primary" style="display: inline" ;>Manage
                            Groups</a>
                </div>
                <div class="addNewPost">
                    <!-- form to get a new post -->
                    <form action="#" id="NewPostForm">
                        <input type="text" placeholder="New Post Title" id="AddNewPostTitle" class="form-control"
                            style="max-width: 250px; display: inline">
                        <input type="submit" class="btn btn-primary" value="Add" class="form-control"
                            style="max-width: 250px; display: inline">
                        <br>
                        <textarea placeholder="New Post Text" id="AddNewPostText" rows="2" class="form-control"
                            style="max-width: 250px; display: inline"></textarea>
                    </form>
                </div>


                <!--form for the post searchbar-->
                <form action="#" id="newPostSearch">
                    <input type="text" placeholder="Search for Posts" id="postSearchBar" class="form-control"
                        style="max-width: 250px; display: inline-block">
                </form>
                <button id="postReturnButton" class="postHeadingClass btn btn-primary">Return to
                    posts</button>
                <p id="postMessage"></p>
                <div class="overflow-auto" style="max-height: 100vh;">
                    <ul id="postList"></ul>
                </div>
                <div class="addNewComment">
                    <!-- form to get a new comment -->
                    <form action="#" id="newCommentForm" style="max-width: 600px">
                        <textarea placeholder="Add a New Comment" id="addNewCommentText" rows="2" class="form-control"
                            style="max-width: 500px;display: inline"></textarea>
                        <input type="submit" class="btn btn-primary" value="Add" style="display: inline">
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <!-- link to include bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
        </script>


    <!-- Import knowledge.js -->
    <script src="knowledge_resources/knowledge.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <!--Importing javascript for the email, dark light mode and the active tab-->
    <script src="commonJavascript&Php/theme-and-navigation.js"></script>

    <!-- Comment Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div id="taskModalContent" class="modal-content">
                <!-- Modal header with a close button -->
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="commentModalLabel">Edit or Delete Comment</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal body -->
                <div id="taskModalBody" class="modal-body">
                    <form action="#" id="editCommentForm" style="max-width: 600px">
                        <textarea placeholder="Edit Comment" id="editCommentText" rows="2" class="form-control"
                            style="max-width: 500px;display: inline"></textarea>
                        <button type="button" id='editCommentButton' class="btn btn-primary" onclick="editComment()"
                            style="display: inline" data-bs-dismiss="modal">Edit</button>
                    </form>
                    <button type="button" id='deleteCommentButton' onclick="removeComment()" class='btn btn-danger'
                        data-bs-dismiss="modal">Delete</button>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer border-0">
                </div>
            </div>
        </div>
    </div>

    <!-- Post Text Modal -->
    <div class="modal fade" id="postTextModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div id="postTextModalContent" class="modal-content">
                <!-- Modal header with a close button -->
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="postTextModalLabel">Edit Post Text</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal body -->
                <div id="postTextModalBody" class="modal-body">
                    <form action="#" id="editPostTextForm" style="max-width: 600px">
                        <textarea placeholder="Edit Post Text" id="editPostText" rows="2" class="form-control"
                            style="max-width: 500px;display: inline"></textarea>
                        <button type="button" id='editPostTextButton' class="btn btn-primary"
                            onclick="editPostMessage()" style="display: inline" data-bs-dismiss="modal">Edit</button>
                    </form>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer border-0">
                </div>
            </div>
        </div>
    </div>

    <!-- Post Title Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div id="postModalContent" class="modal-content">
                <!-- Modal header with a close button -->
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="postModalLabel">Edit or Delete Post Title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal body -->
                <div id="postModalBody" class="modal-body">
                    <form action="#" id="editPostForm" style="max-width: 600px">
                        <textarea placeholder="Edit Post Title" id="editPostNameText" rows="2" class="form-control"
                            style="max-width: 500px;display: inline"></textarea>
                        <button type="button" id='editPostButton' class="btn btn-primary" onclick="editPost()"
                            style="display: inline" data-bs-dismiss="modal">Edit</button>
                    </form>
                    <button type="button" id='deletePostButton' onclick="removePost()" class='btn btn-danger'
                        data-bs-dismiss="modal">Delete</button>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer border-0">
                </div>
            </div>
        </div>
    </div>

    <!-- Topic Modal -->
    <div class="modal fade" id="topicModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div id="topicModalContent" class="modal-content">
                <!-- Modal header with a close button -->
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="topicModalLabel">Edit or Delete Topic</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal body -->
                <div id="topicModalBody" class="modal-body">
                    <form action="#" id="editTopicForm" style="max-width: 600px">
                        <textarea placeholder="Edit Topic Title" id="editTopicText" rows="2" class="form-control"
                            style="max-width: 500px;display: inline"></textarea>
                        <button type="button" id='editTopicButton' class="btn btn-primary" onclick="editTopic()"
                            style="display: inline" data-bs-dismiss="modal">Edit</button>
                    </form>
                    <button type="button" id='deleteTopicButton' onclick="removeTopic()" class='btn btn-danger'
                        data-bs-dismiss="modal">Delete</button>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer border-0">
                </div>
            </div>
        </div>
    </div>




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

</body>

</html>
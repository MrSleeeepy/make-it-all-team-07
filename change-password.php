<?php
include "Database_config/database-connect.php";
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('commonJavascript&Php/session_check.php'); // Include session timeout and activity check

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


// Create a new database connection
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

// Check the database connection
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

// Initialize variables
$currentPassword = $newPassword = $confirmPassword = "";
$currentPassword_err = $newPassword_err = $confirmPassword_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = trim($_POST["currentPassword"]);
    $newPassword = trim($_POST["newPassword"]);
    $confirmPassword = trim($_POST["confirmPassword"]);

    // Validate current password
    if (empty($currentPassword)) {
        $currentPassword_err = "Please enter your current password.";
    }

    // Validate new password
    if (empty($newPassword)) {
        $newPassword_err = "Please enter a new password.";
    } elseif (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
        $newPassword_err = "Password must have at least 8 characters and include at least one number, one uppercase and lowercase letter, and one special character.";
    }

    // Validate confirm password
    if (empty($confirmPassword)) {
        $confirmPassword_err = "Please confirm the password.";
    } elseif ($newPassword != $confirmPassword) {
        $confirmPassword_err = "Password did not match.";
    }

    // Check input errors before updating the database
    if (empty($currentPassword_err) && empty($newPassword_err) && empty($confirmPassword_err)) {
        // Prepare a select statement to fetch the user's current hashed password
        $sql = "SELECT password FROM Users WHERE userID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_id);
            $param_id = $_SESSION["userID"];
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($hashedPassword);
                    if ($stmt->fetch()) {
                        if (password_verify($currentPassword, $hashedPassword)) {
                            // Password is correct, prepare to update the password in the database
                            $updateSql = "UPDATE Users SET password = ? WHERE userID = ?";
                            if ($updateStmt = $conn->prepare($updateSql)) {
                                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                                $updateStmt->bind_param("si", $newHashedPassword, $param_id);
                                if ($updateStmt->execute()) {
                                    // Before redirecting to login page, clear and destroy the session
                                    $_SESSION = array();
                                    session_destroy();

                                    // Redirect to login page with success message
                                    header("location: login.php?password=changed");
                                    exit;
                                } else {
                                    echo "Oops! Something went wrong. Please try again later.";
                                }
                            }
                        } else {
                            $currentPassword_err = "The current password does not match our records.";
                        }
                    }
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}


// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Link Bootstrap CSS framework, our CSS stylesheets and Font Awesome for icons-->
    <link rel="stylesheet" href="index.css">
    <link rel="shortcut icon" href="makeItAllIcon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="stylesheet" href="change-password.css">
    <title>Change Password</title>
</head>

<body >
<!-- Main container-->
<div class="container-fluid text-wrap text-break ">
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
    <!--Container for page content-->
   <div class="container mt-5">
       <div class="row justify-content-center">
           <div class="col-md-8 col-lg-6">
               <div class="pane">
                   <!-- Heading and Global Password Visibility Toggle -->
                   <div class="d-flex justify-content-between align-items-center mb-4">
                       <h2>Change Password</h2>
                       <!-- Global Password Visibility Toggle -->
                       <div class="form-check form-switch">
                           <input class="form-check-input" type="checkbox" id="toggleAllVisibility">
                           <label class="form-check-label" for="toggleAllVisibility">Show All Passwords</label>
                       </div>
                   </div>

                   <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="changePasswordForm">
                       <!-- Current Password with error message if any -->
                       <div class="form-group">
                           <label for="currentPassword">Current Password</label>
                           <input type="password" name="currentPassword" class="form-control <?php echo (!empty($currentPassword_err)) ? 'is-invalid' : ''; ?>" id="currentPassword" required>
                           <span class="invalid-feedback"><?php echo $currentPassword_err; ?></span>
                       </div>
                       <!-- New Password with error message if any -->
                       <div class="form-group">
                           <label for="newPassword">New Password</label>
                           <input type="password" name="newPassword" class="form-control <?php echo (!empty($newPassword_err)) ? 'is-invalid' : ''; ?>" id="newPassword" required>
                           <span class="invalid-feedback"><?php echo $newPassword_err; ?></span>
                       </div>
                       <!-- Confirm Password with error message if any -->
                       <div class="form-group">
                           <label for="confirmPassword">Confirm Password</label>
                           <input type="password" name="confirmPassword" class="form-control <?php echo (!empty($confirmPassword_err)) ? 'is-invalid' : ''; ?>" id="confirmPassword" required>
                           <span class="invalid-feedback"><?php echo $confirmPassword_err; ?></span>
                       </div>
                       <button type="submit" class="btn btn-primary mt-4 w-100">Change Password</button>
                   </form>
               </div>
           </div>
       </div>
   </div>


    <script>
    document.getElementById('toggleAllVisibility').addEventListener('change', function() {
        const passwordFields = document.querySelectorAll('input[type=password], input[type=text]');
        passwordFields.forEach(field => {
            if (field.id === "currentPassword" || field.id === "newPassword" || field.id === "confirmPassword") {
                field.type = this.checked ? 'text' : 'password';
            }
        });
    });
    </script>

     <!--Using Bootstrap JS-->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
     <!--Importing javascript for the email, dark light mode and the active tab-->
     <script src="commonJavascript&Php/theme-and-navigation.js"></script>

    <script>
    function isStrongPassword(password) {
        //regex for strong password
        var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        //return true or false depending on whether entered password matches regex
        return regex.test(password);
    }
    </script>


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
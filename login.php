<?php
session_start(); // Initiates a new session or resumes an existing one.

include('commonJavascript&Php/session_check.php'); // Include session check logic
include "Database_config/database-connect.php";
$conn = null;

// Check if user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
    header("location: productivity.php");
    exit; // Prwevent further script execution after redirect
}

// Establishes a connection to the database.
function dbConnect() {
    global $dbservername, $dbname, $dbusername, $dbpassword, $conn;
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Validates user credentials against the database.
function checkCredentials($username, $password) {
    global $conn; // Accesses the global connection object.
    dbConnect(); // Ensures a database connection is established.
    // Prepares a SQL statement for execution, preventing SQL injection.
    $stmt = $conn->prepare("SELECT userID, password FROM Users WHERE username = ?");
    // Binds the username parameter to the SQL query.
    $stmt->bind_param("s", $username);
    // Executes the prepared statement.
    $stmt->execute();
    // Retrieves the query result.
    $result = $stmt->get_result();
    // Checks if exactly one row is returned, indicating a match.
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc(); // Fetches the result row as an associative array.
        if (password_verify($password, $row['password'])) {
            return $row['userID']; // Returns the userID if the password is correct.
        }
    }
    return false; // Returns false if authentication fails.
}

$login_error = ''; // Variable to hold login error messages.

// Processes the form submission.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Collects the username from the form.
    $password = $_POST['password']; // Collects the password from the form.
    $userID = checkCredentials($username, $password); // Validates the credentials.
    if ($userID !== false) {
        // Sets session variables upon successful login and redirects the user.
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['userID'] = $userID; // Store userID in session
        header("location: productivity.php"); // Redirects to the productivity page.
        exit();
    } else {
        // Sets an error message if login fails.
        $login_error = "Incorrect username or password.";
    }
}
?>
<!-- Defines the document type and version of HTML -->
<!DOCTYPE html>
<!-- Specifies the language of the document -->
<html lang="en">
<head>
    <!-- Sets the character encoding for the web page -->
    <meta charset="UTF-8" />
    <!-- Ensures compatibility with Internet Explorer -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Ensures the page is responsive on all devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Sets the title of the web page -->
    <title>Make-It-All - Login</title>
    <!-- Bootstrap CSS for styling and layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <!-- Custom CSS for specific styling needs -->
    <link rel="stylesheet" href="login.css" />
    <!-- Google Material Icons for UI elements -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Font Awesome for additional icons -->
    <!-- Link to a favicon to appear in the browser tab -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" href="makeItAllIcon.png">
</head>
<!-- Body of the document with a dark theme applied -->
<body class="login-body bg-dark" data-theme="dark">
<!-- Bootstrap container for centering the content -->
<div class="container ">
    <!-- Container for the theme switch elements -->
    <div class="theme-switch-wrapper">
        <!-- Sun icon for light mode -->
        <em id="sunIcon" class="fas fa-sun fa-lg"></em>
        <!-- Custom switch for toggling themes -->
        <div class="theme-switch" id="darkSwitch">
            <!-- Handle of the switch -->
            <div class="theme-switch-handle"></div>
        </div>
        <!-- Moon icon for dark mode -->
        <em id="moonIcon" class="fas fa-moon fa-lg"></em>
    </div>
    <!-- Row for centering the login form -->
    <div class="row justify-content-center align-items-stretch">
        <!-- Column for the logo -->
        <div class="col-12 text-center mb-4">
            <!-- The company logo -->
            <img src="images/MakeItAll.png" alt="MakeItAll Logo" class="logo" />
        </div>
        <!-- Responsive column for the login form -->
        <div class="col-xl-5 col-lg-6 col-md-10 col-sm-12 login-panel">
            <!-- Login form heading -->
            <h2 class="underlined-text">Login</h2>
            <!-- Secure form submission to the same page -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <!-- Form group for the username -->
                <div class="form-group">
                    <!-- Email input field -->
                    <input type="email" class="form-control" placeholder="User Name" name="username" id="emailInput" required />
                </div>
                <!-- Form group for the password with a relative position -->
                <div class="form-group position-relative">
                    <!-- Password input field -->
                    <input type="password" class="form-control" placeholder="Password" name="password" id="passwordInput" required />
                    <!-- Icon for toggling password visibility -->
                    <span class="password-toggle-icon material-icons" id="togglePasswordIcon" onclick="togglePasswordVisibility()">visibility_off</span>
                </div>

                <!-- PHP code to display login errors -->
                <?php if (!empty($login_error)): ?>
                <p class="alert alert-danger"><?php echo $login_error; ?></p>
                <?php endif; ?>
                <!-- Login button -->
                <button type="submit" class="btn btn-primary btn-block">Login Now</button>
                <!-- Text for users who need to register -->
                <p class="text-center mt-3 mb-3">Not signed up?</p>
                <!-- Container for the register button -->
                <div class="social-icons text-center">
                     <!-- Button to navigate to the registration page -->
                    <button type="button" onclick="window.location.href='register.php';" class="btn btn-outline-primary btn-block">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the container -->

<!-- External scripts for Bootstrap and jQuery functionalities -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<!-- Include the external JavaScript file for theme switching and password visibility toggle -->
<script src="commonJavascript&Php/theme-and-password-toggle.js"></script>
</body>

<script>
 //toggle password function
 function togglePasswordVisibility() {
     const input = document.getElementById("passwordInput");
     const icon = document.getElementById("togglePasswordIcon");
     if (input.type === "password") {
         input.type = "text";
         icon.textContent = 'visibility'; // Shows the password
     } else {
         input.type = "password";
         icon.textContent = 'visibility_off'; // Hides the password
     }
 }
</script>
</body>
</html>

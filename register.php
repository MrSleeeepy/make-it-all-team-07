<?php
session_start(); // Start a new session
include "Database_config/database-connect.php";
$conn = null;

// Function to connect to the database
function dbConnect() {
    global $dbservername, $dbname, $dbusername, $dbpassword, $conn;
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Function to check if the username already exists
function usernameExists($username) {
    global $conn;
    dbConnect();
    $stmt = $conn->prepare("SELECT userID FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to check if password meets strong criteria
function isStrongPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

$registration_error = '';

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $firstName = $_POST['firstName'];
    $surname = $_POST['surname'];

       // Email format validation
        if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !preg_match('/@make-it-all\.co\.uk$/', $username)) {
            $registration_error = 'Username must be a valid email ending with @make-it-all.co.uk.';
        } elseif (!isStrongPassword($password)) {
            $registration_error = 'Password does not meet the strong criteria .';
        } elseif ($password !== $confirmPassword) {
            $registration_error = 'Passwords do not match.';
        } elseif (usernameExists($username)) {
            $registration_error = 'Username already exists.';
        } else {
        dbConnect(); // Ensure database connection
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Insert into the database
        
        $stmt = $conn->prepare("INSERT INTO Users (username, password, firstName, surname,registrationDate) VALUES (?, ?, ?, ?,CURRENT_DATE())");
        $stmt->bind_param("ssss", $username, $hashedPassword, $firstName, $surname);
        if ($stmt->execute()) {
            header("location: login.php");
            exit();
        } else {
            $registration_error = 'Error: ' . $conn->error;
        }
    }
}
?>
<!-- Declares the document type and version of HTML -->
<!DOCTYPE html>
<!-- Opens the HTML document, specifying English language -->
<html lang="en">
<head>
 <!-- Specifies the character encoding for the document -->
    <meta charset="UTF-8">
    <!-- Ensures IE compatibility mode is set to the latest rendering engine -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Sets the viewport to ensure proper rendering and touch zooming on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <!-- Sets the title of the document, shown in the browser tab -->
    <title>Make-It-All - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="login-body bg-dark" data-theme="dark">
<!-- Bootstrap container to center the content and apply margin and padding -->
<div class="container">
    <!-- Contains the theme switch elements -->
    <div class="theme-switch-wrapper">
        <!-- Sun icon for light theme -->
        <em id="sunIcon" class="fas fa-sun fa-lg"></em>
        <!-- Custom switch element for toggling themes -->
        <div class="theme-switch" id="darkSwitch">
            <!-- Handle for the theme switch -->
            <div class="theme-switch-handle"></div>
        </div>
        <!-- Moon icon for dark theme -->
        <em id="moonIcon" class="fas fa-moon fa-lg"></em>
    </div>

    <div class="row justify-content-center align-items-stretch">
        <!-- Column for the logo, centered with margin bottom -->
        <div class="col-12 text-center mb-4">
            <!-- Logo image -->
            <img src="images/MakeItAll.png" alt="MakeItAll Logo" class="logo">
        </div>
        <!-- Responsive column sizes for the registration form -->
        <div class="col-xl-5 col-lg-6 col-md-10 col-sm-12 login-panel">
            <!-- Registration form heading -->
            <h2 class="underlined-text">Register</h2>
            <!-- PHP condition to check for registration errors -->
            <?php if (!empty($registration_error)): ?>
                <!-- Displays registration errors -->
                <p class="alert alert-danger"><?php echo $registration_error; ?></p>
            <?php endif; ?>
            <!-- Form for registration with POST method, action sanitized for security -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <!-- Input for first name -->
                    <input type="text" class="form-control" placeholder="First Name" name="firstName" required />
                </div>
                <div class="form-group">
                    <!-- Input for surname -->
                    <input type="text" class="form-control" placeholder="Surname" name="surname" required />
                </div>
                <div class="form-group">
                    <!-- Input for email/username -->
                    <input type="email" class="form-control" placeholder="Username" name="username" required />
                </div>
                <!-- Form group with relative positioning for the password toggle icon -->
                <div class="form-group position-relative">
                    <!-- Password input field -->
                    <input type="password" class="form-control" placeholder="Password" name="password" id="password" required />
                    <!-- Icon to toggle password visibility, calling the JS function -->
                    <span id="togglePasswordIcon1" class="password-toggle-icon material-icons" onclick="togglePasswordVisibility('password', 'togglePasswordIcon1')">visibility_off</span>
                </div>
                <div class="form-group position-relative">
                    <!-- Confirm password input field -->
                    <input type="password" class="form-control" placeholder="Confirm Password" name="confirmPassword" id="confirmPassword" required />
                    <!-- Icon to toggle confirm password visibility -->
                    <span id="togglePasswordIcon2" class="password-toggle-icon material-icons" onclick="togglePasswordVisibility('confirmPassword', 'togglePasswordIcon2')">visibility_off</span>
                </div>
                 <!-- Submit button for the form -->
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <!-- Text for users who already have an account -->
            <p class="text-center mt-3">Already have an account?</p>
            <div class="social-icons text-center">
                <!-- Button to navigate to the login page -->
                <button type="button" onclick="window.location.href='login.php';" class="btn btn-outline-primary btn-block">Login</button>
            </div>
        </div>
         <!-- Includes the external JavaScript file for theme switching and password visibility toggle functionality -->
         <script src="commonJavascript&Php/theme-and-password-toggle.js"></script>



</body>
</html>
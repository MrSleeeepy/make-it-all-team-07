<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <!-- Ensures proper rendering and touch zooming -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Error 404 - Page Not Found</title>
        <!-- Link to custom CSS for styling the 404 error page -->
        <link rel="stylesheet" href="404.css" />
        <!-- Bootstrap CSS for responsive design and styling -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Google Icons for the theme toggle icon -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- Font Awesome Icons for additional iconography -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

    <body class="error-body bg-dark">
        <div class="error-container">
            <!-- Theme toggler button -->
            <div class="theme-switch-wrapper">
                <span id="switch-icon" class="material-icons">brightness_3</span>
            </div>
            <h1>Error 404</h1>
            <p>The page you're looking for cannot be found.</p>
            <!-- Link to navigate back to the home page -->
            <a href="login.php" class="return-home">Return to Home Page</a>
        </div>

        <!-- Bootstrap Bundle JS for Bootstrap components functionality -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
                // Script for handling theme toggle between light and dark modes
                const currentTheme = localStorage.getItem("theme") ? localStorage.getItem("theme") : null;
                const switchIcon = document.getElementById("switch-icon");

                // Apply the saved theme from localStorage
                if (currentTheme) {
                    document.body.setAttribute("data-theme", currentTheme);

                    if (currentTheme === "dark") {
                        switchIcon.textContent = "brightness_3";
                    } else {
                        switchIcon.textContent = "wb_sunny";
                    }
                }

                // Event listener for the theme toggle switch
                switchIcon.addEventListener("click", function () {
                    if (document.body.getAttribute("data-theme") === "dark") {
                        document.body.setAttribute("data-theme", "light");
                        localStorage.setItem("theme", "light");
                        switchIcon.textContent = "wb_sunny";
                    } else {
                        document.body.setAttribute("data-theme", "dark");
                        localStorage.setItem("theme", "dark");
                        switchIcon.textContent = "brightness_3";
                    }
                });
            </script>
    </body>
</html>

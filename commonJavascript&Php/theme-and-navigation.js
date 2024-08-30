// Dark light functionality, look at the last choice of either dark or light
document.addEventListener("DOMContentLoaded", function () {
    let currentTheme = localStorage.getItem("theme") || "light"; // Default to light mode
    applyTheme(currentTheme);
});

document.getElementById("darkSwitch").addEventListener("click", function () {
    let currentTheme = document.body.getAttribute("data-theme") === "dark" ? "light" : "dark";
    applyTheme(currentTheme);
    localStorage.setItem("theme", currentTheme);
});

//changing from dark mode to light or vice versa..
function applyTheme(theme) {
    const switchHandle = document.querySelector(".theme-switch-handle");
    if (theme === "light") {
        document.body.setAttribute("data-theme", "light");
        switchHandle.style.left = "1px";
    } else {
        document.body.setAttribute("data-theme", "dark");
        switchHandle.style.left = "31px";
    }
}

// Active tab for navbar
document.addEventListener("DOMContentLoaded", function () {
    const currentFile = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll(".nav-link");

    navLinks.forEach((link) => {
        if ((currentFile === "knowledge.php" && link.textContent.toLowerCase() === "knowledge") ||
            (currentFile === "productivity.php" && link.textContent.toLowerCase() === "productivity") ||
            (currentFile === "manager.php" && link.textContent.toLowerCase() === "manager")) {
            link.classList.add("nav-link-underlined");
        } else {
            link.classList.remove("nav-link-underlined");
        }
    });
});

// Invitation modal functionality

// Wait for the DOM to fully load before executing the script
document.addEventListener("DOMContentLoaded", function () {
    // Add an event listener to the primary button within the invitation modal
    document.querySelector("#invitationModal .btn-primary").addEventListener("click", async function () {
        // Get the input element for user email
        const userEmailInput = document.getElementById("userEmail");
        // Retrieve the value (email address) entered by the user
        const userEmail = userEmailInput.value;

        // Validate the email address using a regular expression
        if (!userEmail || !/\S+@\S+\.\S+/.test(userEmail)) {
            alert("Please enter a valid email address.");
            return; // Exit the function if the email is not valid
        }

        // Prepare the data to be sent in the POST request
        const formData = new FormData();
        formData.append('email', userEmail);

        try {
            // Use fetch API to send a POST request to 'send-invitation.php'
            const response = await fetch('send-invitation.php', {
                method: 'POST',
                body: formData
            });

            // Wait for the response and get the response text
            const textResponse = await response.text();

            // Check if the server responded with "Success"
            if (textResponse.trim() === "Success") {
                alert("An invitation email has been sent to " + userEmail + ".");
                // Retrieve the modal element and hide it using Bootstrap's Modal API
                const modalElement = document.getElementById('invitationModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                modalInstance.hide();
                userEmailInput.value = ''; // Clear the input after successful operation
            } else {
                // Inform the user if there was a problem with sending the invitation
                alert("There was a problem sending the invitation.");
            }
        } catch (error) {
            // Log and alert the user of any error during the fetch operation
            console.error('Error:', error);
            alert("There was an error sending the invitation.");
        }
    });
});


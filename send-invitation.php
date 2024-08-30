<?php
// Check if the form was submitted via POST and the email field is not empty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['email'])) {
    // Assign the submitted email to a variable
    $toEmail = $_POST['email'];
    // Define the email subject
    $subject = "Invitation to Register";
    // Compose the email message with a link for registration
    $message = "You are invited to register at our site. Please visit the following link to proceed: http://34.78.115.126/register.php";
    // Set email headers, including the sender's email, reply-to address, and X-Mailer version
    $headers = 'From: Make-it-all.co.uk@hotmail.com' . "\r\n" .
               'Reply-To: Make-it-all.co.uk@hotmail.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    // Attempt to send the email and check if it was successful
    if (mail($toEmail, $subject, $message, $headers)) {
        // If email was successfully sent, print a success message
        echo "Success";
    } else {
         // If email sending failed, print an error message
        echo "Error";
    }
} else {
    // If the POST request does not include an email, print a notification message
    echo "No email provided!";
}
?>

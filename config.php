<?php
// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aquasave');

// Establish database connection
function connect_db() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect with message
function redirect($location, $message = '', $message_type = 'info') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
    }
    header("Location: $location");
    exit();
}

// Function to display message
function display_message() {
    if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        
        // Clear the message from session
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        // Set appropriate color based on message type
        $bg_color = 'bg-blue-100 border-blue-200 text-blue-700';
        $icon = '<i class="fas fa-info-circle mr-2"></i>';
        
        if ($message_type === 'success') {
            $bg_color = 'bg-green-100 border-green-200 text-green-700';
            $icon = '<i class="fas fa-check-circle mr-2"></i>';
        } elseif ($message_type === 'error') {
            $bg_color = 'bg-red-100 border-red-200 text-red-700';
            $icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
        } elseif ($message_type === 'warning') {
            $bg_color = 'bg-yellow-100 border-yellow-200 text-yellow-700';
            $icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
        }
        
        return "<div class=\"$bg_color border px-4 py-3 rounded mb-4 flex items-center\">$icon $message</div>";
    }
    
    return '';
}

// Function to send email notifications
function send_email($to, $subject, $message) {
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: AquaSave <noreply@aquasave.com>" . "\r\n";
    
    // Create HTML email template
    $email_template = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #3b82f6; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9fafb; }
            .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>AquaSave</h1>
            </div>
            <div class='content'>
                $message
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " AquaSave. All rights reserved.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    return mail($to, $subject, $email_template, $headers);
}
?>

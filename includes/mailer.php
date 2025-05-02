<?php
/**
 * AquaSave Mailer - A simple email sender class using PHPMailer
 */
class AquaSaveMailer {
    // SMTP configuration
    private $host = 'smtp.gmail.com';
    private $username = 'jamuar66@gmail.com'; // Replace with your Gmail address
    private $password = 'npux tmyn ueck cvxg'; // Replace with an app password from Google account
    private $port = 587;
    private $encryption = 'tls';
    private $from_email = 'noreply@aquasave.com';
    private $from_name = 'AquaSave';
    
    /**
     * Send an email using the configured SMTP settings
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email content (HTML)
     * @param array $attachments Optional array of file paths to attach
     * @return bool True on success, false on failure
     */
    public function send($to, $subject, $message, $attachments = []) {
        // Check if recipient email is provided
        if (empty($to)) {
            return false;
        }
        
        // Format HTML email message
        $htmlMessage = $this->formatEmailTemplate($message);
        
        // Set email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->from_name} <{$this->from_email}>" . "\r\n";
        
        // For now, use PHP's mail() function for simplicity
        // In production, you would use PHPMailer with SMTP here
        return mail($to, $subject, $htmlMessage, $headers);
    }
    
    /**
     * Format email content with AquaSave template
     * 
     * @param string $content The email body content
     * @return string Formatted HTML email
     */
    private function formatEmailTemplate($content) {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #3b82f6; color: #fff; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9fafb; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
                .btn { background-color: #3b82f6; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>AquaSave</h1>
                </div>
                <div class='content'>
                    $content
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " AquaSave. All rights reserved.</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
    
    /**
     * Generate a welcome email for newly activated users
     * 
     * @param string $name User's full name
     * @return string Formatted welcome message
     */
    public function getWelcomeMessage($name) {
        return "<h2>Welcome to AquaSave!</h2>
            <p>Hello $name,</p>
            <p>Welcome to AquaSave! The admin has accepted your request to join.</p>
            <p>You can now log in to your account and begin using all features of the platform to monitor and optimize your water usage.</p>
            <p>Thank you for joining us in our mission to conserve water and promote sustainable practices.</p>
            <p><a href='http://localhost/AquaSave/auth/login.php' class='btn'>Log In Now</a></p>
            <p>If you have any questions, please don't hesitate to contact our support team.</p>
            <p>Best regards,<br>The AquaSave Team</p>";
    }
}
?>

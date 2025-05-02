<?php
require_once '../config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
redirect('../index.php', 'You have been logged out successfully', 'success');
?>

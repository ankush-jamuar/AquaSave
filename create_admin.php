<?php
require_once 'config.php';

// Connect to database
$conn = connect_db();

// Generate a new password hash for 'admin123'
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// First, try to update existing admin user
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@aquasave.com'");
$stmt->bind_param("s", $hashed_password);
$stmt->execute();

// If no rows were updated, create a new admin user
if ($stmt->affected_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, role, status) VALUES ('admin@aquasave.com', ?, 'Admin', 'User', 'admin', 'active')");
    $stmt->bind_param("s", $hashed_password);
    $stmt->execute();
    echo "New admin user created!<br>";
} else {
    echo "Existing admin user password updated!<br>";
}

echo "Admin credentials:<br>";
echo "Email: admin@aquasave.com<br>";
echo "Password: admin123";

$conn->close();
?>

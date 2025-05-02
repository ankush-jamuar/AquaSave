<?php
require_once '../config.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('../dashboard/index.php');
}

$email = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $conn = connect_db();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, email, password, first_name, last_name, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] === 'active') {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        redirect('../admin/index.php', 'Welcome back, ' . $_SESSION['user_name'], 'success');
                    } else {
                        redirect('../dashboard/index.php', 'Welcome back, ' . $_SESSION['user_name'], 'success');
                    }
                } else {
                    $error = 'Your account is pending activation. Please contact admin.';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AquaSave</title>
    <link rel="icon" href="../assets/images/icon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-blue-50 font-sans min-h-screen flex flex-col">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="../index.php" class="text-2xl font-bold text-blue-600"><i class="fas fa-water mr-2"></i>AquaSave</a>
            </div>
            <div class="space-x-4">
                <a href="register.php" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Sign Up</a>
            </div>
        </nav>
    </header>

    <!-- Login Form -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login to Your Account</h1>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php echo display_message(); ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Login</button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Sign up</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <h3 class="text-xl font-bold mb-2">AquaSave</h3>
                    <p class="text-gray-400 text-sm">Smart water conservation platform</p>
                </div>
                <div class="text-center md:text-right">
                    <h3 class="text-xl font-bold mb-2">Contact</h3>
                    <p class="text-gray-400 text-sm"><i class="fas fa-phone mr-2"></i>8825054241</p>
                    <p class="text-gray-400 text-sm"><i class="fas fa-map-marker-alt mr-2"></i>LPU, Jalandhar</p>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-4 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> AquaSave. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        const toggleButton = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');
        
        toggleButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    </script>
</body>
</html>

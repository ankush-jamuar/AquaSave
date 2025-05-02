<?php
require_once '../config.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('../dashboard/index.php');
}

$email = $first_name = $last_name = $address = $city = $state = $zip_code = $phone = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $address = sanitize_input($_POST['address']);
    $city = sanitize_input($_POST['city']);
    $state = sanitize_input($_POST['state']);
    $zip_code = sanitize_input($_POST['zip_code']);
    $phone = sanitize_input($_POST['phone']);
    
    // Validate inputs
    if (empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        $conn = connect_db();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, address, city, state, zip_code, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'pending')");
            $stmt->bind_param("sssssssss", $email, $hashed_password, $first_name, $last_name, $address, $city, $state, $zip_code, $phone);
            
            if ($stmt->execute()) {
                redirect('login.php', 'Registration successful! Your account is pending activation. You will be notified once activated.', 'success');
            } else {
                $error = 'Registration failed. Please try again later.';
            }
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
    <title>Register - AquaSave</title>
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
                <a href="login.php" class="text-blue-600 hover:text-blue-800">Login</a>
            </div>
        </nav>
    </header>

    <!-- Registration Form -->
    <main class="flex-1 flex items-center justify-center p-6 py-10">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create Your Account</h1>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php echo display_message(); ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="first_name" class="block text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="password" class="block text-gray-700 mb-2">Password *</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Min. 8 characters</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-gray-700 mb-2">Confirm Password *</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="password-match text-sm mt-1 hidden text-red-500">Passwords do not match</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 mb-2">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo $address; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="city" class="block text-gray-700 mb-2">City</label>
                        <input type="text" id="city" name="city" value="<?php echo $city; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="state" class="block text-gray-700 mb-2">State</label>
                        <input type="text" id="state" name="state" value="<?php echo $state; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="zip_code" class="block text-gray-700 mb-2">ZIP Code</label>
                        <input type="text" id="zip_code" name="zip_code" value="<?php echo $zip_code; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="phone" class="block text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Register</button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-gray-600">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login</a></p>
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
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
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
        
        // Password confirmation check
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.querySelector('.password-match');
        const registerForm = document.querySelector('form');
        
        const checkPasswordMatch = function() {
            if (confirmPassword.value && password.value !== confirmPassword.value) {
                passwordMatch.classList.remove('hidden');
                passwordMatch.classList.add('text-red-500');
                passwordMatch.textContent = 'Passwords do not match';
                return false;
            } else if (confirmPassword.value && password.value === confirmPassword.value) {
                passwordMatch.classList.remove('hidden');
                passwordMatch.classList.remove('text-red-500');
                passwordMatch.classList.add('text-green-500');
                passwordMatch.textContent = 'Passwords match';
                return true;
            } else {
                passwordMatch.classList.add('hidden');
                return true;
            }
        };
        
        // Check passwords on input
        password.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
        
        // Prevent form submission if passwords don't match
        registerForm.addEventListener('submit', function(e) {
            if (!checkPasswordMatch()) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
            }
        });
    });
    </script>
</body>
</html>

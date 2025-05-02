<?php
require_once '../config.php';

// Set page variables
$page_title = 'My Profile';
$active_page = 'profile';

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Delete account
    if ($_POST['action'] === 'delete_account') {
        $conn = connect_db();
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Delete user's conservation goals
            $stmt = $conn->prepare("DELETE FROM conservation_goals WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Get all devices for this user
            $stmt = $conn->prepare("SELECT id FROM devices WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Delete water usage data for each device
            while ($device = $result->fetch_assoc()) {
                $device_id = $device['id'];
                $stmt = $conn->prepare("DELETE FROM water_usage WHERE device_id = ?");
                $stmt->bind_param("i", $device_id);
                $stmt->execute();
            }
            
            // Delete user's devices
            $stmt = $conn->prepare("DELETE FROM devices WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Delete any other related user data (if there are other tables with user data)
            
            // Finally delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Destroy session and redirect to login page
            session_destroy();
            header('Location: ../index.php?msg=account_deleted');
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = 'Failed to delete account: ' . $e->getMessage();
        }
        
        $conn->close();
    }
    
    // Update profile information
    if ($_POST['action'] === 'update_profile') {
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $city = sanitize_input($_POST['city']);
        $state = sanitize_input($_POST['state']);
        $zip_code = sanitize_input($_POST['zip_code']);
        $phone = sanitize_input($_POST['phone']);
        
        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'First name, last name, and email are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            $conn = connect_db();
            
            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email address already in use by another account';
            } else {
                // Update user information
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, state = ?, zip_code = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $address, $city, $state, $zip_code, $phone, $user_id);
                
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully';
                    
                    // Update session variables
                    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                    $_SESSION['user_email'] = $email;
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
            
            $stmt->close();
            $conn->close();
        }
    }
    
    // Change password
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters long';
        } else {
            $conn = connect_db();
            
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($current_password, $user['password'])) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = 'Password updated successfully';
                    } else {
                        $error = 'Failed to update password. Please try again.';
                    }
                } else {
                    $error = 'Current password is incorrect';
                }
            } else {
                $error = 'User not found';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// Get user information
$conn = connect_db();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$conn->close();

// Include header
include_once '../includes/header.php';
?>

<!-- Profile Content -->
<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Profile Information</h2>
            </div>
            <div class="p-6">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="block text-gray-700 mb-2">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-gray-700 mb-2">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 mb-2">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo $user['address']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="city" class="block text-gray-700 mb-2">City</label>
                            <input type="text" id="city" name="city" value="<?php echo $user['city']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="state" class="block text-gray-700 mb-2">State</label>
                            <input type="text" id="state" name="state" value="<?php echo $user['state']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="zip_code" class="block text-gray-700 mb-2">ZIP Code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?php echo $user['zip_code']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
            </div>
            <div class="p-6">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-4">
                        <label for="current_password" class="block text-gray-700 mb-2">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="block text-gray-700 mb-2">New Password *</label>
                        <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <p class="text-sm text-gray-500 mt-1">Min. 8 characters</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Account Summary -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Account Summary</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-3xl font-bold text-blue-600">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-gray-700">Status</span>
                        <span class="px-2 py-1 rounded-full text-xs <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-gray-700">Role</span>
                        <span><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-gray-700">Member Since</span>
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
                
                <?php
                // Get statistics about the user
                $conn = connect_db();
                
                // Get device count
                $stmt = $conn->prepare("SELECT COUNT(*) as device_count FROM devices WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $device_result = $stmt->get_result();
                $device_count = $device_result->fetch_assoc()['device_count'];
                
                // Get total water usage
                $stmt = $conn->prepare("
                    SELECT SUM(wu.usage_amount) as total_usage
                    FROM water_usage wu
                    JOIN devices d ON wu.device_id = d.id
                    WHERE d.user_id = ?
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $usage_result = $stmt->get_result();
                $total_usage = $usage_result->fetch_assoc()['total_usage'] ?? 0;
                
                // Get active goals count
                $stmt = $conn->prepare("SELECT COUNT(*) as goal_count FROM conservation_goals WHERE user_id = ? AND status = 'active'");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $goal_result = $stmt->get_result();
                $active_goals = $goal_result->fetch_assoc()['goal_count'];
                
                $conn->close();
                ?>
                
                <div class="mt-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Your Stats</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <span class="block text-2xl font-bold text-blue-600"><?php echo $device_count; ?></span>
                            <span class="text-sm text-gray-700">Devices</span>
                        </div>
                        
                        <div class="bg-green-50 p-3 rounded-lg text-center">
                            <span class="block text-2xl font-bold text-green-600"><?php echo $active_goals; ?></span>
                            <span class="text-sm text-gray-700">Active Goals</span>
                        </div>
                        
                        <div class="bg-purple-50 p-3 rounded-lg text-center col-span-2">
                            <span class="block text-2xl font-bold text-purple-600"><?php echo number_format($total_usage, 1); ?></span>
                            <span class="text-sm text-gray-700">Total Water Usage (L)</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Account Actions</h3>
                    
                    <div class="space-y-2">
                        <?php if ($user['status'] === 'pending'): ?>
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-3 rounded">
                                <p>Your account is pending activation. Please wait for administrator approval.</p>
                            </div>
                        <?php endif; ?>
                        
                        <button id="exportDataBtn" class="w-full px-4 py-2 border border-blue-500 text-blue-500 rounded hover:bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-file-export mr-2"></i> Export My Data
                        </button>
                        
                        <button id="deleteAccountBtn" class="w-full px-4 py-2 border border-red-500 text-red-500 rounded hover:bg-red-50 flex items-center justify-center">
                            <i class="fas fa-user-times mr-2"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div id="deleteAccountModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Confirm Account Deletion</h3>
            <button class="close-modal text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4 block text-center"></i>
                <p class="text-gray-700 mb-4">Are you sure you want to delete your account? This action cannot be undone and will result in the loss of all your data.</p>
                <p class="text-gray-700">To confirm, please type "DELETE" below:</p>
                <input type="text" id="deleteConfirmation" class="w-full mt-2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="flex justify-end">
                <button class="close-modal px-4 py-2 border border-gray-300 rounded mr-2 hover:bg-gray-100">Cancel</button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" disabled>Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal handling
    const modals = document.querySelectorAll('.modal');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    deleteAccountBtn.addEventListener('click', () => {
        const modal = document.getElementById('deleteAccountModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
    
    const closeModal = (modal) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };
    
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            closeModal(modal);
        });
    });
    
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });
    
    // Close modals on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal);
                }
            });
        }
    });
    
    // Handle delete confirmation
    const deleteConfirmation = document.getElementById('deleteConfirmation');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    deleteConfirmation.addEventListener('input', function() {
        confirmDeleteBtn.disabled = this.value !== 'DELETE';
    });
    
    confirmDeleteBtn.addEventListener('click', function() {
        // Create and submit form to delete account
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_account';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    });
    
    // Handle data export
    const exportDataBtn = document.getElementById('exportDataBtn');
    
    exportDataBtn.addEventListener('click', function() {
        alert('Data export would be processed here. In a real application, this would generate a download of all your data.');
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>

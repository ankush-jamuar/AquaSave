<?php
require_once '../config.php';

// Set page variables
$page_title = 'User Management';
$active_page = 'users';

// Check if user is admin
if (!is_admin()) {
    redirect('../auth/login.php', 'You do not have permission to access the admin area', 'error');
}

// Initialize variables
$error = '';
$success = '';

// Process user actions (activate, deactivate, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    $conn = connect_db();
    
    // Don't allow actions on own account
    if ($user_id === $_SESSION['user_id']) {
        $error = 'You cannot modify your own account from this page';
    } else {
        if ($action === 'activate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = 'User activated successfully';
            } else {
                $error = 'Failed to activate user';
            }
        } elseif ($action === 'deactivate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = 'User deactivated successfully';
            } else {
                $error = 'Failed to deactivate user';
            }
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = 'User deleted successfully';
            } else {
                $error = 'Failed to delete user';
            }
        }
    }
    
    $conn->close();
}

// Process form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $user_id = intval($_POST['user_id']);
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $status = sanitize_input($_POST['status']);
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'First name, last name, and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!in_array($role, ['admin', 'user', 'service_provider', 'expert'])) {
        $error = 'Invalid role selected';
    } elseif (!in_array($status, ['pending', 'active', 'inactive'])) {
        $error = 'Invalid status selected';
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
            // Update user
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $first_name, $last_name, $email, $role, $status, $user_id);
            
            if ($stmt->execute()) {
                $success = 'User updated successfully';
            } else {
                $error = 'Failed to update user';
            }
        }
        
        $conn->close();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? trim(sanitize_input($_GET['status'])) : '';
$role_filter = isset($_GET['role']) ? trim(sanitize_input($_GET['role'])) : '';
$search = isset($_GET['search']) ? trim(sanitize_input($_GET['search'])) : '';

// Determine if we're showing the edit form
$show_edit_form = false;
$user_to_edit = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user_to_edit = $result->fetch_assoc();
        $show_edit_form = true;
    }
    
    $conn->close();
}

// Get users based on filters
$conn = connect_db();
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($role_filter)) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR role LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AquaSave</title>
    <link rel="icon" href="../assets/images/icon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="../index.php" class="text-2xl font-bold text-blue-600"><i class="fas fa-water mr-2"></i>AquaSave</a>
                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">ADMIN</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center text-gray-700 focus:outline-none">
                        <span class="mr-2"><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-user-circle text-2xl"></i>
                    </button>
                    <div class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-md shadow-xl z-10 hidden group-hover:block">
                        <a href="../dashboard/index.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-500 hover:text-white">User Dashboard</a>
                        <a href="../auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-500 hover:text-white">Logout</a>
                    </div>
                </div>
                <!-- Direct logout button -->
                <a href="../auth/logout.php" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </nav>
    </header>

    <div class="flex flex-1">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-800 text-white hidden md:block">
            <div class="p-6">
                <nav>
                    <ul class="space-y-2">
                        <li>
                            <a href="index.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'dashboard' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-tachometer-alt w-6 text-center"></i>
                                <span class="ml-2">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'users' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-users w-6 text-center"></i>
                                <span class="ml-2">User Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="tips.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'tips' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-lightbulb w-6 text-center"></i>
                                <span class="ml-2">Tip Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="settings.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'settings' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-cog w-6 text-center"></i>
                                <span class="ml-2">System Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800"><?php echo $page_title; ?></h1>
                <p class="text-gray-600">Manage users and their permissions</p>
            </div>
            
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
            
            <?php echo display_message(); ?>
            
            <?php if ($show_edit_form): ?>
                <!-- Edit User Form -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Edit User</h2>
                    
                    <form method="POST" action="users.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" value="<?php echo $user_to_edit['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="first_name" class="block text-gray-700 mb-2">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $user_to_edit['first_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-gray-700 mb-2">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $user_to_edit['last_name']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo $user_to_edit['email']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="role" class="block text-gray-700 mb-2">Role *</label>
                                <select id="role" name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="user" <?php echo $user_to_edit['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user_to_edit['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="service_provider" <?php echo $user_to_edit['role'] === 'service_provider' ? 'selected' : ''; ?>>Service Provider</option>
                                    <option value="expert" <?php echo $user_to_edit['role'] === 'expert' ? 'selected' : ''; ?>>Expert</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-gray-700 mb-2">Status *</label>
                                <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="active" <?php echo $user_to_edit['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo $user_to_edit['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="inactive" <?php echo $user_to_edit['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 mb-4">
                                <strong>Created:</strong> <?php echo date('M d, Y g:i A', strtotime($user_to_edit['created_at'])); ?> |
                                <strong>Last Updated:</strong> <?php echo date('M d, Y g:i A', strtotime($user_to_edit['updated_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <a href="users.php" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Changes</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- User Search and Filters -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <form method="GET" action="users.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-gray-700 mb-2">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Email, name..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="role" class="block text-gray-700 mb-2">Role</label>
                            <select id="role" name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="service_provider" <?php echo $role_filter === 'service_provider' ? 'selected' : ''; ?>>Service Provider</option>
                                <option value="expert" <?php echo $role_filter === 'expert' ? 'selected' : ''; ?>>Expert</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mr-2">Filter</button>
                            <a href="users.php" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Users List -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Users</h2>
                        <span class="text-gray-600"><?php echo $users_result->num_rows; ?> users found</span>
                    </div>
                    
                    <?php if ($users_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 text-left">ID</th>
                                        <th class="py-3 px-4 text-left">Name</th>
                                        <th class="py-3 px-4 text-left">Email</th>
                                        <th class="py-3 px-4 text-left">Role</th>
                                        <th class="py-3 px-4 text-left">Status</th>
                                        <th class="py-3 px-4 text-left">Created</th>
                                        <th class="py-3 px-4 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4"><?php echo $user['id']; ?></td>
                                            <td class="py-3 px-4 font-medium"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                            <td class="py-3 px-4"><?php echo $user['email']; ?></td>
                                            <td class="py-3 px-4"><?php echo ucfirst($user['role']); ?></td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 rounded-full text-xs <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : ($user['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td class="py-3 px-4">
                                                <div class="flex space-x-2">
                                                    <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                        <?php if ($user['status'] === 'pending'): ?>
                                                            <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" class="text-green-500 hover:text-green-700" title="Activate" onclick="return confirm('Are you sure you want to activate this user?');">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php elseif ($user['status'] === 'active'): ?>
                                                            <a href="users.php?action=deactivate&id=<?php echo $user['id']; ?>" class="text-yellow-500 hover:text-yellow-700" title="Deactivate" onclick="return confirm('Are you sure you want to deactivate this user?');">
                                                                <i class="fas fa-ban"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" class="text-green-500 hover:text-green-700" title="Activate" onclick="return confirm('Are you sure you want to activate this user?');">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700" title="Delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-center">
                            <p class="text-gray-600">No users found matching your criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> AquaSave. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

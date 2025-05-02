<?php
require_once '../config.php';

// Set page variables
$page_title = 'Tip Management';
$active_page = 'tips';

// Check if user is admin
if (!is_admin()) {
    redirect('../auth/login.php', 'You do not have permission to access the admin area', 'error');
}

// Initialize variables
$title = $content = $category = $location_relevance = '';
$error = '';
$success = '';
$tip_to_edit = null;

// Process form submission for adding a new tip
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Get and sanitize inputs
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $category = sanitize_input($_POST['category']);
    $location_relevance = sanitize_input($_POST['location_relevance']);
    
    // Validate inputs
    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Title, content, and category are required';
    } else {
        $conn = connect_db();
        
        // Insert new tip
        $stmt = $conn->prepare("INSERT INTO tips (title, content, category, location_relevance) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $category, $location_relevance);
        
        if ($stmt->execute()) {
            $success = 'Tip added successfully';
            // Clear form fields
            $title = $content = $category = $location_relevance = '';
        } else {
            $error = 'Failed to add tip. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process form submission for updating a tip
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $tip_id = intval($_POST['tip_id']);
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $category = sanitize_input($_POST['category']);
    $location_relevance = sanitize_input($_POST['location_relevance']);
    
    // Validate inputs
    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Title, content, and category are required';
    } else {
        $conn = connect_db();
        
        // Update tip
        $stmt = $conn->prepare("UPDATE tips SET title = ?, content = ?, category = ?, location_relevance = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $content, $category, $location_relevance, $tip_id);
        
        if ($stmt->execute()) {
            $success = 'Tip updated successfully';
        } else {
            $error = 'Failed to update tip. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process tip deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $tip_id = intval($_GET['id']);
    
    $conn = connect_db();
    $stmt = $conn->prepare("DELETE FROM tips WHERE id = ?");
    $stmt->bind_param("i", $tip_id);
    
    if ($stmt->execute()) {
        $success = 'Tip deleted successfully';
    } else {
        $error = 'Failed to delete tip';
    }
    
    $stmt->close();
    $conn->close();
}

// Get tip to edit
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $tip_id = intval($_GET['id']);
    
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT * FROM tips WHERE id = ?");
    $stmt->bind_param("i", $tip_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $tip_to_edit = $result->fetch_assoc();
        $title = $tip_to_edit['title'];
        $content = $tip_to_edit['content'];
        $category = $tip_to_edit['category'];
        $location_relevance = $tip_to_edit['location_relevance'];
    }
    
    $stmt->close();
    $conn->close();
}

// Determine if we're showing the add/edit form
$show_form = isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit');

// Get filter parameters
$category_filter = isset($_GET['category']) ? trim(sanitize_input($_GET['category'])) : '';
$search = isset($_GET['search']) ? trim(sanitize_input($_GET['search'])) : '';

// Get all tips based on filters
$conn = connect_db();
$query = "SELECT * FROM tips WHERE 1=1";
$params = [];
$types = "";

if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " AND (title LIKE ? OR content LIKE ? OR category LIKE ? OR location_relevance LIKE ?)";
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
$tips_result = $stmt->get_result();

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
                <p class="text-gray-600">Create and manage water saving tips for users</p>
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
            
            <?php if ($show_form): ?>
                <!-- Add/Edit Tip Form -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <?php echo isset($tip_to_edit) ? 'Edit Tip' : 'Add New Tip'; ?>
                    </h2>
                    
                    <form method="POST" action="tips.php">
                        <input type="hidden" name="action" value="<?php echo isset($tip_to_edit) ? 'update' : 'add'; ?>">
                        <?php if (isset($tip_to_edit)): ?>
                            <input type="hidden" name="tip_id" value="<?php echo $tip_to_edit['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 mb-2">Title *</label>
                            <input type="text" id="title" name="title" value="<?php echo $title; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="block text-gray-700 mb-2">Content *</label>
                            <textarea id="content" name="content" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><?php echo $content; ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="category" class="block text-gray-700 mb-2">Category *</label>
                                <select id="category" name="category" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Category</option>
                                    <option value="Home" <?php echo $category === 'Home' ? 'selected' : ''; ?>>Home</option>
                                    <option value="Garden" <?php echo $category === 'Garden' ? 'selected' : ''; ?>>Garden</option>
                                    <option value="Personal" <?php echo $category === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="location_relevance" class="block text-gray-700 mb-2">Location Relevance</label>
                                <input type="text" id="location_relevance" name="location_relevance" value="<?php echo $location_relevance; ?>" placeholder="e.g., 'All' or 'Dry regions, Hot regions'" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-sm text-gray-500 mt-1">Enter 'All' for global tips or specific regions separated by commas</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <a href="tips.php" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                <?php echo isset($tip_to_edit) ? 'Update Tip' : 'Add Tip'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Tip Search and Filters -->
                <div class="flex flex-wrap justify-between items-center mb-6">
                    <div class="w-full md:w-auto mb-4 md:mb-0">
                        <a href="tips.php?action=add" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            <i class="fas fa-plus mr-1"></i> Add New Tip
                        </a>
                    </div>
                    
                    <div class="w-full md:w-auto">
                        <form method="GET" action="tips.php" class="flex flex-wrap md:flex-nowrap gap-2">
                            <div>
                                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search tips..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <select name="category" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Categories</option>
                                    <option value="Home" <?php echo $category_filter === 'Home' ? 'selected' : ''; ?>>Home</option>
                                    <option value="Garden" <?php echo $category_filter === 'Garden' ? 'selected' : ''; ?>>Garden</option>
                                    <option value="Personal" <?php echo $category_filter === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                                </select>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Filter</button>
                                <a href="tips.php" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tips List -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Water Saving Tips</h2>
                        <span class="text-gray-600"><?php echo $tips_result->num_rows; ?> tips found</span>
                    </div>
                    
                    <?php if ($tips_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 text-left">ID</th>
                                        <th class="py-3 px-4 text-left">Title</th>
                                        <th class="py-3 px-4 text-left">Category</th>
                                        <th class="py-3 px-4 text-left">Location</th>
                                        <th class="py-3 px-4 text-left">Created</th>
                                        <th class="py-3 px-4 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($tip = $tips_result->fetch_assoc()): ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4"><?php echo $tip['id']; ?></td>
                                            <td class="py-3 px-4 font-medium">
                                                <div class="tooltip relative" data-tooltip="<?php echo htmlspecialchars($tip['content']); ?>">
                                                    <?php echo $tip['title']; ?>
                                                    <span class="tooltip-text hidden absolute left-0 top-full mt-2 p-2 bg-gray-800 text-white text-sm rounded shadow-lg z-10 w-64"></span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                                    <?php echo $tip['category']; ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4"><?php echo $tip['location_relevance']; ?></td>
                                            <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($tip['created_at'])); ?></td>
                                            <td class="py-3 px-4">
                                                <div class="flex space-x-2">
                                                    <a href="tips.php?action=edit&id=<?php echo $tip['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="tips.php?action=delete&id=<?php echo $tip['id']; ?>" class="text-red-500 hover:text-red-700" title="Delete" onclick="return confirm('Are you sure you want to delete this tip?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-center">
                            <p class="text-gray-600">No tips found matching your criteria.</p>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tooltips for tip content
        const tooltips = document.querySelectorAll('.tooltip');
        
        tooltips.forEach(tooltip => {
            const tooltipText = tooltip.querySelector('.tooltip-text');
            const content = tooltip.getAttribute('data-tooltip');
            
            tooltip.addEventListener('mouseenter', () => {
                tooltipText.textContent = content;
                tooltipText.classList.remove('hidden');
            });
            
            tooltip.addEventListener('mouseleave', () => {
                tooltipText.classList.add('hidden');
            });
        });
    });
    </script>
</body>
</html>

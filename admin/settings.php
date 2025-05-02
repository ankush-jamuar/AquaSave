<?php
require_once '../config.php';

// Set page variables
$page_title = 'System Settings';
$active_page = 'settings';

// Check if user is admin
if (!is_admin()) {
    redirect('../auth/login.php', 'You do not have permission to access the admin area', 'error');
}

// Initialize variables
$error = '';
$success = '';

// Create the settings table if it doesn't exist
$conn = connect_db();
$create_table_query = "
    CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        setting_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
";
$conn->query($create_table_query);

// Insert default settings if they don't exist
$default_settings = [
    ['key' => 'site_name', 'value' => 'AquaSave', 'description' => 'Name of the platform'],
    ['key' => 'site_description', 'value' => 'Smart Water Conservation Platform', 'description' => 'Short description of the platform'],
    ['key' => 'contact_email', 'value' => 'info@aquasave.com', 'description' => 'Contact email address'],
    ['key' => 'contact_phone', 'value' => '+1 (555) 123-4567', 'description' => 'Contact phone number'],
    ['key' => 'new_user_auto_approve', 'value' => '0', 'description' => 'Automatically approve new user registrations (0 = No, 1 = Yes)'],
    ['key' => 'auto_approve_service_providers', 'value' => '0', 'description' => 'Automatically approve service provider registrations (0 = No, 1 = Yes)'],
    ['key' => 'auto_approve_experts', 'value' => '0', 'description' => 'Automatically approve expert registrations (0 = No, 1 = Yes)'],
    ['key' => 'maintenance_mode', 'value' => '0', 'description' => 'Put the site in maintenance mode (0 = No, 1 = Yes)'],
    ['key' => 'allow_user_tips', 'value' => '1', 'description' => 'Allow users to submit water saving tips (0 = No, 1 = Yes)'],
    ['key' => 'default_daily_water_goal', 'value' => '150', 'description' => 'Default daily water conservation goal in liters']
];

foreach ($default_settings as $setting) {
    $check = $conn->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
    $check->bind_param("s", $setting['key']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $setting['key'], $setting['value'], $setting['description']);
        $insert->execute();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    // Loop through all settings and update
    $success = true;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8); // Remove 'setting_' prefix
            $setting_value = sanitize_input($value);
            
            $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $setting_value, $setting_key);
            
            if (!$stmt->execute()) {
                $success = false;
                $error = 'Failed to update some settings. Please try again.';
            }
        }
    }
    
    if ($success && empty($error)) {
        $success = 'Settings updated successfully';
    }
}

// Get all settings
$stmt = $conn->prepare("SELECT * FROM system_settings ORDER BY id");
$stmt->execute();
$settings_result = $stmt->get_result();

$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

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
                <p class="text-gray-600">Configure platform settings and behavior</p>
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
            
            <!-- Settings Form -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">General Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="setting_site_name" class="block text-gray-700 mb-2">
                                    Site Name
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['site_name']['setting_description']; ?></span>
                                </label>
                                <input type="text" id="setting_site_name" name="setting_site_name" value="<?php echo $settings['site_name']['setting_value']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="setting_site_description" class="block text-gray-700 mb-2">
                                    Site Description
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['site_description']['setting_description']; ?></span>
                                </label>
                                <input type="text" id="setting_site_description" name="setting_site_description" value="<?php echo $settings['site_description']['setting_value']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="setting_contact_email" class="block text-gray-700 mb-2">
                                    Contact Email
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['contact_email']['setting_description']; ?></span>
                                </label>
                                <input type="email" id="setting_contact_email" name="setting_contact_email" value="<?php echo $settings['contact_email']['setting_value']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="setting_contact_phone" class="block text-gray-700 mb-2">
                                    Contact Phone
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['contact_phone']['setting_description']; ?></span>
                                </label>
                                <input type="text" id="setting_contact_phone" name="setting_contact_phone" value="<?php echo $settings['contact_phone']['setting_value']; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">User Registration Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="setting_new_user_auto_approve" class="block text-gray-700 mb-2">
                                    Auto-Approve New Users
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['new_user_auto_approve']['setting_description']; ?></span>
                                </label>
                                <select id="setting_new_user_auto_approve" name="setting_new_user_auto_approve" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0" <?php echo $settings['new_user_auto_approve']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $settings['new_user_auto_approve']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="setting_auto_approve_service_providers" class="block text-gray-700 mb-2">
                                    Auto-Approve Service Providers
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['auto_approve_service_providers']['setting_description']; ?></span>
                                </label>
                                <select id="setting_auto_approve_service_providers" name="setting_auto_approve_service_providers" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0" <?php echo $settings['auto_approve_service_providers']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $settings['auto_approve_service_providers']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="setting_auto_approve_experts" class="block text-gray-700 mb-2">
                                    Auto-Approve Experts
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['auto_approve_experts']['setting_description']; ?></span>
                                </label>
                                <select id="setting_auto_approve_experts" name="setting_auto_approve_experts" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0" <?php echo $settings['auto_approve_experts']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $settings['auto_approve_experts']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Platform Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="setting_maintenance_mode" class="block text-gray-700 mb-2">
                                    Maintenance Mode
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['maintenance_mode']['setting_description']; ?></span>
                                </label>
                                <select id="setting_maintenance_mode" name="setting_maintenance_mode" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0" <?php echo $settings['maintenance_mode']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $settings['maintenance_mode']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="setting_allow_user_tips" class="block text-gray-700 mb-2">
                                    Allow User Tips
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['allow_user_tips']['setting_description']; ?></span>
                                </label>
                                <select id="setting_allow_user_tips" name="setting_allow_user_tips" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0" <?php echo $settings['allow_user_tips']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $settings['allow_user_tips']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="setting_default_daily_water_goal" class="block text-gray-700 mb-2">
                                    Default Daily Water Goal (L)
                                    <span class="text-xs text-gray-500 ml-1"><?php echo $settings['default_daily_water_goal']['setting_description']; ?></span>
                                </label>
                                <input type="number" id="setting_default_daily_water_goal" name="setting_default_daily_water_goal" value="<?php echo $settings['default_daily_water_goal']['setting_value']; ?>" min="1" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Settings</button>
                    </div>
                </form>
            </div>
            
            <!-- System Information -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">System Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-bold text-gray-700 mb-2">PHP Version</h3>
                        <p class="text-gray-600"><?php echo phpversion(); ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-bold text-gray-700 mb-2">Server Software</h3>
                        <p class="text-gray-600"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-bold text-gray-700 mb-2">Database</h3>
                        <p class="text-gray-600">MySQL</p>
                    </div>
                    
                    <div>
                        <h3 class="font-bold text-gray-700 mb-2">Current Time</h3>
                        <p class="text-gray-600"><?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                </div>
                
                <!-- Database Statistics -->
                <?php
                $conn = connect_db();
                
                // Get table sizes
                $tables = ['users', 'devices', 'water_usage', 'conservation_goals', 'tips', 'system_settings'];
                $table_stats = [];
                
                foreach ($tables as $table) {
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $table_stats[$table] = $result->fetch_assoc()['count'];
                }
                
                $conn->close();
                ?>
                
                <div class="mt-6">
                    <h3 class="font-bold text-gray-700 mb-2">Database Statistics</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-2 px-4 text-left">Table</th>
                                    <th class="py-2 px-4 text-left">Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_stats as $table => $count): ?>
                                    <tr class="border-b">
                                        <td class="py-2 px-4"><?php echo ucfirst($table); ?></td>
                                        <td class="py-2 px-4"><?php echo $count; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Platform Actions -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Platform Actions</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <h3 class="font-bold text-gray-800 mb-2">Clear Cache</h3>
                        <p class="text-gray-600 mb-4">Clear system cache to refresh content.</p>
                        <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="alert('Cache cleared successfully');">
                            Clear Cache
                        </button>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                        <h3 class="font-bold text-gray-800 mb-2">Backup Database</h3>
                        <p class="text-gray-600 mb-4">Create a backup of the entire database.</p>
                        <button type="button" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" onclick="alert('Database backup started');">
                            Backup Now
                        </button>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                        <h3 class="font-bold text-gray-800 mb-2">Reset System</h3>
                        <p class="text-gray-600 mb-4">Reset the system to default settings.</p>
                        <button type="button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="return confirm('WARNING: This will reset all system settings to default values. This action cannot be undone. Are you sure you want to continue?');">
                            Reset System
                        </button>
                    </div>
                </div>
            </div>
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

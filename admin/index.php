<?php
require_once '../config.php';

// Set page variables
$page_title = 'Admin Dashboard';
$active_page = 'dashboard';

// Check if user is admin
if (!is_admin()) {
    redirect('../auth/login.php', 'You do not have permission to access the admin area', 'error');
}

// Connect to database
$conn = connect_db();

// Get total users count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$result = $stmt->get_result();
$total_users = $result->fetch_assoc()['count'];

// Get pending users count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$pending_users = $result->fetch_assoc()['count'];

// Get total devices count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM devices");
$stmt->execute();
$result = $stmt->get_result();
$total_devices = $result->fetch_assoc()['count'];

// Get total water usage
$stmt = $conn->prepare("SELECT SUM(usage_amount) as total FROM water_usage");
$stmt->execute();
$result = $stmt->get_result();
$total_water_usage = $result->fetch_assoc()['total'] ?? 0;

// Get recent user registrations
$stmt = $conn->prepare("SELECT id, email, first_name, last_name, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->get_result();

// Get monthly water usage data for chart
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(wu.usage_date, '%Y-%m') as month,
        SUM(wu.usage_amount) as monthly_usage
    FROM water_usage wu
    WHERE wu.usage_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(wu.usage_date, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthly_usage_result = $stmt->get_result();

$months = [];
$monthly_data = [];

while ($row = $monthly_usage_result->fetch_assoc()) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_data[] = $row['monthly_usage'];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <p class="text-gray-600">Welcome to the admin dashboard. Manage your platform from here.</p>
            </div>
            
            <?php echo display_message(); ?>
            
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Users</p>
                            <p class="text-2xl font-bold"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-yellow-100 p-3 mr-4">
                            <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pending Users</p>
                            <p class="text-2xl font-bold"><?php echo $pending_users; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-faucet text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Devices</p>
                            <p class="text-2xl font-bold"><?php echo $total_devices; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-tint text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Water Usage</p>
                            <p class="text-2xl font-bold"><?php echo number_format($total_water_usage, 1); ?> <span class="text-sm">liters</span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Monthly Water Usage Chart -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold mb-2">Monthly Water Usage</h2>
                    <div style="height: 180px;">
                        <canvas id="monthlyUsageChart"></canvas>
                    </div>
                </div>
                
                <!-- User Roles Distribution -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold mb-2">User Roles Distribution</h2>
                    <?php
                    // Get user roles distribution
                    $conn = connect_db();
                    $stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
                    $stmt->execute();
                    $roles_result = $stmt->get_result();
                    
                    $roles = [];
                    $role_counts = [];
                    
                    while ($row = $roles_result->fetch_assoc()) {
                        $roles[] = ucfirst($row['role']);
                        $role_counts[] = $row['count'];
                    }
                    
                    $conn->close();
                    ?>
                    <div style="height: 180px;">
                        <canvas id="userRolesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Recent User Registrations -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Recent Registrations</h2>
                    <a href="users.php" class="text-blue-500 hover:underline">View All</a>
                </div>
                
                <?php if ($recent_users->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-3 px-4 text-left">User</th>
                                    <th class="py-3 px-4 text-left">Email</th>
                                    <th class="py-3 px-4 text-left">Role</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                    <th class="py-3 px-4 text-left">Registered</th>
                                    <th class="py-3 px-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recent_users->fetch_assoc()): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4 font-medium"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                        <td class="py-3 px-4"><?php echo $user['email']; ?></td>
                                        <td class="py-3 px-4"><?php echo ucfirst($user['role']); ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : ($user['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4"><?php echo date('M d, Y g:i A', strtotime($user['created_at'])); ?></td>
                                        <td class="py-3 px-4">
                                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['status'] === 'pending'): ?>
                                                <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" class="text-green-500 hover:text-green-700" title="Activate">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-600">No recent user registrations found.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Quick Actions</h2>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="users.php?action=pending" class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 hover:bg-yellow-100 transition duration-300">
                        <div class="flex items-center">
                            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                                <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">Pending Users</p>
                                <p class="text-sm text-gray-600">Review and approve user registrations</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="tips.php?action=add" class="bg-green-50 p-4 rounded-lg border border-green-100 hover:bg-green-100 transition duration-300">
                        <div class="flex items-center">
                            <div class="rounded-full bg-green-100 p-3 mr-4">
                                <i class="fas fa-lightbulb text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">Add Tip</p>
                                <p class="text-sm text-gray-600">Create new water saving tip</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="settings.php" class="bg-blue-50 p-4 rounded-lg border border-blue-100 hover:bg-blue-100 transition duration-300">
                        <div class="flex items-center">
                            <div class="rounded-full bg-blue-100 p-3 mr-4">
                                <i class="fas fa-cog text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">System Settings</p>
                                <p class="text-sm text-gray-600">Configure platform settings</p>
                            </div>
                        </div>
                    </a>
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
    
    <script>
    // Monthly Water Usage Chart
    const monthlyUsageCtx = document.getElementById('monthlyUsageChart').getContext('2d');
    const monthlyUsageChart = new Chart(monthlyUsageCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Water Usage (Liters)',
                data: <?php echo json_encode($monthly_data); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // User Roles Chart
    const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
    const userRolesChart = new Chart(userRolesCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($roles); ?>,
            datasets: [{
                data: <?php echo json_encode($role_counts); ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(249, 115, 22, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%'
        }
    });
    </script>
</body>
</html>

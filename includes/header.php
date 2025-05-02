<?php
// Check if user is logged in
if (!is_logged_in()) {
    redirect('../auth/login.php', 'Please login to access this page', 'warning');
}

// Get user info for display
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
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
<body class="bg-blue-50 font-sans min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="../index.php" class="text-2xl font-bold text-blue-600"><i class="fas fa-water mr-2"></i>AquaSave</a>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Profile dropdown menu -->
                <div class="relative group">
                    <button id="profileDropdownBtn" class="flex items-center text-gray-700 focus:outline-none">
                        <span class="mr-2"><?php echo $user_name; ?></span>
                        <i class="fas fa-user-circle text-2xl"></i>
                    </button>
                    <div id="profileDropdown" class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-md shadow-xl z-10 hidden">
                        <a href="../dashboard/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-500 hover:text-white">Profile</a>
                        <?php if ($user_role === 'admin'): ?>
                            <a href="../admin/index.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-500 hover:text-white">Admin Panel</a>
                        <?php endif; ?>
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
                            <a href="../dashboard/index.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'dashboard' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-tachometer-alt w-6 text-center"></i>
                                <span class="ml-2">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="../dashboard/devices.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'devices' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-faucet w-6 text-center"></i>
                                <span class="ml-2">My Devices</span>
                            </a>
                        </li>
                        <li>
                            <a href="../dashboard/goals.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'goals' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-bullseye w-6 text-center"></i>
                                <span class="ml-2">Conservation Goals</span>
                            </a>
                        </li>
                        <li>
                            <a href="../dashboard/tips.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'tips' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-lightbulb w-6 text-center"></i>
                                <span class="ml-2">Water Saving Tips</span>
                            </a>
                        </li>
                        <li>
                            <a href="../dashboard/profile.php" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $active_page === 'profile' ? 'bg-blue-600' : 'hover:bg-gray-700'; ?>">
                                <i class="fas fa-user w-6 text-center"></i>
                                <span class="ml-2">My Profile</span>
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
            </div>
            
            <?php echo display_message(); ?>

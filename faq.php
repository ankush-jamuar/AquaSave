<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - AquaSave</title>
    <link rel="icon" href="assets/images/icon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gradient-to-b from-teal-50 to-blue-50 font-sans min-h-screen flex flex-col">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold text-teal-600"><i class="fas fa-water mr-2"></i>AquaSave</a>
            </div>
            <div class="space-x-4">
                <?php if (!is_logged_in()): ?>
                    <a href="auth/login.php" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Login</a>
                    <a href="auth/register.php" class="px-4 py-2 border border-teal-600 text-teal-600 rounded hover:bg-teal-50">Sign Up</a>
                <?php else: ?>
                    <a href="dashboard/index.php" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Dashboard</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold text-center mb-10 text-teal-800">Frequently Asked Questions</h1>
        
        <div class="max-w-3xl mx-auto">
            <!-- FAQ Items -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">What is AquaSave?</h3>
                    <p class="text-gray-600">AquaSave is a smart water conservation platform that helps users track, monitor, and optimize their water usage. Our platform provides real-time analytics, conservation goals, and personalized water-saving tips.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">How does AquaSave track water usage?</h3>
                    <p class="text-gray-600">AquaSave tracks water usage through smart water meters and IoT devices connected to your water fixtures. These devices send data to our platform, which processes and displays your water consumption patterns.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">How accurate is the water usage data?</h3>
                    <p class="text-gray-600">Our system provides highly accurate measurements with an error margin of less than 2%. The smart meters are calibrated regularly to ensure precise readings of your water consumption.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">Can I set water conservation goals?</h3>
                    <p class="text-gray-600">Yes! AquaSave allows you to set personalized water conservation goals. You can track your progress through our intuitive dashboard and receive notifications when you're approaching or have achieved your goals.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">How do I add devices to my account?</h3>
                    <p class="text-gray-600">Once logged in, navigate to the "Devices" section of your dashboard. Click on "Add New Device" and follow the setup instructions. You'll need to provide the device ID and location to properly integrate it with your account.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">What if I have trouble connecting my devices?</h3>
                    <p class="text-gray-600">If you're experiencing issues connecting your devices, please check our troubleshooting guide in the support section or contact our customer support team at info@aquasave.com.</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-600">
                    <h3 class="text-xl font-semibold text-teal-800 mb-2">Is my data secure?</h3>
                    <p class="text-gray-600">Yes, we take data security very seriously. All your personal information and water usage data are encrypted and stored securely. We never share your data with third parties without your explicit consent.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">AquaSave</h3>
                    <p class="text-gray-400">Smart water conservation platform helping users track and optimize water usage.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="support.php" class="text-gray-400 hover:text-white">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Resources</h3>
                    <ul class="space-y-2">
                        <li><a href="auth/login.php" class="text-gray-400 hover:text-white">Login</a></li>
                        <li><a href="auth/register.php" class="text-gray-400 hover:text-white">Sign Up</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-400"><i class="fas fa-envelope mr-2"></i> info@aquasave.com</li>
                        <li class="text-gray-400"><i class="fas fa-phone mr-2"></i> 8825054241</li>
                        <li class="text-gray-400"><i class="fas fa-map-marker-alt mr-2"></i> LPU, Jalandhar</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> AquaSave. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

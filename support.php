<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - AquaSave</title>
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
        <h1 class="text-3xl font-bold text-center mb-10 text-teal-800">Support Center</h1>
        
        <div class="max-w-4xl mx-auto">
            <!-- Support Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white rounded-lg shadow-md p-6 text-center border-t-4 border-teal-600">
                    <div class="text-4xl text-teal-600 mb-4"><i class="fas fa-life-ring"></i></div>
                    <h2 class="text-xl font-bold mb-2">Customer Support</h2>
                    <p class="text-gray-600 mb-4">Having issues with your account or devices? Our customer support team is ready to help.</p>
                    <p class="font-semibold">Email: support@aquasave.com</p>
                    <p class="font-semibold">Phone: 8825054241</p>
                    <p class="text-sm text-gray-500 mt-2">Response time: Within 24 hours</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 text-center border-t-4 border-teal-600">
                    <div class="text-4xl text-teal-600 mb-4"><i class="fas fa-tools"></i></div>
                    <h2 class="text-xl font-bold mb-2">Technical Support</h2>
                    <p class="text-gray-600 mb-4">Need help with device installation or technical issues? Our tech team is here to assist.</p>
                    <p class="font-semibold">Email: tech@aquasave.com</p>
                    <p class="font-semibold">Phone: 8825054241 (ext. 2)</p>
                    <p class="text-sm text-gray-500 mt-2">Available: Mon-Fri, 9am-5pm</p>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8 border-t-4 border-teal-600">
                <h2 class="text-2xl font-bold mb-6 text-center text-teal-800">Contact Us</h2>
                
                <form action="#" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-gray-700 mb-2">Your Name</label>
                            <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600" required>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600" required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-gray-700 mb-2">Subject</label>
                        <select id="subject" name="subject" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600" required>
                            <option value="">Select a subject</option>
                            <option value="account">Account Issues</option>
                            <option value="device">Device Problems</option>
                            <option value="billing">Billing Questions</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="message" class="block text-gray-700 mb-2">Your Message</label>
                        <textarea id="message" name="message" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600" required></textarea>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full md:w-auto px-6 py-3 bg-teal-600 text-white rounded-lg font-bold hover:bg-teal-700 transition duration-300">Submit Request</button>
                    </div>
                </form>
            </div>
            
            <!-- FAQ Teaser -->
            <div class="mt-12 text-center">
                <h2 class="text-2xl font-bold mb-4 text-teal-800">Frequently Asked Questions</h2>
                <p class="text-gray-600 mb-6">Find quick answers to common questions about our platform.</p>
                <a href="faq.php" class="px-6 py-3 bg-teal-600 text-white rounded-lg font-bold hover:bg-teal-700 transition duration-300">View FAQ</a>
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

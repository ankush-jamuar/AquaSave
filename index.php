<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaSave - Smart Water Conservation Platform</title>
    <link rel="icon" href="assets/images/icon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-blue-50 font-sans">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold text-blue-600"><i class="fas fa-water mr-2"></i>AquaSave</a>
            </div>
            <div class="space-x-4">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard/index.php" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                    <a href="auth/logout.php" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="text-blue-600 hover:text-blue-800">Login</a>
                    <a href="auth/register.php" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <?php
    // Display messages
    if (isset($_GET['msg']) && $_GET['msg'] === 'account_deleted') {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-auto my-4 max-w-4xl" role="alert">
                <p class="font-bold">Account Deleted</p>
                <p>Your account and all associated data have been successfully deleted.</p>
              </div>';
    }
    ?>

    <!-- Hero Section -->
    <section class="py-10 bg-gradient-to-r from-teal-600 to-blue-800 text-white overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 text-center md:text-left mb-8 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Save Water.<br>Save Life.</h1>
                    <p class="text-xl mb-8 max-w-lg">Track, monitor, and optimize your water usage with our smart platform. Join us in making every drop count.</p>
                    <div class="flex flex-wrap justify-center md:justify-start space-x-0 md:space-x-4 space-y-3 md:space-y-0">
                        <?php if (!is_logged_in()): ?>
                            <a href="auth/register.php" class="px-6 py-3 bg-white text-teal-700 rounded-lg font-bold hover:bg-teal-50 transition duration-300 shadow-lg transform hover:-translate-y-1">Get Started</a>
                            <a href="#features" class="px-6 py-3 border-2 border-white text-white rounded-lg font-bold hover:bg-white hover:text-teal-700 transition duration-300 shadow-lg transform hover:-translate-y-1">Learn More</a>
                        <?php else: ?>
                            <a href="dashboard/index.php" class="px-6 py-3 bg-white text-teal-700 rounded-lg font-bold hover:bg-teal-50 transition duration-300 shadow-lg transform hover:-translate-y-1">Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="md:w-1/2 relative p-4">
                    <div class="relative rounded-xl overflow-hidden shadow-2xl transform transition-all hover:scale-105 duration-300 border-4 border-white/30" style="max-width: 500px; margin: 0 auto;">
                        <img src="assets/images/main.jpg" alt="Water Conservation" class="w-full h-auto object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-teal-900 to-transparent opacity-30"></div>
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-teal-400 rounded-full opacity-20 z-0"></div>
                    <div class="absolute -top-6 -left-6 w-24 h-24 bg-blue-400 rounded-full opacity-20 z-0"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Key Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-blue-50 p-6 rounded-lg shadow-md">
                    <div class="text-4xl text-blue-600 mb-4"><i class="fas fa-tachometer-alt"></i></div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Real-time Monitoring</h3>
                    <p class="text-gray-600">Connect your devices and track water usage in real-time with detailed analytics and insights.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-lg shadow-md">
                    <div class="text-4xl text-blue-600 mb-4"><i class="fas fa-bullseye"></i></div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Conservation Goals</h3>
                    <p class="text-gray-600">Set personalized water conservation goals and track your progress towards achieving them.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-lg shadow-md">
                    <div class="text-4xl text-blue-600 mb-4"><i class="fas fa-lightbulb"></i></div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Smart Tips</h3>
                    <p class="text-gray-600">Receive personalized water-saving tips based on your location and usage patterns.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">How It Works</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold">1</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Sign Up</h3>
                    <p class="text-gray-600">Create your account and complete your profile with relevant information.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold">2</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Connect Devices</h3>
                    <p class="text-gray-600">Add your water-consuming devices to the platform for tracking.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold">3</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Monitor Usage</h3>
                    <p class="text-gray-600">Track your water consumption through detailed analytics and reports.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold">4</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Save Water</h3>
                    <p class="text-gray-600">Implement tips and track your progress towards conservation goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">What Our Users Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"AquaSave has helped our family reduce our water bill by 30%. The real-time monitoring and personalized tips have been incredibly helpful."</p>
                    <p class="font-bold text-gray-800">- Sarah Johnson</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"As a property manager, AquaSave has been instrumental in helping us identify water waste and implement conservation measures across our properties."</p>
                    <p class="font-bold text-gray-800">- Michael Rodriguez</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"The conservation goal setting feature has made saving water a fun challenge for our entire household. We've become much more conscious of our water usage."</p>
                    <p class="font-bold text-gray-800">- Lisa Thompson</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Saving Water?</h2>
            <p class="text-xl mb-8">Join thousands of users who are making a difference with AquaSave.</p>
            <?php if (!is_logged_in()): ?>
                <a href="auth/register.php" class="px-6 py-3 bg-white text-blue-700 rounded-lg font-bold hover:bg-blue-50 transition duration-300">Sign Up Now</a>
            <?php else: ?>
                <a href="dashboard/index.php" class="px-6 py-3 bg-white text-blue-700 rounded-lg font-bold hover:bg-blue-50 transition duration-300">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
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
                        <li><a href="#features" class="text-gray-400 hover:text-white">Features</a></li>
                        <li><a href="auth/login.php" class="text-gray-400 hover:text-white">Login</a></li>
                        <li><a href="auth/register.php" class="text-gray-400 hover:text-white">Sign Up</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Resources</h3>
                    <ul class="space-y-2">
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="support.php" class="text-gray-400 hover:text-white">Support</a></li>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
require_once '../config.php';

// Set page variables
$page_title = 'Water Saving Tips';
$active_page = 'tips';

// Get user ID and location info
$user_id = $_SESSION['user_id'] ?? 0;
$conn = connect_db();

// Get user location info
$stmt = $conn->prepare("SELECT city, state FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_location = $user_result->fetch_assoc();
$user_state = $user_location['state'] ?? '';

// Get filter parameters
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$categories = ['All', 'Home', 'Garden', 'Personal'];

// Get tips based on filters and user location
$query = "SELECT * FROM tips WHERE 1=1";
$params = [];
$types = "";

if (!empty($category) && $category !== 'All') {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($user_state)) {
    $query .= " AND (location_relevance = 'All' OR location_relevance LIKE ?)";
    $params[] = "%$user_state%";
    $types .= "s";
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tips_result = $stmt->get_result();

$conn->close();

// Include header
include_once '../includes/header.php';
?>

<!-- Tips Content -->
<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl font-bold text-gray-800">Water Saving Tips</h2>
            <p class="text-gray-600">Discover practical ways to conserve water in your daily life</p>
        </div>
        
        <div class="flex space-x-2">
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo urlencode($cat); ?>" class="px-4 py-2 rounded-full <?php echo $category === $cat || (empty($category) && $cat === 'All') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo $cat; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($tips_result->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($tip = $tips_result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <span class="inline-block px-3 py-1 text-sm font-semibold bg-blue-100 text-blue-800 rounded-full mr-2">
                        <?php echo $tip['category']; ?>
                    </span>
                    <?php if ($tip['location_relevance'] !== 'All'): ?>
                        <span class="inline-block px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-800 rounded-full">
                            <?php echo $tip['location_relevance']; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-3"><?php echo $tip['title']; ?></h3>
                    <p class="text-gray-700"><?php echo $tip['content']; ?></p>
                </div>
                <div class="px-6 py-3 bg-gray-50 flex justify-between items-center">
                    <p class="text-xs text-gray-500">Added on <?php echo date('M d, Y', strtotime($tip['created_at'])); ?></p>
                    <button class="text-blue-500 hover:text-blue-700 save-tip" data-tip-id="<?php echo $tip['id']; ?>">
                        <i class="far fa-bookmark"></i> Save
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-lightbulb text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No tips available</h3>
        <p class="text-gray-600">No water saving tips found for the selected criteria.</p>
        <a href="tips.php" class="mt-4 inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">View All Tips</a>
    </div>
<?php endif; ?>

<!-- Water Saving Challenge Section -->
<div class="mt-10 bg-blue-50 rounded-lg shadow-md p-6">
    <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl font-bold text-blue-800 mb-2">Water Saving Challenge</h2>
            <p class="text-blue-600 max-w-xl">Join our monthly water conservation challenge and compete with other users to save the most water!</p>
        </div>
        <a href="#" class="px-6 py-3 bg-blue-500 text-white rounded-lg font-bold hover:bg-blue-600 transition duration-300">Join Challenge</a>
    </div>
</div>

<!-- Personalized Recommendations -->
<div class="mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Personalized Recommendations</h2>
    
    <?php
    // Get user's device types to provide relevant tips
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT DISTINCT type FROM devices WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $devices_result = $stmt->get_result();
    $device_types = [];
    
    while ($device = $devices_result->fetch_assoc()) {
        $device_types[] = $device['type'];
    }
    
    $conn->close();
    ?>
    
    <?php if (count($device_types) > 0): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-xl font-bold">Based on Your Devices</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($device_types as $device_type): ?>
                        <div class="border rounded-lg p-4 hover:border-blue-500 transition-colors duration-300">
                            <h4 class="font-bold text-lg mb-2">Tips for Your <?php echo $device_type; ?></h4>
                            
                            <?php if ($device_type === 'Shower'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Install a low-flow showerhead to reduce water usage by up to 40%</li>
                                    <li>Take shorter showers - aim for under 5 minutes</li>
                                    <li>Turn off the water while lathering with soap</li>
                                </ul>
                            <?php elseif ($device_type === 'Washing Machine'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Always wash full loads to maximize efficiency</li>
                                    <li>Use the appropriate water level setting for each load size</li>
                                    <li>Consider upgrading to a high-efficiency washing machine</li>
                                </ul>
                            <?php elseif ($device_type === 'Dishwasher'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Only run the dishwasher when it's full</li>
                                    <li>Scrape dishes instead of rinsing them before loading</li>
                                    <li>Use eco-mode when available to reduce water consumption</li>
                                </ul>
                            <?php elseif ($device_type === 'Irrigation System'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Water early in the morning to reduce evaporation</li>
                                    <li>Consider installing a rain sensor to prevent watering during rainfall</li>
                                    <li>Adjust sprinklers to avoid watering sidewalks and driveways</li>
                                </ul>
                            <?php elseif ($device_type === 'Swimming Pool'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Use a pool cover to reduce evaporation by up to 95%</li>
                                    <li>Check for and repair leaks promptly</li>
                                    <li>Maintain proper chemical balance to reduce the need for water replacement</li>
                                </ul>
                            <?php elseif ($device_type === 'Toilet'): ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Check for leaks by adding food coloring to the tank</li>
                                    <li>Install a dual-flush toilet or water displacement device</li>
                                    <li>Follow the "if it's yellow, let it mellow" approach when appropriate</li>
                                </ul>
                            <?php else: ?>
                                <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                    <li>Regularly check for and fix leaks</li>
                                    <li>Consider upgrading to water-efficient models</li>
                                    <li>Use water-saving techniques specific to this device</li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-700 mb-4">Add your devices to get personalized water saving recommendations.</p>
            <a href="devices.php" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add Devices</a>
        </div>
    <?php endif; ?>
</div>

<!-- User Submitted Tips Section -->
<div class="mt-10 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h2 class="text-xl font-bold">Submit Your Tip</h2>
    </div>
    <div class="p-6">
        <p class="text-gray-700 mb-4">Have a great water-saving tip to share with the community? Submit it here!</p>
        <form id="tipForm" class="space-y-4">
            <div>
                <label for="tip_title" class="block text-gray-700 mb-2">Tip Title *</label>
                <input type="text" id="tip_title" name="tip_title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="tip_content" class="block text-gray-700 mb-2">Tip Content *</label>
                <textarea id="tip_content" name="tip_content" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Submit Tip</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tip form submission
    const tipForm = document.getElementById('tipForm');
    if (tipForm) {
        tipForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Display success message (in a real app this would submit to the server)
            alert('Thank you for submitting your tip! It will be reviewed by our team.');
            
            // Reset form
            tipForm.reset();
        });
    }
    
    // Handle saving tips
    const saveTipButtons = document.querySelectorAll('.save-tip');
    saveTipButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tipId = this.getAttribute('data-tip-id');
            
            // Toggle saved state (in a real app this would save to the database)
            if (this.innerHTML.includes('fa-bookmark')) {
                this.innerHTML = '<i class="fas fa-bookmark"></i> Saved';
                this.classList.add('text-blue-700');
            } else {
                this.innerHTML = '<i class="far fa-bookmark"></i> Save';
                this.classList.remove('text-blue-700');
            }
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>

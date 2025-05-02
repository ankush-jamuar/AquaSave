<?php
require_once '../config.php';

// Set page variables
$page_title = 'Conservation Goals';
$active_page = 'goals';

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize variables
$target_amount = $start_date = $end_date = '';
$error = '';
$success = '';

// Process form submission for adding a new goal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Get and sanitize inputs
    $target_amount = floatval($_POST['target_amount'] ?? 0);
    $start_date = sanitize_input($_POST['start_date']);
    $end_date = sanitize_input($_POST['end_date']);
    
    // Validate inputs
    if ($target_amount <= 0) {
        $error = 'Please enter a valid target amount';
    } elseif (empty($start_date) || empty($end_date)) {
        $error = 'Please select both start and end dates';
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $error = 'End date must be after start date';
    } else {
        $conn = connect_db();
        
        // Insert new goal
        $stmt = $conn->prepare("INSERT INTO conservation_goals (user_id, target_amount, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $target_amount, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $success = 'Conservation goal set successfully';
            // Clear form fields
            $target_amount = $start_date = $end_date = '';
        } else {
            $error = 'Failed to set goal. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process form submission for deleting a goal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $goal_id = intval($_POST['goal_id'] ?? 0);
    
    if ($goal_id > 0) {
        $conn = connect_db();
        
        // Delete goal (ensuring it belongs to the user)
        $stmt = $conn->prepare("DELETE FROM conservation_goals WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $goal_id, $user_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Goal deleted successfully';
        } else {
            $error = 'Failed to delete goal';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Get all goals for the user
$conn = connect_db();
$stmt = $conn->prepare("
    SELECT 
        cg.*,
        (SELECT SUM(wu.usage_amount) 
         FROM water_usage wu
         JOIN devices d ON wu.device_id = d.id
         WHERE d.user_id = cg.user_id 
           AND wu.usage_date BETWEEN cg.start_date AND cg.end_date) as current_usage
    FROM conservation_goals cg
    WHERE cg.user_id = ?
    ORDER BY cg.start_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals_result = $stmt->get_result();

// Get average water usage per day
$stmt = $conn->prepare("
    SELECT 
        AVG(daily_total) as avg_daily_usage
    FROM (
        SELECT 
            DATE(wu.usage_date) as usage_day,
            SUM(wu.usage_amount) as daily_total
        FROM water_usage wu
        JOIN devices d ON wu.device_id = d.id
        WHERE d.user_id = ?
        GROUP BY DATE(wu.usage_date)
    ) as daily_usage
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_result = $stmt->get_result();
$avg_daily_usage = $avg_result->fetch_assoc()['avg_daily_usage'] ?? 0;

$conn->close();

// Include header
include_once '../includes/header.php';
?>

<!-- Goals Content -->
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
    <!-- Goals List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">My Conservation Goals</h2>
                <button data-modal="addGoalModal" class="open-modal px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    <i class="fas fa-plus mr-1"></i> Set New Goal
                </button>
            </div>
            
            <?php if ($goals_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 text-left">Period</th>
                                <th class="py-3 px-4 text-left">Target (L)</th>
                                <th class="py-3 px-4 text-left">Current Usage (L)</th>
                                <th class="py-3 px-4 text-left">Progress</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($goal = $goals_result->fetch_assoc()): 
                                $start = new DateTime($goal['start_date']);
                                $end = new DateTime($goal['end_date']);
                                $current = new DateTime();
                                
                                // Calculate days passed and total days
                                $days_passed = ($current > $start) ? $start->diff($current)->days : 0;
                                $total_days = $start->diff($end)->days;
                                
                                // Ensure we don't exceed the total
                                $days_passed = min($days_passed, $total_days);
                                
                                // Calculate expected usage based on days passed
                                $expected_usage = ($days_passed / $total_days) * $goal['target_amount'];
                                
                                // Current usage
                                $current_usage = floatval($goal['current_usage'] ?? 0);
                                
                                // Calculate progress percentage (against target)
                                $progress_percentage = ($goal['target_amount'] > 0) ? min(100, ($current_usage / $goal['target_amount']) * 100) : 0;
                                
                                // Determine if we're on track
                                $on_track = $current_usage <= $expected_usage;
                            ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <?php echo date('M d, Y', strtotime($goal['start_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($goal['end_date'])); ?>
                                    </td>
                                    <td class="py-3 px-4 font-medium"><?php echo number_format($goal['target_amount'], 1); ?></td>
                                    <td class="py-3 px-4"><?php echo number_format($current_usage, 1); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="h-2.5 rounded-full <?php echo $on_track ? 'bg-green-500' : 'bg-red-500'; ?>" style="width: <?php echo $progress_percentage; ?>%"></div>
                                        </div>
                                        <div class="text-xs mt-1"><?php echo number_format($progress_percentage, 1); ?>%</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($goal['status'] === 'completed'): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Completed</span>
                                        <?php elseif ($goal['status'] === 'failed'): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Failed</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $on_track ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $on_track ? 'On Track' : 'Behind Target'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this goal?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-12 px-6">
                    <i class="fas fa-bullseye text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 mb-4">You haven't set any conservation goals yet</p>
                    <button data-modal="addGoalModal" class="open-modal px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-plus mr-1"></i> Set Your First Goal
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Goal Tips Section -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Your Water Usage</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-blue-100 p-3 mr-4">
                        <i class="fas fa-tint text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Average Daily Usage</p>
                        <p class="text-2xl font-bold"><?php echo number_format($avg_daily_usage, 1); ?> <span class="text-sm">liters</span></p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="font-bold text-gray-800 mb-2">Recommended Target</h3>
                    <p class="text-gray-600 mb-2">Based on your average usage, a good conservation goal might be:</p>
                    <p class="text-xl font-bold text-blue-600"><?php echo number_format($avg_daily_usage * 0.8, 1); ?> <span class="text-sm">liters/day</span></p>
                    <p class="text-xs text-gray-500 mt-1">(20% reduction from current average)</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Goal Setting Tips</h2>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800 mb-2">Smart Goal Setting</h3>
                    <p class="text-gray-600">Set Specific, Measurable, Achievable, Relevant, and Time-bound water conservation goals.</p>
                </div>
                
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800 mb-2">Start Small</h3>
                    <p class="text-gray-600">Begin with a modest 10-15% reduction and gradually increase your conservation targets.</p>
                </div>
                
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800 mb-2">Track Regularly</h3>
                    <p class="text-gray-600">Monitor your progress frequently to stay motivated and make adjustments as needed.</p>
                </div>
                
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">Celebrate Success</h3>
                    <p class="text-gray-600">Acknowledge and reward yourself when you achieve your conservation milestones.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div id="addGoalModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Set Conservation Goal</h3>
            <button class="close-modal text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="add">
            <div class="p-6">
                <div class="mb-4">
                    <label for="target_amount" class="block text-gray-700 mb-2">Target Amount (Liters) *</label>
                    <input type="number" id="target_amount" name="target_amount" step="0.1" min="0.1" value="<?php echo $target_amount; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if ($avg_daily_usage > 0): ?>
                        <p class="text-sm text-gray-500 mt-1">Your average daily usage is <?php echo number_format($avg_daily_usage, 1); ?> liters</p>
                    <?php endif; ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="start_date" class="block text-gray-700 mb-2">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date ?: date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-gray-700 mb-2">End Date *</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date ?: date('Y-m-d', strtotime('+30 days')); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> Setting a conservation goal helps you track and reduce your water consumption over time.
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button type="button" class="close-modal px-4 py-2 border border-gray-300 rounded mr-2 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Set Goal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal handling
    const modals = document.querySelectorAll('.modal');
    const openButtons = document.querySelectorAll('.open-modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
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
});
</script>

<?php include_once '../includes/footer.php'; ?>
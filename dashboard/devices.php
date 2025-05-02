<?php
require_once '../config.php';

// Set page variables
$page_title = 'My Devices';
$active_page = 'devices';

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize variables
$name = $type = $model = $serial_number = '';
$error = '';
$success = '';

// Process form submission for adding a new device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Get and sanitize inputs
    $name = sanitize_input($_POST['name']);
    $type = sanitize_input($_POST['type']);
    $model = sanitize_input($_POST['model']);
    $serial_number = sanitize_input($_POST['serial_number']);
    
    // Validate inputs
    if (empty($name) || empty($type)) {
        $error = 'Device name and type are required';
    } else {
        $conn = connect_db();
        
        // Insert new device
        $stmt = $conn->prepare("INSERT INTO devices (user_id, name, type, model, serial_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $type, $model, $serial_number);
        
        if ($stmt->execute()) {
            $success = 'Device added successfully';
            // Clear form fields
            $name = $type = $model = $serial_number = '';
        } else {
            $error = 'Failed to add device. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process request to delete a device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $device_id = $_POST['device_id'] ?? 0;
    
    if ($device_id > 0) {
        $conn = connect_db();
        
        // Make sure the device belongs to the user
        $stmt = $conn->prepare("DELETE FROM devices WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $device_id, $user_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Device deleted successfully';
        } else {
            $error = 'Failed to delete device';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process request to update device status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $device_id = $_POST['device_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($device_id > 0 && in_array($status, ['active', 'inactive'])) {
        $conn = connect_db();
        
        // Make sure the device belongs to the user
        $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $device_id, $user_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Device status updated successfully';
        } else {
            $error = 'Failed to update device status';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Process form submission for adding water usage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_usage') {
    $device_id = $_POST['device_id'] ?? 0;
    $usage_amount = floatval($_POST['usage_amount'] ?? 0);
    $usage_date = $_POST['usage_date'] ?? date('Y-m-d');
    
    if ($device_id > 0 && $usage_amount > 0) {
        $conn = connect_db();
        
        // Make sure the device belongs to the user
        $stmt = $conn->prepare("SELECT id FROM devices WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $device_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Insert water usage
            $stmt = $conn->prepare("INSERT INTO water_usage (device_id, usage_amount, usage_date) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $device_id, $usage_amount, $usage_date);
            
            if ($stmt->execute()) {
                $success = 'Water usage recorded successfully';
            } else {
                $error = 'Failed to record water usage';
            }
        } else {
            $error = 'Invalid device selected';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Device and usage amount are required';
    }
}

// Get all devices for the user
$conn = connect_db();
$stmt = $conn->prepare("SELECT * FROM devices WHERE user_id = ? ORDER BY name");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$devices_result = $stmt->get_result();
$conn->close();

// Include header
include_once '../includes/header.php';
?>

<!-- Devices Content -->
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
    <!-- Device List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">My Devices</h2>
                <button data-modal="addDeviceModal" class="open-modal px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    <i class="fas fa-plus mr-1"></i> Add Device
                </button>
            </div>
            
            <?php if ($devices_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 text-left">Device</th>
                                <th class="py-3 px-4 text-left">Type</th>
                                <th class="py-3 px-4 text-left">Model</th>
                                <th class="py-3 px-4 text-left">Serial Number</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($device = $devices_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium"><?php echo $device['name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $device['type']; ?></td>
                                    <td class="py-3 px-4"><?php echo $device['model']; ?></td>
                                    <td class="py-3 px-4"><?php echo $device['serial_number']; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $device['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($device['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <button data-modal="addUsageModal" data-device-id="<?php echo $device['id']; ?>" data-device-name="<?php echo $device['name']; ?>" class="open-modal text-blue-500 hover:text-blue-700" title="Record Usage">
                                                <i class="fas fa-tint"></i>
                                            </button>
                                            
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline" onsubmit="return confirm('Are you sure you want to change the status of this device?');">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $device['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" class="<?php echo $device['status'] === 'active' ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'; ?>" title="<?php echo $device['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $device['status'] === 'active' ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this device?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-12 px-6">
                    <i class="fas fa-faucet text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 mb-4">You haven't added any devices yet</p>
                    <button data-modal="addDeviceModal" class="open-modal px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-plus mr-1"></i> Add Your First Device
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Get recent water usage data
        $conn = connect_db();
        $stmt = $conn->prepare("
            SELECT 
                wu.id,
                d.name as device_name,
                wu.usage_amount,
                wu.usage_date
            FROM water_usage wu
            JOIN devices d ON wu.device_id = d.id
            WHERE d.user_id = ? 
            ORDER BY wu.usage_date DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $usage_result = $stmt->get_result();
        $conn->close();
        ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Recent Water Usage</h2>
            </div>
            
            <?php if ($usage_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 text-left">Device</th>
                                <th class="py-3 px-4 text-left">Amount (L)</th>
                                <th class="py-3 px-4 text-left">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($usage = $usage_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4"><?php echo $usage['device_name']; ?></td>
                                    <td class="py-3 px-4"><?php echo number_format($usage['usage_amount'], 1); ?></td>
                                    <td class="py-3 px-4"><?php echo date('M d, Y g:i A', strtotime($usage['usage_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-12 px-6">
                    <i class="fas fa-water text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No water usage data recorded yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tips Section -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-bold text-gray-800">Device Tips</h2>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="font-bold text-gray-800 mb-2">Why Connect Your Devices?</h3>
                    <p class="text-gray-600">Adding your water-consuming devices helps you track usage patterns and identify opportunities for conservation.</p>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-bold text-gray-800 mb-2">Common Water-Using Devices</h3>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1">
                        <li>Shower Systems</li>
                        <li>Washing Machines</li>
                        <li>Dishwashers</li>
                        <li>Garden Irrigation</li>
                        <li>Swimming Pools</li>
                        <li>Water Heaters</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">Recording Usage</h3>
                    <p class="text-gray-600">Regularly record your water usage for accurate analytics and better conservation planning.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Device Modal -->
<div id="addDeviceModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Add New Device</h3>
            <button class="close-modal text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="add">
            <div class="p-6">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 mb-2">Device Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="type" class="block text-gray-700 mb-2">Device Type *</label>
                    <select id="type" name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="" <?php echo empty($type) ? 'selected' : ''; ?>>Select Type</option>
                        <option value="Shower" <?php echo $type === 'Shower' ? 'selected' : ''; ?>>Shower</option>
                        <option value="Washing Machine" <?php echo $type === 'Washing Machine' ? 'selected' : ''; ?>>Washing Machine</option>
                        <option value="Dishwasher" <?php echo $type === 'Dishwasher' ? 'selected' : ''; ?>>Dishwasher</option>
                        <option value="Irrigation System" <?php echo $type === 'Irrigation System' ? 'selected' : ''; ?>>Irrigation System</option>
                        <option value="Swimming Pool" <?php echo $type === 'Swimming Pool' ? 'selected' : ''; ?>>Swimming Pool</option>
                        <option value="Water Heater" <?php echo $type === 'Water Heater' ? 'selected' : ''; ?>>Water Heater</option>
                        <option value="Faucet" <?php echo $type === 'Faucet' ? 'selected' : ''; ?>>Faucet</option>
                        <option value="Toilet" <?php echo $type === 'Toilet' ? 'selected' : ''; ?>>Toilet</option>
                        <option value="Other" <?php echo $type === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="model" class="block text-gray-700 mb-2">Model (Optional)</label>
                    <input type="text" id="model" name="model" value="<?php echo $model; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="serial_number" class="block text-gray-700 mb-2">Serial Number (Optional)</label>
                    <input type="text" id="serial_number" name="serial_number" value="<?php echo $serial_number; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button type="button" class="close-modal px-4 py-2 border border-gray-300 rounded mr-2 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add Device</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Usage Modal -->
<div id="addUsageModal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Record Water Usage</h3>
            <button class="close-modal text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="add_usage">
            <input type="hidden" id="usage_device_id" name="device_id" value="">
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Device</label>
                    <p id="usage_device_name" class="font-semibold"></p>
                </div>
                
                <div class="mb-4">
                    <label for="usage_amount" class="block text-gray-700 mb-2">Usage Amount (Liters) *</label>
                    <input type="number" id="usage_amount" name="usage_amount" step="0.1" min="0.1" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="usage_date" class="block text-gray-700 mb-2">Usage Date *</label>
                    <input type="date" id="usage_date" name="usage_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button type="button" class="close-modal px-4 py-2 border border-gray-300 rounded mr-2 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Record Usage</button>
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
                // If it's the usage modal, set the device info
                if (modalId === 'addUsageModal') {
                    const deviceId = button.getAttribute('data-device-id');
                    const deviceName = button.getAttribute('data-device-name');
                    
                    document.getElementById('usage_device_id').value = deviceId;
                    document.getElementById('usage_device_name').textContent = deviceName;
                }
                
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

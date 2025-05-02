<?php
require_once '../config.php';

// Set page variables
$page_title = 'Dashboard';
$active_page = 'dashboard';

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Connect to database
$conn = connect_db();

// Get total devices for the user
$stmt = $conn->prepare("SELECT COUNT(*) as total_devices FROM devices WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$devices_result = $stmt->get_result();
$total_devices = $devices_result->fetch_assoc()['total_devices'];

// Get active conservation goals
$stmt = $conn->prepare("SELECT COUNT(*) as active_goals FROM conservation_goals WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals_result = $stmt->get_result();
$active_goals = $goals_result->fetch_assoc()['active_goals'];

// Get total water usage (last 7 days)
$stmt = $conn->prepare("
    SELECT SUM(wu.usage_amount) as total_usage 
    FROM water_usage wu
    JOIN devices d ON wu.device_id = d.id
    WHERE d.user_id = ? 
    AND wu.usage_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$usage_result = $stmt->get_result();
$total_usage = $usage_result->fetch_assoc()['total_usage'] ?? 0;

// Get daily water usage (last 7 days)
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(wu.usage_date, '%Y-%m-%d') as day,
        SUM(wu.usage_amount) as daily_usage
    FROM water_usage wu
    JOIN devices d ON wu.device_id = d.id
    WHERE d.user_id = ? 
    AND wu.usage_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
    GROUP BY DATE_FORMAT(wu.usage_date, '%Y-%m-%d')
    ORDER BY day
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$daily_usage_result = $stmt->get_result();

$labels = [];
$data = [];

while ($row = $daily_usage_result->fetch_assoc()) {
    $labels[] = $row['day'];
    $data[] = $row['daily_usage'];
}

// Get device-wise usage breakdown
$stmt = $conn->prepare("
    SELECT 
        d.name as device_name,
        SUM(wu.usage_amount) as device_usage
    FROM water_usage wu
    JOIN devices d ON wu.device_id = d.id
    WHERE d.user_id = ? 
    AND wu.usage_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
    GROUP BY d.id
    ORDER BY device_usage DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$device_usage_result = $stmt->get_result();

$device_labels = [];
$device_data = [];

while ($row = $device_usage_result->fetch_assoc()) {
    $device_labels[] = $row['device_name'];
    $device_data[] = $row['device_usage'];
}

$conn->close();

// Include header
include_once '../includes/header.php';
?>

<!-- Dashboard Content -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <i class="fas fa-faucet text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Devices</p>
                <p class="text-2xl font-bold"><?php echo $total_devices; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <i class="fas fa-tint text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Usage (7 days)</p>
                <p class="text-2xl font-bold"><?php echo number_format($total_usage, 1); ?> <span class="text-sm">liters</span></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="rounded-full bg-purple-100 p-3 mr-4">
                <i class="fas fa-bullseye text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active Goals</p>
                <p class="text-2xl font-bold"><?php echo $active_goals; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-2">Daily Water Usage (Last 7 Days)</h2>
        <div style="height: 180px;">
            <canvas id="dailyUsageChart"></canvas>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-2">Device Usage Breakdown (30 Days)</h2>
        <?php if (count($device_labels) > 0): ?>
            <div style="height: 180px;">
                <canvas id="deviceUsageChart"></canvas>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center h-64">
                <i class="fas fa-chart-pie text-gray-300 text-4xl mb-4"></i>
                <p class="text-gray-500">No device usage data available</p>
                <a href="devices.php" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add Devices</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Recent Activity</h2>
        <a href="#" class="text-blue-500 hover:underline">View All</a>
    </div>
    
    <?php
    // Get recent water usage recordings
    $conn = connect_db();
    $stmt = $conn->prepare("
        SELECT 
            d.name as device_name,
            wu.usage_amount,
            wu.usage_date
        FROM water_usage wu
        JOIN devices d ON wu.device_id = d.id
        WHERE d.user_id = ? 
        ORDER BY wu.usage_date DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent_usage = $stmt->get_result();
    $conn->close();
    ?>
    
    <?php if ($recent_usage->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 text-left">Device</th>
                        <th class="py-2 px-4 text-left">Amount (L)</th>
                        <th class="py-2 px-4 text-left">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_usage->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-3 px-4"><?php echo $row['device_name']; ?></td>
                            <td class="py-3 px-4"><?php echo number_format($row['usage_amount'], 1); ?></td>
                            <td class="py-3 px-4"><?php echo date('M d, Y g:i A', strtotime($row['usage_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center h-32">
            <i class="fas fa-water text-gray-300 text-4xl mb-4"></i>
            <p class="text-gray-500">No recent water usage data</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Chart for daily water usage
const dailyUsageCtx = document.getElementById('dailyUsageChart').getContext('2d');
const dailyUsageChart = new Chart(dailyUsageCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Daily Water Usage (Liters)',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 10
                    }
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 10
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 10,
                    font: {
                        size: 10
                    }
                }
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
});

<?php if (count($device_labels) > 0): ?>
// Chart for device usage breakdown
const deviceUsageCtx = document.getElementById('deviceUsageChart').getContext('2d');
const deviceUsageChart = new Chart(deviceUsageCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($device_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($device_data); ?>,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 10,
                    font: {
                        size: 10
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php include_once '../includes/footer.php'; ?>

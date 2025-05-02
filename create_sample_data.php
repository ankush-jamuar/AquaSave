<?php
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('auth/login.php', 'Please login first to generate sample data', 'warning');
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Connect to database
$conn = connect_db();

// Check if user already has devices to avoid duplicate data
$stmt = $conn->prepare("SELECT COUNT(*) as device_count FROM devices WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$device_count = $result->fetch_assoc()['device_count'];

// Only generate data if user doesn't have devices yet
if ($device_count == 0) {
    // Add sample devices
    $devices = [
        ['Bathroom Shower', 'Shower', 'Low-Flow 2000', 'SH-12345'],
        ['Kitchen Sink', 'Faucet', 'Eco-Flow', 'FK-54321'],
        ['Main Bathroom Toilet', 'Toilet', 'Water Saver 500', 'TL-98765'],
        ['Washing Machine', 'Washing Machine', 'EcoWash 3000', 'WM-45678'],
        ['Garden Sprinkler', 'Irrigation System', 'Smart Sprinkler', 'GS-87654']
    ];
    
    $device_ids = [];
    
    // Insert devices
    $stmt = $conn->prepare("INSERT INTO devices (user_id, name, type, model, serial_number, status) VALUES (?, ?, ?, ?, ?, 'active')");
    
    foreach ($devices as $device) {
        $stmt->bind_param("issss", $user_id, $device[0], $device[1], $device[2], $device[3]);
        $stmt->execute();
        $device_ids[] = $conn->insert_id;
    }
    
    echo "<p>✅ Added 5 sample devices</p>";
    
    // Generate water usage data for past 30 days
    $stmt = $conn->prepare("INSERT INTO water_usage (device_id, usage_amount, usage_date) VALUES (?, ?, ?)");
    
    $today = new DateTime();
    $water_usage_count = 0;
    
    // Loop through each day
    for ($i = 30; $i >= 0; $i--) {
        $date = clone $today;
        $date->modify("-$i day");
        $date_string = $date->format('Y-m-d H:i:s');
        
        // Add 1-3 entries per device per day with some randomness
        foreach ($device_ids as $device_id) {
            $entries = rand(1, 3);
            
            for ($j = 0; $j < $entries; $j++) {
                // Generate random water usage based on device type
                $device_index = array_search($device_id, $device_ids);
                $device_type = $devices[$device_index][1];
                
                // Different device types use different amounts of water
                switch ($device_type) {
                    case 'Shower':
                        $usage = round(rand(350, 750) / 10, 1); // 35-75 liters
                        break;
                    case 'Faucet':
                        $usage = round(rand(50, 150) / 10, 1); // 5-15 liters
                        break;
                    case 'Toilet':
                        $usage = round(rand(40, 80) / 10, 1); // 4-8 liters
                        break;
                    case 'Washing Machine':
                        $usage = round(rand(400, 1000) / 10, 1); // 40-100 liters
                        break;
                    case 'Irrigation System':
                        $usage = round(rand(1000, 3000) / 10, 1); // 100-300 liters
                        break;
                    default:
                        $usage = round(rand(100, 500) / 10, 1); // 10-50 liters
                }
                
                // Add some randomness for weekends (more usage)
                $day_of_week = $date->format('N');
                if ($day_of_week >= 6) { // Weekend
                    $usage *= 1.2;
                }
                
                // Add time variation
                $hour = rand(6, 22); // Between 6 AM and 10 PM
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $date_with_time = $date->format('Y-m-d') . " $hour:$minute:$second";
                
                $stmt->bind_param("ids", $device_id, $usage, $date_with_time);
                $stmt->execute();
                $water_usage_count++;
            }
        }
    }
    
    echo "<p>✅ Added $water_usage_count water usage records across the past 30 days</p>";
    
    // Add conservation goals
    $goals = [
        [date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 4500], // Current month
        [date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-30 days')), 5000, 'completed'], // Past goal (completed)
        [date('Y-m-d', strtotime('-90 days')), date('Y-m-d', strtotime('-60 days')), 5500, 'failed'] // Past goal (failed)
    ];
    
    $stmt = $conn->prepare("INSERT INTO conservation_goals (user_id, start_date, end_date, target_amount, status) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($goals as $goal) {
        $status = isset($goal[3]) ? $goal[3] : 'active';
        $stmt->bind_param("issds", $user_id, $goal[0], $goal[1], $goal[2], $status);
        $stmt->execute();
    }
    
    echo "<p>✅ Added 3 conservation goals</p>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #d1fae5; border: 1px solid #34d399; border-radius: 5px;'>";
    echo "<strong>Sample data created successfully!</strong><br>";
    echo "5 devices with realistic water usage data for the past month have been added to your account.<br>";
    echo "Now you can view meaningful charts and statistics on your dashboard.";
    echo "</div>";
    
    echo "<p style='margin-top: 20px;'><a href='dashboard/index.php' style='padding: 10px 15px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
} else {
    echo "<div style='padding: 15px; background-color: #fee2e2; border: 1px solid #ef4444; border-radius: 5px;'>";
    echo "<strong>You already have devices in your account.</strong><br>";
    echo "To avoid duplicate data, sample data generation has been skipped. You can view your existing data on the dashboard.";
    echo "</div>";
    
    echo "<p style='margin-top: 20px;'><a href='dashboard/index.php' style='padding: 10px 15px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Sample Data - AquaSave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <h1>AquaSave Sample Data Generator</h1>
    
    <!-- Results will appear above this line -->
</body>
</html>

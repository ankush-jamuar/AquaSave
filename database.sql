CREATE DATABASE IF NOT EXISTS aquasave;

USE aquasave;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip_code VARCHAR(20),
    phone VARCHAR(20),
    role ENUM('admin', 'user', 'service_provider', 'expert') NOT NULL DEFAULT 'user',
    status ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Devices table
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    model VARCHAR(50),
    serial_number VARCHAR(50),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Water usage table
CREATE TABLE IF NOT EXISTS water_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    usage_amount DECIMAL(10, 2) NOT NULL, -- in liters
    usage_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- Conservation goals table
CREATE TABLE IF NOT EXISTS conservation_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL, -- in liters
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'failed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tips table
CREATE TABLE IF NOT EXISTS tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    location_relevance VARCHAR(100), -- comma-separated states or regions
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (email, password, first_name, last_name, role, status)
VALUES ('admin@aquasave.com', '$2y$10$8WxYD4htUVYyH7f1EW0q5e5HzYm1UeY.qQYI3ZgwVn1ThqG4nrXGm', 'Admin', 'User', 'admin', 'active');

-- Insert sample water saving tips
INSERT INTO tips (title, content, category, location_relevance) VALUES 
('Fix Leaking Faucets', 'A dripping faucet can waste up to 20 gallons of water per day. Fix leaks promptly to save water and money.', 'Home', 'All'),
('Install Low-Flow Showerheads', 'Low-flow showerheads can reduce water usage by up to 40% without compromising pressure.', 'Home', 'All'),
('Water Plants in the Morning', 'Watering plants in the early morning reduces evaporation and ensures more water reaches the roots.', 'Garden', 'All'),
('Collect Rainwater', 'Use rain barrels to collect rainwater for garden irrigation.', 'Garden', 'All'),
('Use Full Loads for Washing', 'Always run your washing machine and dishwasher with full loads to maximize efficiency.', 'Home', 'All'),
('Install Drip Irrigation', 'Drip irrigation systems deliver water directly to plant roots, reducing waste from evaporation.', 'Garden', 'Dry regions'),
('Choose Native Plants', 'Native plants are adapted to your local climate and typically require less water.', 'Garden', 'All'),
('Take Shorter Showers', 'Reducing your shower time by just 2 minutes can save up to 10 gallons of water.', 'Personal', 'All'),
('Use a Pool Cover', 'A pool cover can reduce evaporation by up to 95% when the pool is not in use.', 'Home', 'Hot regions'),
('Check for Toilet Leaks', 'Add food coloring to your toilet tank - if color appears in the bowl without flushing, you have a leak.', 'Home', 'All');

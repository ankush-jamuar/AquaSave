// Form validation and handling
document.addEventListener('DOMContentLoaded', function() {
    // Find any forms that need validation
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        // Add submit event listener
        form.addEventListener('submit', function(event) {
            // Prevent default form submission
            event.preventDefault();
            
            // Validate the form
            if (validateForm(form)) {
                // If form is valid, handle the submission
                handleFormSubmission(form);
            }
        });
        
        // Add input event listeners for real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            // For select elements, validate on change
            if (input.tagName.toLowerCase() === 'select') {
                input.addEventListener('change', function() {
                    validateField(input);
                });
            }
        });
    });
    
    // Form validation function
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Field validation function
    function validateField(field) {
        // Get the error message element
        let errorElement = field.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('error-message')) {
            // Create error message element if it doesn't exist
            errorElement = document.createElement('p');
            errorElement.className = 'error-message text-red-500 text-sm mt-1 hidden';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
        
        // Check required fields
        if (field.hasAttribute('required') && !field.value.trim()) {
            showError(field, errorElement, 'This field is required');
            return false;
        }
        
        // Validate email format
        if (field.type === 'email' && field.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value.trim())) {
                showError(field, errorElement, 'Please enter a valid email address');
                return false;
            }
        }
        
        // Validate password strength if required
        if (field.id === 'password' && field.value.trim()) {
            const passwordStrength = checkPasswordStrength(field.value.trim());
            if (passwordStrength !== 'strong') {
                showError(field, errorElement, 'Password should be at least 8 characters with numbers, uppercase and lowercase letters');
                return false;
            }
        }
        
        // Validate password confirmation
        if (field.id === 'confirm-password') {
            const password = document.getElementById('password');
            if (password && field.value !== password.value) {
                showError(field, errorElement, 'Passwords do not match');
                return false;
            }
        }
        
        // Validate phone number format
        if (field.id === 'phone' && field.value.trim()) {
            const phoneRegex = /^\+?[0-9]{10,15}$/;
            if (!phoneRegex.test(field.value.replace(/[\s()-]/g, ''))) {
                showError(field, errorElement, 'Please enter a valid phone number');
                return false;
            }
        }
        
        // Custom validation based on data attributes
        if (field.dataset.validate) {
            switch (field.dataset.validate) {
                case 'zipcode':
                    const zipcodeRegex = /^\d{5}(-\d{4})?$/;
                    if (!zipcodeRegex.test(field.value.trim())) {
                        showError(field, errorElement, 'Please enter a valid ZIP code');
                        return false;
                    }
                    break;
                    
                case 'number':
                    if (isNaN(field.value) || field.value === '') {
                        showError(field, errorElement, 'Please enter a valid number');
                        return false;
                    }
                    
                    // Check min and max if specified
                    if (field.hasAttribute('min') && parseFloat(field.value) < parseFloat(field.getAttribute('min'))) {
                        showError(field, errorElement, `Value should be at least ${field.getAttribute('min')}`);
                        return false;
                    }
                    
                    if (field.hasAttribute('max') && parseFloat(field.value) > parseFloat(field.getAttribute('max'))) {
                        showError(field, errorElement, `Value should be at most ${field.getAttribute('max')}`);
                        return false;
                    }
                    break;
            }
        }
        
        // Field is valid
        hideError(field, errorElement);
        return true;
    }
    
    // Function to show validation error
    function showError(field, errorElement, message) {
        field.classList.add('border-red-500');
        field.classList.remove('border-green-500');
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
    
    // Function to hide validation error
    function hideError(field, errorElement) {
        field.classList.remove('border-red-500');
        field.classList.add('border-green-500');
        errorElement.classList.add('hidden');
    }
    
    // Function to check password strength
    function checkPasswordStrength(password) {
        if (password.length < 8) {
            return 'weak';
        }
        
        let hasNumber = /\d/.test(password);
        let hasUppercase = /[A-Z]/.test(password);
        let hasLowercase = /[a-z]/.test(password);
        
        if (hasNumber && hasUppercase && hasLowercase) {
            return 'strong';
        }
        
        return 'medium';
    }
    
    // Form submission handler
    function handleFormSubmission(form) {
        const formType = form.dataset.formType;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Disable submit button and show loading state
        if (submitButton) {
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        }
        
        // Simulate form submission (in a real application, this would be an actual API call)
        setTimeout(() => {
            // Re-enable button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButton.dataset.originalText || 'Submit';
            }
            
            switch (formType) {
                case 'registration':
                    // Show success notification
                    window.showNotification('Registration successful! Please check your email for verification.', 'success');
                    
                    // Clear form
                    form.reset();
                    
                    // Redirect to login page after timeout
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 2000);
                    break;
                    
                case 'device-connect':
                    window.showNotification('Device connected successfully!', 'success');
                    
                    // For demo purposes, update UI to show connected device
                    const deviceList = document.querySelector('.connected-devices-list');
                    if (deviceList) {
                        const deviceName = form.querySelector('#device-name').value || 'New Device';
                        const deviceType = form.querySelector('#device-type').value || 'Smart Meter';
                        
                        const deviceElement = document.createElement('div');
                        deviceElement.className = 'bg-white p-4 rounded-lg shadow mb-4 flex items-center';
                        deviceElement.innerHTML = `
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-tint text-blue-500"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">${deviceName}</h4>
                                <p class="text-sm text-gray-600">${deviceType}</p>
                            </div>
                            <div class="ml-auto">
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Connected</span>
                            </div>
                        `;
                        
                        deviceList.appendChild(deviceElement);
                    }
                    
                    // Clear form
                    form.reset();
                    break;
                    
                case 'goal-setting':
                    window.showNotification('Conservation goal set successfully!', 'success');
                    
                    // Update goal display
                    const goalDisplay = document.querySelector('.current-goal');
                    if (goalDisplay) {
                        const goalValue = form.querySelector('#goal-value').value || '0';
                        goalDisplay.textContent = `${goalValue} gallons per day`;
                    }
                    
                    // Clear form
                    form.reset();
                    
                    // Close modal if exists
                    const modal = document.querySelector('.goal-modal');
                    if (modal) {
                        modal.classList.add('hidden');
                    }
                    break;
                    
                default:
                    window.showNotification('Form submitted successfully!', 'success');
                    form.reset();
            }
        }, 1500);
    }

    // Location selector for water tips
    const locationSelect = document.getElementById('location-select');
    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            updateWaterTips(this.value);
        });
    }

    // Function to update water tips based on location
    function updateWaterTips(location) {
        const tipsContainer = document.querySelector('.water-tips-container');
        if (!tipsContainer) return;
        
        // Show loading state
        tipsContainer.innerHTML = '<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-blue-500 text-3xl"></i><p class="mt-2 text-gray-600">Loading location-specific tips...</p></div>';
        
        // Simulate loading tips (in a real app, this would be an API call)
        setTimeout(() => {
            // Get tips based on location
            const tips = getLocationSpecificTips(location);
            
            // Update container
            tipsContainer.innerHTML = '';
            
            tips.forEach(tip => {
                const tipElement = document.createElement('div');
                tipElement.className = 'bg-white rounded-lg shadow-md p-5 mb-4 water-card';
                tipElement.innerHTML = `
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas ${tip.icon} text-blue-500"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800 mb-2">${tip.title}</h4>
                            <p class="text-gray-600">${tip.description}</p>
                            <div class="mt-3 flex items-center text-sm">
                                <span class="text-blue-500">Potential Savings:</span>
                                <span class="ml-2 bg-blue-50 text-blue-700 px-2 py-1 rounded">${tip.savings}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                tipsContainer.appendChild(tipElement);
            });
        }, 1000);
    }
    
    // Function to get location-specific water tips
    function getLocationSpecificTips(location) {
        // These would come from an API in a real application
        const commonTips = [
            {
                title: 'Fix Leaky Faucets',
                description: 'A faucet that drips once per second can waste more than 3,000 gallons per year.',
                icon: 'fa-wrench',
                savings: '3,000+ gallons/year'
            },
            {
                title: 'Shorter Showers',
                description: 'Reducing your shower time by just 2 minutes can save up to 150 gallons per month.',
                icon: 'fa-shower',
                savings: '150 gallons/month'
            },
            {
                title: 'Turn Off While Brushing',
                description: 'Turn off the water while brushing your teeth and save up to 8 gallons per day.',
                icon: 'fa-sink',
                savings: '8 gallons/day'
            }
        ];
        
        // Location-specific tips
        const locationTips = {
            'desert': [
                {
                    title: 'Xeriscaping',
                    description: 'Replace water-intensive plants with drought-resistant varieties native to arid regions.',
                    icon: 'fa-seedling',
                    savings: '750 gallons/month'
                },
                {
                    title: 'Rainwater Harvesting',
                    description: 'Though rainfall is rare, setting up a system to capture it when it occurs can significantly reduce water usage.',
                    icon: 'fa-cloud-rain',
                    savings: 'Up to 30% of water usage'
                }
            ],
            'coastal': [
                {
                    title: 'Salt-Tolerant Landscaping',
                    description: 'Use salt-tolerant plants for landscaping to reduce freshwater irrigation needs near coastal areas.',
                    icon: 'fa-leaf',
                    savings: '500 gallons/month'
                },
                {
                    title: 'Greywater Systems',
                    description: 'Install systems to reuse household water for landscape irrigation.',
                    icon: 'fa-recycle',
                    savings: '30-50% of outdoor water use'
                }
            ],
            'urban': [
                {
                    title: 'High-Efficiency Appliances',
                    description: 'Replace old appliances with water-efficient models with ENERGY STAR certification.',
                    icon: 'fa-washer',
                    savings: '16 gallons/day'
                },
                {
                    title: 'Smart Irrigation Controllers',
                    description: 'Use weather-based controllers that adjust watering based on local conditions.',
                    icon: 'fa-clock',
                    savings: '40 gallons/day'
                }
            ],
            'rural': [
                {
                    title: 'Drip Irrigation',
                    description: 'Use drip irrigation for gardens and crops to minimize water waste.',
                    icon: 'fa-faucet-drip',
                    savings: '30-50% compared to sprinklers'
                },
                {
                    title: 'Mulching',
                    description: 'Apply mulch around plants to retain moisture and reduce evaporation.',
                    icon: 'fa-layer-group',
                    savings: '20-30% of garden water use'
                }
            ]
        };
        
        // Return combined tips
        return [...(locationTips[location] || []), ...commonTips];
    }
});

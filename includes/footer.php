        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <p class="text-sm"><i class="fas fa-phone mr-1"></i> 8825054241 | <i class="fas fa-map-marker-alt mx-1"></i> LPU, Jalandhar</p>
                </div>
                <div>
                    <p class="text-sm">&copy; <?php echo date('Y'); ?> AquaSave. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for dropdown menu -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Profile dropdown functionality
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        
        if (profileDropdownBtn && profileDropdown) {
            // Toggle dropdown on button click
            profileDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileDropdownBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        }
    });
    </script>
</body>
</html>

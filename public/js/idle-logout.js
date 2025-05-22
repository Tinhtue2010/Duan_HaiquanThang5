document.addEventListener('DOMContentLoaded', function () {
    const logoutTime = 10 * 60 * 1000; // 10 minutes in milliseconds
    const logoutForm = document.getElementById('logout-form');
    const storageKey = 'lastActivityTime';
    const checkInterval = 5000; // Check every 5 seconds
    
    // Function to update the last activity time
    const updateActivity = () => {
        const now = Date.now();
        localStorage.setItem(storageKey, now.toString());
    };
    
    // Function to check if we should logout
    const checkInactivity = () => {
        const lastActivity = parseInt(localStorage.getItem(storageKey) || '0');
        const now = Date.now();
        
        if (now - lastActivity > logoutTime) {
            // User has been inactive for too long, trigger logout
            if (logoutForm) {
                logoutForm.submit();
            }
        }
    };
    
    // List of events to consider as user activity
    const activityEvents = ['click', 'mousemove', 'keydown', 'scroll', 'change'];
    
    // Register activity listeners
    activityEvents.forEach(event => {
        window.addEventListener(event, updateActivity);
    });
    
    // Handle storage events from other tabs
    window.addEventListener('storage', function(e) {
        if (e.key === storageKey) {
            // Another tab has updated the activity time
            // No need to do anything, just continue with our regular interval checks
        }
    });
    
    // Set initial activity time when the page loads
    updateActivity();
    
    // Set up periodic checks for inactivity
    setInterval(checkInactivity, checkInterval);
});
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.dashboard-sidebar');
    const toggleButton = document.querySelector('.sidebar-toggle');

    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Close the sidebar when clicking outside of it on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 991.98 && sidebar.classList.contains('show')) {
            if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
});
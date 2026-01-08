// =========================
// SIDEBAR GLOBAL FUNCTIONALITY
// =========================

// DOM Elements
const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');
const container = document.querySelector('.container');

// Sidebar Toggle Functionality
function toggleSidebar() {
    if (!sidebar || !container) return;
    
    sidebar.classList.toggle('collapsed');
    container.classList.toggle('sidebar-collapsed');
    
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    updateMenuToggleIcon(isCollapsed);
}

function updateMenuToggleIcon(isCollapsed) {
    if (menuToggle) {
        menuToggle.textContent = isCollapsed ? '☰' : '✕';
    }
}

function initSidebarState() {
    if (!sidebar || !container) return;
    
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        // MOBILE → sidebar selalu TUTUP saat load
        sidebar.classList.add('collapsed');
        container.classList.add('sidebar-collapsed');
    } else {
        // DESKTOP → cek localStorage
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            container.classList.add('sidebar-collapsed');
            updateMenuToggleIcon(true);
        } else {
            sidebar.classList.remove('collapsed');
            container.classList.remove('sidebar-collapsed');
            updateMenuToggleIcon(false);
        }
    }
}

// Initialize sidebar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize sidebar state
    initSidebarState();
    
    // Sidebar toggle manual
    if (menuToggle) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        const isMobile = window.innerWidth <= 768;
        if (isMobile && sidebar && !sidebar.classList.contains('collapsed')) {
            const clickedInsideSidebar = sidebar.contains(e.target);
            const clickedMenuToggle = menuToggle && menuToggle.contains(e.target);
            
            if (!clickedInsideSidebar && !clickedMenuToggle) {
                sidebar.classList.add('collapsed');
                container.classList.add('sidebar-collapsed');
                updateMenuToggleIcon(true);
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', initSidebarState);
});
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const body = document.body;

    if (!toggle || !sidebar) return;

    /* =========================
       STAFF MODE
    ========================= */
    body.classList.add('is-staff');

    /* =========================
       OVERLAY (MOBILE ONLY)
    ========================= */
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    /* =========================
       DEFAULT STATE
       Desktop: OPEN
       Mobile : CLOSED
    ========================= */
    const isMobile = () => window.innerWidth <= 768;

    const savedState = localStorage.getItem('sidebarCollapsed');

    if (isMobile()) {
        collapse(false); // mobile default closed
    } else {
        if (savedState === 'true') {
            collapse(false);
        } else {
            expand(false); // desktop default open
        }
    }

    /* =========================
       TOGGLE BUTTON
    ========================= */
    toggle.addEventListener('click', () => {
        sidebar.classList.contains('collapsed')
            ? expand(true)
            : collapse(true);
    });

    /* =========================
       OVERLAY CLICK (MOBILE)
    ========================= */
    overlay.addEventListener('click', () => {
        if (isMobile()) collapse(true);
    });

    /* =========================
       WINDOW RESIZE HANDLER
    ========================= */
    window.addEventListener('resize', () => {
        if (isMobile()) {
            overlay.classList.remove('active');
            body.classList.remove('sidebar-shift'); // 🚫 no shift on mobile
        } else {
            overlay.classList.remove('active');
            if (!sidebar.classList.contains('collapsed')) {
                body.classList.add('sidebar-shift'); // ✅ shift on desktop
            }
        }
    });

    /* =========================
       FUNCTIONS
    ========================= */
    function collapse(save = true) {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        body.classList.remove('sidebar-shift');
        overlay.classList.remove('active');

        if (save) localStorage.setItem('sidebarCollapsed', 'true');
    }

    function expand(save = true) {
        sidebar.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');

        if (isMobile()) {
            overlay.classList.add('active');
            body.classList.remove('sidebar-shift'); // 🚫 mobile no shift
        } else {
            body.classList.add('sidebar-shift'); // ✅ desktop shift
        }

        if (save) localStorage.setItem('sidebarCollapsed', 'false');
    }
});

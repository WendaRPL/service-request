/**
 * nav.js - Modular Navigation & Profile System (FINAL VERSION)
 * Handles: Profile Fetching, Dropdown, Modal, Sidebar, & Profile Image Upload
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
       1. DATA FETCHING (GET PROFILE VIA AJAX)
    ========================================== */
function loadUserProfile() {
    fetch('direct/get_profile.php')
        .then(response => response.json())
        .then(res => {
            if (res.status) {
                const user = res.data;
                
                // Jika profile_pic ada, tambahkan timestamp anti-cache
                // Jika null, tetap null
                const picUrl = user.profile_pic ? `${user.profile_pic}?v=${new Date().getTime()}` : null;

                // Update UI Username
                document.querySelectorAll('.username').forEach(el => el.textContent = user.username);
                
                // Update Foto Navbar
                const navIconContainer = document.getElementById('profileNavIcon');
                updateImageOrIcon(navIconContainer, picUrl, 'nav-profile-img');

                // Update Foto Modal
                const modalIconContainer = document.getElementById('profileIcon') || document.getElementById('profilePreview');
                updateImageOrIcon(modalIconContainer, picUrl, 'profile-img-preview', 'profilePreview');
            }
        })
        .catch(err => console.error("Gagal load profil:", err));
}

    // Helper Function untuk ganti Icon <i> jadi Img <img> secara dinamis
    function updateImageOrIcon(targetEl, imageUrl, className, newId = null) {
        if (!targetEl) return;

        if (imageUrl) {
            if (targetEl.tagName === 'I') {
                const img = document.createElement('img');
                if (newId) img.id = newId;
                img.className = className;
                img.src = imageUrl;
                targetEl.replaceWith(img);
            } else {
                targetEl.src = imageUrl;
            }
        } else {
            if (targetEl.tagName === 'IMG') {
                const i = document.createElement('i');
                i.className = 'fas fa-user-circle ' + (className === 'nav-profile-img' ? 'profile-icon' : '');
                if (newId || targetEl.id) i.id = newId || targetEl.id;
                targetEl.replaceWith(i);
            }
        }
    }

    // Jalankan load data saat startup
    loadUserProfile();

    /* ==========================================
       2. PROFILE DROPDOWN & MODAL
    ========================================== */
    const profileToggle   = document.getElementById('profileToggle');
    const profileMenu     = document.getElementById('profileMenu');
    const profileModal    = document.getElementById('profileModal');
    const profileUpload   = document.getElementById('profileUpload');

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('active');
        });
    }

    document.addEventListener('click', (e) => {
        if (profileMenu && !profileMenu.contains(e.target) && !profileToggle.contains(e.target)) {
            profileMenu.classList.remove('active');
        }
    });

    window.openProfileModal = function () {
        if (!profileModal) return;
        if (profileMenu) profileMenu.classList.remove('active');
        profileModal.classList.add('active');
    };

    window.closeProfileModal = function () {
        if (profileModal) profileModal.classList.remove('active');
    };

    if (profileModal) {
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) closeProfileModal();
        });
    }

    /* ==========================================
       3. PROFILE IMAGE UPLOAD (AJAX)
    ========================================== */
    if (profileUpload) {
        profileUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert("File terlalu besar! Maksimal 2MB.");
                this.value = "";
                return;
            }

            // Preview Instan
            const reader = new FileReader();
            reader.onload = function(e) {
                let pPreview = document.getElementById('profilePreview');
                let pIcon    = document.getElementById('profileIcon');
                updateImageOrIcon(pIcon || pPreview, e.target.result, 'profile-img-preview', 'profilePreview');
            };
            reader.readAsDataURL(file);

            // Upload via AJAX
            const formData = new FormData();
            formData.append('profile_pic', file);

            fetch('direct/upload_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Refresh semua foto (Navbar & Modal) biar sinkron sama DB
                    loadUserProfile();
                    alert("Foto profil berhasil diperbarui!");
                } else {
                    alert("Gagal upload: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan sistem.");
            });
        });
    }

    /* ==========================================
       4. SIDEBAR SYSTEM (STAFF IT ONLY)
    ========================================== */
    const toggle  = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const body    = document.body;

    if (!toggle || !sidebar) return;

    body.classList.add('is-staff');

    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    const isMobile   = () => window.innerWidth <= 768;
    const savedState = localStorage.getItem('sidebarCollapsed');

    if (isMobile()) {
        collapse(false);
    } else {
        savedState === 'true' ? collapse(false) : expand(false);
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('collapsed') ? expand(true) : collapse(true);
    });

    overlay.addEventListener('click', () => {
        if (isMobile()) collapse(true);
    });

    window.addEventListener('resize', () => {
        overlay.classList.remove('active');
        body.classList.toggle('sidebar-shift', !isMobile() && !sidebar.classList.contains('collapsed'));
        if (isMobile() && !sidebar.classList.contains('collapsed')) collapse(false);
    });

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
        if (!isMobile()) {
            body.classList.add('sidebar-shift');
        } else {
            overlay.classList.add('active');
        }
        if (save) localStorage.setItem('sidebarCollapsed', 'false');
    }
});
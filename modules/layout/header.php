<?php
require_once __DIR__ . '/../../direct/config.php';
require_once __DIR__ . '/../../routines/function.php';
?>

<body>
<div class="container">

    <!-- NAVBAR (GLOBAL) -->
    <header class="navbar">
        <div class="navbar-left">

            <?php if (isStaffIT()): ?>
                <button class="menu-toggle" id="menuToggle">☰</button>
            <?php endif; ?>

            <span class="system-title"><?= $page_title ?></span>
        </div>

        <div class="navbar-right">
            <div class="profile-dropdown">
                <div class="user-profile" id="profileToggle">
                    <span class="username">
                        <?= htmlspecialchars($username ?? 'Guest') ?>
                    </span>
                    <i class="fas fa-user-circle profile-icon" id="profileNavIcon"></i>
                </div>

                <div class="profile-menu" id="profileMenu">
                    <div class="profile-item" onclick="openProfileModal()">
                        <i class="fas fa-id-badge"></i>
                        Profile
                    </div>

                    <a href="direct/logout.php"
                    class="profile-item logout"
                    onclick="return confirm('Yakin mau logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

                <!-- PROFILE MODAL -->
<div id="profileModal" class="profile-overlay">
        <div class="modal-content profile-modal">

            <div class="profile-header">
                <h2>My Profile</h2>
                <span class="profile-close-btn" onclick="closeProfileModal()">
                    <i class="fas fa-times"></i>
                </span>
            </div>

            <div class="modal-body profile-body">
                <div class="profile-avatar" onclick="document.getElementById('profileUpload').click()" title="Klik untuk ubah foto">
                    <?php if (!empty($profile_pic) && file_exists("uploads/user/avatar/" . $profile_pic)): ?>
                        <img src="uploads/user/avatar/<?= $profile_pic ?>" id="profilePreview" class="profile-img-preview">
                    <?php else: ?>
                        <i class="fas fa-user-circle" id="profileIcon"></i>
                    <?php endif; ?>
                    
                    <div class="avatar-edit-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Ubah Foto</span>
                    </div>

                    <input type="file" id="profileUpload" hidden accept="image/*">
                </div>

                <div class="profile-info">
                    <div class="info-row">
                        <span class="label">Username</span>
                        <span class="value"><?= htmlspecialchars($username ?? '-') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="label">Role</span>
                        <span class="value"><?= htmlspecialchars($role_name ?? 'User') ?></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer profile-footer">
                <a href="direct/logout.php"
                   class="profile-logout-btn"
                   onclick="return confirm('Yakin mau logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

        </div>
    </div>
</header>

    <!-- SIDEBAR (STAFF IT ONLY) -->
    <?php if (isStaffIT()): ?>
        <aside class="sidebar" id="sidebar">
            <nav>
                <ul>

                    <?php if (can('enable')): ?>
                        <li class="<?= isActivePage('home.php') ?>">
                            <a href="home.php">Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('update_request')): ?>
                        <li class="<?= isActivePage('done_cancelled.php') ?>">
                            <a href="done_cancelled.php">Done &amp; Canceled</a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('handling_request')): ?>
                        <li class="<?= isActivePage('reporting.php') ?>">
                            <a href="reporting.php">Reporting</a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('update_toko') || can('update_role')): ?>
                        <li class="<?= isActivePage('master_data.php') ?>">
                            <a href="master_data.php">Master Data</a>
                        </li>
                    <?php endif; ?>

                    <!-- Kita cek: apakah dia punya key 'manage_user' DAN isinya adalah 'all' -->
                    <?php if (permission('manage_user') === 'all'): ?>
                        <li class="<?= isActivePage('user_management.php') ?>">
                            <a href="user_management.php">User Management</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        </aside>
    <?php endif; ?>

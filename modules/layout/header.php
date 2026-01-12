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
            <div class="user-profile">
                <span class="username">
                    <?= htmlspecialchars($username ?? 'Guest') ?>
                </span>

                <i class="fas fa-user-circle profile-icon"></i>

                <!-- LOGOUT -->
                <a href="/service-request/direct/logout.php"
                   class="logout-link"
                   title="Logout"
                   onclick="return confirm('Yakin mau logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
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
                            <a href="#">Reporting</a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('update_toko') || can('update_role')): ?>
                        <li class="<?= isActivePage('master_data.php') ?>">
                            <a href="master_data.php">Master Data</a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('manage_user')): ?>
                        <li class="<?= isActivePage('user_management.php') ?>">
                            <a href="user_management.php">User Management</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        </aside>
    <?php endif; ?>

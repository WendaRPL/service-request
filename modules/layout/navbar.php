<?php
    $role_id = $_SESSION['role_id'] ?? 0;

    // Mapping role id → nama dan warna
    $roles = [
        1 => ["name" => "Admin", "color" => "#ff4d4d"],    // merah
        2 => ["name" => "Supervisor", "color" => "#4d79ff"],  // biru
        3 => ["name" => "Staff", "color" => "#33cc33"],    // hijau
    ];
    $roleName  = $roles[$role_id]['name'] ?? "User";
    $roleColor = $roles[$role_id]['color'] ?? "#aaaaaa";

    // === Breadcrumb Dinamis ===
    $current_page = basename($_SERVER['PHP_SELF'], ".php");
    $page_titles = [
        "home"      => "Home",
        "user_manage"  => "User Manage",
        "input_report"   => "Input Laporan",
        "Approval"  => "Approval",
    ];
    $breadcrumb_title = $page_titles[$current_page] ?? ucfirst($current_page);
?>

<div class="header">
  <div class="nav" aria-label="Breadcrumb">
    <!-- Breadcrumb -->
    <div class="breadcrumb-wrapper">
      <ol class="breadcrumb">
        <li>
          <a href="home.php" class="breadcrumb-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
          </a>
        </li>
        <?php if ($current_page !== "home"): ?>
          <li class="breadcrumb-separator"> / </li>
          <li class="breadcrumb-item active">
            <span><?= $breadcrumb_title ?></span>
          </li>
        <?php endif; ?>
      </ol>
    </div>

    <!-- Nav Icons -->
<div class="nav-icons-wrapper">
  <div class="nav-icons">
    <div class="notification" id="openInbox">
      <i class="fas fa-bell fa-lg"></i>
      <span class="notification-badge" id="notifCount">0</span>
    </div>
  </div>
</div>

<!-- Modal Inbox -->
<div id="inboxModal" class="modal-inbox hidden">
  <div class="modal-inbox-content">
    <div class="modal-inbox-header">
      <h2>📥 Inbox</h2>
      <span class="modal-inbox-close" id="closeInbox">&times;</span>
    </div>

    <div class="modal-inbox-body">
      <ul id="notifList" class="notif-list"></ul>
    </div>

    <div class="modal-inbox-footer">
      <button id="markReadBtn" class="mark-read-btn">Tandai semua dibaca</button>
    </div>
  </div>
</div>

      <!-- User info -->
      <div class="user-info" style="display:flex; align-items:center; gap:10px;">
        <span style="color:<?= $roleColor ?>; font-weight:bold;"><?= $roleName ?></span>
        <div class="user-dropdown">
          <div class="user-avatar user-dropdown" onclick="toggleUserMenu()">
            <img id="userAvatarImg" src="/uploads/user/avatar/test-avatar.jpg" alt="User Avatar">
          </div>
          <div class="user-menu" id="userMenu">
            <p>Halo, <?= $_SESSION['name'] ?? 'User'; ?></p>
            <a href="#" onclick="openProfileModal(); return false;"><i class="fas fa-id-card"></i> Profil</a>
            <hr>
            <a href="#" onclick="openPasswordModal(); return false;"><i class="fas fa-lock"></i> Ubah Password</a>
            <hr>
            <?php if ($role_id == 1): ?>
            <a href="user_manage.php"><i class="fa fa-users" aria-hidden="true"></i>User Management</a>
            <?php endif ?>
            <hr>
            <a href="direct/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
            </div>
<!-- Modal Profil -->
<div id="profileModal" class="modal-inbox hidden">
  <div class="modal-inbox-content profile-modal">
    <div class="modal-inbox-header">
      <h2>👤 Profil Pengguna</h2>
      <span class="modal-inbox-close" onclick="closeProfileModal()">&times;</span>
    </div>

    <div class="profile-content">
      <div class="avatar-wrapper">
        <img id="profilePic" src="/uploads/user/avatar/test-avatar.jpg" alt="Avatar">
        <input type="file" id="uploadAvatar" accept="image/*">
        <label for="uploadAvatar" class="avatar-edit">
          <i class="fas fa-camera"></i>
        </label>
      </div>
      <p class="avatar-caption">Klik kamera untuk ganti foto</p>

      <div class="profile-info">
        <!-- Nama -->
        <div class="profile-field" data-field="name">
          <label>Nama:</label>
          <span class="field-value"><?= $_SESSION['name'] ?? 'User' ?></span>
          <button class="edit-btn" onclick="enableEdit(this)">
            <i class="fas fa-edit"></i>
          </button>
        </div>

        <!-- Initial -->
        <div class="profile-field" data-field="initial">
          <label>Initial:</label>
          <span class="field-value"><?= $_SESSION['initial'] ?? 'N/A' ?></span>
          <button class="edit-btn" onclick="enableEdit(this)">
            <i class="fas fa-edit"></i>
          </button>
        </div>

        <!-- Role -->
        <div class="profile-field">
          <label>Role:</label>
          <span><?= $roleName ?></span>
        </div>

        <!-- User ID -->
        <div class="profile-field">
          <label>User ID:</label>
          <span><?= $_SESSION['user_id'] ?? 'N/A' ?></span>
        </div>

        <!-- Username -->
        <div class="profile-field">
          <label>Username:</label>
          <span><?= $_SESSION['username'] ?? 'N/A' ?></span>
        </div>

        <!-- Last Login -->
        <div class="profile-field">
          <label>Terakhir Login:</label>
          <span><?= date('d/m/Y H:i:s') ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Password -->
<div id="passwordModal" class="modal-inbox hidden">
  <div class="modal-inbox-content password-modal">
    <div class="modal-inbox-header">
      <h2>🔒 Ubah Password</h2>
      <span class="modal-inbox-close" onclick="closePasswordModal()">&times;</span>
    </div>

    <form id="changePasswordForm" class="password-form">
      <div class="form-group">
        <label for="currentPassword">Password Saat Ini</label>
        <input type="password" id="currentPassword" required>
      </div>
      <div class="form-group">
        <label for="newPassword">Password Baru</label>
        <input type="password" id="newPassword" required minlength="6">
      </div>
      <div class="form-group">
        <label for="confirmPassword">Konfirmasi Password Baru</label>
        <input type="password" id="confirmPassword" required minlength="6">
      </div>
      <div class="form-actions">
        <button type="submit" class="submit-password-btn">
          <i class="fas fa-save"></i> Simpan Password
        </button>
        <button type="button" class="cancel-password-btn" onclick="resetPasswordForm(); closePasswordModal();">
          <i class="fas fa-times"></i> Batal
        </button>
      </div>
    </form>
  </div>
</div>

</div>

<?php include 'script.php'; ?>

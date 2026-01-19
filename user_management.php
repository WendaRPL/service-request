<?php
require_once "direct/config.php";

$pageTitle = "User Management";
ob_start();
?>
<link rel="stylesheet" href="dist/css/user_management.css">

<main class="dashboard-content">

    <button class="action-button add-user">+ Add User</button>

    <section class="user-management-section">
        <!-- ⚠️ PENTING: container kosong -->
        <div class="accordion-container" id="userContainer">
            <p style="padding:20px;">Loading users...</p>
        </div>
    </section>

</main>

<!-- =========================
     MODAL ADD USER
========================= -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="addUserModal">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2>Add New User</h2>
            </div>

            <div class="modal-body">
                <div class="user-form-grid">

                    <div class="form-column">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="add-username" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" id="add-password" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>Retype Password</label>
                            <input type="password" id="add-retype" class="form-input">
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-group">
                            <label>Role</label>
                            <select id="add-role" class="form-select">
                                <option value="">-- Pilih Role --</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-lanjut" id="btn-save-user">Save User</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="editUserModal">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2>Edit User</h2>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit-id">

                <div class="user-form-grid">
                    <div class="form-column">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="edit-username" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" id="edit-password" class="form-input" placeholder="Kosongkan jika tidak diganti">
                        </div>

                        <div class="form-group">
                            <label>Retype Password</label>
                            <input type="password" id="edit-retype" class="form-input">
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-group">
                            <label>Role</label>
                            <select id="edit-role" class="form-select"></select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-lanjut" id="btn-update-user">Update User</button>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="dist/js/user_management.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>

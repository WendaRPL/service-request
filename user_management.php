<?php
require_once "direct/config.php";

$pageTitle = "User Management";


ob_start();
?>
<link rel="stylesheet" href="dist/css/user_management.css">
    
        <!-- isi konten -->
        <main class="dashboard-content">

            <button class="action-button add-user">+ Add User</button>

            <section class="user-management-section">
                <div class="accordion-container">
                    <!-- User Item -->
                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span class="user-title">
                                John Doe — Staff IT
                            </span>
                            <span class="icon">⌄</span>
                        </button>

                        <div class="accordion-content">
                            <div class="accordion-body">
                                <p><strong>Username:</strong> johndoe_it</p>
                                <p><strong>Role:</strong> User</p>
                                <p><strong>Last Modified:</strong> 12 Jan 2025 14:22</p>

                                <div class="actions">
                                    <button class="action-btn edit-btn">Edit</button>
                                    <button class="action-btn delete-btn">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Item -->
                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span class="user-title">
                                Jane Smith — Staff IT
                            </span>
                            <span class="icon">⌄</span>
                        </button>

                        <div class="accordion-content">
                            <div class="accordion-body">
                                <p><strong>Username:</strong> janes_smith</p>
                                <p><strong>Role:</strong> Staff IT</p>
                                <p><strong>Last Modified:</strong> 10 Jan 2025 09:10</p>

                                <div class="actions">
                                    <button class="action-btn edit-btn">Edit</button>
                                    <button class="action-btn delete-btn">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

    </div>

    <!-- Modal Add User -->
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
                                <label class="form-label">Username:</label>
                                <input type="text" name="username" class="form-input" placeholder="Masukkan username...">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password:</label>
                                <input type="password" name="password" class="form-input" placeholder="********">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Retype Password:</label>
                                <input type="password" name="retype_password" class="form-input" placeholder="********">
                            </div>
                        </div>

                        <div class="form-column">
                            <div class="form-group">
                                <label class="form-label">Role:</label>
                                <select name="role" class="form-select">
                                    <option value="">-- Pilih Role --</option>
                                    <option value="staff_it">Staff IT</option>
                                    <option value="user">User</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status Akses:</label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="enable" checked>
                                    <span class="checkbox-custom"></span>
                                    Akun Aktif (Enable)
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Expiration Date:</label>
                                <div class="expiration-row">
                                    <input type="date" name="expiration_date" class="form-input">
                                    <button type="button" class="btn-forever">Forever</button>
                                </div>
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

    <!-- Modal Edit User -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" data-modal="editUserModal">&times;</span>

            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Edit User</h2>
                </div>

                <div class="modal-body">

                    <div class="user-form-grid">
                        
                        <!-- LEFT -->
                        <div class="form-column">
                            <div class="form-group">
                                <label class="form-label">Username:</label>
                                <input type="text" id="edit-username" class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">New Password:</label>
                                <input type="password" id="edit-password" class="form-input" placeholder="Kosongkan jika tidak diganti">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Retype Password:</label>
                                <input type="password" id="edit-retype-password" class="form-input">
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="form-column">
                            <div class="form-group">
                                <label class="form-label">Role:</label>
                                <select id="edit-role" class="form-select">
                                    <option value="staff_it">Staff IT</option>
                                    <option value="user">User</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status Akses:</label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="edit-enable">
                                    <span class="checkbox-custom"></span>
                                    Akun Aktif (Enable)
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Expiration Date:</label>
                                <div class="expiration-row">
                                    <input type="date" id="edit-expiration" class="form-input">
                                    <button type="button" class="btn-forever" id="edit-forever">Forever</button>
                                </div>
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

    
    <script src="dist/js/user_management.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>
<?php
require_once "direct/config.php";

$pageTitle = "Master Data";
ob_start();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="dist/css/master_data.css">

<div class="container">
    <main class="dashboard-content">

        <button class="action-button add-button" id="btnAddMaster">
            + Add Data
        </button>

        <div class="master-data-container">
            <div class="master-data-card">
                <div class="tabs-nav">
                    <button class="tab-button active" data-tab="toko">Toko</button>
                    <button class="tab-button" data-tab="karyawan">Karyawan</button>
                    <button class="tab-button" data-tab="jenis_kendala">Jenis Kendala</button>
                    <button class="tab-button" data-tab="role">Role</button>
                </div>

                <div class="tab-content active">
                    <div class="table-container"></div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- =========================
    MODAL ADD MASTER DATA
========================= -->
<div id="addMasterModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2 id="addMasterTitle">Tambah Data</h2>
            </div>

            <div class="modal-body">
                <form id="mainAddForm">

                    <!-- =========================
                        TOKO
                    ========================== -->
                    <div class="master-form" id="formToko">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Toko</label>
                                <input type="text" id="addNamaToko" class="field-value">
                            </div>
                            <div class="field-item">
                                <label class="field-label">Kode Toko</label>
                                <input type="text" id="addKodeToko" class="field-value">
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        KARYAWAN
                    ========================== -->
                    <div class="master-form" id="formKaryawan" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item full-width-field" style="position: relative;">
                                <label class="field-label">Cari & Pilih Akun User</label>
                                <input type="text" id="searchUserKaryawan" class="field-value" placeholder="Ketik nama atau username..." autocomplete="off">
                                
                                <input type="hidden" id="addUserKaryawan">     <input type="hidden" id="addUsernameKaryawan"> <div id="userSearchResults" class="live-search-results"></div>
                            </div>

                            <div class="field-item full-width-field">
                                <label class="field-label">Penempatan Toko</label>
                                <div class="multi-dropdown" id="addKaryawanTokoDropdown">
                                    <div class="dropdown-header">Pilih Toko...</div>
                                    <div class="dropdown-list" id="addKaryawanTokoList">
                                        </div>
                                </div>
                                <small class="hint-text">Ceklis satu atau lebih toko.</small>
                            </div>
                        </div>
                    </div>


                    <!-- =========================
                        JENIS KENDALA
                    ========================== -->
                    <div class="master-form" id="formJenisKendala" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Tipe Kendala</label>
                                <select id="addTipeKendala" class="field-value">
                                    <option value="">-- Pilih --</option>
                                    <option value="Software">Software</option>
                                    <option value="Hardware">Hardware</option>
                                </select>
                            </div>
                            <div class="field-item">
                                <label class="field-label">Jenis Kendala</label>
                                <input type="text" id="addJenisKendala" class="field-value">
                            </div>
                            <div class="field-item">
                                <label class="field-label">Pertanyaan Turunan</label>
                                <label><input type="radio" name="addTurunan" value="Ya"> Ya</label>
                                <label><input type="radio" name="addTurunan" value="Tidak" checked> Tidak</label>
                            </div>
                        </div>
                    </div>

<!-- =========================
ROLE MODAL (Full Fixed)
========================= -->
<div class="master-form" id="formRole" style="display:none;">
    <div class="field-grid">

        <!-- Nama Role -->
        <div class="field-item">
            <label class="field-label">Nama Role</label>
            <input type="text" id="addNamaRole" class="field-value">
        </div>

        <!-- Permissions -->
        <div class="field-item full-width-field">
            <label class="field-label">Permissions</label>

            <!-- BOOLEAN -->
            <div class="key-group">
                <strong>Boolean Permission</strong><br><br>
                <label class="key-row">
                    <input type="checkbox" name="roleBoolKeys[]" value="enable"> Enable
                </label>
                <label class="key-row">
                    <input type="checkbox" name="roleBoolKeys[]" value="update_toko"> Update Toko
                </label>
                <label class="key-row">
                    <input type="checkbox" name="roleBoolKeys[]" value="update_role"> Update Role
                </label>
                <label class="key-row">
                    <input type="checkbox" name="roleBoolKeys[]" value="handling_request"> Handling Request
                </label>
            </div>

            <hr>

            <!-- STRING KEYS (Multi-dropdown) -->
            <?php
            $stringKeys = [
                'manage_user' => 'Manage User',
                'update_user_toko' => 'Update User (By Toko)',
                'input_request' => 'Input Request',
                'cancel_request' => 'Cancel Request',
                'update_request' => 'Update Request',
                'delete_request' => 'Delete Request'
            ];
            foreach ($stringKeys as $key => $label): ?>
            <div class="key-group">
                <label class="key-row">
                    <input type="checkbox" id="key_<?= $key ?>" data-target="value_<?= $key ?>">
                    <?= $label ?>
                </label>
                <div class="key-value-wrapper disabled" id="value_<?= $key ?>">
                    <div class="multi-dropdown">
                        <div class="dropdown-header">Pilih Toko</div>
                        <div class="dropdown-list" id="<?= $key ?>TokoList">
                            <!-- DIISI VIA AJAX -->
                        </div>
                    </div>
                    <small class="hint-text">Bisa pilih lebih dari satu toko</small>
                </div>
            </div>
            <hr>
            <?php endforeach; ?>

            <!-- EXPIRATION -->
            <div class="key-group">
                <strong>Expiration Date</strong><br><br>
                <label class="key-row">
                    <input type="checkbox" id="roleForever"> Forever
                </label>
                <input type="datetime-local" id="roleExpirationDate" class="field-value">
            </div>
        </div>
    </div>
</div>
</form>
    <!-- Footer Buttons -->
    <div class="modal-action">
        <button type="button" class="btn-submit" id="btnSaveMaster">Save</button>
        <button type="button" class="btn-cancel" id="btnCancelMaster">Cancel</button>
    </div>
</div>
</div>
</div>
</div>

<!-- =========================
     MODAL EDIT MASTER DATA
    ========================== -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <div class="modal-detail">
                <!-- HEADER -->
                <div class="modal-header">
                    <h2 id="editMasterTitle">Edit Data</h2>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- =========================
                        EDIT : TOKO
                    ========================== -->
                    <div class="master-form" id="editFormToko">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Toko</label>
                                <input
                                    type="text"
                                    id="editNamaToko"
                                    class="field-value"
                                    placeholder="Masukkan nama toko"
                                >
                            </div>

                            <div class="field-item">
                                <label class="field-label">Kode Toko</label>
                                <input
                                    type="text"
                                    id="editKodeToko"
                                    class="field-value"
                                    placeholder="Masukkan kode toko"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        EDIT : KARYAWAN
                    ========================== -->
                    <div class="master-form" id="editFormKaryawan" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Karyawan</label>
                                <input type="text" id="editNamaKaryawan" class="field-value" readonly style="background:#f9f9f9">
                            </div>

                            <div class="field-item full-width-field">
                                <label class="field-label">Update Penempatan Toko</label>
                                <div class="multi-dropdown" id="editKaryawanTokoDropdown">
                                    <div class="dropdown-header">Pilih Toko...</div>
                                    <div class="dropdown-list" id="editKaryawanTokoList">
                                        </div>
                                </div>
                                <small class="hint-text">Ceklis ulang toko yang ditugaskan.</small>
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        EDIT : JENIS KENDALA
                    ========================== -->
                    <div class="master-form" id="editFormJenisKendala" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Tipe Kendala</label>
                                <select id="editTipeKendala" class="field-value">
                                    <option>Software</option>
                                    <option>Hardware</option>
                                </select>
                            </div>

                            <div class="field-item">
                                <label class="field-label">Jenis Kendala</label>
                                <input
                                    type="text"
                                    id="editJenisKendala"
                                    class="field-value"
                                >
                            </div>

                            <div class="field-item">
                                <label class="field-label">Mempunyai Pertanyaan Turunan</label>
                                <div style="display:flex; gap:20px; padding-top:8px;">
                                    <label>
                                        <input type="radio" name="editTurunan" value="Ya">
                                        Ya
                                    </label>
                                    <label>
                                        <input type="radio" name="editTurunan" value="Tidak">
                                        Tidak
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

<!-- =========================
     EDIT : ROLE
========================= -->
<div class="master-form" id="editFormRole" style="display:none;">
    <div class="field-grid">

        <!-- Nama Role -->
        <div class="field-item">
            <label class="field-label">Nama Role</label>
            <input type="text" id="editNamaRole" class="field-value">
        </div>

        <!-- Permissions -->
        <div class="field-item full-width-field">
            <label class="field-label">Permissions</label>

            <!-- BOOLEAN PERMISSION -->
            <div class="key-group">
                <strong>Boolean Permission</strong><br><br>

                <label class="key-row">
                    <input type="checkbox"
                           id="edit_key_enable"
                           name="roleBoolKeys[]"
                           value="enable">
                    Enable
                </label>

                <label class="key-row">
                    <input type="checkbox"
                           id="edit_key_update_toko"
                           name="roleBoolKeys[]"
                           value="update_toko">
                    Update Toko
                </label>

                <label class="key-row">
                    <input type="checkbox"
                           id="edit_key_update_role"
                           name="roleBoolKeys[]"
                           value="update_role">
                    Update Role
                </label>

                <label class="key-row">
                    <input type="checkbox"
                           id="edit_key_handling_request"
                           name="roleBoolKeys[]"
                           value="handling_request">
                    Handling Request
                </label>
            </div>

            <hr>

            <!-- STRING PERMISSION -->
            <?php
            $stringKeys = [
                'manage_user' => 'Manage User',
                'update_user_toko' => 'Update User (By Toko)',
                'input_request' => 'Input Request',
                'cancel_request' => 'Cancel Request',
                'update_request' => 'Update Request',
                'delete_request' => 'Delete Request'
            ];
            foreach ($stringKeys as $key => $label): ?>
            <div class="key-group">

                <label class="key-row">
                    <input type="checkbox"
                           id="edit_key_<?= $key ?>">
                    <?= $label ?>
                </label>

                <div class="key-value-wrapper disabled"
                     id="edit_value_<?= $key ?>">

                    <div class="multi-dropdown">
                        <div class="dropdown-header">Pilih Toko</div>
                        <div class="dropdown-list"
                             id="edit_<?= $key ?>TokoList">
                        </div>
                    </div>

                    <small class="hint-text">
                        Bisa pilih lebih dari satu toko
                    </small>
                </div>
            </div>
            <hr>
            <?php endforeach; ?>

            <!-- EXPIRATION -->
            <div class="key-group">
                <strong>Expiration Date</strong><br><br>

                <label class="key-row">
                    <input type="checkbox" id="editRoleForever">
                    Forever
                </label>

                <input type="datetime-local"
                       id="editRoleExpirationDate"
                       class="field-value">
            </div>

        </div>
    </div>
</div>

<!-- ACTION -->
<div class="modal-actions">
    <button class="btn-submit" onclick="updateData()">Update</button>
</div>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="dist/js/master_data.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>

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
                    <div class="table-container">
                        <table id="mainDataTable" class="display responsive nowrap" style="width:100%">
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="addMasterModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="addMasterModal">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2 id="addMasterTitle">Tambah Data</h2>
            </div>

            <div class="modal-body">
                <form id="mainAddForm">
                    <div class="master-form" id="formToko">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Toko</label>
                                <input type="text" id="addNamaToko" class="field-value" placeholder="Masukkan nama toko">
                            </div>
                            <div class="field-item">
                                <label class="field-label">Kode Toko</label>
                                <input type="text" id="addKodeToko" class="field-value" placeholder="Masukkan kode toko">
                            </div>
                        </div>
                    </div>

                    <div class="master-form" id="formKaryawan" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Karyawan</label>
                                <input type="text" id="addNamaKaryawan" class="field-value" placeholder="Masukkan nama karyawan">
                            </div>
                            <div class="field-item">
                                <label class="field-label">Toko</label>
                                <select id="addTokoKaryawan" class="field-value">
                                    <option value="">-- Pilih Toko --</option>
                                    </select>
                            </div>
                        </div>
                    </div>

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
                                <input type="text" id="addJenisKendala" class="field-value" placeholder="Masukkan jenis kendala">
                            </div>
                            <div class="field-item">
                                <label class="field-label">Mempunyai Pertanyaan Turunan</label>
                                <div style="display:flex; gap:20px; padding-top:8px;">
                                    <label><input type="radio" name="addTurunan" value="Ya"> Ya</label>
                                    <label><input type="radio" name="addTurunan" value="Tidak" checked> Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="master-form" id="formRole" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Role</label>
                                <input type="text" id="addNamaRole" class="field-value" placeholder="Masukkan nama role">
                            </div>
                            <div class="field-item full-width-field">
                                <label class="field-label">List Of Keys</label>
                                <div class="field-value" style="min-height:120px;">
                                    <label><input type="checkbox" name="roleKeys[]" value="Dashboard"> Dashboard</label><br>
                                    <label><input type="checkbox" name="roleKeys[]" value="Master Data"> Master Data</label><br>
                                    <label><input type="checkbox" name="roleKeys[]" value="Service Request"> Service Request</label><br>
                                    <label><input type="checkbox" name="roleKeys[]" value="Report"> Report</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" data-modal="addMasterModal">Batal</button>
                <button class="btn-submit" onclick="saveData()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ADD -->
    <div id="addMasterModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" data-modal="addMasterModal">&times;</span>

            <div class="modal-detail">
                <!-- HEADER -->
                <div class="modal-header">
                    <h2 id="addMasterTitle">Tambah Data</h2>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <!-- =========================
                        TOKO
                    ========================== -->
                    <div class="master-form" id="formToko">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Toko</label>
                                <input type="text" class="field-value" placeholder="Masukkan nama toko">
                            </div>

                            <div class="field-item">
                                <label class="field-label">Kode Toko</label>
                                <input type="text" class="field-value" placeholder="Masukkan kode toko">
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        KARYAWAN
                    ========================== -->
                    <div class="master-form" id="formKaryawan" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Karyawan</label>
                                <input type="text" class="field-value" placeholder="Masukkan nama karyawan">
                            </div>

                            <div class="field-item">
                                <label class="field-label">Toko</label>
                                <select class="field-value">
                                    <option>-- Pilih Toko --</option>
                                    <option>Toko A</option>
                                    <option>Toko B</option>
                                </select>
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
                                <select class="field-value">
                                    <option>-- Pilih --</option>
                                    <option>Software</option>
                                    <option>Hardware</option>
                                </select>
                            </div>

                            <div class="field-item">
                                <label class="field-label">Jenis Kendala</label>
                                <input type="text" class="field-value" placeholder="Masukkan jenis kendala">
                            </div>

                            <div class="field-item">
                                <label class="field-label">Mempunyai Pertanyaan Turunan</label>
                                <div style="display:flex; gap:20px; padding-top:8px;">
                                    <label><input type="radio" name="turunan"> Ya</label>
                                    <label><input type="radio" name="turunan"> Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        ROLE
                    ========================== -->
                    <div class="master-form" id="formRole" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Role</label>
                                <input type="text" class="field-value" placeholder="Masukkan nama role">
                            </div>

                            <div class="field-item full-width-field">
                                <label class="field-label">List Of Keys</label>
                                <div class="field-value" style="min-height:120px;">
                                    <label><input type="checkbox"> Dashboard</label><br>
                                    <label><input type="checkbox"> Master Data</label><br>
                                    <label><input type="checkbox"> Service Request</label><br>
                                    <label><input type="checkbox"> Report</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ACTION -->
                <div class="modal-actions">
                    <button class="btn-cancel" data-modal="addMasterModal">Batal</button>
                    <button class="btn-submit">Save</button>
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
                                <input
                                    type="text"
                                    id="editNamaKaryawan"
                                    class="field-value"
                                >
                            </div>

                            <div class="field-item">
                                <label class="field-label">Toko</label>
                                <select id="editTokoKaryawan" class="field-value">
                                    <option>-- Pilih Toko --</option>
                                    <option>Toko A</option>
                                    <option>Toko B</option>
                                </select>
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
                    ========================== -->
                    <div class="master-form" id="editFormRole" style="display:none;">
                        <div class="field-grid">
                            <div class="field-item">
                                <label class="field-label">Nama Role</label>
                                <input
                                    type="text"
                                    id="editNamaRole"
                                    class="field-value"
                                >
                            </div>

                            <div class="field-item full-width-field">
                                <label class="field-label">List Of Keys</label>
                                <div class="field-value" style="min-height:120px;">
                                    <label><input type="checkbox"> Dashboard</label><br>
                                    <label><input type="checkbox"> Master Data</label><br>
                                    <label><input type="checkbox"> Service Request</label><br>
                                    <label><input type="checkbox"> Report</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ACTION -->
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeModal('editModal')">
                        Batal
                    </button>
                    <button class="btn-submit" onclick="updateData()">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="dist/js/master_data.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>
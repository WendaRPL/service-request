<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="dist/css/master_data.css">
    <link rel="stylesheet" href="dist/css/sidebar.css">
    <link rel="stylesheet" href="dist/css/navbar.css">
    <link rel="stylesheet" href="dist/css/base.css">
</head>
<body>
    <div class="container">
        <!-- disini navbar dan sidebar -->
        <header class="navbar">
            <div class="navbar-left">
                <span class="staff-label">STAFF IT</span>
                <button class="menu-toggle">☰</button>
                <span class="system-title">SERVICE REQUEST SYSTEM</span>
            </div>
            <div class="navbar-right">
                <span class="username">Username</span>
                <div class="user-icon">👤</div>
            </div>
        </header>
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="dashboard_staffit.php" data-page="dashboard">Dashboard</a></li>
                    <li><a href="done_canceled.php" data-page="done-canceled">Done & Canceled</a></li>
                    <li><a href="reporting.php" data-page="reporting">Reporting</a></li>
                    <li class="active"><a href="master_data.php" data-page="master-data">Master Data</a></li>
                    <li><a href="user_management.php" data-page="user-management">User Management</a></li>
                </ul>
            </nav>
        </aside>

        <!-- main content isi disini -->
        <main class="dashboard-content">
            <h1>MASTER DATA</h1>
            
            <button class="action-button add-button" id="btnAddMaster">
                + Add Data
            </button>

            <div class="master-data-container">
                <div class="master-data-card">
                    <div class="tabs-nav">
                        <button class="tab-button active">Toko</button>
                        <button class="tab-button">Karyawan</button>
                        <button class="tab-button">Jenis Kendala</button>
                        <button class="tab-button">Role</button>
                    </div>

                    <div class="tab-content active">
                        <div class="table-container">
                            <!-- Table akan di-generate oleh JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
                                <div class="custom-dropdown">
                                    <div class="dropdown-header" onclick="toggleDropdown(this)">
                                        <span>Pilih Keys...</span>
                                        <i class="arrow-icon">▼</i>
                                    </div>
                                    <div class="dropdown-list">
                                        <div class="key-item">
                                            <label><input type="checkbox" onchange="toggleKeyInput(this)"> Dashboard</label>
                                            <input type="text" class="key-value-input" placeholder="Value...">
                                        </div>
                                        <div class="key-item">
                                            <label><input type="checkbox" onchange="toggleKeyInput(this)"> Master Data</label>
                                            <input type="text" class="key-value-input" placeholder="Value...">
                                        </div>
                                        <div class="key-item">
                                            <label><input type="checkbox" onchange="toggleKeyInput(this)"> Service Request</label>
                                            <input type="text" class="key-value-input" placeholder="Value...">
                                        </div>
                                        <div class="key-item">
                                            <label><input type="checkbox" onchange="toggleKeyInput(this)"> Report</label>
                                            <input type="text" class="key-value-input" placeholder="Value...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ACTION -->
                <div class="modal-actions">
                    <button class="btn-submit">Tambahkan</button>
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
                    <button class="btn-submit" onclick="updateData()">Update</button>
                </div>
            </div>
        </div>
    </div>

        
    <script src="dist/js/master_data.js"></script>
    <script src="dist/js/sidebar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
</body>
</html>

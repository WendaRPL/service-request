<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="dist/css/done_canceled.css">
    <link rel="stylesheet" href="dist/css/sidebar.css">
    <link rel="stylesheet" href="dist/css/navbar.css">
    <link rel="stylesheet" href="dist/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- navbar dan sidebar -->
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
                    <li class="active"><a href="done_canceled.php" data-page="done-canceled">Done & Canceled</a></li>
                    <li><a href="reporting.php" data-page="reporting">Reporting</a></li>
                    <li><a href="master_data.php" data-page="master-data">Master Data</a></li>
                    <li><a href="user_management.php" data-page="user-management">User Management</a></li>
                </ul>
            </nav>
        </aside>

        <!-- main content -->
        <main class="dashboard-content">
            <h1>DONE & CANCELED REQUESTS</h1>

            <div class="border-queue done-header">
                    <button class="btn-filter-toggle" id="btnToggleFilter">
                        Filter ▼
                    </button>
                </div>

                <!-- FILTER PANEL -->
                <div class="filter-panel" id="filterPanel">
                    <div class="filter-grid">
                        
                        <div class="filter-group">
                            <label>Toko</label>
                            <select id="filter-toko">
                                <option value="">Semua</option>
                                <option value="Hikomi">Hikomi</option>
                                <option value="Toyomatsu">Toyomatsu</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Peminta</label>
                            <select id="filter-peminta">
                                <option value="">Semua</option>
                                <option value="Dina">Dina</option>
                                <option value="Rani">Rani</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Handler</label>
                            <select id="filter-handler">
                                <option value="">Semua</option>
                                <option value="Reza">Reza</option>
                                <option value="Salman">Salman</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Dari Tanggal</label>
                            <input type="date" id="filter-start-date">
                        </div>

                        <div class="filter-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="filter-end-date">
                        </div>

                    </div>
                    
                    <div class="filter-actions">
                        <button class="btn-filter btn-reset" id="btnResetFilter">
                            Reset
                        </button>
                        <button class="btn-filter btn-terapkan" id="btnApplyFilter">
                            Terapkan
                        </button>
                    </div>
                </div>

                <!-- TABLE CONTAINER -->
                <div class="all-request-table">

                    <!-- TABLE -->
                    <div class="table-container">
                        <table id="doneCanceledTable" class="display responsive nowrap">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Toko</th>
                                    <th>User</th>
                                    <th>Peminta</th>
                                    <th>S/H</th>
                                    <th>Jenis Kendala</th>
                                    <th>Status</th>
                                    <th>Handling By</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1.</td>
                                    <td>Hikomi</td>
                                    <td>Mina</td>
                                    <td>Dina</td>
                                    <td class="urgency-cell">
                                        <span class="urgency-code">S</span>
                                        <span class="urgency-badge high">High</span>
                                    </td>
                                    <td>SFA</td>
                                    <td class="status-done">Done</td>
                                    <td>Reza</td>
                                    <td>2025-12-13</td>
                                    <td>
                                        <button class="btn-detail">Open Details</button>
                                        <button class="btn-report">Report</button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2.</td>
                                    <td>Toyomatsu</td>
                                    <td>Sifa</td>
                                    <td>Rani</td>
                                    <td class="urgency-cell">
                                        <span class="urgency-code">H</span>
                                        <span class="urgency-badge low">Low</span>
                                    </td>
                                    <td>Printer</td>
                                    <td class="status-canceled">Canceled</td>
                                    <td>Salman</td>
                                    <td>2025-01-20</td>
                                    <td>
                                        <button class="btn-detail">Open Details</button>
                                        <button class="btn-report">Report</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div>

    <!-- Modal Detail  -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Detail Request</h2>
                </div>

                <div class="modal-body">
                    <div class="field-grid">

                        <div class="field-item">
                            <div class="field-label">No. Request</div>
                            <div class="field-value" id="d-no" class="field-value" readonly></div>
                        </div>
                        
                        <div class="field-item">
                            <label class="field-label">Toko</label>
                            <input type="text" id="d-toko" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">User</label>
                            <input type="text" id="d-user" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Peminta</label>
                            <input type="text" id="d-peminta" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">S/H</label>
                            <input type="text" id="d-urgensi" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Jenis Kendala</label>
                            <input type="text" id="d-kendala" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Status</label>
                            <input type="text" id="d-status" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Handling By</label>
                            <input type="text" id="d-handler" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Tanggal</label>
                            <input type="text" id="d-tanggal" class="field-value" readonly>
                        </div>

                        <!-- Field full width untuk deskripsi -->
                        <div class="field-item full-width-field">
                            <div class="field-label">Deskripsi Kendala</div>
                            <div class="field-value textarea-value" id="detail-deskripsi">...</div>
                        </div>
                        
                        <!-- Field full width untuk foto -->
                        <div class="field-item full-width-field">
                            <div class="field-label">Lampiran Foto</div>
                            <div class="field-value">
                                <div class="foto-preview">
                                    <div class="foto-placeholder">
                                        <i>📷</i>
                                        <span>Tidak ada lampiran foto</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Report -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>

            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Laporan Penyelesaian</h2>
                </div>

                <div class="modal-body">
                    <div class="field-grid">

                        <!-- input sebelumnya -->
                        <div class="section-divider">Informasi Sebelumnya <span style="color: #eb2700ff; font-weight:600">(Read Only)</span></div>

                        <div class="field-item">
                            <label class="field-label">No. Request</label>
                            <input type="text" id="r-no" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Toko</label>
                            <input type="text" id="r-toko-old" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">User</label>
                            <input type="text" id="r-user-old" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Peminta</label>
                            <input type="text" id="r-peminta-old" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">S/H</label>
                            <input type="text" id="r-sh-old" class="field-value" readonly>
                        </div>

                        <div class="field-item">
                            <label class="field-label">Jenis Kendala</label>
                            <input type="text" id="r-kendala-old" class="field-value" readonly>
                        </div>

                        <!-- input user yang salah input -->
                        <div class="field-item full-width-field">
                            <label class="field-label input">Pengisian Untuk User yang Salah Input <span style="color: #eb2700ff;">(Dapat diisi)</span></label>
                            <textarea
                                id="r-user-wrong-input"
                                class="field-value textarea-value"
                                rows="4"
                                placeholder="Jelaskan data apa yang salah diinput oleh user..."
                            ></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel">Batal</button>
                    <button class="btn-submit" id="btn-submit-report">
                        Simpan Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>


    
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script src="dist/js/sidebar.js"></script>
<script src="dist/js/done_canceled.js"></script>
</body>
</html>
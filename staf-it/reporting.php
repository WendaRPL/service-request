<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="dist/css/reporting.css">
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
                    <li class="active"><a href="reporting.php" data-page="reporting">Reporting</a></li>
                    <li><a href="master_data.php" data-page="master-data">Master Data</a></li>
                    <li><a href="user_management.php" data-page="user-management">User Management</a></li>
                </ul>
            </nav>
        </aside>

        <!-- main content isi disini -->
        <main class="dashboard-content">
            <h1>REPORTING</h1>

            <div class="border-queue report-header">
                <button class="btn-filter-toggle" id="btnToggleReportFilter">
                    Filter ▼
                </button>
            </div>

            <!-- FILTER PANEL -->
            <div class="filter-panel" id="reportFilterPanel">
                <div class="filter-grid">

                    <div class="filter-group">
                        <label>Toko</label>
                        <select id="report-filter-toko">
                            <option value="">Semua</option>
                            <option value="Hikomi">Hikomi</option>
                            <option value="Toyomatsu">Toyomatsu</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Penerima</label>
                        <select id="report-filter-user">
                            <option value="">Semua</option>
                            <option value="Dina">Dina</option>
                            <option value="Rani">Rani</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select id="report-filter-status">
                            <option value="">Semua</option>
                            <option value="Done">Done</option>
                            <option value="Canceled">Canceled</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <input type="date" id="report-filter-start-date">
                    </div>

                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" id="report-filter-end-date">
                    </div>

                </div>

                <div class="filter-actions">
                    <button class="btn-filter btn-reset" id="btnResetReportFilter">
                        Reset
                    </button>
                    <button class="btn-filter btn-terapkan" id="btnApplyReportFilter">
                        Terapkan
                    </button>
                </div>
            </div>

            <div class="all-request-table">

            <div class="export-buttons">
                <button class="export-btn" onclick="copyTable()">Copy</button>
                <button class="export-btn" onclick="exportTable('excel')">Export Excel</button>
                <button class="export-btn" onclick="exportTable('csv')">Export CSV</button>
                <button class="export-btn" onclick="exportTable('pdf')">Export PDF</button>
                <button class="export-btn" onclick="printSemua()">Print Semua</button>
            </div>

                <div class="table-container">
                    <table id="reportingTable" class="display responsive nowrap">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>No.</th>
                                <th>Toko</th>
                                <th>User</th>
                                <th>Peminta</th>
                                <th>S/H</th>
                                <th>Jenis Kendala</th>
                                <th>Status</th>
                                <th>Handler</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="row-check"></td>
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
                                <td>2024-03-10</td>
                                <td>
                                    <button class="btn-detail">Open Details</button>
                                </td>
                            </tr>

                            <tr>
                                <td><input type="checkbox" class="row-check"></td>
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
                                <td>2024-03-09</td>
                                <td>
                                    <button class="btn-detail">Open Details</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Detail Reporting -->
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

    
    <script src="dist/js/reporting.js"></script>
    <script src="dist/js/sidebar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- Dependency -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
</body>
</html> 

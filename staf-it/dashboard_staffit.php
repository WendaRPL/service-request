<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="dist/css/dashboard.css">
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
                    <li class="active"><a href="dashboard_staffit.php" data-page="dashboard">Dashboard</a></li>
                    <li><a href="done_canceled.php" data-page="done-canceled">Done & Canceled</a></li>
                    <li><a href="reporting.php" data-page="reporting">Reporting</a></li>
                    <li><a href="master_data.php" data-page="master-data">Master Data</a></li>
                    <li><a href="user_management.php" data-page="user-management">User Management</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- main content isi disini -->
        <main class="dashboard-content">
            <h1>DASHBOARD</h1>
            
            <div class="main-layout">
                <div class="urgency-queue-panel">
                    <h2>Open</h2>
                    <p class="sort-info">Urutan berdasarkan Urgensi (High→Low) > Waktu</p>

                    <div class="select-all-row">
                        <input type="checkbox" id="select-all">
                        <label for="select-all">Pilih semua</label>
                        
                        <div class="action-dropdown-wrapper">
                            <button class="action-btn" id="actionDropdownBtn">Aksi ▼</button>  
                            <div class="action-dropdown-menu" id="actionDropdown">  
                                <div class="dropdown-item" data-action="accept">Accept</div>
                                <div class="dropdown-item" data-action="change-urgency">Ganti Level Urgensi</div>
                            </div>
                        </div>
                    </div>

                   <div class="queue-items-container">
                        <div class="queue-item">
                            <input type="checkbox" id="user1">
                            <label for="user1">
                                <span class="queue-name">Luna</span>
                                <abbr class="queue-store">HK</abbr>
                                <span class="urgency-badge high">High</span>
                                <div class="queue-datetime">
                                    <span class="date">13/03/2024</span>
                                    <span class="time">12:30</span>
                                </div>
                            </label>
                            <button class="open-detail-btn">Open Details</button>
                        </div>
                        <div class="queue-item">
                            <input type="checkbox" id="user2">
                            <label for="user2">
                                <span class="queue-name">Fulan</span>
                                <abbr class="queue-store">TM</abbr>
                                <span class="urgency-badge low">Low</span>
                                <div class="queue-datetime">
                                    <span class="date">13/03/2024</span>
                                    <span class="time">12:50</span>
                                </div>
                            </label>
                            <button class="open-detail-btn">Open Details</button>
                        </div>
                        <div class="queue-item">
                            <input type="checkbox" id="user3">
                            <label for="user3">
                                <span class="queue-name">Mita</span>
                                <abbr class="queue-store">RJ</abbr>
                                <span class="urgency-badge low">Low</span>
                                <div class="queue-datetime">
                                    <span class="date">13/03/2024</span>
                                    <span class="time">11:00</span>
                                </div>
                            </label>
                            <button class="open-detail-btn">Open Details</button>
                        </div>
                    </div>
                </div>

                <div class="all-request-table">
                    <div class="border-queue">
                        <h2>All Request (Queue)</h2>
                    </div>
                    
                    <div class="table-container">
                        <table id="requestTable">
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-request-id="1">
                                    <td>1.</td>
                                    <td>Hikomi</td>
                                    <td>Mina</td>
                                    <td>Dina</td>
                                    <td class="urgency-cell">
                                        <span class="urgency-code">S</span>
                                        <span class="urgency-badge high">High</span>
                                    </td>
                                    <td>SFA</td>
                                    <td class="status-on-process">
                                        <div class="on-process-card">On Process</div>
                                    </td>
                                    <td>Reza</td>
                                    <td class="action-buttons">
                                        <button class="btn-detail">Open Details</button>
                                        <button class="btn-transfer">Transfer Handler</button>
                                        <button class="btn-status">Ubah Status</button>
                                    </td>
                                </tr>
                                <tr data-request-id="2">
                                    <td>2.</td>
                                    <td>Toyomatsu</td>
                                    <td>Sifa</td>
                                    <td>Rani</td>
                                    <td class="urgency-cell">
                                        <span class="urgency-code">H</span>
                                        <span class="urgency-badge low">Low</span>
                                    </td>
                                    <td>Printer Error</td>
                                    <td class="status-on-process">
                                        <div class="on-process-card">On Process</div>
                                    </td>
                                    <td>Salman</td>
                                    <td class="action-buttons">
                                        <button class="btn-detail">Open Details</button>
                                        <button class="btn-transfer">Transfer Handler</button>
                                        <button class="btn-status">Ubah Status</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL 1: DETAIL REQUEST -->
    <div id="requestDetailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" data-modal="requestDetailModal">&times;</span>
            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Detail Permintaan Layanan</h2>
                </div>
                
                <div class="modal-body">
                    <!-- Grid 2 kolom untuk field -->
                    <div class="field-grid">
                        <!-- Kolom 1 -->
                        <div class="field-item">
                            <div class="field-label">No. Request</div>
                            <div class="field-value" id="detail-no">...</div>
                        </div>
                        
                        <!-- Kolom 2 -->
                        <div class="field-item">
                            <div class="field-label">Toko</div>
                            <div class="field-value" id="detail-toko">...</div>
                        </div>

                        <!-- Kolom 3 -->
                        <div class="field-item">
                            <div class="field-label">User</div>
                            <div class="field-value" id="detail-user">...</div>
                        </div>
                        
                        <!-- Kolom 4 -->
                        <div class="field-item">
                            <div class="field-label">Peminta</div>
                            <div class="field-value" id="detail-peminta">...</div>
                        </div>
                        
                        <!-- Kolom 5 -->
                        <div class="field-item">
                            <div class="field-label">Handling by</div>
                            <div class="field-value" id="detail-staff">...</div>
                        </div>
                        
                        <!-- Kolom 6 -->
                        <div class="field-item">
                            <div class="field-label">Jenis Kendala</div>
                            <div class="field-value" id="detail-jenis">...</div>
                        </div>
                        
                        <!-- Kolom 7 -->
                        <div class="field-item">
                            <div class="field-label">Tipe Kendala (S/H)</div>
                            <div class="field-value" id="detail-sh">...</div>
                        </div>
                        
                        <!-- Kolom 8 -->
                        <div class="field-item">
                            <div class="field-label">Status</div>
                            <div class="field-value">
                                <span class="status-value" id="detail-status">...</span>
                            </div>
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
                    <button class="btn-cancel" data-modal="requestDetailModal">Cancel</button>
                    <button class="btn-accept" id="btn-accept-request">Accept</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: TRANSFER HANDLER -->
    <div id="transferHandlerModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" data-modal="transferHandlerModal">&times;</span>
            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Ganti Handler</h2>
                </div>
                
                <div class="modal-body">
                    <div class="field-grid">
                        <!-- Kolom 1 -->
                        <div class="field-item">
                            <div class="field-label">No. Request</div>
                            <div class="field-value" id="transfer-no">...</div>
                        </div>

                        <!-- Kolom 2 -->
                        <div class="field-item">
                            <div class="field-label">Toko</div>
                            <div class="field-value" id="transfer-toko">...</div>
                        </div>

                        <!-- Kolom 3 -->
                        <div class="field-item">
                            <div class="field-label">User</div>
                            <div class="field-value" id="transfer-user">...</div>
                        </div>
                        
                        <!-- Kolom 4 -->
                        <div class="field-item">
                            <div class="field-label">Peminta</div>
                            <div class="field-value" id="transfer-requester">...</div>
                        </div>
                        
                        <!-- Kolom 5 -->
                        <div class="field-item">
                            <div class="field-label">Jenis Kendala</div>
                            <div class="field-value" id="transfer-jenis">...</div>
                        </div>
                        
                        <!-- Kolom 6 -->
                        <div class="field-item">
                            <div class="field-label">Tipe Kendala</div>
                            <div class="field-value" id="transfer-tipe">...</div>
                        </div>
                        
                        <!-- Field full width untuk deskripsi -->
                        <div class="field-item full-width-field">
                            <div class="field-label">Deskripsi Kendala</div>
                            <div class="field-value textarea-value" id="transfer-deskripsi">...</div>
                        </div>
                    </div>

                    <!-- Bagian bawah: Form ganti handler -->
                    <div class="form-group">
                        <label for="transfer-handler-select">Pilih Handler Baru</label>
                        <select id="transfer-handler-select" class="form-select">
                            <option value="" disabled selected style="color: #000000ff;">-- Pilih Staff IT --</option>
                            <option value="Reza">Reza</option>
                            <option value="Salman">Salman</option>
                            <option value="Aldy">Aldy</option>
                            <option value="Rio">Rio</option>
                        </select>
                        <div class="select-hint" style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                            * Pilih staff IT yang akan menangani request ini
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel" data-modal="transferHandlerModal">Back</button>
                    <button class="btn-submit" id="btn-submit-transfer">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: UBAH STATUS -->
    <div id="ubahStatusModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" data-modal="ubahStatusModal">&times;</span>
            <div class="modal-detail">
                <div class="modal-header">
                    <h2>Update Status Service Request</h2>
                </div>
                
                <div class="modal-body">
                    <!-- Info User & Toko -->
                    <div class="user-info-section">
                        <div class="user-info-item">
                            <span class="info-label">User:</span>
                            <span class="info-value" id="ubah-status-user">Nama User</span>
                        </div>
                        <div class="user-info-item">
                            <span class="info-label">Toko:</span>
                            <span class="info-value" id="ubah-status-toko">Nama Toko</span>
                        </div>
                    </div>
                    
                    <!-- Grid 2 kolom -->
                    <div class="status-form-grid">
                        <!-- Kolom Kiri -->
                        <div class="form-column">
                            <!-- Tindakan yang dilakukan -->
                            <div class="form-group">
                                <label class="form-label">Tindakan yang dilakukan: (Action)</label>
                                <input type="text" class="form-input" id="ubah-status-tindakan" 
                                    placeholder="Masukkan tindakan yang dilakukan...">
                            </div>
                            
                            <!-- Hasil akhirnya -->
                            <div class="form-group">
                                <label class="form-label">Hasil akhirnya: (Result)</label>
                                <textarea class="form-textarea" id="ubah-status-hasil" 
                                        placeholder="Deskripsikan hasil akhir tindakan..."></textarea>
                            </div>
                            
                            <!-- Edit Tipe Kendala -->
                            <div class="form-group">
                                <label class="form-label">Edit Tipe Kendala:</label>
                                <select class="form-select" id="ubah-status-jenis">
                                    <option value="">-- Pilih Kendala --</option>
                                    <option value="sfa">SFA</option>
                                    <option value="jaringan">Jaringan</option>
                                    <option value="error">Error</option>
                                    <option value="aplikasi_lambat">Aplikasi Lambat</option>
                                    <option value="tidak_bisa_login">Tidak Bisa Login</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            <!-- Terjadi di siapa saja -->
                            <div class="form-group">
                                <label class="form-label">Terjadi di siapa saja:</label>
                                <select class="form-select" id="ubah-status-terjadi">
                                    <option value="">-- Pilih --</option>
                                    <option value="individu">Individu</option>
                                    <option value="kelompok">Kelompok</option>
                                    <option value="semua">Semua User</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="form-column">                                                 
                            <!-- User penerima Service -->
                            <div class="form-group">
                                <label class="form-label">Ubah penerima Service:</label>
                                <select class="form-select" id="ubah-status-penerima">
                                    <option value="">-- Pilih --</option>
                                    <option value="user1">Dina</option>
                                    <option value="user2">Siwan</option>
                                    <option value="user3">Kozirou</option>
                                </select>
                            </div>

                            <!-- Ubah Status -->
                            <div class="form-group">
                                <label class="form-label">Ubah Status:</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="status" value="done" checked>
                                        <span class="radio-custom"></span>
                                        Done
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="status" value="on-repair">
                                        <span class="radio-custom"></span>
                                        On Repair
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Ketidaksesuaian laporan -->
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="ubah-status-ketidaksesuaian">
                                    <span class="checkbox-custom"></span>
                                    Ketidaksesuaian laporan (opsional)
                                </label>
                                <textarea class="form-textarea" id="ubah-status-ketidaksesuaian-detail" 
                                        placeholder="Deskripsikan ketidaksesuaian..." 
                                        style="margin-top: 8px; display: none;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel" data-modal="ubahStatusModal">Cancel</button>
                    <button class="btn-lanjut" id="btn-submit-status">Lanjut</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Permintaan Password -->
    <div id="passwordModal" class="modal">
        <div class="modal-content password-modal">
            <span class="close-btn" data-modal="passwordModal">&times;</span>
            <div class="modal-detail">
                <div class="modal-header">
                    <h2>User sudah menerima informasi diagnosa & estimasi waktu pelayanan</h2>
                </div>
                
                <div class="modal-body password-modal-body">
                    <!-- Informasi User -->
                    <div class="user-confirmation">
                        <div class="confirmation-item">
                            <span class="confirmation-label">User:</span>
                            <span class="confirmation-value" id="password-user">Nama User</span>
                        </div>
                        <div class="confirmation-item">
                            <span class="confirmation-label">Status:</span>
                            <span class="confirmation-value" id="password-status">Done</span>
                        </div>
                    </div>
                    
                    <!-- Input Password -->
                    <div class="password-section">
                        <label class="form-label">Masukkan Password:</label>
                        <div class="password-input-group">
                            <input type="password" class="form-input password-input" 
                                id="input-password" placeholder="Ketik password di sini...">
                            <button class="btn-show-password" type="button">
                                <i class="eye-icon">👁️</i>
                            </button>
                        </div>
                        <div class="password-hint">
                            *Password diperlukan untuk konfirmasi perubahan status
                        </div>
                    </div>
                    
                    <!-- Checkbox Konfirmasi -->
                    <div class="confirmation-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="confirm-checkbox">
                            <span class="checkbox-custom"></span>
                            Saya telah mengonfirmasi bahwa user telah menerima informasi
                        </label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-cancel" data-modal="passwordModal">Cancel</button>
                    <button class="btn-submit" id="btn-final-submit" disabled>Submit</button>
                </div>
            </div>
        </div>
    </div>

    
    <script src="dist/js/dashboard.js"></script>
    <script src="dist/js/sidebar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
</body>
</html> 


// done_cancelled.js - PHP+AJAX VERSION

// =========================
// GLOBAL VARIABLES
// =========================
let currentTab = 'done';
let doneTable = null;
let canceledTable = null;

// =========================
// DOM READY
// =========================
document.addEventListener('DOMContentLoaded', function() {
    initDataTables();
    initFilterToggle();
    initFilters();
    initModals();
    initButtonActions();
    setDefaultDates();
    
    // Tab switching
    document.getElementById('tab-done').addEventListener('click', () => switchTab('done'));
    document.getElementById('tab-canceled').addEventListener('click', () => switchTab('canceled'));
});

// =========================
// DATATABLES INITIALIZATION
// =========================
function initDataTables() {
    // Initialize Done Table
    if ($('#doneTable').length) {
        doneTable = $('#doneTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[5, 'desc']], // Sort by tanggal
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 7] }, // No dan Aksi tidak bisa di-sort
                { className: 'dt-center', targets: [0, 4] }, // Center untuk No dan Urgensi
                { responsivePriority: 1, targets: [1, 2, 3] } // Priority untuk kolom penting
            ]
        });
    }
    
    // Initialize Canceled Table
    if ($('#canceledTable').length) {
        canceledTable = $('#canceledTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[5, 'desc']],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 7] },
                { className: 'dt-center', targets: [0, 4] },
                { responsivePriority: 1, targets: [1, 2, 3] }
            ]
        });
    }
}

// =========================
// TAB SWITCHING
// =========================
function switchTab(tab) {
    currentTab = tab;
    
    const doneTab = document.getElementById('tab-done');
    const canceledTab = document.getElementById('tab-canceled');
    const doneContainer = document.getElementById('done-table');
    const canceledContainer = document.getElementById('canceled-table');
    
    if (tab === 'done') {
        doneTab.classList.add('active');
        canceledTab.classList.remove('active');
        doneContainer.style.display = 'block';
        canceledContainer.style.display = 'none';
        
        // Redraw table jika perlu
        if (doneTable) {
            setTimeout(() => doneTable.columns.adjust().responsive.recalc(), 100);
        }
    } else {
        doneTab.classList.remove('active');
        canceledTab.classList.add('active');
        doneContainer.style.display = 'none';
        canceledContainer.style.display = 'block';
        
        if (canceledTable) {
            setTimeout(() => canceledTable.columns.adjust().responsive.recalc(), 100);
        }
    }
}

// =========================
// FILTER TOGGLE
// =========================
function initFilterToggle() {
    const btnToggleFilter = document.getElementById('btnToggleFilter');
    const filterPanel = document.getElementById('filterPanel');
    
    if (btnToggleFilter && filterPanel) {
        btnToggleFilter.addEventListener('click', function() {
            filterPanel.classList.toggle('show');
            this.innerHTML = filterPanel.classList.contains('show') 
                ? 'Filter ▲' 
                : 'Filter ▼';
        });
    }
}

// =========================
// FILTER FUNCTIONS
// =========================
function initFilters() {
    // Apply Filter
    document.getElementById('btnApplyFilter')?.addEventListener('click', function() {
        applyFilters();
    });
    
    // Reset Filter
    document.getElementById('btnResetFilter')?.addEventListener('click', function() {
        resetFilters();
    });
    
    // Enter key untuk filter date
    document.querySelectorAll('#filterPanel input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    });
}

function applyFilters() {
    const toko = document.getElementById('filter-toko').value.trim();
    const peminta = document.getElementById('filter-peminta').value.trim();
    const handler = document.getElementById('filter-handler').value.trim();
    const startDate = document.getElementById('filter-start-date').value;
    const endDate = document.getElementById('filter-end-date').value;
    
    // Apply ke tabel yang aktif
    const table = currentTab === 'done' ? doneTable : canceledTable;
    
    if (table) {
        table.columns().search(''); // Reset semua
        
        // Filter per kolom
        if (toko) table.column(1).search(toko, true, false);
        if (peminta) table.column(2).search(peminta, true, false);
        if (handler) table.column(6).search(handler, true, false);
        
        // Filter tanggal (kolom 5)
        if (startDate || endDate) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const dateStr = data[5]; // Tanggal dalam format "01 Jan 2025 14:30"
                const rowDate = parseCustomDate(dateStr);
                
                if (!rowDate) return true;
                
                if (startDate && rowDate < new Date(startDate)) return false;
                if (endDate && rowDate > new Date(endDate + ' 23:59:59')) return false;
                
                return true;
            });
        }
        
        table.draw();
        
        // Hapus filter tanggal setelah draw
        if (startDate || endDate) {
            $.fn.dataTable.ext.search.pop();
        }
    }
}

function parseCustomDate(dateStr) {
    // Parse format "01 Jan 2025 14:30"
    const months = {
        'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
        'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
    };
    
    const parts = dateStr.split(' ');
    if (parts.length >= 3) {
        const day = parseInt(parts[0]);
        const month = months[parts[1]];
        const year = parseInt(parts[2]);
        
        let hour = 0, minute = 0;
        if (parts.length > 3) {
            const timeParts = parts[3].split(':');
            hour = parseInt(timeParts[0]) || 0;
            minute = parseInt(timeParts[1]) || 0;
        }
        
        return new Date(year, month, day, hour, minute);
    }
    
    return null;
}

function resetFilters() {
    // Reset input values
    document.getElementById('filter-toko').value = '';
    document.getElementById('filter-peminta').value = '';
    document.getElementById('filter-handler').value = '';
    document.getElementById('filter-start-date').value = '';
    document.getElementById('filter-end-date').value = '';
    
    // Reset tabel
    const table = currentTab === 'done' ? doneTable : canceledTable;
    if (table) {
        table.columns().search('');
        table.search('');
        table.draw();
    }
}

// =========================
// MODAL FUNCTIONS
// =========================
function initModals() {
    // Close modal dengan klik di luar
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Close modal dengan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    
    // Close button
    document.querySelectorAll('.close-btn, .btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
}

function initButtonActions() {
    // Event delegation untuk detail button
    document.addEventListener('click', function(e) {
        // Detail button
        if (e.target.classList.contains('btn-detail') || 
            e.target.closest('.btn-detail')) {
            const button = e.target.classList.contains('btn-detail') 
                ? e.target 
                : e.target.closest('.btn-detail');
            
            const requestId = button.getAttribute('data-id');
            if (requestId) {
                openDetailModal(requestId);
            }
        }
    });
}

// =========================
// DETAIL MODAL WITH AJAX
// =========================
function openDetailModal(requestId) {
    const modal = document.getElementById('detailModal');
    const modalBody = document.getElementById('modalDetailBody');
    
    // Show loading
    modalBody.innerHTML = `
        <div class="loading-spinner" style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // AJAX request
    fetch(`ajax_get_detail.php?id=${requestId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(result => {
            if (result.success && result.data) {
                const data = result.data;
                modalBody.innerHTML = generateDetailHTML(data);
            } else {
                modalBody.innerHTML = `
                    <div class="error-state" style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-triangle fa-2x" style="color: #e74c3c;"></i>
                        <p>${result.error || 'Gagal memuat data'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `
                <div class="error-state" style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle fa-2x" style="color: #e74c3c;"></i>
                    <p>Terjadi kesalahan saat mengambil data</p>
                    <small>${error.message}</small>
                </div>
            `;
        });
}

function generateDetailHTML(data) {
    // Tentukan class urgensi
    let urgensiClass = '';
    switch(data.level_urgensi) {
        case 'Sangat Tinggi': urgensiClass = 'very-high'; break;
        case 'Tinggi': urgensiClass = 'high'; break;
        case 'Sedang': urgensiClass = 'medium'; break;
        case 'Rendah': urgensiClass = 'low'; break;
    }
    
    return `
        <div class="field-grid">
            <div class="field-item">
                <label class="field-label">ID Request</label>
                <input type="text" class="field-value" value="#${data.id}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Status</label>
                <input type="text" class="field-value status-value" value="${data.status_nama}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Toko</label>
                <input type="text" class="field-value" value="${data.user_toko || '-'}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Peminta</label>
                <input type="text" class="field-value" value="${data.peminta_nama || '-'}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Email Peminta</label>
                <input type="text" class="field-value" value="${data.peminta_email || '-'}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Tanggal Request</label>
                <input type="text" class="field-value" value="${data.created_at_formatted}" readonly>
            </div>
            
            <div class="field-item full-width-field">
                <div class="section-divider">Informasi Kendala</div>
            </div>
            
            <div class="field-item">
                <label class="field-label">Jenis Kendala</label>
                <input type="text" class="field-value" value="${data.jenis_kendala || '-'}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Level Urgensi</label>
                <div class="field-value urgency-cell" style="padding: 5px;">
                    <div class="urgency-card">
                        <span class="urgency-code">${data.jenis || 'S'}</span>
                        <span class="urgency-badge ${urgensiClass}">
                            ${data.level_urgensi || 'Sedang'}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="field-item full-width-field">
                <label class="field-label">Deskripsi Kendala</label>
                <textarea class="field-value textarea-value" rows="4" readonly>${data.deskripsi_kendala || 'Tidak ada deskripsi'}</textarea>
            </div>
            
            <div class="field-item full-width-field">
                <div class="section-divider">Informasi Penyelesaian</div>
            </div>
            
            <div class="field-item">
                <label class="field-label">Handler</label>
                <input type="text" class="field-value" value="${data.staff_nama || '-'}" readonly>
            </div>
            
            <div class="field-item">
                <label class="field-label">Tanggal ${data.status_nama}</label>
                <input type="text" class="field-value" value="${data.status_timestamp_formatted}" readonly>
            </div>
            
            <div class="field-item full-width-field">
                <label class="field-label">Catatan Penyelesaian</label>
                <textarea class="field-value textarea-value" rows="3" readonly>${data.catatan_penyelesaian || 'Tidak ada catatan'}</textarea>
            </div>
        </div>
    `;
}

// =========================
// UTILITY FUNCTIONS
// =========================
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            document.body.style.overflow = '';
        }, 300);
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal.show').forEach(modal => {
        modal.classList.remove('show');
    });
    document.body.style.overflow = '';
}

function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today);
    lastWeek.setDate(today.getDate() - 7);
    
    // Format YYYY-MM-DD
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    document.getElementById('filter-start-date').value = formatDate(lastWeek);
    document.getElementById('filter-end-date').value = formatDate(today);
}

// =========================
// NAVIGATION
// =========================
function goToDashboard() {
    window.location.href = 'home.php';
}

// =========================
// RESPONSIVE FIX
// =========================
window.addEventListener('resize', function() {
    if (doneTable) {
        setTimeout(() => doneTable.columns.adjust().responsive.recalc(), 100);
    }
    if (canceledTable) {
        setTimeout(() => canceledTable.columns.adjust().responsive.recalc(), 100);
    }
});
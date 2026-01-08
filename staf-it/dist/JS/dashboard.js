// =========================
// DASHBOARD FUNCTIONALITY
// =========================

// =========================
// MODAL MANAGEMENT SYSTEM
// =========================

// Fungsi untuk membuka modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Fungsi untuk menutup modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('show');
    document.body.style.overflow = 'auto';

    if (modalId === 'requestDetailModal') {
        const btnAccept = document.getElementById('btn-accept-request');
        if (btnAccept) btnAccept.style.display = 'none';
    }
}

// =========================
// DETAIL REQUEST MODAL
// =========================

function openRequestDetail(requestId) {
    const requestRow = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!requestRow) return;

    const cells = requestRow.querySelectorAll('td');

    const statusText =
        cells[5].querySelector('.on-process-card')
            ? cells[5].querySelector('.on-process-card').textContent.trim()
            : cells[5].textContent.trim();

    document.getElementById('detail-no').textContent = requestId;
    document.getElementById('detail-toko').textContent = cells[1].textContent;
    document.getElementById('detail-user').textContent = cells[2].textContent;
    document.getElementById('detail-peminta').textContent = cells[3].textContent;
    document.getElementById('detail-sh').textContent = cells[4].textContent;
    document.getElementById('detail-jenis').textContent = cells[5].textContent;
    document.getElementById('detail-status').textContent = cells[6].textContent;
    document.getElementById('detail-staff').textContent = cells[7].textContent;
    document.getElementById('detail-deskripsi').textContent = '';
            

    const btnAccept = document.getElementById('btn-accept-request');

    // 🔥 RESET STATE (INI KUNCI UTAMA)
    btnAccept.style.display = 'none';

    // 🔥 TENTUKAN ULANG BERDASARKAN STATUS
    if (statusText.toLowerCase() === 'open') {
        btnAccept.style.display = 'inline-flex';
    }

    openModal('requestDetailModal');
}

// =========================
// TRANSFER HANDLER MODAL
// =========================

function openTransferHandler(requestId) {
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!row) return;

    const cells = row.querySelectorAll('td');

    // ambil data dari tabel
    document.getElementById('transfer-no').textContent = requestId;
    document.getElementById('transfer-user').textContent = cells[2].textContent;
    document.getElementById('transfer-requester').textContent = cells[3].textContent;
    document.getElementById('transfer-toko').textContent = cells[1].textContent;
    document.getElementById('transfer-jenis').textContent = cells[5].textContent;
    document.getElementById('transfer-deskripsi').textContent = '-';
    document.getElementById('transfer-tipe').textContent = cells[4].textContent;

    // Set current handler di dropdown jika ada
    const selectEl = document.getElementById('transfer-handler-select');
    const currentStaff = cells[5].textContent;
    if (selectEl && currentStaff) {
        selectEl.value = currentStaff;
    }

    openModal('transferHandlerModal');
}

// =========================
// UBAH STATUS MODAL
// =========================
// Global variable untuk menyimpan requestId yang sedang diproses
let currentRequestId = null;

function openUbahStatus(requestId) {
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!row) return;

    const cells = row.querySelectorAll('td');
    
    // Simpan requestId global
    currentRequestId = requestId;
    
    // Set data ke modal
    document.getElementById('ubah-status-user').textContent = cells[2].textContent;
    document.getElementById('ubah-status-toko').textContent = cells[1].textContent;; 
    
    // Set data dari tabel ke form
    document.getElementById('ubah-status-jenis').value = getJenisValue(cells[3].textContent);
    
    // Buka modal
    openModal('ubahStatusModal');
}

function getJenisValue(jenisText) {
    const jenis = jenisText.toLowerCase();
    if (jenis.includes('hardware') || jenis.includes('printer')) return 'hardware';
    if (jenis.includes('software') || jenis.includes('sfa')) return 'software';
}

// =========================
// ACTION DROPDOWN FUNCTIONALITY
// =========================

let currentDropdown = null;

function toggleActionDropdown() {
    const actionBtn = document.querySelector('.action-btn');
    if (!actionBtn) return;
    
    // Cek apakah ada item yang dipilih
    const checkboxes = document.querySelectorAll('.queue-item input[type="checkbox"]:checked');
    
    // Hapus dropdown lama jika ada
    if (currentDropdown) {
        currentDropdown.remove();
        currentDropdown = null;
        return;
    }
    
    // Buat dropdown baru
    currentDropdown = document.createElement('div');
    currentDropdown.className = 'action-dropdown';
    currentDropdown.innerHTML = `
        <div class="dropdown-option" data-action="accept">Accept</div>
        <div class="dropdown-option" data-action="change-urgency">Ganti Level Urgensi</div>
    `;
    
    // Styling dropdown
    currentDropdown.style.cssText = `
        position: fixed;
        background: white;
        border: 1px solid var(--light-gray);
        border-radius: var(--radius-sm);
        box-shadow: 0 4px 12px var(--shadow-medium);
        z-index: 1000;
        min-width: 180px;
        overflow: hidden;
        animation: fadeIn 0.2s ease;
    `;
    
    // Posisikan dropdown di bawah tombol
    const rect = actionBtn.getBoundingClientRect();
    currentDropdown.style.top = `${rect.bottom + 5}px`;
    currentDropdown.style.left = `${rect.left}px`;
    
    document.body.appendChild(currentDropdown);
    
    // Styling options
    const options = currentDropdown.querySelectorAll('.dropdown-option');
    options.forEach(option => {
        option.style.cssText = `
            padding: 10px 15px;
            cursor: pointer;
            font-size: 0.85rem;
            color: var(--dark-gray);
            border-bottom: 1px solid var(--fourth);
            transition: var(--transition);
        `;
        
        option.addEventListener('mouseenter', () => {
            option.style.backgroundColor = 'var(--fourth)';
        });
        
        option.addEventListener('mouseleave', () => {
            option.style.backgroundColor = 'white';
        });
        
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            handleDropdownAction(option.getAttribute('data-action'), checkboxes);
            currentDropdown.remove();
            currentDropdown = null;
        });
    });
    
    // Hapus border bottom dari item terakhir
    options[options.length - 1].style.borderBottom = 'none';
    
    // Close dropdown ketika klik di luar
    const closeDropdownHandler = (e) => {
        if (currentDropdown && !currentDropdown.contains(e.target) && e.target !== actionBtn) {
            currentDropdown.remove();
            currentDropdown = null;
            document.removeEventListener('click', closeDropdownHandler);
        }
    };
    
    setTimeout(() => {
        document.addEventListener('click', closeDropdownHandler);
    }, 10);
}

function handleDropdownAction(action, checkboxes) {
    if (checkboxes.length === 0) {
        alert('Pilih setidaknya satu item dari antrian.');
        return;
    }
    
    if (action === 'accept') {
        const confirmAccept = confirm(`Accept ${checkboxes.length} item yang dipilih?`);
        if (confirmAccept) {
            alert(`${checkboxes.length} item telah di-accept.`);
            checkboxes.forEach(cb => {
                const row = cb.closest('.queue-item');
                row.style.opacity = '0.6';
                row.style.textDecoration = 'line-through';
            });
        }
    } else if (action === 'change-urgency') {
        const newUrgency = prompt(
            'Masukkan level urgensi (Very High / High / Medium / Low):',
            'Medium'
        );

        if (!newUrgency) return;

        const urgencyMap = {
            'very high': {
                short: 'VH',
                class: 'very-high',
                border: 'var(--error)'
            },
            'high': {
                short: 'H',
                class: 'high',
                border: 'var(--error)'
            },
            'medium': {
                short: 'M',
                class: 'medium',
                border: 'var(--first)'
            },
            'low': {
                short: 'L',
                class: 'low',
                border: 'var(--success)'
            }
        };

        const key = newUrgency.toLowerCase();

        if (!urgencyMap[key]) {
            alert('Level urgensi tidak valid!');
            return;
        }

        checkboxes.forEach(cb => {
            const row = cb.closest('.queue-item');

            /* ===== QUEUE LIST ===== */
            if (row) {
                row.style.borderLeftColor = urgencyMap[key].border;
            }

            /* ===== TABLE ===== */
            const tableRow = cb.closest('tr');
            if (!tableRow) return;

            const urgencyCell = tableRow.querySelector('.urgency-cell');
            if (!urgencyCell) return;

            const textEl = urgencyCell.querySelector('.urgency-code');
            const badgeEl = urgencyCell.querySelector('.urgency-badge');

            if (textEl) textEl.textContent = urgencyMap[key].short;
            
            if (badgeEl) {
                badgeEl.textContent = newUrgency;
                badgeEl.className = `urgency-badge ${urgencyMap[key].class}`;
            }
        });

        alert(`Level urgensi diubah menjadi: ${newUrgency}`);
    }
}

// =========================
// INITIALIZE DASHBOARD
// =========================

document.addEventListener('DOMContentLoaded', () => {
    // =========================
    // MODAL EVENT LISTENERS
    // =========================
    
    // Close semua modal dengan ESC
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                modal.classList.remove('show');
            });
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close modal ketika klik di luar content
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close button untuk semua modal
    document.querySelectorAll('.close-btn').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Detail Request Modal - Close button
    const btnClose = document.querySelector('#requestDetailModal .btn-cancel');
    if (btnClose) {
        btnClose.addEventListener('click', () => {
            closeModal('requestDetailModal');
        });
    }
    
    // Transfer Handler Modal - Back button
    const btnCancel = document.querySelector('#transferHandlerModal .btn-cancel');
    if (btnCancel) {
        btnCancel.addEventListener('click', () => {
            closeModal('transferHandlerModal');
        });
    }
    
    // Transfer Handler Modal - Submit button
    const btnSubmit = document.querySelector('#transferHandlerModal .btn-submit');
    if (btnSubmit) {
        btnSubmit.addEventListener('click', () => {
            const newHandler = document.getElementById('transfer-handler-select').value;
            if (!newHandler) {
                alert('Silakan pilih handler baru!');
                return;
            }
            
            // Simulasi submit
            alert(`Handler berhasil diganti ke: ${newHandler}`);
            closeModal('transferHandlerModal');
        });
    }

    // =========================
    // UBAH STATUS MODAL EVENT LISTENERS
    // =========================

    // Toggle textarea ketidaksesuaian
    const ketidaksesuaianCheckbox = document.getElementById('ubah-status-ketidaksesuaian');
    const ketidaksesuaianTextarea = document.getElementById('ubah-status-ketidaksesuaian-detail');

    if (ketidaksesuaianCheckbox && ketidaksesuaianTextarea) {
        ketidaksesuaianCheckbox.addEventListener('change', function() {
            ketidaksesuaianTextarea.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Submit button untuk ubah status (Tombol "Lanjut")
    const btnSubmitStatus = document.getElementById('btn-submit-status');
    if (btnSubmitStatus) {
        btnSubmitStatus.addEventListener('click', function() {
            // Kumpulkan data dari modal ubah status
            const statusData = {
                user: document.getElementById('ubah-status-user').textContent,
                toko: document.getElementById('ubah-status-toko').textContent,
                tindakan: document.getElementById('ubah-status-tindakan').value,
                hasil: document.getElementById('ubah-status-hasil').value,
                jenis: document.getElementById('ubah-status-jenis').value,
                status: document.querySelector('input[name="status"]:checked').value,
                ketidaksesuaian: document.getElementById('ubah-status-ketidaksesuaian').checked,
                ketidaksesuaianDetail: document.getElementById('ubah-status-ketidaksesuaian-detail').value,
                terjadi: document.getElementById('ubah-status-terjadi').value,
                penerima: document.getElementById('ubah-status-penerima').value,
                requestId: currentRequestId
            };
            
            // Validasi form
            if (!statusData.tindakan.trim()) {
                alert('Mohon isi tindakan yang dilakukan');
                return;
            }
            
            if (!statusData.hasil.trim()) {
                alert('Mohon isi hasil akhir tindakan');
                return;
            }
            
            if (!statusData.jenis) {
                alert('Mohon pilih jenis kendala');
                return;
            }
            
            // Update informasi di modal password
            document.getElementById('password-user').textContent = statusData.user;
            document.getElementById('password-status').textContent = 
                statusData.status === 'done' ? 'Done' : 'On Repair';
            
            // Simpan data di localStorage untuk nanti
            localStorage.setItem('pendingStatusData', JSON.stringify(statusData));
            
            // Reset form password
            document.getElementById('input-password').value = '';
            document.getElementById('confirm-checkbox').checked = false;
            document.getElementById('btn-final-submit').disabled = true;
            
            // Tutup modal pertama dan buka modal password
            closeModal('ubahStatusModal');
            openModal('passwordModal');
        });
    }

    // Cancel button untuk ubah status
    const btnCancelStatus = document.querySelector('#ubahStatusModal .btn-cancel');
    if (btnCancelStatus) {
        btnCancelStatus.addEventListener('click', () => {
            closeModal('ubahStatusModal');
        });
    }

    // =========================
    // PASSWORD MODAL FUNCTIONALITY
    // =========================

    // Password modal elements
    const passwordElements = {
        btnShowPassword: document.querySelector('#passwordModal .btn-show-password'),
        inputPassword: document.getElementById('input-password'),
        confirmCheckbox: document.getElementById('confirm-checkbox'),
        btnFinalSubmit: document.getElementById('btn-final-submit'),
        btnBackPassword: document.querySelector('#passwordModal .btn-cancel')
    };

    // Toggle password visibility
    if (passwordElements.btnShowPassword) {
        passwordElements.btnShowPassword.addEventListener('click', () => {
            const isPassword = passwordElements.inputPassword.type === 'password';
            passwordElements.inputPassword.type = isPassword ? 'text' : 'password';
            passwordElements.btnShowPassword.querySelector('.eye-icon').textContent = 
                isPassword ? '🔒' : '👁️';
        });
    }

    // Validate password form
    function validatePasswordForm() {
        const isPasswordFilled = passwordElements.inputPassword?.value.trim().length > 0;
        const isCheckboxChecked = passwordElements.confirmCheckbox?.checked;
        if (passwordElements.btnFinalSubmit) {
            passwordElements.btnFinalSubmit.disabled = !(isPasswordFilled && isCheckboxChecked);
        }
    }

    // Add validation listeners
    if (passwordElements.inputPassword) {
        passwordElements.inputPassword.addEventListener('input', validatePasswordForm);
    }

    if (passwordElements.confirmCheckbox) {
        passwordElements.confirmCheckbox.addEventListener('change', validatePasswordForm);
    }

    // Back button - return to status modal
    if (passwordElements.btnBackPassword) {
        passwordElements.btnBackPassword.addEventListener('click', () => {
            closeModal('passwordModal');
            openModal('ubahStatusModal');
        });
    }

    // Submit button - final submission
    if (passwordElements.btnFinalSubmit) {
        passwordElements.btnFinalSubmit.addEventListener('click', () => {
            // Validation
            if (!passwordElements.inputPassword?.value.trim()) {
                alert('Password harus diisi');
                return;
            }
            
            if (!passwordElements.confirmCheckbox?.checked) {
                alert('Harap konfirmasi bahwa user telah menerima informasi');
                return;
            }
            
            // Get data from localStorage
            const statusDataStr = localStorage.getItem('pendingStatusData');
            if (!statusDataStr) {
                alert('Data tidak ditemukan. Silakan ulangi proses.');
                closeModal('passwordModal');
                return;
            }
            
            const statusData = JSON.parse(statusDataStr);
            
            // Prepare final data
            const finalData = {
                ...statusData,
                password: passwordElements.inputPassword.value,
                confirmed: true,
                timestamp: new Date().toISOString()
            };
            
            // Simulate server submission
            console.log('Data dikirim:', finalData);
            
            // Show success message
            const statusText = statusData.status === 'done' ? 'Done' : 'On Repair';
            alert(`Status berhasil diubah menjadi: ${statusText}`);
            
            // Update UI if needed
            updateRequestStatus(statusData.requestId, statusText);
            
            // Clean up and close
            resetPasswordForm();
            localStorage.removeItem('pendingStatusData');
            closeModal('passwordModal');
        });
    }

    // Helper function to update UI
    function updateRequestStatus(requestId, statusText) {
        if (!requestId) return;
        
        const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
        if (!row) return;
        
        const statusCell = row.querySelector('.status-on-process .on-process-card');
        if (!statusCell) return;
        
        statusCell.textContent = statusText;
        
        // Update color based on status
        if (statusText === 'Done') {
            statusCell.style.backgroundColor = '#28a745';
            statusCell.style.color = 'white';
            statusCell.style.border = '1px solid #218838';
        } else if (statusText === 'On Repair') {
            statusCell.style.backgroundColor = '#ffc107';
            statusCell.style.color = '#212529';
            statusCell.style.border = '1px solid #e0a800';
        }
    }

    // Helper function to reset form
    function resetPasswordForm() {
        if (passwordElements.inputPassword) {
            passwordElements.inputPassword.value = '';
        }
        if (passwordElements.confirmCheckbox) {
            passwordElements.confirmCheckbox.checked = false;
        }
        if (passwordElements.btnFinalSubmit) {
            passwordElements.btnFinalSubmit.disabled = true;
        }
    }

    // =========================
    // BUTTON EVENT LISTENERS
    // =========================
    
    // Detail buttons - Open Queue Panel
    document.querySelectorAll('.open-detail-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const queueItem = button.closest('.queue-item');
            const label = queueItem.querySelector('label').textContent;
            const parts = label.split(' ');
            const user = parts[0];
            
            // Set data ke modal
            document.getElementById('detail-no').textContent = '-';
            document.getElementById('detail-toko').textContent = '';
            document.getElementById('detail-user').textContent = '';
            document.getElementById('detail-peminta').textContent = user;
            document.getElementById('detail-sh').textContent = 'S';
            document.getElementById('detail-jenis').textContent = 'Lain-lain';
            document.getElementById('detail-status').textContent = 'Open';
            document.getElementById('detail-staff').textContent = 'Belum ditugaskan';
            document.getElementById('detail-deskripsi').textContent = '';
            
            openModal('requestDetailModal');
        });
    });
    
    // Action dropdown button
    const actionBtn = document.querySelector('.action-btn');
    if (actionBtn) {
        actionBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleActionDropdown();
        });
    }
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (event) => {
            const checkboxes = document.querySelectorAll('.queue-item input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });
    }

    // =========================
    // ACCEPT REQUEST (DETAIL MODAL)
    // =========================

    const btnAcceptRequest = document.getElementById('btn-accept-request');

    if (btnAcceptRequest) {
        btnAcceptRequest.addEventListener('click', () => {
            const requestNo = document.getElementById('detail-no').textContent;

            if (!confirm(`Accept request No: ${requestNo}?`)) return;

            const row = document.querySelector(`tr[data-request-id="${requestNo}"]`);
            if (row) {
                const statusCell = row.querySelector('.on-process-card');

                if (statusCell) {
                    statusCell.textContent = 'On Process';
                    statusCell.style.background = '#17a2b8';
                    statusCell.style.color = '#fff';
                }

                // OPTIONAL: tandai sudah bukan OPEN
                row.dataset.status = 'on_process';
            }

            alert(`Request ${requestNo} berhasil di-accept`);
            closeModal('requestDetailModal');
        });
    }

    // =========================
    // DATATABLES EVENT HANDLERS
    // =========================
    
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#requestTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 25, 50, 100],
            ordering: true,
            searching: true,
            info: true,
            columnDefs: [
                { 
                    orderable: false, 
                    targets: [8] 
                },
                { 
                    className: 'dt-center', 
                    targets: [0] 
                }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                paginate: {
                    previous: "‹",
                    next: "›"
                }
            }
        });
        
        // Detail button click
        $('#requestTable tbody').on('click', '.btn-detail', function (e) {
            e.stopPropagation();
            
            let tr = $(this).closest('tr');
            
            // Jika klik di child row → ambil parent row
            if (tr.hasClass('child')) {
                tr = tr.prev();
            }
            
            const requestId = tr.data('request-id');
            if (!requestId) return;
            
            openRequestDetail(requestId);
        });
        
        // Transfer button click
        $('#requestTable tbody').on('click', '.btn-transfer', function (e) {
            e.stopPropagation();
            
            let tr = $(this).closest('tr');
            
            // Jika klik di child row → ambil parent row
            if (tr.hasClass('child')) {
                tr = tr.prev();
            }
            
            const requestId = tr.data('request-id');
            if (!requestId) return;
            
            openTransferHandler(requestId);
        });

        // Ubah Status button click
        $('#requestTable tbody').on('click', '.btn-status', function (e) {
            e.stopPropagation();
            
            let tr = $(this).closest('tr');
            
            // Jika klik di child row → ambil parent row
            if (tr.hasClass('child')) {
                tr = tr.prev();
            }
            
            const requestId = tr.data('request-id');
            if (!requestId) return;
            
            // Buka modal ubah status
            openUbahStatus(requestId);
        });
        
        // Adjust DataTable on window resize
        window.addEventListener('resize', () => {
            if (table) {
                table.columns.adjust().responsive.recalc();
            }
        });
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dropdown-option:hover {
        background-color: var(--fourth) !important;
    }
    
    .dropdown-option[data-action="accept"]:hover {
        color: var(--success) !important;
    }
    
    .dropdown-option[data-action="change-urgency"]:hover {
        color: var(--first) !important;
    }
    
    /* Modal show/hide animation */
    .modal {
        display: none;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .modal.show {
        display: flex;
        opacity: 1;
    }
`;
document.head.appendChild(style);
// =========================
// GABUNGAN SEMUA FUNGSI
// =========================

// VARIABLE GLOBAL
let unratedRequests = [
    {
        id: 1,
        title: 'SFA System Error',
        date: '2024-01-15',
        staff: 'Reza',
        deskripsi: 'Sistem SFA tidak bisa login, muncul error 500',
        hasilService: 'Melakukan reset database user, memperbaiki konfigurasi koneksi database, dan mengupdate patch security untuk aplikasi SFA.',
        userRating: 0
    },
    {
        id: 3,
        title: 'Printer Issues',
        date: '2024-01-16',
        staff: 'Aldy',
        deskripsi: 'Printer tidak bisa mencetak, kertas macet',
        hasilService: 'Membersihkan roller printer, mengganti cartridge yang bermasalah, dan melakukan kalibrasi ulang printer. Juga menginstall driver terbaru.',
        userRating: 0
    }
];

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded - Initializing home page');
    
    // Initialize unrated counter
    updateUnratedCount();
    
    // Initialize animations
    initAnimations();
    
    // Initialize modal event listeners
    initModalEvents();
    
    // Dashboard modal functionality (jika ada di halaman)
    initDashboardModals();
    
    // Dashboard action dropdown functionality (jika ada di halaman)
    initActionDropdown();
    
    // Dashboard select all functionality (jika ada di halaman)
    initSelectAll();
    
    // Make table rows clickable (jika ada di halaman)
    initClickableTableRows();
});

// ========== ANIMATIONS ==========
function initAnimations() {
    const elements = document.querySelectorAll('.sidebar-box, .content-area, .request-table tr');
    elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        setTimeout(() => {
            el.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s forwards`;
        }, 100);
    });
}

// ========== UNRATED MODAL FUNCTIONS ==========
function updateUnratedCount() {
    const badge = document.getElementById('unratedCount');
    if (badge) {
        badge.textContent = unratedRequests.length;
        if (unratedRequests.length > 0) {
            badge.classList.add('pulse');
        } else {
            badge.classList.remove('pulse');
        }
    }
}

function lihatUnrated() {
    console.log('Membuka modal unrated');
    openUnratedModal();
}

function openUnratedModal() {
    const modal = document.getElementById('unratedModal');
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }
    
    // Add modal-open class to body for blur effect
    document.body.classList.add('modal-open');
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Render content
    renderUnratedList();
    
    console.log('Modal opened successfully');
}

function closeUnratedModal() {
    const modal = document.getElementById('unratedModal');
    if (!modal) return;
    
    // Remove modal-open class
    document.body.classList.remove('modal-open');
    
    // Hide modal
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    
    console.log('Modal closed');
}

function renderUnratedList() {
    const listContainer = document.getElementById('unratedList');
    if (!listContainer) {
        console.error('unratedList element not found!');
        return;
    }
    
    if (unratedRequests.length === 0) {
        listContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <h4>Semua request sudah di-rating!</h4>
                <p>Tidak ada request yang perlu diberi rating saat ini.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    unratedRequests.forEach((request) => {
        html += `
            <div class="unrated-item" data-id="${request.id}">
                <div class="unrated-item-header">
                    <div class="unrated-info">
                        <h4>${request.title}</h4>
                        <div class="unrated-meta">
                            <span><i class="fas fa-user-tie"></i> ${request.staff}</span>
                            <span><i class="fas fa-calendar"></i> ${request.date}</span>
                        </div>
                    </div>
                    <span class="unrated-date">Selesai</span>
                </div>
                
                <!-- DESKRIPSI KENDALA -->
                <div class="service-detail">
                    <div class="detail-header">
                        <i class="fas fa-exclamation-circle"></i>
                        <h5>Deskripsi Kendala</h5>
                    </div>
                    <div class="detail-content">
                        <p>${request.deskripsi}</p>
                    </div>
                </div>
                
                <!-- HASIL SERVICE -->
                <div class="service-detail">
                    <div class="detail-header">
                        <i class="fas fa-check-circle"></i>
                        <h5>Hasil Service</h5>
                    </div>
                    <div class="detail-content">
                        <p>${request.hasilService}</p>
                    </div>
                </div>
                
                <div class="rating-section">
                    <div class="rating-title">Berikan Rating (1-5 bintang):</div>
                    
                    <div class="rating-stars" id="stars-${request.id}">
                        <i class="fas fa-star" data-value="1" onclick="setRating(${request.id}, 1)"></i>
                        <i class="fas fa-star" data-value="2" onclick="setRating(${request.id}, 2)"></i>
                        <i class="fas fa-star" data-value="3" onclick="setRating(${request.id}, 3)"></i>
                        <i class="fas fa-star" data-value="4" onclick="setRating(${request.id}, 4)"></i>
                        <i class="fas fa-star" data-value="5" onclick="setRating(${request.id}, 5)"></i>
                    </div>
                    
                    <div class="rating-comment">
                        <textarea id="comment-${request.id}" 
                                  placeholder="Tulis komentar atau feedback (opsional)..."
                                  rows="2"></textarea>
                    </div>
                    
                    <div class="rating-actions">
                        <button class="rating-btn submit" 
                                onclick="submitRating(${request.id})" 
                                disabled 
                                id="submit-btn-${request.id}">
                            <i class="fas fa-check"></i> Kirim Rating
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
    
    // Highlight stars if already rated
    unratedRequests.forEach(request => {
        if (request.userRating > 0) {
            highlightStars(request.id, request.userRating);
            enableSubmitButton(request.id);
        }
    });
}

function setRating(requestId, rating) {
    console.log('Setting rating:', requestId, rating);
    
    // Highlight stars
    highlightStars(requestId, rating);
    
    // Update request data
    const requestIndex = unratedRequests.findIndex(r => r.id === requestId);
    if (requestIndex !== -1) {
        unratedRequests[requestIndex].userRating = rating;
    }
    
    // Enable submit button
    enableSubmitButton(requestId);
}

function highlightStars(requestId, rating) {
    const starsContainer = document.getElementById(`stars-${requestId}`);
    if (!starsContainer) {
        console.error('Stars container not found for ID:', requestId);
        return;
    }
    
    const stars = starsContainer.querySelectorAll('.fas.fa-star');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
            star.style.color = '#FFA726';
        } else {
            star.classList.remove('active');
            star.style.color = '#ddd';
        }
    });
}

function enableSubmitButton(requestId) {
    const submitBtn = document.getElementById(`submit-btn-${requestId}`);
    if (submitBtn) {
        submitBtn.disabled = false;
        console.log('Submit button enabled for request:', requestId);
    } else {
        console.error('Submit button not found for ID:', requestId);
    }
}

function submitRating(requestId) {
    const requestIndex = unratedRequests.findIndex(r => r.id === requestId);
    if (requestIndex === -1) {
        console.error('Request not found:', requestId);
        return;
    }
    
    const request = unratedRequests[requestIndex];
    const comment = document.getElementById(`comment-${requestId}`)?.value || '';
    const rating = request.userRating || 0;
    
    if (rating === 0) {
        alert('Pilih rating terlebih dahulu!');
        return;
    }
    
    // Show confirmation
    if (!confirm(`Kirim rating ${rating} bintang untuk "${request.title}"?`)) {
        return;
    }
    
    // Simulate API call
    console.log('Submitting rating:', { 
        requestId, 
        rating, 
        comment,
        deskripsi: request.deskripsi,
        hasilService: request.hasilService 
    });
    
    // Show success notification
    showNotification(`Rating ${rating} bintang berhasil dikirim untuk: ${request.title}`, 'success');
    
    // Remove from list after delay
    setTimeout(() => {
        removeUnratedRequest(requestId);
    }, 1500);
}

function skipRating(requestId) {
    const requestIndex = unratedRequests.findIndex(r => r.id === requestId);
    if (requestIndex === -1) return;
    
    const request = unratedRequests[requestIndex];
    
    if (!confirm(`Yakin ingin melewatkan rating untuk "${request.title}"?\nAnda tidak bisa memberikan rating lagi nanti.`)) {
        return;
    }
    
    showNotification(`Rating untuk "${request.title}" dilewatkan`, 'info');
    
    setTimeout(() => {
        removeUnratedRequest(requestId);
    }, 1000);
}

function removeUnratedRequest(requestId) {
    // Remove from array
    unratedRequests = unratedRequests.filter(r => r.id !== requestId);
    
    // Update UI
    updateUnratedCount();
    renderUnratedList();
}

// ========== REQUEST MODAL FUNCTIONS ==========
function ajukanRequest(type) {
    openRequestModal();
}

function openRequestModal() {
    const modal = document.getElementById('requestModal');
    if (!modal) return;
    
    document.body.classList.add('modal-open');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('requestModal');
    if (!modal) return;
    
    document.body.classList.remove('modal-open');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    resetForm();
}

function toggleNamaLain(show) {
    const namaLainGroup = document.getElementById('namaLainGroup');
    const namaLainInput = document.getElementById('nama_lain');
    
    if (show) {
        namaLainGroup.style.display = 'flex';
        namaLainInput.required = true;
    } else {
        namaLainGroup.style.display = 'none';
        namaLainInput.required = false;
        namaLainInput.value = '';
    }
}

function toggleHardwareFields(show) {
    const jenisHardwareGroup = document.getElementById('jenisHardwareGroup');
    const kodeHardwareGroup = document.getElementById('kodeHardwareGroup');
    
    if (show) {
        jenisHardwareGroup.style.display = 'flex';
        kodeHardwareGroup.style.display = 'flex';
        document.getElementById('jenis_hardware').required = true;
    } else {
        jenisHardwareGroup.style.display = 'none';
        kodeHardwareGroup.style.display = 'none';
        document.getElementById('jenis_hardware').required = false;
        document.getElementById('jenis_hardware').value = '';
        document.getElementById('kode_hardware').value = '';
    }
}

function triggerFileUpload() {
    document.getElementById('fileUpload').click();
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    const fileName = document.getElementById('fileName');
    
    if (file) {
        fileName.textContent = file.name;
        fileName.style.color = 'var(--success)';
        fileName.style.fontWeight = '600';
        fileName.innerHTML = `<i class="fas fa-check-circle"></i> ${file.name}`;
    }
}

function resetForm() {
    const form = document.querySelector('#requestModal .request-form');
    form.reset();
    
    document.getElementById('namaLainGroup').style.display = 'none';
    document.getElementById('jenisHardwareGroup').style.display = 'none';
    document.getElementById('kodeHardwareGroup').style.display = 'none';
    
    const fileName = document.getElementById('fileName');
    fileName.textContent = 'Masukkan SS/Image';
    fileName.style.color = '';
    fileName.style.fontWeight = '';
    fileName.innerHTML = 'Masukkan SS/Image';
    
    document.getElementById('fileUpload').value = '';
}

function submitRequestForm(event) {
    event.preventDefault();
    
    const formData = {
        toko: document.getElementById('toko').value,
        untukSiapa: document.querySelector('input[name="untuk_siapa"]:checked').value,
        namaLain: document.getElementById('nama_lain').value,
        tipeKendala: document.querySelector('input[name="tipe_kendala"]:checked').value,
        jenisHardware: document.getElementById('jenis_hardware').value,
        kodeHardware: document.getElementById('kode_hardware').value,
        jenisKendala: document.getElementById('jenis_kendala').value,
        terjadiDi: document.getElementById('terjadi_di').value,
        deskripsi: document.getElementById('deskripsi').value,
        file: document.getElementById('fileUpload').files[0]
    };
    
    // Validate form
    if (!formData.toko) {
        alert('Harap pilih toko terlebih dahulu');
        return;
    }
    
    if (!formData.jenisKendala) {
        alert('Harap pilih jenis kendala');
        return;
    }
    
    if (!formData.terjadiDi) {
        alert('Harap pilih siapa yang terdampak');
        return;
    }
    
    if (!formData.deskripsi.trim()) {
        alert('Harap isi deskripsi kendala');
        return;
    }
    
    if (formData.untukSiapa === 'orang_lain' && !formData.namaLain.trim()) {
        alert('Harap isi nama orang lain');
        return;
    }
    
    if (formData.tipeKendala === 'Hardware' && !formData.jenisHardware) {
        alert('Harap pilih jenis hardware');
        return;
    }
    
    // Show success message
    showNotification('Request berhasil diajukan! Tim IT akan segera menindaklanjuti.', 'success');
    
    // Close modal
    closeModal();
    
    // Simulate API call
    console.log('Form Data Submitted:', formData);
}

// ========== DASHBOARD FUNCTIONS ==========
function initDashboardModals() {
    const detailModal = document.getElementById('requestDetailModal');
    const closeModalBtn = document.querySelector('.close-btn');
    
    if (!detailModal || !closeModalBtn) return; // Jika tidak ada di halaman ini, skip
    
    // Detail buttons - Open Queue Panel
    document.querySelectorAll('.open-detail-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const queueItem = button.closest('.queue-item');
            const label = queueItem.querySelector('label').textContent;
            const parts = label.split(' ');
            const user = parts[0];
            const urgency = parts[1];
            
            if (detailModal) {
                document.getElementById('detail-no').textContent = 'Queue Item';
                document.getElementById('detail-peminta').textContent = user;
                document.getElementById('detail-sh').textContent = urgency;
                document.getElementById('detail-jenis').textContent = 'Lain-lain';
                document.getElementById('detail-status').textContent = 'Open';
                document.getElementById('detail-staff').textContent = 'Belum ditugaskan';
                
                detailModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Detail buttons - All Request Table
    document.querySelectorAll('.btn-detail').forEach((button, index) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            openRequestDetail(index + 1);
        });
    });
    
    // Transfer buttons
    document.querySelectorAll('.btn-transfer, .btn-transfer-modal').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const staffName = prompt('Masukkan nama Staff IT baru:');
            if (staffName) {
                alert(`Request akan ditransfer ke: ${staffName}`);
                if (button.classList.contains('btn-transfer-modal')) {
                    document.getElementById('detail-staff').textContent = staffName;
                }
            }
        });
    });
    
    // Status buttons
    document.querySelectorAll('.btn-status, .btn-status-modal').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const newStatus = prompt('Masukkan status baru:');
            if (newStatus) {
                alert(`Status diubah menjadi: ${newStatus}`);
                if (button.classList.contains('btn-status-modal')) {
                    document.getElementById('detail-status').textContent = newStatus;
                }
            }
        });
    });
    
    // Modal close functionality
    closeModalBtn.addEventListener('click', closeDashboardModal);
    
    window.addEventListener('click', (event) => {
        if (event.target === detailModal) {
            closeDashboardModal();
        }
    });
    
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && detailModal.style.display === 'flex') {
            closeDashboardModal();
        }
    });
}

function openRequestDetail(requestId) {
    const detailModal = document.getElementById('requestDetailModal');
    if (!detailModal) return;
    
    const requestRow = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!requestRow) return;
    
    const cells = requestRow.querySelectorAll('td');
    
    document.getElementById('detail-no').textContent = requestId;
    document.getElementById('detail-peminta').textContent = cells[1].textContent;
    document.getElementById('detail-sh').textContent = cells[2].textContent;
    document.getElementById('detail-jenis').textContent = cells[3].textContent;
    document.getElementById('detail-status').textContent = cells[4].textContent;
    document.getElementById('detail-staff').textContent = cells[5].textContent;
    
    detailModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDashboardModal() {
    const detailModal = document.getElementById('requestDetailModal');
    if (!detailModal) return;
    
    detailModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ========== ACTION DROPDOWN FUNCTIONS ==========
let currentDropdown = null;

function initActionDropdown() {
    const actionBtn = document.querySelector('.action-btn');
    if (!actionBtn) return; // Jika tidak ada di halaman ini, skip
    
    actionBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleActionDropdown();
    });
}

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
        const newUrgency = prompt('Masukkan level urgensi baru (High/Medium/Low):', 'Medium');
        if (newUrgency) {
            checkboxes.forEach(cb => {
                const row = cb.closest('.queue-item');
                const label = row.querySelector('label');
                const text = label.textContent;
                const parts = text.split(' ');
                
                parts[1] = newUrgency;
                label.textContent = parts.join(' ');
                
                const urgency = newUrgency.toLowerCase();
                row.style.borderLeftColor = urgency === 'high' ? 'var(--error)' : 
                                          urgency === 'medium' ? 'var(--first)' : 'var(--success)';
            });
            
            alert(`Level urgensi diubah menjadi: ${newUrgency}`);
        }
    }
}

// ========== OTHER FUNCTIONS ==========
function initSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (!selectAllCheckbox) return; // Jika tidak ada di halaman ini, skip
    
    selectAllCheckbox.addEventListener('change', (event) => {
        const checkboxes = document.querySelectorAll('.queue-item input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = event.target.checked;
        });
    });
}

function initClickableTableRows() {
    const tableRows = document.querySelectorAll('tr[data-request-id]');
    if (tableRows.length === 0) return; // Jika tidak ada di halaman ini, skip
    
    tableRows.forEach(row => {
        row.addEventListener('click', (event) => {
            if (event.target.tagName === 'BUTTON' || event.target.closest('button')) return;
            const requestId = row.getAttribute('data-request-id');
            openRequestDetail(requestId);
        });
        row.style.cursor = 'pointer';
    });
}

function lihatRiwayat() {
    const userData = {
        username: document.querySelector('.username')?.textContent || 'User',
    };
    
    localStorage.setItem('userData', JSON.stringify(userData));
    
    window.location.href = 'done_cancelled.php';
}

function openDetails(id) {
    alert(`Membuka detail untuk request ID: ${id}`);
}

function batalkanRequest(id) {
    if (confirm(`Apakah Anda yakin ingin membatalkan request ID: ${id}?`)) {
        alert(`Request ID: ${id} berhasil dibatalkan`);
        
        const row = document.querySelector(`tr:nth-child(${id + 1})`);
        if (row) {
            const statusCell = row.querySelector('.status-on-process');
            if (statusCell) {
                statusCell.className = 'status-canceled';
                statusCell.textContent = 'Canceled';
                const cancelBtn = row.querySelector('.btn-cancel');
                if (cancelBtn) cancelBtn.style.display = 'none';
            }
        }
    }
}

// ========== HELPER FUNCTIONS ==========
function showNotification(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.display = 'block';
    }, 10);
    
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

function initModalEvents() {
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const unratedModal = document.getElementById('unratedModal');
        const requestModal = document.getElementById('requestModal');
        
        if (unratedModal && unratedModal.classList.contains('active') && event.target === unratedModal) {
            closeUnratedModal();
        }
        
        if (requestModal && requestModal.classList.contains('active') && event.target === requestModal) {
            closeModal();
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const unratedModal = document.getElementById('unratedModal');
            const requestModal = document.getElementById('requestModal');
            
            if (unratedModal && unratedModal.classList.contains('active')) {
                closeUnratedModal();
            }
            
            if (requestModal && requestModal.classList.contains('active')) {
                closeModal();
            }
        }
    });
}

// ========== DEBUG FUNCTIONS ==========
function debugUnratedModal() {
    console.log('=== DEBUG UNRATED MODAL ===');
    console.log('Modal element:', document.getElementById('unratedModal'));
    console.log('Modal active:', document.getElementById('unratedModal')?.classList.contains('active'));
    console.log('Unrated requests:', unratedRequests);
    console.log('Unrated count badge:', document.getElementById('unratedCount'));
    console.log('Body modal-open:', document.body.classList.contains('modal-open'));
    
    // Force open modal for testing
    openUnratedModal();
}

// Test function untuk debug
function testSetRating() {
    console.log('Testing setRating function...');
    // Test rating untuk request pertama
    if (unratedRequests.length > 0) {
        const firstRequestId = unratedRequests[0].id;
        console.log('Testing rating for request ID:', firstRequestId);
        setRating(firstRequestId, 3);
    }
}

// Test langsung dari console
function debugRating() {
    console.log('=== RATING DEBUG ===');
    console.log('All unrated requests:', unratedRequests);
    
    // Cek apakah ada button submit
    const submitButtons = document.querySelectorAll('.rating-btn.submit');
    console.log('Submit buttons found:', submitButtons.length);
    
    submitButtons.forEach((btn, index) => {
        console.log(`Button ${index}:`, {
            id: btn.id,
            disabled: btn.disabled,
            innerHTML: btn.innerHTML
        });
    });
    
    // Test enable button
    if (unratedRequests.length > 0) {
        const firstId = unratedRequests[0].id;
        enableSubmitButton(firstId);
    }
}

// Tambah request baru untuk testing
function addTestRequest() {
    const newId = unratedRequests.length > 0 ? Math.max(...unratedRequests.map(r => r.id)) + 1 : 1;
    
    const newRequest = {
        id: newId,
        title: 'Software Installation',
        date: new Date().toISOString().split('T')[0],
        staff: 'Salman',
        deskripsi: 'Membutuhkan instalasi software accounting terbaru untuk laporan bulanan',
        hasilService: 'Menginstall software AccountingPro v3.2, melakukan konfigurasi database, mengatur hak akses user, dan memberikan training singkat penggunaan fitur baru.',
        userRating: 0
    };
    
    unratedRequests.push(newRequest);
    updateUnratedCount();
    
    // Jika modal terbuka, update list
    if (document.getElementById('unratedModal')?.classList.contains('active')) {
        renderUnratedList();
    }
    
    showNotification('Request testing ditambahkan!', 'info');
}

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
`;
document.head.appendChild(style);
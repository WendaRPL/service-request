// Sample data
const doneData = [
    {
        id: 1,
        peminta: "John Doe",
        jenis: "S",
        kendala: "SFA System Error",
        tglSelesai: "2025-10-15",
        handler: "Reza",
        rating: 5,
        detail: "Permintaan perbaikan sistem SFA yang error pada modul laporan. Telah diperbaiki dengan update patch terbaru."
    },
    {
        id: 2,
        peminta: "John doe",
        jenis: "H",
        kendala: "Printer Issues",
        tglSelesai: "2025-10-14",
        handler: "Aldy",
        rating: 4,
        detail: "Printer tidak bisa mencetak dokumen. Masalah pada koneksi jaringan printer, telah diperbaiki dengan konfigurasi ulang."
    },
    {
        id: 3,
        peminta: "John Doe",
        jenis: "S",
        kendala: "Software Installation",
        tglSelesai: "2025-10-13",
        handler: "Reza",
        rating: 5,
        detail: "Installasi software accounting baru. Proses berjalan lancar, user sudah bisa menggunakan dengan baik."
    }
];

const canceledData = [
    {
        id: 4,
        peminta: "John Doe",
        jenis: "S",
        kendala: "Email Configuration",
        tglDibatalkan: "2025-10-12",
        handler: "-",
        alasan: "Permintaan dibatalkan oleh user karena sudah dikerjakan sendiri",
        detail: "User membatalkan permintaan konfigurasi email karena sudah berhasil mengkonfigurasi sendiri."
    },
    {
        id: 5,
        peminta: "John Doe",
        jenis: "H",
        kendala: "Monitor Issues",
        tglDibatalkan: "2025-10-11",
        handler: "Salman",
        alasan: "Monitor ternyata hanya kabel yang longgar",
        detail: "Setelah dicek, masalah hanya pada kabel monitor yang longgar. User memasang kembali sendiri."
    }
];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadDoneData();
    
    // Set initial tab
    switchTab('done');
    
    // Add click event to nav home
    document.querySelector('.nav-home').addEventListener('click', goToDashboard);
});

// Tab switching function
function switchTab(tab) {
    const doneTab = document.getElementById('tab-done');
    const canceledTab = document.getElementById('tab-canceled');
    const doneTable = document.getElementById('done-table');
    const canceledTable = document.getElementById('canceled-table');
    const emptyDone = document.getElementById('empty-done');
    const emptyCanceled = document.getElementById('empty-canceled');
    
    // Update active tab
    if (tab === 'done') {
        doneTab.classList.add('active');
        canceledTab.classList.remove('active');
        doneTable.style.display = 'block';
        canceledTable.style.display = 'none';
        
        // Check if done data exists
        if (doneData.length === 0) {
            emptyDone.style.display = 'block';
            doneTable.style.display = 'none';
        } else {
            emptyDone.style.display = 'none';
        }
        emptyCanceled.style.display = 'none';
        
    } else {
        doneTab.classList.remove('active');
        canceledTab.classList.add('active');
        doneTable.style.display = 'none';
        canceledTable.style.display = 'block';
        
        // Check if canceled data exists
        if (canceledData.length === 0) {
            emptyCanceled.style.display = 'block';
            canceledTable.style.display = 'none';
        } else {
            emptyCanceled.style.display = 'none';
        }
        emptyDone.style.display = 'none';
    }
}

// Load done data
function loadDoneData() {
    const tbody = document.getElementById('done-tbody');
    tbody.innerHTML = '';
    
    doneData.forEach((item, index) => {
        const row = document.createElement('tr');
        const stars = getStarRating(item.rating);
        
        row.innerHTML = `
            <td>${index + 1}.</td>
            <td>${item.peminta}</td>
            <td>${item.jenis}</td>
            <td>${item.kendala}</td>
            <td><span class="status-done">${formatDate(item.tglSelesai)}</span></td>
            <td>${item.handler}</td>
            <td>
                <button class="detail-button" onclick="openDetailModal('done', ${item.id})">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </td>
            <td class="rating">${stars}</td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Load canceled data
    loadCanceledData();
}

// Load canceled data
function loadCanceledData() {
    const tbody = document.getElementById('canceled-tbody');
    tbody.innerHTML = '';
    
    canceledData.forEach((item, index) => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${index + 1}.</td>
            <td>${item.peminta}</td>
            <td>${item.jenis}</td>
            <td>${item.kendala}</td>
            <td><span class="status-canceled">${formatDate(item.tglDibatalkan)}</span></td>
            <td>${item.handler}</td>
            <td>
                <button class="detail-button" onclick="openDetailModal('canceled', ${item.id})">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </td>
            <td>${item.alasan}</td>
        `;
        
        tbody.appendChild(row);
    });
}

// Get star rating HTML
function getStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star filled"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// Open detail modal
function openDetailModal(type, id) {
    let data;
    if (type === 'done') {
        data = doneData.find(item => item.id === id);
    } else {
        data = canceledData.find(item => item.id === id);
    }
    
    if (!data) return;
    
    const modalBody = document.getElementById('modal-body');
    
    if (type === 'done') {
        modalBody.innerHTML = `
            <div class="detail-section">
                <h4>Informasi Request</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>ID Request:</strong>
                        <span>#${data.id}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Peminta:</strong>
                        <span>${data.peminta}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Jenis Kendala:</strong>
                        <span>${data.jenis === 'S' ? 'Software' : 'Hardware'}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Deskripsi Kendala:</strong>
                        <span>${data.kendala}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Tanggal Selesai:</strong>
                        <span>${formatDate(data.tglSelesai)}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Handler:</strong>
                        <span>${data.handler}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Rating:</strong>
                        <span class="rating">${getStarRating(data.rating)}</span>
                    </div>
                </div>
            </div>
            <div class="detail-section">
                <h4>Detail Penyelesaian</h4>
                <div class="detail-content">
                    ${data.detail}
                </div>
            </div>
        `;
    } else {
        modalBody.innerHTML = `
            <div class="detail-section">
                <h4>Informasi Request</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>ID Request:</strong>
                        <span>#${data.id}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Peminta:</strong>
                        <span>${data.peminta}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Jenis Kendala:</strong>
                        <span>${data.jenis === 'S' ? 'Software' : 'Hardware'}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Deskripsi Kendala:</strong>
                        <span>${data.kendala}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Tanggal Dibatalkan:</strong>
                        <span>${formatDate(data.tglDibatalkan)}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Handler:</strong>
                        <span>${data.handler}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Alasan Pembatalan:</strong>
                        <span>${data.alasan}</span>
                    </div>
                </div>
            </div>
            <div class="detail-section">
                <h4>Detail Pembatalan</h4>
                <div class="detail-content">
                    ${data.detail}
                </div>
            </div>
        `;
    }
    
    // Show modal
    const modal = document.getElementById('detailModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
}

// Close detail modal
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.remove('active');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Go back to dashboard
function goToDashboard() {
    window.location.href = 'home.php';
}

// Tambahkan CSS untuk detail content di dalam file CSS
const detailCSS = `
.detail-section {
    margin-bottom: 25px;
}

.detail-section h4 {
    color: var(--dark-gray);
    font-size: 1.1rem;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--third);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item strong {
    color: var(--medium-gray);
    font-size: 0.9rem;
    font-weight: 500;
}

.detail-item span {
    color: var(--dark-gray);
    font-size: 1rem;
}

.detail-content {
    background: var(--fourth);
    padding: 20px;
    border-radius: var(--radius-sm);
    border-left: 4px solid var(--first);
    color: var(--dark-gray);
    line-height: 1.6;
}
`;

// Add the CSS to the page
const style = document.createElement('style');
style.textContent = detailCSS;
document.head.appendChild(style);

// Close modal when clicking outside
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
    }
});
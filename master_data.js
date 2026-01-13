// =========================
// MASTER DATA FUNCTIONALITY
// =========================

let currentTab = 'toko';
let currentDataTables = {};

document.addEventListener('DOMContentLoaded', function() {
    initTabs();             
    loadTabContent('toko');  
    initAddButton();
    initModals();
});

// Data dummy untuk setiap tab
const dummyData = {
    toko: [
        { id: 1, tanggal: '2025-12-15', dibuat_oleh: 'User', nama: 'Hikomi', kode: 'TPJ001' },
        { id: 2, tanggal: '2025-12-14', dibuat_oleh: 'Staff IT', nama: 'Toyomatsu', kode: 'TCS002' },
        { id: 3, tanggal: '2025-12-14', dibuat_oleh: 'User', nama: 'Robin Jaya', kode: 'TCU003' },
    ],
    karyawan: [
        { id: 7, tanggal: '2025-12-10', dibuat_oleh: 'Staff IT', nama: 'Dina Wirawan', toko: 'Hikomi' },
        { id: 9, tanggal: '2025-12-08', dibuat_oleh: 'Staff IT', nama: 'Mita Yulius', toko: 'Toyomatsu' },
        { id: 10, tanggal: '2025-12-07', dibuat_oleh: 'Staff IT', nama: 'John Tobius', toko: 'Robin Jaya' }
    ],
    jenis_kendala: [
        { id: 1, tanggal: '2025-12-15', dibuat_oleh: 'Admin', tipe: 'Hardware', jenis: 'Printer Error', turunan: 'Ya' },
        { id: 2, tanggal: '2025-12-14', dibuat_oleh: 'Staff IT', tipe: 'Software', jenis: 'SFA Error', turunan: 'Tidak' },
        { id: 3, tanggal: '2025-12-14', dibuat_oleh: 'Admin', tipe: 'Network', jenis: 'Internet Down', turunan: 'Tidak' }
    ],
    role: [
        { id: 1, tanggal: '2025-12-15', dibuat_oleh: 'Admin', nama: 'Admin' },
        { id: 2, tanggal: '2025-12-14', dibuat_oleh: 'Staff IT', nama: 'Staff IT' },
        { id: 3, tanggal: '2025-12-14', dibuat_oleh: 'Admin', nama: 'User' },
    ]
};

// Initialize tabs
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.textContent.toLowerCase().replace(' ', '_');
            currentTab = tabName;
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update add button text
            updateAddButtonText();
            
            // Load tab content
            loadTabContent(tabName);
        });
    });
}

// Update add button text
function updateAddButtonText() {
    const addButton = document.querySelector('.add-button');
    if (!addButton) return;
    
    const buttonTextMap = {
        'toko': 'Add Toko',
        'karyawan': 'Add Karyawan',
        'jenis_kendala': 'Add Jenis Kendala',
        'role': 'Add Role'
    };
    
    addButton.innerHTML = `+ ${buttonTextMap[currentTab] || 'Add Data'}`;
}

// Load tab content
function loadTabContent(tabName) {
    const contentContainer = document.querySelector('.tab-content.active');
    if (!contentContainer) return;
    
    // Get table container
    const tableContainer = contentContainer.querySelector('.table-container');
    if (!tableContainer) return;
    
    // Destroy existing DataTable if exists
    Object.values(currentDataTables).forEach(dt => {
        if (dt) dt.destroy();
    });
    currentDataTables = {};

    
    // Clear table container
    tableContainer.innerHTML = '';
    
    // Create new table
    const table = document.createElement('table');
    table.className = 'data-table';
    table.id = `table-${tabName}`;
    
    // Generate table HTML
    const data = dummyData[tabName] || [];
    
    // Create table header
    let thead = '<thead><tr>';
    if (tabName === 'toko') {
        thead += '<th>No.</th><th>Tanggal dibuat</th><th>Dibuat oleh</th><th>Nama Toko</th><th>Kode Toko</th><th>Aksi</th>';
    } else if (tabName === 'karyawan') {
        thead += '<th>No.</th><th>Tanggal dibuat</th><th>Dibuat oleh</th><th>Nama Karyawan</th><th>Nama Toko</th><th>Aksi</th>';
    } else if (tabName === 'jenis_kendala') {
        thead += '<th>No.</th><th>Tanggal dibuat</th><th>Dibuat oleh</th><th>Tipe Kendala</th><th>Jenis Kendala</th><th>Pertanyaan Turunan</th><th>Aksi</th>';
    } else if (tabName === 'role') {
        thead += '<th>No.</th><th>Tanggal dibuat</th><th>Dibuat oleh</th><th>Nama Role</th><th>Aksi</th>';
    }
    thead += '</tr></thead>';
    
    // Create table body
    let tbody = '<tbody>';
    data.forEach((item, index) => {
        tbody += '<tr>';
        tbody += `<td>${index + 1}.</td>`;
        tbody += `<td>${item.tanggal}</td>`;
        tbody += `<td>${item.dibuat_oleh}</td>`;
        
        if (tabName === 'toko') {
            tbody += `<td>${item.nama}</td>`;
            tbody += `<td>${item.kode}</td>`;
        } else if (tabName === 'karyawan') {
            tbody += `<td>${item.nama}</td>`;
            tbody += `<td>${item.toko}</td>`;
        } else if (tabName === 'jenis_kendala') {
            tbody += `<td>${item.tipe}</td>`;
            tbody += `<td>${item.jenis}</td>`;
            tbody += `<td><span class="yes-no-badge ${item.turunan === 'Ya' ? 'badge-yes' : 'badge-no'}">${item.turunan}</span></td>`;
        } else if (tabName === 'role') {
            tbody += `<td>${item.nama}</td>`;
        }
        
        tbody += `<td>
            <button class="action-edit" data-id="${item.id}" onclick="openEditModal(${item.id})">
                <span class="icon">✎</span> Edit
            </button>
            <button class="action-delete" data-id="${item.id}" onclick="confirmDelete(${item.id})">
                <span class="icon">🗑</span> Hapus
            </button>
        </td>`;
        
        tbody += '</tr>';
    });
    tbody += '</tbody>';
    
    table.innerHTML = thead + tbody;
    tableContainer.appendChild(table);
    
    // Initialize DataTable
    setTimeout(() => {
        const dt = $(table).DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            searching: true,
            info: true,

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
            },

            columnDefs: [
                {
                    orderable: false,
                    targets: [0, -1] // No & Aksi
                },
            ]
        });

        currentDataTables[tabName] = dt;
    }, 100);
} 

// Open Add Modal
function openAddModal() {
    const modal = document.getElementById('addMasterModal');
    if (!modal) return;

    modal.classList.add('show');

    // Hide semua form
    document.querySelectorAll('.master-form').forEach(form => {
        form.style.display = 'none';
    });

    const titleMap = {
        toko: 'Tambah Toko',
        karyawan: 'Tambah Karyawan',
        jenis_kendala: 'Tambah Jenis Kendala',
        role: 'Tambah Role'
    };

    document.getElementById('addMasterTitle').innerText =
        titleMap[currentTab] || 'Tambah Data';

    // Show form sesuai tab aktif
    if (currentTab === 'toko') {
        document.getElementById('formToko').style.display = 'block';
    }
    if (currentTab === 'karyawan') {
        document.getElementById('formKaryawan').style.display = 'block';
    }
    if (currentTab === 'jenis_kendala') {
        document.getElementById('formJenisKendala').style.display = 'block';
    }
    if (currentTab === 'role') {
        document.getElementById('formRole').style.display = 'block';
    }
}
function initAddButton() {
    const addButton = document.querySelector('.add-button');
    if (!addButton) return;
    addButton.addEventListener('click', openAddModal);
}

/* =========================
   EDIT MODAL HANDLER
========================= */
function openEditModal(id) {
    const modal = document.getElementById('editModal');
    if (!modal) return;

    const data = dummyData[currentTab]?.find(item => item.id == id);
    if (!data) return;

    modal.classList.add('show');

    // hide semua form
    document.querySelectorAll('#editModal .master-form').forEach(f => {
        f.style.display = 'none';
    });

    const titleMap = {
        toko: 'Edit Toko',
        karyawan: 'Edit Karyawan',
        jenis_kendala: 'Edit Jenis Kendala',
        role: 'Edit Role'
    };

    document.getElementById('editMasterTitle').innerText =
        titleMap[currentTab] || 'Edit Data';

    // SHOW & FILL FORM
    if (currentTab === 'toko') {
        document.getElementById('editFormToko').style.display = 'block';
        document.querySelector('#editFormToko [name="nama"]').value = data.nama;
        document.querySelector('#editFormToko [name="kode"]').value = data.kode;
    }

    if (currentTab === 'karyawan') {
        document.getElementById('editFormKaryawan').style.display = 'block';
        document.querySelector('#editFormKaryawan [name="nama"]').value = data.nama;
        document.querySelector('#editFormKaryawan [name="toko"]').value = data.toko;
    }

    if (currentTab === 'jenis_kendala') {
        document.getElementById('editFormJenisKendala').style.display = 'block';
        document.querySelector('#editFormJenisKendala [name="tipe"]').value = data.tipe;
        document.querySelector('#editFormJenisKendala [name="jenis"]').value = data.jenis;
    }

    if (currentTab === 'role') {
        document.getElementById('editFormRole').style.display = 'block';
        document.querySelector('#editFormRole [name="nama"]').value = data.nama;
    }

    // simpan id yang diedit
    modal.dataset.editId = id;
}

// Confirm delete
function confirmDelete(id) {
    const data = dummyData[currentTab]?.find(item => item.id == id);
    if (!data) return;
    
    const itemName = data.nama || data.jenis || data.kode || '';
    
    const titleMap = {
        'toko': 'Toko',
        'karyawan': 'Karyawan',
        'jenis_kendala': 'Jenis Kendala',
        'role': 'Role'
    };
    
    const itemTitle = titleMap[currentTab] || 'Item';
    
    if (confirm(`Apakah Anda yakin ingin menghapus ${itemTitle} "${itemName}"?`)) {
        alert(`${itemTitle} "${itemName}" berhasil dihapus`);
        // Di sini implementasi AJAX untuk hapus data
        // Untuk demo, reload tab content
        loadTabContent(currentTab);
    }
}

function initModals() {

    // Close via tombol X & Batal
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal');
            if (modal) modal.classList.remove('show');
        });
    });

    // Close via klik background
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Close via ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(m => {
                m.classList.remove('show');
            });
        }
    });
}

// Update data
function updateData() {
    alert(`Data ${currentTab} berhasil diperbarui`);
    closeModal('editModal');
    // Untuk demo, reload tab content
    loadTabContent(currentTab);
}

// Fungsi Munculkan Input Teks saat Checkbox di-klik
function toggleKeyInput(checkbox) {
    // Cari input di dalam key-item yang sama
    const keyItem = checkbox.closest('.key-item');
    const input = keyItem.querySelector('.key-value-input');
    
    if (checkbox.checked) {
        input.style.display = 'block';
        setTimeout(() => input.focus(), 10); // Delay dikit biar smooth
    } else {
        input.style.display = 'none';
        input.value = ''; // Reset value kalau batal pilih
    }
}

// Fungsi Buka Tutup Dropdown
function toggleDropdown(header) {
    const list = header.nextElementSibling;
    list.classList.toggle('show');
    
    // Opsional: putar icon panah jika ada
    const icon = header.querySelector('.arrow-icon');
    if(icon) icon.style.transform = list.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)';
}
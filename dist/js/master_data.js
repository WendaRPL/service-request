/**
 * MASTER DATA FUNCTIONALITY - EDIT MODAL SYNC VERSION
 */

let currentTab = 'toko';
let dataTableInstance = null;
let currentEditId = null;

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    loadTabContent(currentTab);
    initModals();
});

/* =========================
   TAB HANDLER
========================= */
function initTabs() {
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab-button')
                .forEach(t => t.classList.remove('active'));

            tab.classList.add('active');
            currentTab = tab.dataset.tab;
            loadTabContent(currentTab);
        });
    });
}

/* =========================
   LOAD DATA VIA AJAX
========================= */
function loadTabContent(tab) {
    const container = document.querySelector('.table-container');
    if (!container) return;

    container.innerHTML = `<p style="padding:20px;">Loading data...</p>`;

    $.ajax({
        url: 'direct/get_master_data.php',
        method: 'GET',
        data: { tab },
        dataType: 'json',
        success: res => {
            console.log('DATA:', res);
            renderTable(tab, Array.isArray(res) ? res : []);
        },
        error: xhr => {
            console.error(xhr.responseText);
            container.innerHTML = `<p style="color:red;">Gagal load data</p>`;
        }
    });
}

/* =========================
   RENDER DATATABLE
========================= */
function renderTable(tab, data) {
    const container = document.querySelector('.table-container');
    container.innerHTML = '';

    const tableId = `table-${tab}`;
    const table = document.createElement('table');

    table.id = tableId;
    table.className = 'display nowrap';
    table.style.width = '100%';
    container.appendChild(table);

    if (dataTableInstance) {
        dataTableInstance.destroy();
        dataTableInstance = null;
    }

    let columns = [
        {
            title: 'No',
            data: null,
            render: (d, t, r, m) => m.row + 1,
            width: '5%'
        },
        { title: 'Tanggal', data: 'tanggal' },
        { title: 'Dibuat Oleh', data: 'dibuat_oleh' }
    ];

    if (tab === 'toko') {
        columns.push(
            { title: 'Nama Toko', data: 'nama' },
            { title: 'Kode', data: 'kode' }
        );
    }

    if (tab === 'karyawan') {
        columns.push(
            { title: 'Nama Karyawan', data: 'nama' },
            { title: 'Toko', data: 'toko' }
        );
    }

    if (tab === 'jenis_kendala') {
        columns.push(
            { title: 'Tipe', data: 'tipe' },
            { title: 'Jenis', data: 'jenis' },
            {
                title: 'Turunan',
                data: 'turunan',
                render: v =>
                    `<span class="yes-no-badge ${v === 'Ya' ? 'badge-yes' : 'badge-no'}">
                        ${v ?? '-'}
                    </span>`
            }
        );
    }

    if (tab === 'role') {
        columns.push({ title: 'Nama Role', data: 'nama' });
    }

    columns.push({
        title: 'Aksi',
        data: 'id',
        orderable: false,
        render: id => `
            <button class="action-edit" onclick="openEditModal('${tab}', ${id})">
                Edit
            </button>
            <button class="action-delete" onclick="confirmDelete('${tab}', ${id})">
                Hapus
            </button>
        `
    });

    dataTableInstance = $(`#${tableId}`).DataTable({
        data,
        columns,
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Cari...',
            zeroRecords: 'Belum ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: {
                previous: '‹',
                next: '›'
            }
        },
        columnDefs: [
            { orderable: false, targets: [0, -1] }
        ]
    });
}

/* =========================
   EDIT MODAL HANDLER
========================= */
function openEditModal(tab, id) {
    currentEditId = id;

    const modal = document.getElementById('editModal');
    const title = document.getElementById('editMasterTitle');

    if (!modal) return;

    // Hide semua form
    document.querySelectorAll('#editModal .master-form')
        .forEach(f => f.style.display = 'none');

    // Ganti title + show form sesuai tab
    if (tab === 'toko') {
        title.innerText = 'Edit Toko';
        document.getElementById('editFormToko').style.display = 'block';
    }

    if (tab === 'karyawan') {
        title.innerText = 'Edit Karyawan';
        document.getElementById('editFormKaryawan').style.display = 'block';
    }

    if (tab === 'jenis_kendala') {
        title.innerText = 'Edit Jenis Kendala';
        document.getElementById('editFormJenisKendala').style.display = 'block';
    }

    if (tab === 'role') {
        title.innerText = 'Edit Role';
        document.getElementById('editFormRole').style.display = 'block';
    }

    modal.classList.add('show');

    console.log('OPEN EDIT MODAL:', tab, id);
}

/* =========================
   DELETE
========================= */
function confirmDelete(tab, id) {
    if (!confirm('Yakin mau hapus data ini?')) return;

    $.post('direct/delete_master.php', { tab, id }, () => {
        loadTabContent(tab);
    });
}

/* =========================
   MODAL INIT
========================= */
function initModals() {
    document.querySelectorAll('.close-btn, .btn-cancel')
        .forEach(btn => {
            btn.onclick = () =>
                btn.closest('.modal')?.classList.remove('show');
        });
}

/* =========================
   ADD MODAL HANDLER
========================= */

// tombol + Add Data
document.addEventListener('DOMContentLoaded', () => {
    const btnAdd = document.getElementById('btnAddMaster');
    if (btnAdd) {
        btnAdd.addEventListener('click', openAddModal);
    }
});

function openAddModal() {
    const modal = document.getElementById('addMasterModal');
    if (!modal) return;

    // buka modal
    modal.classList.add('show');

    // hide semua form dulu
    modal.querySelectorAll('.master-form').forEach(f => {
        f.style.display = 'none';
    });

    // set title
    const titleMap = {
        toko: 'Tambah Toko',
        karyawan: 'Tambah Karyawan',
        jenis_kendala: 'Tambah Jenis Kendala',
        role: 'Tambah Role'
    };

    const titleEl = document.getElementById('addMasterTitle');
    if (titleEl) {
        titleEl.innerText = titleMap[currentTab] || 'Tambah Data';
    }

    // tampilkan form sesuai tab aktif
    if (currentTab === 'toko') {
        document.getElementById('formToko')?.style.setProperty('display', 'block');
    }

    if (currentTab === 'karyawan') {
        document.getElementById('formKaryawan')?.style.setProperty('display', 'block');
    }

    if (currentTab === 'jenis_kendala') {
        document.getElementById('formJenisKendala')?.style.setProperty('display', 'block');
    }

    if (currentTab === 'role') {
        document.getElementById('formRole')?.style.setProperty('display', 'block');
    }
}

/* =========================
   CLOSE MODAL (ADD)
========================= */
document.querySelectorAll(
    '#addMasterModal .close-btn, #addMasterModal .btn-cancel'
).forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.modal')?.classList.remove('show');
    });
});

/* =========================
   SAVE ADD DATA (AJAX)
========================= */

document.querySelector('#addMasterModal .btn-submit')
    ?.addEventListener('click', saveAddData);

function saveAddData() {
    let payload = { tab: currentTab };

    if (currentTab === 'toko') {
        payload.nama = document.querySelector('#formToko input:nth-child(1)')?.value.trim();
        payload.kode = document.querySelector('#formToko input:nth-child(2)')?.value.trim();
    }

    if (currentTab === 'karyawan') {
        payload.nama = document.querySelector('#formKaryawan input')?.value.trim();
        payload.toko = document.querySelector('#formKaryawan select')?.value;
    }

    if (currentTab === 'jenis_kendala') {
        payload.tipe = document.querySelector('#formJenisKendala select')?.value;
        payload.jenis = document.querySelector('#formJenisKendala input')?.value.trim();
        payload.turunan =
            document.querySelector('#formJenisKendala input[name="turunan"]:checked')
                ?.value ?? 'Tidak';
    }

    if (currentTab === 'role') {
        payload.nama = document.querySelector('#formRole input')?.value.trim();
    }

    console.log('PAYLOAD ADD:', payload);

    $.ajax({
        url: 'direct/add_master_data.php',
        method: 'POST',
        data: payload,
        success: function (res) {
            alert('Data berhasil ditambahkan!');
            document.getElementById('addMasterModal')?.classList.remove('show');
            loadTabContent(currentTab);
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Gagal menambahkan data');
        }
    });
}

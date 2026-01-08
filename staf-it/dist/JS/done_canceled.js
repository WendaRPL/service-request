// =========================
// GLOBAL FILTER STATE (WAJIB)
// =========================
const filterState = {
    toko: '',
    peminta: '',
    handler: '',
    start: '',
    end: ''
};

// =========================
// GLOBAL DATATABLE FILTER (JANGAN DIHAPUS)
// =========================
$.fn.dataTable.ext.search.push(function (settings, data) {
    const colToko    = data[1]?.trim();
    const colPeminta = data[3]?.trim();
    const colHandler = data[7]?.trim();
    const colTanggal = data[8];

    if (filterState.toko && colToko !== filterState.toko) return false;
    if (filterState.peminta && colPeminta !== filterState.peminta) return false;
    if (filterState.handler && colHandler !== filterState.handler) return false;

    if (filterState.start || filterState.end) {
        const rowDate = new Date(colTanggal);
        if (filterState.start && rowDate < new Date(filterState.start)) return false;
        if (filterState.end && rowDate > new Date(filterState.end)) return false;
    }

    return true;
});

// =========================
// DOM READY
// =========================
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    initFilterToggle();
    initFilters();
    initModals();
    initButtonActions();
    setDefaultDates();
});

// =========================
// DATATABLE INIT
// =========================
function initializeDataTable() {
    const table = $('#doneCanceledTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        info: true,
        order: [[8, 'desc']],

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
                targets: [9]
            },
            {
                className: 'dt-center',
                targets: [0, 3, 5, 8]
            },
            {
                responsivePriority: 1,
                targets: [1, 2, 6]
            },
            {
                targets: 0,
                type: 'num'
            }
        ]
    });

    window.doneCanceledTable = table;
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
// FILTER HANDLER
// =========================
function initFilters() {
    document.getElementById('btnApplyFilter')
        ?.addEventListener('click', applyFilters);

    document.getElementById('btnResetFilter')
        ?.addEventListener('click', resetFilters);
}

function applyFilters() {
    const table = window.doneCanceledTable;
    if (!table) return;

    filterState.toko    = document.getElementById('filter-toko')?.value || '';
    filterState.peminta = document.getElementById('filter-peminta')?.value || '';
    filterState.handler = document.getElementById('filter-handler')?.value || '';
    filterState.start   = document.getElementById('filter-start-date')?.value || '';
    filterState.end     = document.getElementById('filter-end-date')?.value || '';

    table.draw();
}

function resetFilters() {
    document.querySelectorAll('#filterPanel select, #filterPanel input')
        .forEach(el => el.value = '');

    filterState.toko = '';
    filterState.peminta = '';
    filterState.handler = '';
    filterState.start = '';
    filterState.end = '';

    const table = window.doneCanceledTable;
    table.search('');
    table.columns().search('');
    table.draw();
}

// =========================
// MODAL HANDLER
// =========================
function initModals() {
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(m => {
                m.classList.remove('show');
            });
            document.body.style.overflow = '';
        }
    });

    document.querySelectorAll('.close-btn, .btn-cancel')
        .forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                modal?.classList.remove('show');
            });
        });
}

// =========================
// BUTTON ACTIONS
// =========================
function initButtonActions() {
    document.addEventListener('click', function (e) {

        const detailBtn = e.target.closest('.btn-detail');
        const reportBtn = e.target.closest('.btn-report');

        if (detailBtn) {
            const table = window.doneCanceledTable;
            const tr = detailBtn.closest('tr');

            const row = tr.classList.contains('child')
                ? table.row(tr.previousElementSibling)
                : table.row(tr);

            const data = row.data();
            if (!data) return;

            // ambil no request
            const noRequest = row.index() + 1 + table.page.info().start;

            // =========================
            // ISI MODAL DETAIL  
            // =========================
            document.getElementById('d-no').textContent = noRequest;

            document.getElementById('d-toko').value    = data[1];
            document.getElementById('d-user').value    = data[2];
            document.getElementById('d-peminta').value = data[3];

            document.getElementById('d-kendala').value = data[5];
            document.getElementById('d-status').value  = data[6];  
            document.getElementById('d-handler').value = data[7];
            document.getElementById('d-tanggal').value = data[8];

            document.getElementById('detail-deskripsi').textContent = '';

            // === AMBIL CELL URGENCY DARI ROW  ===
            const urgencyCell = tr.classList.contains('child')
                ? tr.previousElementSibling.querySelector('td.urgency-cell')
                : tr.querySelector('td.urgency-cell');

            // === AMBIL KODE S/H ===
            const shCode = urgencyCell
                ?.querySelector('.urgency-code')
                ?.textContent.trim() || '';

            // === AMBIL BADGE LOW / HIGH ===
            const shBadge = urgencyCell
                ?.querySelector('.urgency-badge')
                ?.textContent.trim() || '';

            // === ISI KE MODAL (SIMPLE & BERSIH) ===
            document.getElementById('d-urgensi').value =
                shBadge ? `${shCode} - ${shBadge}` : shCode;

            // =========================
            // OPEN MODAL
            // =========================
            document.getElementById('detailModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // =========================
        // REPORT MODAL (UPDATED)
        // =========================
        if (reportBtn) {
            const table = window.doneCanceledTable;
            if (!table) return;

            const tr = reportBtn.closest('tr');

            let row = table.row(tr);
            if (!row.data()) {
                row = table.row(tr.previousElementSibling);
            }

            const data = row.data();
            if (!data) return;

            // =========================
            // NO REQUEST
            // =========================
            document.getElementById('r-no').textContent =
                row.index() + 1 + table.page.info().start;

            // =========================
            // DATA LAMA (READ ONLY)
            // =========================
            document.getElementById('r-toko-old').value    = data[1];
            document.getElementById('r-user-old').value    = data[2];
            document.getElementById('r-peminta-old').value = data[3];
            document.getElementById('r-kendala-old').value = data[5];

            // =========================
            // AMBIL URGENCY (SAMA KAYA DETAIL)
            // =========================
            const urgencyCell = tr.classList.contains('child')
                ? tr.previousElementSibling.querySelector('td.urgency-cell')
                : tr.querySelector('td.urgency-cell');

            const shCode = urgencyCell
                ?.querySelector('.urgency-code')
                ?.textContent.trim() || '';

            const shBadge = urgencyCell
                ?.querySelector('.urgency-badge')
                ?.textContent.trim() || '';

            document.getElementById('r-sh-old').value =
                shBadge ? `${shCode} - ${shBadge}` : shCode;

            // =========================
            // RESET TEXTAREA KESALAHAN
            // =========================
            const wrongInputEl = document.getElementById('r-user-wrong-input');
            if (wrongInputEl) {
                wrongInputEl.value = '';
            }

            // =========================
            // OPEN MODAL
            // =========================
            document.getElementById('reportModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

    });
}

function getRowDataFromButton(btn) {
    const table = window.doneCanceledTable;
    const tr = btn.closest('tr');

    if (tr.classList.contains('child')) {
        return table.row(tr.previousElementSibling).data();
    }

    return table.row(tr).data();
}

// =========================
// SUBMIT REPORT
// =========================
document.getElementById('btn-submit-report')?.addEventListener('click', () => {
    const kesalahan = document.getElementById('r-kesalahan').value.trim();
    if (!kesalahan) {
        alert('Kesalahan input user wajib diisi!');
        return;
    }

    console.log('DATA LAPORAN:', {
        toko: r_toko.value,
        user: r_user.value,
        action: r_action.value,
        result: r_result.value,
        kesalahan_input: kesalahan
    });

    alert('Laporan berhasil disimpan');
    document.getElementById('reportModal').classList.remove('show');
});

// =========================
// DEFAULT DATE
// =========================
function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today);
    lastWeek.setDate(today.getDate() - 7);

    document.getElementById('filter-start-date').valueAsDate = lastWeek;
    document.getElementById('filter-end-date').valueAsDate = today;
}

// =========================
// RESPONSIVE FIX
// =========================
window.addEventListener('resize', function() {
    if (window.doneCanceledTable) {
        setTimeout(() => {
            window.doneCanceledTable.columns.adjust().responsive.recalc();
        }, 200);
    }
});
 
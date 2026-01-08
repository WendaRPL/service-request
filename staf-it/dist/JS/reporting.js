// =========================
// REPORTING (FIXED FULL)
// =========================

document.addEventListener('DOMContentLoaded', function () {
    initializeReportingTable();
    initReportFilterToggle();
    initReportFilters();
    initReportButtonActions();
    setDefaultReportDates();
});

// =========================
// DATATABLE INIT
// =========================
function initializeReportingTable() {
    // INIT DATATABLE
    const table = $('#reportingTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,

        order: [[9, 'desc']], // kolom Tanggal

        columnDefs: [
            { orderable: false, targets: [0, 10] }, // checkbox & aksi
            { className: 'dt-center', targets: [0, 1, 5, 9] }
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

    window.reportingTable = table;

    initCheckboxHandlers(table);
}

// =========================
// EXPORT BUTTON CONFIG
// =========================
function getExportButtons() {
    return [
        exportBtn('copy'),
        exportBtn('excelHtml5', 'Laporan Service Request'),
        exportBtn('csvHtml5', 'Laporan Service Request'),
        exportBtn('pdfHtml5', 'Laporan Service Request', true),
        exportBtn('print', 'Laporan Service Request')
    ];
}

function exportBtn(type, title = '', landscape = false) {
    return {
        extend: type,
        className: 'd-none',
        title,
        orientation: landscape ? 'landscape' : 'portrait',
        exportOptions: {
            columns: ':not(:first-child):not(:last-child)',
            rows: getSelectedRows
        }
    };
}

// =========================
// ROW SELECTION LOGIC
// =========================
function getSelectedRows(idx, data, node) {
    const checked = $('input.row-check:checked', node).length > 0;
    const anyChecked = $('.row-check:checked').length > 0;

    // kalau ADA yg dicentang → export yg dicentang
    if (anyChecked) return checked;

    // kalau TIDAK ADA → export semua
    return true;
}

// =========================
// CHECKBOX HANDLER
// =========================
function initCheckboxHandlers(table) {

    // Select All
    $('#checkAll').on('change', function () {
        const checked = this.checked;
        $('.row-check', table.rows({ search: 'applied' }).nodes())
            .prop('checked', checked);
    });

    // Single checkbox
    $('#reportingTable tbody').on('change', '.row-check', function () {
        const total = $('.row-check').length;
        const checked = $('.row-check:checked').length;

        $('#checkAll').prop('checked', total === checked);
    });

    // Reset checkbox saat redraw
    table.on('draw', function () {
        $('#checkAll').prop('checked', false);
    });
}

// =========================
// EXPORT HANDLER
// =========================
function copyTable() {
    window.reportingTable?.button(0).trigger();
}

function exportTable(type) {
    const map = {
        excel: 1,
        csv: 2,
        pdf: 3
    };
    window.reportingTable?.button(map[type]).trigger();
}

function printSemua() {
    window.reportingTable?.button(4).trigger();
}

// =========================
// FILTER TOGGLE
// =========================
function initReportFilterToggle() {
    const btn = document.getElementById('btnToggleReportFilter');
    const panel = document.getElementById('reportFilterPanel');
    if (!btn || !panel) return;

    btn.addEventListener('click', function () {
        panel.classList.toggle('show');
        this.innerHTML = panel.classList.contains('show')
            ? 'Filter ▲'
            : 'Filter ▼';
    });
}

// =========================
// FILTER HANDLER
// =========================
function initReportFilters() {
    document
        .querySelectorAll('#reportFilterPanel select, #reportFilterPanel input')
        .forEach(el => el.addEventListener('change', applyReportFilters));

    document.getElementById('btnApplyReportFilter')
        ?.addEventListener('click', applyReportFilters);

    document.getElementById('btnResetReportFilter')
        ?.addEventListener('click', resetReportFilters);
}

// =========================
// APPLY FILTER
// =========================
function applyReportFilters() {
    const table = window.reportingTable;
    if (!table) return;

    const toko = $('#report-filter-toko').val();
    const user = $('#report-filter-user').val();
    const status = $('#report-filter-status').val();
    const startDate = $('#report-filter-start-date').val();
    const endDate = $('#report-filter-end-date').val();

    table.columns().search('');

    if (toko) table.column(2).search(toko);
    if (user) table.column(3).search(user);
    if (status) table.column(6).search(status);

    $.fn.dataTable.ext.search.push(function (settings, data) {
        const rowDate = new Date(data[8]);
        if (startDate && rowDate < new Date(startDate)) return false;
        if (endDate && rowDate > new Date(endDate)) return false;
        return true;
    });

    table.draw();
    $.fn.dataTable.ext.search.pop();
}

// =========================
// RESET FILTER
// =========================
function resetReportFilters() {
    $('#reportFilterPanel select, #reportFilterPanel input').val('');
    setDefaultReportDates();
    applyReportFilters();
}

// =========================
// RESPONSIVE FIX
// =========================
window.addEventListener('resize', function () {
    if (window.reportingTable) {
        setTimeout(() => {
            window.reportingTable.columns.adjust().responsive.recalc();
        }, 200);
    }
});

// exporting helper functions
function getSelectedRowCount() {
    const checked = $('.row-check:checked').length;
    return checked > 0
        ? checked
        : window.reportingTable.rows({ search: 'applied' }).count();
}

function getToday() {
    return new Date().toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

function getUserName() {
    return window.currentUserName || 'Admin IT';
}

function initReportButtonActions() {
    document.addEventListener('click', function (e) {

        const detailBtn = e.target.closest('.btn-detail');
        if (!detailBtn) return;

        const table = window.reportingTable;
        if (!table) return;

        const tr = detailBtn.closest('tr');

        const row = tr.classList.contains('child')
            ? table.row(tr.previousElementSibling)
            : table.row(tr);

        const data = row.data();
        if (!data) return;

        // =========================
        // ISI MODAL DETAIL REPORTING
        // =========================
        document.getElementById('d-toko').value    = data[2];
        document.getElementById('d-user').value    = data[3];
        document.getElementById('d-peminta').value = data[4];
        document.getElementById('d-kendala').value = data[6];
        document.getElementById('d-status').value  = data[7];
        document.getElementById('d-handler').value = data[8];
        document.getElementById('d-tanggal').value = data[9];

        document.getElementById('detail-deskripsi').textContent = '';

        // =========================
        // AMBIL URGENCY (S/H + BADGE)
        // =========================
        const urgencyCell = tr.classList.contains('child')
            ? tr.previousElementSibling.querySelector('td.urgency-cell')
            : tr.querySelector('td.urgency-cell');

        const shCode =
            urgencyCell?.querySelector('.urgency-code')?.textContent.trim() || '';

        const shBadge =
            urgencyCell?.querySelector('.urgency-badge')?.textContent.trim() || '';

        document.getElementById('d-urgensi').value =
            shBadge ? `${shCode} - ${shBadge}` : shCode;

        // =========================
        // OPEN MODAL
        // =========================
        document.getElementById('detailModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    });
}

// ========== CLOSE VIA OVERLAY ==========
document.addEventListener('click', function (e) {
    const modal = e.target.closest('.modal');
    if (e.target.classList.contains('modal')) {
        modal.classList.remove('show');
    }
});

// ========== CLOSE VIA ESC ==========
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
        });
    }
});

// ========== CLOSE VIA BUTTON (X & CANCEL) ==========
document.addEventListener('click', function (e) {

    if (
        e.target.classList.contains('close-btn') ||
        e.target.classList.contains('btn-cancel')
    ) {
        const modal = e.target.closest('.modal');
        if (modal) modal.classList.remove('show');
    }

});
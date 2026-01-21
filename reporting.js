// ==========================================
// REPORTING SYSTEM (ULTIMATE CONSOLIDATED)
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    // Memastikan inisialisasi hanya dipanggil sekali di awal
    if (!$.fn.DataTable.isDataTable('#reportingTable')) {
        initializeReportingTable();
    }
    initReportFilters();
    initReportFilterToggle();  
});

// ==========================================
// 1. DATATABLE INIT & CONFIG
// ==========================================
function initializeReportingTable() {
    // Cek lagi untuk keamanan reinitialise
    if ($.fn.DataTable.isDataTable('#reportingTable')) {
        $('#reportingTable').DataTable().destroy();
    }

    const table = $('#reportingTable').DataTable({
        dom: '<"top"lf>rt<"bottom"ip>', 
        lengthChange: true, 
        lengthMenu: [10, 25, 50, 100, "Semua"],
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Laporan Service Request',
                exportOptions: { columns: [2, 3, 4, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'csvHtml5',
                title: 'Laporan Service Request',
                exportOptions: { columns: [2, 3, 4, 6, 7, 8, 9, 10] }
            }
        ],
        responsive: true,
        autoWidth: false,
        ordering: true,
        order: [[10, 'desc']], 
        columnDefs: [
            { orderable: false, targets: [0, 11] },
            { className: 'dt-center', targets: [0, 1, 5, 7, 10] }
        ],
        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            search: "_INPUT_",
            searchPlaceholder: "Cari data...",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "‹", next: "›" }
        }
    });

    window.reportingTable = table;
    initCheckboxHandlers(table);
}

// ==========================================
// 2. CORE ENGINE (FUNGSI PEMBUAT KONTEN LAPORAN)
// ==========================================
function getReportContent(selectedRows) {
    const table = window.reportingTable;
    const now = new Date();
    const dateString = now.toLocaleDateString('id-ID', { 
        day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' 
    });

    let tableRows = '';
    selectedRows.forEach((rowElement, idx) => {
        const d = table.row(rowElement).data();
        const shCode = $(rowElement).find('.urgency-code').text().trim() || '-';
        const shBadge = $(rowElement).find('.urgency-badge').text().trim() || '';
        const fullUrgency = shBadge ? `${shCode} - ${shBadge}` : shCode;
        const cleanStatus = $(rowElement).find('td:nth-child(8)').text().trim(); 

        tableRows += `
            <tr>
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${idx + 1}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[2]}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[3]}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[4]}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${fullUrgency}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[6]}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${cleanStatus}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[8]}</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 7pt; word-break: break-all;">${d[9] || '-'}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${d[10]}</td>
            </tr>
        `;
    });

    return `
        <div style="padding: 10px; font-family: Arial, sans-serif; color: #000; background: #fff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px;">
                <div style="text-align: left;">
                    <h2 style="margin:0; font-size: 16pt; font-weight: bold; color: #333;">LAPORAN SERVICE REQUEST</h2>
                    <p style="margin:2px 0; font-size: 9pt; color: #555;">Sistem Informasi IT Helpdesk</p>
                </div>
                <div style="text-align: right; font-size: 8pt;">
                    <strong>TANGGAL CETAK:</strong> ${dateString}<br>
                    <strong>TOTAL DATA:</strong> ${selectedRows.length} Baris
                </div>
            </div>
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8pt; border: 1px solid #000;">
                <thead>
                    <tr style="background-color: #f0f0f0 !important; color: #000 !important;">
                        <th style="border: 1px solid #000; padding: 6px; width: 25px; text-align: center;">NO</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 55px; text-align: left;">TOKO</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 55px; text-align: left;">PEMINTA</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 55px; text-align: left;">PENERIMA</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 55px; text-align: center;">S/H</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 65px; text-align: left;">KENDALA</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 50px; text-align: center;">STATUS</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: left;">HANDLER</th>
                        <th style="border: 1px solid #000; padding: 6px; text-align: left;">USER SALAH INPUT</th>
                        <th style="border: 1px solid #000; padding: 6px; width: 65px; text-align: center;">TANGGAL</th>
                    </tr>
                </thead>
                <tbody>${tableRows}</tbody>
            </table>
        </div>`;
}

function generateProfessionalReport() {
    const table = window.reportingTable;
    let selectedRows = table.rows().nodes().toArray().filter(row => $(row).find('.row-check').prop('checked'));

    if (selectedRows.length === 0) {
        const konfirmasi = confirm('Tidak ada user dicentang. Apakah Anda ingin memproses semua data yang tampil?');
        if (konfirmasi) {
            selectedRows = table.rows({ search: 'applied' }).nodes().toArray();
        } else {
            return; 
        }
    }

    const content = getReportContent(selectedRows);
    const w = window.open('', '_blank');
    w.document.write(`
        <html>
            <head>
                <title>Laporan Service Request</title>
                <style>
                    @page { size: landscape; margin: 0.8cm; }
                    body { margin: 0; padding: 0; }
                    @media print { body { -webkit-print-color-adjust: exact; } }
                </style>
            </head>
            <body>
                ${content}
                <script>window.onload = function() { window.print(); window.close(); }</script>
            </body>
        </html>
    `);
    w.document.close();
}

// ==========================================
// 3. EXPORT BUTTON TRIGGERS
// ==========================================
function copyTable() {
    const table = window.reportingTable;
    let targetRows = $('.row-check:checked').closest('tr');

    if (targetRows.length === 0) {
        const konfirmasi = confirm('Tidak ada data dipilih. Copy semua data yang tampil ke clipboard?');
        if (!konfirmasi) return;
        targetRows = $(table.rows({ search: 'applied' }).nodes());
    }

    let copyText = "No\tToko\tPeminta\tPenerima\tS/H\tKendala\tStatus\tHandler\tSalah Input\tTanggal\n";
    targetRows.each(function(i) {
        const d = table.row(this).data();
        const shCode = $(this).find('.urgency-code').text().trim() || '-';
        const shBadge = $(this).find('.urgency-badge').text().trim() || '';
        const fullUrgency = shBadge ? `${shCode} - ${shBadge}` : shCode;
        const status = $(this).find('td:nth-child(8)').text().trim();
        copyText += `${i+1}\t${d[2]}\t${d[3]}\t${d[4]}\t${fullUrgency}\t${d[6]}\t${status}\t${d[8]}\t${d[9]}\t${d[10]}\n`;
    });
    navigator.clipboard.writeText(copyText).then(() => alert('Berhasil copy ke clipboard!'));
}

function exportTable(type) {
    const table = window.reportingTable;
    let selectedRows = table.rows().nodes().toArray().filter(row => $(row).find('.row-check').prop('checked'));

    if (type === 'pdf') {
        if (selectedRows.length === 0) {
            const konfirmasi = confirm('Export semua data ke PDF?');
            if (!konfirmasi) return;
            selectedRows = table.rows({ search: 'applied' }).nodes().toArray();
        }
        const content = getReportContent(selectedRows);
        const w = window.open('', '_blank');
        w.document.write(`<html><head><title>Laporan Service Request</title><style>@page { size: A4 portrait; margin: 1cm; } body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 6px; font-size: 8pt; word-wrap: break-word; } th { background-color: #f2f2f2 !important; }</style></head><body>${content}<script>window.onload = function() { window.print(); }</script></body></html>`);
        w.document.close();
        return;
    }

    const totalChecked = $('.row-check:checked').length;
    if (totalChecked === 0) {
        const konfirmasi = confirm(`Tidak ada data dipilih. Export semua data ke ${type.toUpperCase()}?`);
        if (!konfirmasi) return;
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        $(visibleRows).find('.row-check').prop('checked', true);
    }
    
    if (type === 'excel') table.button(0).trigger();
    if (type === 'csv') table.button(1).trigger();

    if (totalChecked === 0) {
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        $(visibleRows).find('.row-check').prop('checked', false);
        $('#checkAll').prop('checked', false);
    }
}

function printSemua() {
    generateProfessionalReport(); 
}

// ==========================================
// 4. CHECKBOX HANDLERS
// ==========================================
function initCheckboxHandlers(table) {
    $('#checkAll').on('change', function () {
        const isChecked = this.checked;
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        $(visibleRows).find('.row-check').prop('checked', isChecked);
    });

    $('#reportingTable tbody').on('change', '.row-check', function () {
        const totalChecked = $('.row-check:checked').length;
        const totalVisible = table.rows({ search: 'applied' }).count();
        $('#checkAll').prop('checked', totalVisible === totalChecked && totalVisible > 0);
    });

    table.on('draw', function () {
        $('#checkAll').prop('checked', false);
    });
}
 
// ==========================================
// 5. FILTER LOGIC
// ==========================================
function initReportFilterToggle() {
    $('#btnToggleReportFilter').on('click', function() {
        const panel = $('#reportFilterPanel');
        panel.toggleClass('show');
        $(this).text(panel.hasClass('show') ? 'Filter ▲' : 'Filter ▼');
    });
}

function initReportFilters() {
    // Trigger Live Filter saat ada perubahan pada input/select
    $('.live-report-filter').on('change keyup input', function() {
        applyReportFilters();
    });

    $('#btnApplyReportFilter').on('click', applyReportFilters);
    
    $('#btnResetReportFilter').on('click', function() {
        $('#reportFilterPanel select, #reportFilterPanel input').val('');
        // Kosongkan pencarian kolom
        const table = window.reportingTable;
        table.columns().search('').draw();
        
        // Hapus filter custom (ext.search)
        $.fn.dataTable.ext.search.pop();
        table.draw();
    });
}

function applyReportFilters() {
    const table = window.reportingTable;
    if (!table) return;

    // 1. Ambil nilai dari element input filter
    const toko = $('#report-filter-toko').val();
    const penerima = $('#report-filter-penerima').val();
    const handler = $('#report-filter-handler').val();
    const status = $('#report-filter-status').val(); 
    const wrongInputFilter = $('#report-filter-wrong-input').val();
    const startDate = $('#report-filter-start-date').val();
    const endDate = $('#report-filter-end-date').val();

    // 2. Filter Kolom Langsung (Fixed Index berdasarkan SQL)
    table.column(1).search(toko || '');    // nama_toko
    table.column(3).search(penerima || ''); // penerima (Indeks 3 di SQL)
    
    // Filter Status (Indeks 7) - Menggunakan Regex Exact Match agar akurat
    if (status) {
        table.column(7).search('^' + status + '$', true, false); 
    } else {
        table.column(7).search('');
    }

    // Filter Handler (Indeks 9)
    table.column(9).search(handler || ''); 

    // 3. Filter Custom (Tanggal & Salah Input)
    $.fn.dataTable.ext.search.pop(); 
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        // Pastikan hanya memfilter tabel reporting
        if (settings.nTable.id !== 'reportingTable') return true;

        // --- Logika Filter Tanggal (Indeks 12: input_datetime) ---
        const rowEl = settings.aoData[dataIndex].nTr;
        // Ambil dari attribute data-tanggal di <tr> atau kolom 12
        const rawDateStr = $(rowEl).attr('data-tanggal') || data[12]; 
        
        if (rawDateStr && (startDate || endDate)) {
            const rowDateOnly = rawDateStr.split(' ')[0]; // Ambil YYYY-MM-DD
            if (startDate && rowDateOnly < startDate) return false;
            if (endDate && rowDateOnly > endDate) return false;
        }

        // --- Logika Filter Salah Input (Indeks 11: ketidaksesuaian_detail) ---
        const cellValue = (data[11] || "").trim();
        
        if (wrongInputFilter === "salah-input") {
            // Tampilkan jika ada isinya dan bukan strip
            if (cellValue === "-" || cellValue === "") return false;
        } 
        else if (wrongInputFilter === "normal") {
            // Tampilkan jika hanya berisi strip atau kosong
            if (cellValue !== "-" && cellValue !== "") return false;
        }

        return true;
    });

    table.draw();
}

// ==========================================
// 6. MODAL SYSTEM
// ==========================================
function modal(target, action = 'open') {
    const modalEl = typeof target === 'string' ? document.getElementById(target) : target;
    if (!modalEl) return;
    if (action === 'open') {
        modalEl.classList.add('show');
        document.body.style.overflow = 'hidden';
    } else {
        modalEl.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('close-btn')) modal(e.target.closest('.modal'), 'close');
    if (e.target.classList.contains('modal')) modal(e.target, 'close');
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal.show').forEach(m => modal(m, 'close'));
});
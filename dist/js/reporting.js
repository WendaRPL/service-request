// ==========================================
// REPORTING SYSTEM (ULTIMATE CONSOLIDATED)
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    // Memastikan inisialisasi hanya dipanggil sekali di awal
    if (!$.fn.DataTable.isDataTable('#reportingTable')) {
        initializeReportingTable();
    }
    initReportFilters();
});

// ==========================================
// 1. DATATABLE INIT & CONFIG
// ==========================================
function initializeReportingTable() { 
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
        order: [[4, 'asc'], [5, 'asc']],
        columnDefs: [ 
            { responsivePriority: 0, targets: [0, 1, 4, 5] }, 
            { responsivePriority: 2, targets: 11 },   
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
// 2. CORE ENGINE  
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
        const jenisKendala = d[6] || '-';  
        
        // Gabung S/H dan Jenis Kendala
        const combinedKendala = `${shCode} - ${jenisKendala} (${shBadge})`;
        
        const cleanStatus = $(rowElement).find('.status-card').text().trim() || '-'; 
        const tanggal = d[10] || '-'; 
        const deskripsi = d[9] || '-'; // Mengambil data deskripsi (index ke-9)
        const salahInput = d[10] || '-'; // Data ketidaksesuaian biasanya ada di d[10] atau d[11] tergantung query SQL Anda

        tableRows += `
            <tr>
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${idx + 1}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; width: 65px;">${tanggal}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[3]}</td> 
                <td style="border: 1px solid #000; padding: 4px;">${d[4]}</td> 
                <td style="border: 1px solid #000; padding: 4px;">${combinedKendala}</td> 
                <td style="border: 1px solid #000; padding: 4px; font-size: 7pt; word-break: break-all;">${deskripsi}</td> 
                <td style="border: 1px solid #000; padding: 4px; text-align: center;">${cleanStatus}</td>
                <td style="border: 1px solid #000; padding: 4px;">${d[8]}</td> 
                <td style="border: 1px solid #000; padding: 4px; font-size: 7pt; word-break: break-all;">${salahInput}</td>
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
                <strong>TOTAL DATA:</strong> ${selectedRows.length} 
            </div>
        </div>
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8pt; border: 1px solid #000;">
            <thead>
                <tr style="background-color: #f0f0f0 !important; color: #000 !important;">
                    <th style="border: 1px solid #000; padding: 6px; width: 30px; text-align: center;">NO</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 65px; text-align: center;">TANGGAL</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: left;">PEMINTA</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: left;">PENERIMA</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 100px; text-align: left;">KENDALA (URGENSI)</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 120px; text-align: left;">DESKRIPSI</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 55px; text-align: center;">STATUS</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: left;">HANDLER</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 90px; text-align: left;">KETIDAKSESUAIAN</th>
                </tr>
            </thead>
            <tbody>${tableRows}</tbody>
        </table>
    </div>`;
}

function generateProfessionalReport() {
    const table = window.reportingTable;
    if (!table) return;

    let selectedRows = table.rows({ search: 'applied' }).nodes().toArray().filter(row => { 
        return $(row).find('.row-check').is(':checked');
    });

    if (selectedRows.length === 0) {
        if (!confirm('Tidak ada data dicentang. Cetak semua data yang tampil?')) return;
        selectedRows = table.rows({ search: 'applied' }).nodes().toArray();
    }

    const content = getReportContent(selectedRows);
    const w = window.open('', '_blank');

    if (!w) {
        alert('Popup diblokir! Harap izinkan popup di browser Anda.');
        return;
    }

    w.document.write(`
        <html>
            <head>
                <title>Laporan Service Request</title>
                <style>
                    @page { size: landscape; margin: 0.8cm; }
                    body { margin: 0; padding: 0; font-family: Arial; }
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
    const table = $('#reportingTable').DataTable(); // Panggil instance dengan cara ini lebih aman
    
    if (!table) return;

    // Ambil baris yang tercentang (Support Desktop & Mobile)
    let selectedRows = table.rows({ search: 'applied' }).nodes().toArray().filter(row => {
        return $(row).find('.row-check').is(':checked');
    });

    // LOGIKA PDF (CUSTOM)
    if (type === 'pdf') {
        if (selectedRows.length === 0) {
            if (!confirm('Export semua data yang tampil ke PDF?')) return;
            selectedRows = table.rows({ search: 'applied' }).nodes().toArray();
        }

        const content = getReportContent(selectedRows);
        const w = window.open('', '_blank');
        
        if (!w || w.closed || typeof w.closed == 'undefined') { 
            alert('Popup diblokir! Harap izinkan popup di browser Anda.');
            return;
        }

        w.document.write(`<html><head><title>Laporan Service Request</title><style>@page { size: A4 portrait; margin: 1cm; } body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 6px; font-size: 8pt; word-wrap: break-word; } th { background-color: #f2f2f2 !important; }</style></head><body>${content}<script>window.onload = function() { window.print(); }</script></body></html>`);
        w.document.close();
        return;
    }

    // LOGIKA EXCEL & CSV (Memicu Button Bawaan)
    if (selectedRows.length === 0) {
        if (!confirm(`Export semua data yang tampil ke ${type.toUpperCase()}?`)) return;
    }

    // Gunakan pemicu button berdasarkan class bawaan DataTables
    if (type === 'excel') {
        table.button('.buttons-excel').trigger();
    } else if (type === 'csv') {
        table.button('.buttons-csv').trigger();
    }
}

// Pastikan fungsi ini tersedia agar tidak error "printSemua is not defined"
function printSemua() {
    generateProfessionalReport();
}

// ==========================================
// 4. CHECKBOX HANDLERS
// ==========================================
function initCheckboxHandlers(table) {
    // 1. Handle Check All (Sinkron Desktop & Mobile)
    $('#checkAll').on('change', function () {
        const isChecked = $(this).is(':checked');
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        
        // Centang semua checkbox di baris asli (Desktop)
        $(visibleRows).find('.row-check').prop('checked', isChecked);
        
        // Centang semua checkbox di baris detail (Mobile) jika sedang terbuka
        $('.dtr-details .row-check').prop('checked', isChecked);
    });

    // 2. Handle Klik Checkbox Satuan (Delegasi Event)
    $(document).on('change', '.row-check', function () {
        const isChecked = $(this).is(':checked');
        const $li = $(this).closest('li'); // Jika di mobile, checkbox ada di dalam LI
        const $tr = $(this).closest('tr');
        
        let targetTr;

        if ($tr.hasClass('child') || $li.length > 0) {
            // JIKA MOBILE: Cari baris induk (parent) dari baris detail ini
            const childRow = $(this).closest('tr.child');
            targetTr = childRow.prev('.parent');
        } else {
            // JIKA DESKTOP
            targetTr = $tr;
        }
 
        targetTr.find('.row-check').prop('checked', isChecked);

        // Update status Check All di header
        const totalVisible = table.rows({ search: 'applied' }).count();
        const totalChecked = table.rows({ search: 'applied' }).nodes().toArray()
            .filter(row => $(row).find('.row-check').is(':checked')).length;

        $('#checkAll').prop('checked', totalVisible === totalChecked && totalVisible > 0);
    });
}
 
// ==========================================
// 5. FILTER LOGIC 
// ==========================================
function initReportFilters() {
 
    $('#btnToggleReportFilter').on('click', function() {
        const panel = $('#reportFilterPanel');

        panel.stop(true, true).slideToggle(250);  

        $(this).toggleClass('active');
        $(this).html(panel.is(':visible') ? 'Filter ▲' : 'Filter ▼');
    });
 
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'reportingTable') return true;

        const fToko = $('#report-filter-toko').val();
        const fPenerima = $('#report-filter-penerima').val();
        const fHandler = $('#report-filter-handler').val();
        const fStatus = $('#report-filter-status').val();  
        const fWrong = $('#report-filter-wrong-input').val();
        const fStart = $('#report-filter-start-date').val();
        const fEnd = $('#report-filter-end-date').val();

        const rowEl = settings.aoData[dataIndex].nTr;
        const rowToko = $(rowEl).attr('data-toko') || "";
        const rowPenerima = $(rowEl).attr('data-penerima') || "";
        const rowHandler = $(rowEl).attr('data-handler') || "";
        const rowWrongInput = $(rowEl).attr('data-wrong') || "";
        const rowStatusText = $(rowEl).find('.status-card').text().trim();

        if (fToko && rowToko !== fToko) return false;
        if (fPenerima && rowPenerima !== fPenerima) return false;
        if (fHandler && rowHandler !== fHandler) return false;
        if (fStatus && rowStatusText !== fStatus) return false;

        if (fWrong === "salah-input") {
            if (rowWrongInput === "-" || rowWrongInput === "") return false;
        } else if (fWrong === "normal") {
            if (rowWrongInput !== "-" && rowWrongInput !== "") return false;
        }

        if (fStart || fEnd) {
            const rawDate = $(rowEl).attr('data-tanggal');
            if (rawDate && rawDate !== '-') {
                const rowDate = rawDate.split(' ')[0];
                if (fStart && rowDate < fStart) return false;
                if (fEnd && rowDate > fEnd) return false;
            } else {
                return false;
            }
        }

        return true;
    });
 
    $('.live-report-filter').on('change keyup input select2:select select2:unselect', function() {
        if (window.reportingTable) {
            window.reportingTable.draw();
        }
    });
 
    $('#btnResetReportFilter').on('click', function() {
        $('.live-report-filter').val('').trigger('change');
        if (window.reportingTable) window.reportingTable.draw();
    });
}
  
// =========================
// DETAIL MODAL ACTIONS  
// =========================
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detailModal');
    const closeBtn = document.querySelector('.close-btn');

    // Gunakan Delegation: Tangkap klik pada level DOCUMENT atau ID TABEL
    document.addEventListener('click', function(e) {
        // Cek apakah elemen yang diklik adalah .btn-detail
        const button = e.target.closest('.btn-detail');
        
        if (button) {
            // Dapatkan baris TR tempat tombol berada
            const row = button.closest('tr');
            
            // Pengambilan data dari atribut tombol
            const data = {
                id: button.getAttribute('data-id'),
                toko: button.getAttribute('data-toko'),
                peminta: button.getAttribute('data-peminta'),
                penerima: button.getAttribute('data-penerima'),
                terjadi: button.getAttribute('data-terjadi'),
                sh: button.getAttribute('data-sh'),
                jenis: button.getAttribute('data-jenis'),
                hw: button.getAttribute('data-hw'),
                status: button.getAttribute('data-status'),
                staff: button.getAttribute('data-staff'),
                tanggal: button.getAttribute('data-tanggal'),
                deskripsi: button.getAttribute('data-deskripsi'),
                upload: button.getAttribute('data-upload'), 
                wrong: button.getAttribute('data-wrong') 
            };

            // Isi field modal
            document.getElementById('detail-no').textContent = data.id || '-';
            document.getElementById('detail-toko').textContent = data.toko || '-'; 
            document.getElementById('detail-penerima').textContent = data.peminta || '-';
            document.getElementById('detail-peminta').textContent = data.penerima || '-';
            document.getElementById('detail-terjadi').textContent = data.terjadi || '-';
            document.getElementById('detail-sh').innerHTML = data.sh || '-';
            document.getElementById('detail-jenis').textContent = data.jenis || '-';
            document.getElementById('detail-kode-hw').textContent = data.hw || '-';
            document.getElementById('detail-status').textContent = data.status || '-';
            document.getElementById('detail-staff').textContent = data.staff || '-';
            document.getElementById('detail-tanggal').textContent = data.tanggal || '-';
            
            document.getElementById('detail-deskripsi').value = data.deskripsi || '-';
            document.getElementById('detail-wrong').value = data.wrong || '-';

            const displayArea = document.getElementById('detail-upload-display');
            if (displayArea) {
                displayArea.innerHTML = ''; // Reset konten sebelumnya

                if (data.upload && data.upload !== '' && data.upload !== '-') {
                    const fileName = data.upload;
                    const fileExt = fileName.split('.').pop().toLowerCase();
                    // Pastikan path folder 'uploads/' sudah sesuai dengan struktur folder Anda
                    const filePath = 'uploads/' + fileName;  
                    
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) { 
                        // Jika format gambar, tampilkan preview foto
                        displayArea.innerHTML = `
                            <a href="${filePath}" target="_blank">
                                <img src="${filePath}" style="max-width:100%; max-height:200px; border-radius:8px; border:1px solid #ddd; cursor:pointer; margin-top:5px;">
                                <br><small style="color: #666;">Klik gambar untuk memperbesar</small>
                            </a>`;
                    } else { 
                        // Jika format dokumen (PDF, Excel, dll), tampilkan ikon dan link
                        displayArea.innerHTML = `
                            <div style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                                <i class="fas fa-file-alt" style="font-size: 20px; color: #555;"></i> 
                                <a href="${filePath}" target="_blank" style="color: #007bff; font-weight: bold; text-decoration: none;">
                                    Lihat Dokumen (${fileExt.toUpperCase()})
                                </a>
                            </div>`;
                    }
                } else {
                    // Jika tidak ada file
                    displayArea.innerHTML = '<span style="color: #999; font-style: italic;">Tidak ada lampiran</span>';
                }
            }

            modal.style.display = 'flex';
        }
    });

    closeBtn.onclick = function() { modal.style.display = 'none'; }
     
});
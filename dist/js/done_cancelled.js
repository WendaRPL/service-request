// =========================
// GLOBAL VARIABLES
// =========================
let currentTab = 'done';
window.doneTable = null;
window.canceledTable = null;

// =========================
// DOM READY
// =========================
document.addEventListener('DOMContentLoaded', function () {
    initDataTables(); 
    initModals();
    initReportButtonActions();   
    setDefaultDates();

    document.getElementById('tab-done')?.addEventListener('click', () => switchTab('done'));
    document.getElementById('tab-canceled')?.addEventListener('click', () => switchTab('canceled'));
});

// =========================
// MODAL CONTROLLER (SAFE)
// =========================
function modal(id, action = 'open') {
    const el = document.getElementById(id);
    if (!el) return;

    if (action === 'open') {
        // tutup modal lain dulu (1 modal aktif)
        document.querySelectorAll('.modal.show')
            .forEach(m => m.classList.remove('show'));

        el.classList.add('show');
        document.body.style.overflow = 'hidden';
    } else {
        el.classList.remove('show');

        // unlock body kalau tidak ada modal terbuka
        if (!document.querySelector('.modal.show')) {
            document.body.style.overflow = 'auto';
        }
    }
}

function initModals() { 
    document.addEventListener('click', e => {
        if (e.target.classList.contains('close-btn')) {
            const modalEl = e.target.closest('.modal');
            if (modalEl) modal(modalEl.id, 'close');
        }
    });
 
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show')
                .forEach(m => modal(m.id, 'close'));
        }
    });
}

// =========================
// DATATABLE
// =========================
let doneTable, canceledTable;

function initDataTables() {
    // Konfigurasi umum untuk kedua tabel
    const tableConfig = {
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
            { orderable: false, targets: [9] },
            { className: 'dt-center', targets: [0] },
            { responsivePriority: 1, targets: [1, 2, 6] },
            { targets: 0, type: 'num' }
        ]
    };
 
    if ($('#doneTable').length) {
        window.doneTable = $('#doneTable').DataTable(tableConfig);
    }
 
    if ($('#canceledTable').length) {
        window.canceledTable = $('#canceledTable').DataTable(tableConfig);
    }
}

// =========================
// NAVIGATION
// =========================
function goToDashboard() {
    window.location.href = 'home.php';
}

// =========================
// FILTER SYSTEM (LIVE)
// =========================
$(document).ready(function() { 
    const tableDone = $('#doneTable').DataTable();
    const tableCanceled = $('#canceledTable').DataTable();

    // 2. Logika Toggle Panel Filter
    $('#btnToggleFilter').on('click', function() {
        $('#filterPanel').slideToggle();
        $(this).toggleClass('active');
    });
 
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) { 
            const row = $(settings.nTable).DataTable().row(dataIndex).node();
            const dateStr = $(row).attr('data-tanggal');  
            
            const min = $('#filter-start-date').val();  
            const max = $('#filter-end-date').val();  
            
            if (!dateStr) return true;

            const date = new Date(dateStr);
            const startDate = min ? new Date(min) : null;
            const endDate = max ? new Date(max) : null;

            if (startDate && date < startDate) return false;
            if (endDate && date > endDate) return false;
            
            return true;
        }
    );
 
    function applyFilters() {
        const toko = $('#filter-toko').val();
        const penerima = $('#filter-penerima').val();
        const handler = $('#filter-handler').val();
 
        tableDone
            .column(1).search(toko)     
            .column(3).search(penerima)  
            .column(7).search(handler)   
            .draw();

        // Terapkan ke tabel Canceled
        tableCanceled
            .column(1).search(toko)     
            .column(3).search(penerima) 
            .column(7).search(handler)   
            .draw();
    }
 
    $('.live-filter').on('change keyup', function() {
        applyFilters();
    });
});

// =========================
// TAB SWITCH
// =========================
function switchTab(tab) {
    currentTab = tab; 

    if (tab === 'done') {
        $('#done-table').show();
        $('#canceled-table').hide(); 
        if (window.doneTable) window.doneTable.columns.adjust().responsive.recalc();
    } else {
        $('#done-table').hide();
        $('#canceled-table').show(); 
        if (window.canceledTable) window.canceledTable.columns.adjust().responsive.recalc();
    }
 
    $('.tab-button').removeClass('active');
    $(`#tab-${tab}`).addClass('active');
}

// =========================
// DETAIL MODAL ACTIONS  
// =========================
document.addEventListener('click', function (e) { 
    if (e.target.closest('.btn-detail')) {
        const btn = e.target.closest('.btn-detail');
         
        const data = {
            id: btn.getAttribute('data-id'),
            toko: btn.getAttribute('data-toko'),
            peminta: btn.getAttribute('data-peminta'),
            penerima: btn.getAttribute('data-penerima'),
            terjadi: btn.getAttribute('data-terjadi'),
            sh: btn.getAttribute('data-sh'),
            jenis: btn.getAttribute('data-jenis'),
            hw: btn.getAttribute('data-kode-hw'),
            status: btn.getAttribute('data-status'),
            staff: btn.getAttribute('data-staff'),
            tanggal: btn.getAttribute('data-tanggal'),
            deskripsi: btn.getAttribute('data-deskripsi'),
            upload: btn.getAttribute('data-upload')
        };
 
        document.getElementById('detail-no').innerText = data.id;
        document.getElementById('detail-toko').innerText = data.toko;
        document.getElementById('detail-peminta').innerText = data.peminta;
        document.getElementById('detail-penerima').innerText = data.penerima;
        document.getElementById('detail-terjadi').innerText = data.terjadi;
        document.getElementById('detail-sh').innerHTML = data.sh;
        document.getElementById('detail-jenis').innerText = data.jenis;
        document.getElementById('detail-kode-hw').innerText = data.hw || '-';
        document.getElementById('detail-status').innerText = data.status;
        document.getElementById('detail-staff').innerText = data.staff;
        document.getElementById('detail-tanggal').innerText = data.tanggal;
        document.getElementById('detail-deskripsi').value = data.deskripsi; // Gunakan .value untuk textarea
 
        const attachmentContainer = document.getElementById('attachment-container');
        attachmentContainer.innerHTML = '';  
        
        if (data.upload && data.upload !== '') {
            const img = document.createElement('img');
            img.src = 'uploads/' + data.upload;  
            img.style.maxWidth = '100%';
            img.style.borderRadius = '8px';
            img.alt = 'Lampiran';
            attachmentContainer.appendChild(img);
        } else {
            attachmentContainer.innerHTML = '<span style="color: #999; font-style: italic;">Tidak ada lampiran</span>';
        }
 
        document.getElementById('detailModal').classList.add('show');
    }
 
    if (e.target.classList.contains('close-btn')) {
        const modalEl = e.target.closest('.modal');
        if (modalEl) {
            modal(modalEl, 'close');  
        }
    }
});

// =========================
// REPORT BUTTON ACTIONS
// ========================= 
function initReportButtonActions() { 
    $(document).on('click', '.btn-report', function () {
        const btn = $(this);
         
        const data = {
            id:      btn.attr('data-id'),
            toko:    btn.attr('data-toko'),
            user:    btn.attr('data-user'),
            peminta: btn.attr('data-peminta'),
            sh:      btn.attr('data-sh'),  
            kendala: btn.attr('data-kendala'),
            kode:    btn.attr('data-kode'),
            wrong:   btn.attr('data-wrong')
        };
 
        const shText = data.sh ? data.sh.replace(/<[^>]*>?/gm, '').trim() : '-';
 
        $('#r-no').val(data.id || '-');
        $('#r-toko-old').val(data.toko || '-');
        $('#r-user-old').val(data.user || '-');
        $('#r-peminta-old').val(data.peminta || '-');
        $('#r-sh-old').val(shText);
        $('#r-kendala-old').val(data.kendala || '-');
        $('#r-kode-old').val(data.kode || '-');
         
        $('#r-user-wrong-input').val(data.wrong !== '-' ? data.wrong : '');
 
        modal('reportModal', 'open');
    });
 
    $('#btn-submit-report').off('click').on('click', function() {
        const $btn = $(this);
        const requestId = $('#r-no').val();
        const wrongInput = $('#r-user-wrong-input').val();

        if(!wrongInput.trim()) {
            alert('Mohon isi detail ketidaksesuaian input.');
            return;
        }

        $btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'direct/process_update_report.php', 
            type: 'POST',
            dataType: 'json',
            data: {
                id: requestId,
                ketidaksesuaian: wrongInput
            },
            success: function(res) {
                if(res.success) {
                    alert('Laporan berhasil diperbarui!');
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi ke server.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Simpan Laporan');
            }
        });
    });
}

// =========================
// DEFAULT DATE
// =========================
function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today);
    lastWeek.setDate(today.getDate() - 7);

    const f = d => d.toISOString().split('T')[0];
    $('#filter-start-date').val(f(lastWeek));
    $('#filter-end-date').val(f(today));
}

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
    initFilters();
    initModals();
    initReportButtonActions(); // Untuk Detail Modal
    initReportModalActions();  // Untuk Report Modal
    initSubmitReportActions();
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
    // CLOSE BUTTON ONLY
    document.addEventListener('click', e => {
        if (e.target.classList.contains('close-btn')) {
            const modalEl = e.target.closest('.modal');
            if (modalEl) modal(modalEl.id, 'close');
        }
    });

    // ESC KEY
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
    const commonConfig = {
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        info: true,
        order: [[8, 'desc']], 
        lengthChange: false,
        columnDefs: [
            { orderable: false, targets: [9] },  
            { className: 'dt-center', targets: [0, 6, 9] }
        ],
        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            search: "_INPUT_",
            searchPlaceholder: "Cari data...",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "‹", next: "›" }
        }
    };

    if ($('#doneTable').length) {
        doneTable = $('#doneTable').DataTable(commonConfig);
    }

    if ($('#canceledTable').length) {
        canceledTable = $('#canceledTable').DataTable(commonConfig);
    }

    // PENTING: Paksa penyesuaian ukuran kolom setelah inisialisasi
    setTimeout(() => {
        if (window.doneTable) window.doneTable.columns.adjust().responsive.recalc();
        if (window.canceledTable) window.canceledTable.columns.adjust().responsive.recalc();
    }, 500);
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
function initFilters() {
    // 1. Toggle Panel Filter
    $('#btnToggleFilter').on('click', function() {
        $('#filterPanel').toggleClass('show');
    });

    // 2. Inisialisasi Select2 Multi-select untuk Toko
    if ($.fn.select2) {
        $('#filter-toko').select2({
            placeholder: "Pilih satu atau lebih toko...",
            allowClear: true,
            closeOnSelect: false
        });
    }

    // 3. LOGIKA FILTER CUSTOM DATATABLES
    // Fungsi ini akan dipanggil setiap kali table.draw() dijalankan
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        // Ambil baris (row) tabel saat ini
        const row = $(settings.nTable).find('tbody tr').eq(dataIndex);
        
        // Ambil nilai dari input filter
        const selectedTokos = $('#filter-toko').val(); // Array (karena multiple)
        const selectedPeminta = $('#filter-peminta').val();
        const selectedHandler = $('#filter-handler').val();
        const startDate = $('#filter-start-date').val();
        const endDate = $('#filter-end-date').val();

        // Ambil data dari atribut data-* di elemen TR
        const rowToko = row.attr('data-toko');
        const rowPeminta = row.attr('data-peminta');
        const rowHandler = row.attr('data-handler');
        const rowTanggal = row.attr('data-tanggal'); // Format: YYYY-MM-DD

        // Filter Toko (Multi-select)
        if (selectedTokos && selectedTokos.length > 0) {
            if (!selectedTokos.includes(rowToko)) return false;
        }

        // Filter Peminta
        if (selectedPeminta && rowPeminta !== selectedPeminta) return false;

        // Filter Handling By (Staff IT)
        if (selectedHandler && rowHandler !== selectedHandler) return false;

        // Filter Rentang Tanggal
        if (startDate && rowTanggal < startDate) return false;
        if (endDate && rowTanggal > endDate) return false;

        return true;
    });

    // 4. EVENT: Jalankan Filter Setiap Ada Perubahan (Live)
    $('.live-filter, #filter-toko').on('change', function() {
        // Refresh kedua tabel (Done & Canceled)
        if (window.doneTable) window.doneTable.draw();
        if (window.canceledTable) window.canceledTable.draw();
    });
}

// =========================
// TAB SWITCH
// =========================
function switchTab(tab) {
    // 1. Update Tombol UI
    $('.tab-button').removeClass('active');
    $(`#tab-${tab}`).addClass('active');

    // 2. Tampilkan/Sembunyikan Container
    if (tab === 'done') {
        $('#canceled-table').hide();
        $('#done-table').show();
        // 3. WAJIB: Adjust agar layout datatable tidak berantakan
        if (doneTable) {
            doneTable.columns.adjust().responsive.recalc();
        }
    } else {
        $('#done-table').hide();
        $('#canceled-table').show();
        // 3. WAJIB: Adjust agar data Canceled yang tadinya 'hide' muncul sempurna
        if (canceledTable) {
            canceledTable.columns.adjust().responsive.recalc();
        }
    }
}

// =========================
// DETAIL MODAL
// =========================
function initReportButtonActions() {
    $(document).on('click', '.btn-detail', function () {
        const tr = $(this).closest('tr');

        $('#d-no').text(tr.find('td:eq(0)').text());
        $('#d-toko').val(tr.data('toko'));
        $('#d-user').val(tr.data('peminta'));
        $('#d-peminta').val(tr.data('penerima'));
        $('#d-urgensi').val(tr.data('sh'));
        $('#d-kendala').val(tr.data('jenis'));
        $('#d-status').val(tr.data('status'));
        $('#d-handler').val(tr.data('handler'));
        $('#d-tanggal').val(tr.data('tanggal'));
        $('#detail-deskripsi').val(tr.data('desc'));

        modal('detailModal', 'open');
    });
}

// ==========================================
// REPORT MODAL ACTIONS (DINAMIS)
// ==========================================
function initReportModalActions() {
    // Tombol untuk buka modal report
    $(document).on('click', '.btn-report', function () {
        const tr = $(this).closest('tr');

        // 1. Ambil Data dari TR
        const data = {
            no:      tr.find('td:eq(1)').text().replace('.', ''), // Ambil No dari kolom ke-2
            toko:    tr.data('toko'),
            peminta: tr.data('peminta'),
            penerima:tr.data('penerima'),
            sh:      tr.data('sh'),
            urgensi: tr.data('urgensi'),
            jenis:   tr.data('jenis'),
            wrong:   tr.data('wrong') // Ambil data "salah input" jika sebelumnya sudah ada
        };

        // 2. Masukkan ke elemen Modal Report (Read Only Fields)
        $('#r-no').val(data.no);
        $('#r-toko-old').val(data.toko);
        $('#r-user-old').val(data.peminta);
        $('#r-peminta-old').val(data.penerima);
        $('#r-sh-old').val(data.sh + ' (' + data.urgensi + ')');
        $('#r-kendala-old').val(data.jenis);
        
        // 3. Masukkan ke elemen Modal Report (Editable Field)
        $('#r-user-wrong-input').val(data.wrong);

        // 4. Buka Modal
        modal('reportModal', 'open');
    });

    // Handle Simpan Laporan
    $('#btn-submit-report').on('click', function() {
        const requestId = $('#r-no').val(); // Atau ambil dari tr.data('request-id')
        const wrongInput = $('#r-user-wrong-input').val();

        if(!wrongInput.trim()) {
            alert('Mohon isi detail salah input.');
            return;
        }

        // Contoh AJAX Sederhana
        console.log("Simpan data untuk ID:", requestId, "Isi:", wrongInput);
        // Kirim ke server via AJAX disini...
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

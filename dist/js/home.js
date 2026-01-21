// =========================
// GLOBAL VARIABLES
// =========================
let currentDropdown = null;
let currentRequestId = null;

// =========================
// MODAL MANAGEMENT SYSTEM 
// =========================

function modal(target, action = 'open') {
    let modalEl = null;

    // Target bisa ID string, element, atau selector
    if (typeof target === 'string') {
        modalEl = document.getElementById(target) || document.querySelector(target);
    } else if (target instanceof HTMLElement) {
        modalEl = target;
    }

    if (!modalEl || !modalEl.classList.contains('modal')) return;

    if (action === 'open') {
        modalEl.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    if (action === 'close') {
        modalEl.classList.remove('show');
        document.body.style.overflow = 'auto';

        // Reset khusus (opsional)
        if (modalEl.id === 'requestDetailModal') {
            const btnAccept = document.getElementById('btn-accept-request');
            if (btnAccept) btnAccept.style.display = 'none';
        }
    }
}

/* =========================
   BACKWARD COMPATIBILITY
========================= */
function openModal(id) {
    document.querySelectorAll('.modal').forEach(m => {
        m.classList.remove('show');
    });
    document.getElementById(id)?.classList.add('show');
    document.body.classList.add('modal-open');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('show');

    // cek apakah masih ada modal terbuka
    const anyOpen = document.querySelector('.modal.show');
    if (!anyOpen) {
        document.body.classList.remove('modal-open');
    }
}

/* =========================
   GLOBAL EVENT HANDLERS
========================= */

// Klik luar modal (overlay)
document.addEventListener('click', function (e) {
    // Tombol close (X)
    if (e.target.classList.contains('close-btn')) {
        const parentModal = e.target.closest('.modal');
        if (parentModal) {
            modal(parentModal, 'close');
        }
    }
});

// Fungsi untuk membuka detail dari panel "Open"  
function openQueueDetail(button) { 
    const data = {
        id: button.getAttribute('data-id'),
        toko: button.getAttribute('data-toko'),
        peminta: button.getAttribute('data-peminta'),
        penerima: button.getAttribute('data-penerima'),
        sh: button.getAttribute('data-sh'), 
        kodeHardware: button.getAttribute('data-kode-hw'),
        jenis: button.getAttribute('data-jenis'),
        status: button.getAttribute('data-status'), 
        staff: '-', 
        desc: button.getAttribute('data-desc'),
        terjadi: button.getAttribute('data-terjadi'),
        upload: button.getAttribute('data-upload')
    };
    fillAndOpenDetail(data);
}

function fillAndOpenDetail(data) {
    const setEl = (id, val, isHTML = false) => {
        const el = document.getElementById(id);
        if (el) {
            if (isHTML) el.innerHTML = val || '-';
            else el.textContent = val || '-';
        }
    };

    setEl('detail-no', data.id);
    setEl('detail-toko', data.toko);
    setEl('detail-peminta', data.peminta);
    setEl('detail-penerima', data.penerima);
    setEl('detail-user', data.penerima); // Backup ID
    setEl('detail-sh', data.sh, true);
    setEl('detail-jenis', data.jenis);
    setEl('detail-status', data.status);
    setEl('detail-staff', data.staff);
    setEl('detail-kode-hw', data.kodeHardware);
    setEl('detail-terjadi', data.terjadi);

    // Deskripsi
    const descEl = document.getElementById('detail-deskripsi');
    if (descEl) {
        if (descEl.tagName === 'TEXTAREA') descEl.value = data.desc;
        else descEl.textContent = data.desc;
    }

    // HANDLER UNTUK UPLOAD (Gambar/File)
    const uploadContainer = document.getElementById('detail-upload-container');
    if (uploadContainer) {
        if (data.upload && data.upload !== '') {
            const ext = data.upload.split('.').pop().toLowerCase();
            const path = 'uploads/' + data.upload; // Sesuaikan folder upload Anda
            
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                uploadContainer.innerHTML = `<img src="${path}" style="max-width:100%; cursor:pointer;" onclick="window.open('${path}')">`;
            } else {
                uploadContainer.innerHTML = `<a href="${path}" target="_blank">Lihat Lampiran File (.${ext})</a>`;
            }
        } else {
            uploadContainer.innerHTML = '<em>Tidak ada lampiran</em>';
        }
    }

    // Tombol Accept
    const acceptBtn = document.getElementById('btnAcceptFromModal');
    if (acceptBtn) {
        acceptBtn.dataset.id = data.id;
        acceptBtn.style.display = (String(data.status).toLowerCase() === 'open') ? 'inline-block' : 'none';
    }

    openModal('requestDetailModal');
}

// =========================
// DETAIL REQUEST MODAL
// =========================
function openDetailRequest(requestId) { 
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!row) {
        console.error("Row tidak ditemukan untuk ID:", requestId);
        return;
    }

    // Ambil data dari atribut row dan kolom
    const cells = row.querySelectorAll('td');
    const data = {
        id: requestId,
        toko: cells[1].textContent.trim(),
        peminta: cells[2].textContent.trim(),
        penerima: cells[3].textContent.trim(),
        sh: cells[4].innerHTML, 
        jenis: cells[5].textContent.trim(),
        status: row.querySelector('.status-card')?.textContent.trim() || cells[6].textContent.trim(),
        staff: cells[7]?.textContent.trim() || '-',
        terjadi: row.getAttribute('data-terjadi-pada') || '',
        desc: row.getAttribute('data-deskripsi') || '', 
        kodeHardware: row.getAttribute('data-kode-hw') || '-',
        upload: row.getAttribute('data-upload') || ''
    };
    fillAndOpenDetail(data);

    // Isi ke elemen Modal Detail
    document.getElementById('detail-no').textContent = data.id;
    document.getElementById('detail-toko').textContent = data.toko;
    document.getElementById('detail-penerima').textContent = data.penerima;
    document.getElementById('detail-peminta').textContent = data.peminta;
    document.getElementById('detail-sh').innerHTML = data.sh;
    document.getElementById('detail-jenis').textContent = data.jenis;
    document.getElementById('detail-status').textContent = data.status;
    document.getElementById('detail-staff').textContent = data.staff; 
    document.getElementById('detail-kode-hw').textContent = data.kodeHardware; 
    
    const detailTerjadi = document.getElementById('detail-terjadi');
    if (detailTerjadi) detailTerjadi.textContent = data.terjadi;

    const descElem = document.getElementById('detail-deskripsi');
    if (descElem) {
        if(descElem.tagName === 'TEXTAREA') descElem.value = data.desc;
        else descElem.textContent = data.desc;
    }

    // Logika tombol accept di dalam modal
    const acceptBtn = document.getElementById('btnAcceptFromModal');
    if (acceptBtn) {
        acceptBtn.dataset.id = data.id;
        acceptBtn.style.display = (data.status.toLowerCase() === 'open') ? 'inline-block' : 'none';
    }

    openModal('requestDetailModal');
}

// =========================
// TRANSFER HANDLER MODAL LOGIC
// =========================
function openTransferHandler(requestId) {
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!row) return;

    const badgeUrgencyHTML = row.querySelector('td:nth-child(5)').innerHTML;
    const kodeHardware = row.getAttribute('data-kode-hw');
    const cells = row.querySelectorAll('td');
    
    // Ambil nilai terjadi_pada
    const terjadiPadaValue = row.getAttribute('data-terjadi-pada') || '';

    document.getElementById('transfer-no').textContent = requestId;
    document.getElementById('transfer-toko').textContent = cells[1].textContent;
    document.getElementById('transfer-user').textContent = cells[3].textContent; 
    document.getElementById('transfer-requester').textContent = cells[2].textContent;
    document.getElementById('transfer-tipe').innerHTML = badgeUrgencyHTML;
    document.getElementById('transfer-jenis').textContent = cells[5].textContent;
    document.getElementById('transfer-kode-hw').textContent = kodeHardware;  

    // ISI field baru di modal transfer
    const transferTerjadiElem = document.getElementById('transfer-terjadi');
    if (transferTerjadiElem) {
        transferTerjadiElem.textContent = terjadiPadaValue;
    }

    const descArea = document.getElementById('transfer-deskripsi');
    if (descArea) {
        descArea.value = row.querySelector('.btn-detail')?.getAttribute('data-desc') || '';
    }

    currentRequestId = requestId;
    openModal('transferHandlerModal');
}

// Handler untuk tombol Submit di bagian bawah modal
const btnSubmitTransfer = document.getElementById('btn-submit-transfer');
if (btnSubmitTransfer) {
    btnSubmitTransfer.addEventListener('click', function() {
        const targetStaffId = document.getElementById('transfer-staff-id').value;

        // Validasi
        if (!targetStaffId) {
            alert('Pilih staff tujuan transfer');
            return;
        }

        if (!confirm('Ingin mentransfer request ini ke staff IT terpilih?')) return;

        // Persiapkan data
        const fd = new FormData();
        fd.append('request_id', currentRequestId);
        fd.append('target_staff_id', targetStaffId);

        // Eksekusi ke file PHP (Pastikan nama filenya benar)
        fetch('direct/process_transfer_handler.php', { 
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('Berhasil: ' + res.message);
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan sistem saat menghubungi server.');
        });
    });
}

// ==========================================
// 2. UBAH STATUS 
// ==========================================
let tempStatusData = {};

// --- FUNGSI BARU: Update Dropdown Jenis Berdasarkan Tipe ---
function updateJenisDropdown(tipe, selectedJenis = "") {
    const selectJenis = document.getElementById('ubah-status-jenis');
    const groupHW = document.getElementById('group-kode-hardware');
    const inputHW = document.getElementById('ubah-status-kode-hw');

    if (!selectJenis) return;
    
    selectJenis.innerHTML = ''; // Kosongkan dulu
    
    // Tampilkan input Kode Hardware hanya jika Tipe = Hardware
    if (tipe === 'hardware') {
        if (groupHW) groupHW.style.display = 'block';
    } else {
        if (groupHW) groupHW.style.display = 'none';
        if (inputHW) inputHW.value = ''; // Reset nilai jika pindah ke software
    }
    
    // Ambil data dari objek dataKendala
    const list = dataKendala[tipe.toLowerCase()];
    if (list) {
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            
            // Logika pencocokan: Jika teks di DB/Tabel sama dengan list, set SELECTED
            if (item.trim() === selectedJenis.trim()) {
                opt.selected = true;
            }
            selectJenis.appendChild(opt);
        });
    }
}

// Event Listener: Tipe Kendala diubah
document.getElementById('ubah-status-tipe')?.addEventListener('change', function() {
    updateJenisDropdown(this.value);
});

// Event Listener: Toggle Detail Ketidaksesuaian
document.getElementById('ubah-status-ketidaksesuaian')?.addEventListener('change', function() {
    const detailField = document.getElementById('ubah-status-ketidaksesuaian-detail');
    detailField.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) detailField.value = "";
});

// Event Listener: "Terjadi di siapa (diri sendiri/orang lain)"
document.getElementById('ubah-status-terjadi')?.addEventListener('change', function() {
    const groupPenerima = document.getElementById('group-penerima-baru');  
    if (this.value === 'orang_lain') {
        groupPenerima.style.display = 'block';
    } else {
        groupPenerima.style.display = 'none';
        document.getElementById('ubah-status-penerima').value = ""; 
    }
});

// Listener untuk dropdown Semua / Beberapa / Satu
document.getElementById('ubah-terjadi-siapa')?.addEventListener('change', togglePenerimaGroup);

// Fungsi pembantu agar logika show/hide tidak duplikat
function togglePenerimaGroup() {
    const groupPenerima = document.getElementById('group-penerima-baru');
    const statusTerjadi = document.getElementById('ubah-status-terjadi').value;
    const terjadiSiapa = document.getElementById('ubah-terjadi-siapa').value;

    // Tampilkan jika Status Terjadi = 'orang_lain' 
    // ATAU Cakupan Terjadi Siapa = 'beberapa'
    if (statusTerjadi === 'orang_lain' || terjadiSiapa === 'beberapa') {
        groupPenerima.style.display = 'block';
    } else {
        groupPenerima.style.display = 'none';
        // Opsional: Reset nilai select penerima jika disembunyikan
        // document.getElementById('ubah-status-penerima').value = ""; 
    }
}

function openUbahStatus(requestId) {
    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
    if (!row) return;

    // Ambil data identitas
    const idPeminta = row.getAttribute('data-id-peminta');
    const idPenerima = row.getAttribute('data-penerima-id');
    const idStatus = parseInt(row.getAttribute('data-id-status'));
    const terjadiPada = row.getAttribute('data-terjadi-pada');
    
    // Ambil elemen badge S/H dari tabel (Copy element-nya agar class-nya ikut)
    const badgeUrgency = row.querySelector('td:nth-child(5)').innerHTML; // Mengambil HTML di kolom tipe
    const shCodeText = row.querySelector('.urgency-code')?.textContent.trim() || "";
    
    // Ambil teks dari kolom tabel
    const cells = row.querySelectorAll('td');
    const namaPeminta = cells[2].textContent.trim();
    const namaPenerima = cells[3].textContent.trim();
    const jenisTeks = cells[5].textContent.trim();

    // 1. Set Dropdown Tipe (Logic: Cek kata 'software' di dalam kode)
    const selectTipe = document.getElementById('ubah-status-tipe');
    const tipeKey = shCodeText.toLowerCase().includes('software') ? 'software' : 'hardware';
    selectTipe.value = tipeKey;
    
    // 2. Isi Dropdown Jenis & Handle Kode Hardware
    updateJenisDropdown(tipeKey, jenisTeks);

    // Jika hardware, isi input kode hardware jika datanya ada di atribut row
    if (tipeKey === 'hardware') {
        document.getElementById('ubah-status-kode-hw').value = row.getAttribute('data-kode-hw') || "";
    }

    // 3. Set Terjadi Di & Penerima
    const selectTerjadi = document.getElementById('ubah-status-terjadi');
    const groupPenerima = document.getElementById('group-penerima-baru');
    const selectPenerima = document.getElementById('ubah-status-penerima');

    if (idPeminta === idPenerima) {
        selectTerjadi.value = 'diri_sendiri';
        groupPenerima.style.display = 'none';
    } else {
        selectTerjadi.value = 'orang_lain';
        groupPenerima.style.display = 'block';
        selectPenerima.value = idPenerima;
    }

    // 4. Set Dropdown "Edit Terjadi Pada Siapa Saja" (Cakupan)
    const selectTerjadiSiapa = document.getElementById('ubah-terjadi-siapa');
    if (selectTerjadiSiapa) {
        selectTerjadiSiapa.value = terjadiPada || "";
    }

    // 5. Set Status Radio
    const valStatus = (idStatus === 4) ? 'done' : 'on-repair';
    const radioToClick = document.querySelector(`input[name="status"][value="${valStatus}"]`);
    if (radioToClick) radioToClick.checked = true;

    // 6. Isi field lainnya
    document.getElementById('ubah-status-deskripsi-val').value = row.getAttribute('data-deskripsi') || "";
    document.getElementById('ubah-status-tindakan').value = row.getAttribute('data-tindakan-it') || "";
    document.getElementById('ubah-status-hasil').value = row.getAttribute('data-hasil-it') || "";
    
    // --- UPDATE UI READ ONLY (Tampilan Atas Modal) ---
    document.getElementById('ubah-status-toko-val').textContent = cells[1].textContent.trim();
    document.getElementById('ubah-status-req-val').textContent = requestId;
    document.getElementById('ubah-status-peminta-val').textContent = namaPeminta;
    document.getElementById('ubah-status-penerima-val').textContent = namaPenerima;
    document.getElementById('ubah-status-terjadi-val').textContent = terjadiPada;
    
    const kodeHardware = row.getAttribute('data-kode-hw') || '';
    document.getElementById('ubah-status-kode-val').textContent = kodeHardware;
    
    // Gunakan innerHTML agar Badge S/H berwarna muncul di modal
    document.getElementById('ubah-status-tipe-val').innerHTML = badgeUrgency;
    document.getElementById('ubah-status-jenis-val').textContent = jenisTeks;

    togglePenerimaGroup();

    currentRequestId = requestId;
    openModal('ubahStatusModal');
}

// Event Listener Tombol Lanjut (Submit Modal Ubah Status)
document.getElementById('btn-submit-status').addEventListener('click', function() {
    const row = document.querySelector(`tr[data-request-id="${currentRequestId}"]`);
    if (!row) return;

    const tipe = document.getElementById('ubah-status-tipe').value;
    const idJenis = document.getElementById('ubah-status-jenis').value; 
    const kodeHardware = document.getElementById('ubah-status-kode-hw').value;  
    const terjadi = document.getElementById('ubah-status-terjadi').value;
    const selectPenerima = document.getElementById('ubah-status-penerima');
    
    let finalPenerimaId;
    let finalPenerimaNama;

    if (terjadi === 'orang_lain') {
        finalPenerimaId = selectPenerima.value;
        finalPenerimaNama = selectPenerima.options[selectPenerima.selectedIndex].text;
    } else {
        // Jika diri sendiri, gunakan data-id-peminta dari row tabel
        finalPenerimaId = row.getAttribute('data-id-peminta');
        finalPenerimaNama = document.getElementById('ubah-status-peminta-val').textContent;
    }

    // Validasi Dasar
    if (!idJenis) { alert("Jenis kendala tidak boleh kosong!"); return; }
    if (terjadi === 'orang_lain' && !finalPenerimaId) { alert("Pilih penerima baru!"); return; }

    // Simpan ke tempStatusData (Menyertakan kode_hw)
    tempStatusData = {
        request_id: currentRequestId,
        tindakan: document.getElementById('ubah-status-tindakan').value,
        hasil: document.getElementById('ubah-status-hasil').value,
        tipe: tipe,
        jenis: idJenis,
        kodeHardware: kodeHardware, 
        penerima_id: finalPenerimaId,
        terjadi_pada: document.getElementById('ubah-terjadi-siapa').value,
        status_baru: document.querySelector('input[name="status"]:checked').value,
        ketidaksesuaian_detail: document.getElementById('ubah-status-ketidaksesuaian').checked ? 
                                document.getElementById('ubah-status-ketidaksesuaian-detail').value : null
    };

    // ISI DATA READ-ONLY DI PASSWORD MODAL SEBELUM DIBUKA
    document.getElementById('password-peminta-val').textContent = document.getElementById('ubah-status-peminta-val').textContent;
    document.getElementById('password-user-val').textContent = finalPenerimaNama;
    document.getElementById('password-action-val').textContent = tempStatusData.tindakan || '';
    document.getElementById('password-result-val').textContent = tempStatusData.hasil || '';
    document.getElementById('password-status-val').textContent = tempStatusData.status_baru.toUpperCase();

    // TAMBAHKAN INI: Update label input password agar menyebutkan nama user
    const pwdLabel = document.querySelector('.password-section .form-label');
    if (pwdLabel) {
        pwdLabel.innerHTML = `Masukkan Password User <strong style="color: #0056b3;">${finalPenerimaNama}</strong>:`;
    }

    closeModal('ubahStatusModal');
});

$(document).ready(function() {
    $('.select2-multiple').select2({
        placeholder: "-- Pilih Toko --",
        allowClear: true,
        closeOnSelect: false, // Penting: Agar tidak tertutup pas klik checkbox
        width: '100%',
        templateResult: function (data) {
            // Jika ini placeholder, jangan kasih checkbox
            if (!data.id) return data.text;

            // Buat elemen checkbox + teks
            var $result = $(
                '<div class="checkbox-item">' +
                    '<input type="checkbox" ' + (data.selected ? 'checked' : '') + ' /> ' +
                    '<label style="margin-left: 10px; cursor: pointer;">' + data.text + '</label>' +
                '</div>'
            );
            return $result;
        },
        templateSelection: function (data) {
            // Tulisan yang muncul di kotak dropdown setelah dipilih
            return data.text;
        }
    });

    // Event agar saat baris diklik, checkbox-nya sinkron
    $('.select2-multiple').on('select2:select select2:unselect', function (e) {
        $(this).select2('open'); // Paksa dropdown tetap terbuka
    });
});

// Saat menangkap data untuk dikirim ke process_request.php
function getFormData() {
    const selectedToko = $('#toko').val();  

    if (selectedToko && selectedToko.length > 0) {
        selectedToko.forEach(id => {
            // Kirim ke PHP dengan nama toko_id[]
            formData.append('toko_id[]', id); 
        });
    }
}

// Logic Validasi & Final Submit
const confirmCheck = document.getElementById('confirm-checkbox');
const inputPass = document.getElementById('input-password');
const btnFinalSubmit = document.getElementById('btn-final-submit');

function validateFinalForm() {
    // Tombol aktif jika checkbox dicentang DAN password diisi
    btnFinalSubmit.disabled = !(confirmCheck.checked && inputPass.value.length > 0);
}

confirmCheck.addEventListener('change', validateFinalForm);
inputPass.addEventListener('input', validateFinalForm);

btnFinalSubmit.addEventListener('click', () => {
    const fd = new FormData();
    fd.append('password', inputPass.value);
    fd.append('data', JSON.stringify(tempStatusData));

    // Memanggil file proses
    fetch('direct/process_update_status.php', {  
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan sistem.');
    });
});

// --- 3. FITUR SHOW/HIDE PASSWORD (DISESUAIKAN DENGAN ID HTML BARU) ---
document.getElementById('toggle-password')?.addEventListener('click', function() {
    const input = document.getElementById('input-password');
    const icon = this.querySelector('.eye-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '🔒'; 
    } else {
        input.type = 'password';
        icon.textContent = '👁️'; 
    }
});

// =========================
// INITIALIZE DASHBOARD
// =========================

document.addEventListener('DOMContentLoaded', () => {
    // ===== ACTION DROPDOWN BUTTON =====
    document.querySelector('.action-btn')
        ?.addEventListener('click', toggleActionDropdown);

    // ===== CLOSE DROPDOWN OUTSIDE CLICK =====
    document.addEventListener('click', () => {
        if (currentDropdown) {
            currentDropdown.remove();
            currentDropdown = null;
        }
    });

    // Helper function to reset form
    function updateRequestStatus(requestId, statusText) {
        if (!requestId) return;
        
        const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
        if (!row) return;
        
        const statusCard = row.querySelector('.status-card');
        if (!statusCard) return;
        
        // Set teks baru (misal: "Done")
        statusCard.textContent = statusText;
        
        // Buat nama class berdasarkan teks (misal: "status-done")
        const cleanStatus = statusText.toLowerCase().trim().replace(/\s+/g, '-');
        
        // Ganti class secara total agar warna CSS di atas langsung aktif
        statusCard.className = `status-card status-${cleanStatus}`;
    }

    // =========================
    // BUTTON EVENT LISTENERS
    // =========================
    document.querySelectorAll('.open-detail-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            
            fillAndOpenDetail(button.dataset);
        });
    });

    // ==========================================
    // PUSAT EKSEKUSI ACCEPT (SINGLE & BULK)
    // ==========================================
    function executeAcceptAction(ids, action = 'accept', urgencyLevel = null) {
        if (!ids || ids.length === 0) {
            alert('Tidak ada item yang dipilih.');
            return;
        }

        const fd = new FormData();
        fd.append('action', action);
        fd.append('request_ids', JSON.stringify(ids));
        
        if (action === 'change_urgency' && urgencyLevel) {
            fd.append('urgency_level', urgencyLevel);
        }

        fetch('direct/process_accept.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(res.message || 'Berhasil diproses');
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Terjadi kesalahan sistem.');
        });
    }

    // accept modal
    const btnAcceptFromModal = document.getElementById('btnAcceptFromModal');
    if (btnAcceptFromModal) {
        btnAcceptFromModal.addEventListener('click', () => {
            const requestId = btnAcceptFromModal.dataset.id;
            if (!confirm('Konfirmasi Accept Request ini?')) return;
            
            // Panggil fungsi pusat dengan membungkus ID ke dalam Array [requestId]
            executeAcceptAction([requestId]); 
        });
    }

    // =========================
    // ACTION DROPDOWN
    // =========================

    function toggleActionDropdown(e) {
        e.stopPropagation();

        const actionBtn = document.querySelector('.action-btn');
        if (!actionBtn) return;

        // toggle close
        if (currentDropdown) {
            currentDropdown.remove();
            currentDropdown = null;
            return;
        }

        currentDropdown = document.createElement('div');
        currentDropdown.className = 'action-dropdown';
        currentDropdown.innerHTML = `
            <div class="dropdown-option" data-action="accept">Accept</div>
            <div class="dropdown-option" data-action="change-urgency">Ganti Level Urgensi</div>
        `;

        const rect = actionBtn.getBoundingClientRect();
        currentDropdown.style.cssText = `
            position: fixed;
            top: ${rect.bottom + 5}px;
            left: ${rect.left}px;
            background: white;
            border: 1px solid var(--light-gray);
            border-radius: var(--radius-sm);
            box-shadow: 0 4px 12px var(--shadow-medium);
            z-index: 1000;
            min-width: 180px;
            overflow: hidden;
            animation: fadeIn 0.2s ease;
        `;

        document.body.appendChild(currentDropdown);

        currentDropdown.querySelectorAll('.dropdown-option').forEach((option, idx) => {
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

            option.addEventListener('click', (ev) => {
                ev.stopPropagation();

                // 🔥 AMBIL CHECKBOX TERBARU DI SINI
                const selected = document.querySelectorAll(
                    '.queue-item input[type="checkbox"]:checked'
                );

                handleBulkAction(option.dataset.action, selected);

                currentDropdown.remove();
                currentDropdown = null;
            });

            if (idx === currentDropdown.children.length - 1) {
                option.style.borderBottom = 'none';
            }
        });

        // klik luar → close
        setTimeout(() => {
        }, 10);
    }

    const selectAllBtn = document.getElementById('select-all');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.queue-item input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
 
    // =========================
    // BULK ACTION HANDLER
    // =========================
    function handleBulkAction(action, checkboxes) {
        if (checkboxes.length === 0) {
            alert('Pilih minimal satu request');
            return;
        }

        const list = document.getElementById('bulkRequestList');
        list.innerHTML = '';

        checkboxes.forEach(cb => {
            let store, peminta, penerima, shBadge, jenis;

            // Cek apakah checkbox berasal dari CARD (Queue Items)
            const card = cb.closest('.queue-item');
            // Cek apakah checkbox berasal dari TABEL (All Request)
            const row = cb.closest('tr');

            if (card) {
                // Ambil data dari atribut data- button detail yang ada di dalam card
                const btn = card.querySelector('.open-detail-btn');
                store = card.querySelector('.queue-store')?.textContent.trim() || '-';
                peminta = card.querySelector('.queue-name')?.textContent.trim() || '-';
                penerima = btn.getAttribute('data-penerima') || '-';
                shBadge = btn.getAttribute('data-sh') || '-';
                jenis = btn.getAttribute('data-jenis') || '-';
            } else if (row) {
                // Ambil data dari kolom tabel (DataTables)
                store = row.cells[1].textContent.trim();
                peminta = row.cells[2].textContent.trim();
                penerima = row.cells[3].textContent.trim();
                shBadge = row.cells[4].innerHTML;
                jenis = row.cells[5].textContent.trim();
            }

            if (store) {
                list.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td><b>${store}</b></td>
                        <td>${peminta}</td>
                        <td>${penerima}</td>
                        <td><div class="sh-badge-wrapper">${shBadge}</div></td>
                        <td>${jenis}</td>
                    </tr>
                `);
            }
        });

        // Update Judul Modal
        const titleEl = document.getElementById('bulkModalTitle');
        const btnConfirm = document.getElementById('btnConfirmBulk');
        
        if (action === 'accept') {
            titleEl.textContent = 'Konfirmasi Accept Request';
            btnConfirm.textContent = 'Accept Now';
            btnConfirm.className = 'btn-accept'; // Sesuaikan class warna
        } else {
            titleEl.textContent = 'Konfirmasi Ganti Urgensi';
            btnConfirm.textContent = 'Update Urgensi';
            btnConfirm.className = 'btn-status'; // Sesuaikan class warna
        }
        
        document.getElementById('urgencyInputContainer').style.display = 
            action === 'change-urgency' ? 'block' : 'none';

        openModal('modalBulkAction');
    }

    const btnConfirmBulk = document.getElementById('btnConfirmBulk');
    if (btnConfirmBulk) {
        btnConfirmBulk.addEventListener('click', executeBulkAction);
    }

    // =========================
    // EXECUTE BULK
    // =========================
    function executeBulkAction() {
        const selected = document.querySelectorAll('.queue-item input[type="checkbox"]:checked');
        const ids = Array.from(selected).map(cb => cb.value);
        
        // Tentukan action berdasarkan judul modal atau state
        const isUrgency = document.getElementById('urgencyInputContainer').style.display === 'block';
        const action = isUrgency ? 'change_urgency' : 'accept';
        const urgencyLevel = isUrgency ? document.getElementById('bulkUrgencySelect').value : null;

        if (ids.length === 0) return;

        if (!confirm(`Konfirmasi proses ${ids.length} data terpilih?`)) return;

        // Panggil fungsi pusat fetch yang sudah Anda buat
        executeAcceptAction(ids, action, urgencyLevel);
    }

    syncQueueUrgencyColors();

    function syncQueueUrgencyColors() {
        document.querySelectorAll('.queue-item').forEach(item => {
            const badge = item.querySelector('.urgency-badge');
            if (!badge) return;

            // hapus class lama
            item.classList.remove('very-high', 'high', 'medium', 'low');

            // cari urgensi dari badge
            const urgency = [...badge.classList].find(cls =>
                ['very-high', 'high', 'medium', 'low'].includes(cls)
            );

            if (urgency) {
                item.classList.add(urgency);
            }
        });
    }
    
    // =========================
    // DATATABLES EVENT HANDLERS
    // =========================
    
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#requestTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 25, 50, 100],
            ordering: true,
            searching: true,
            info: true,
            columnDefs: [
                { 
                    orderable: false, 
                    targets: [8] 
                },
                { 
                    className: 'dt-center', 
                    targets: [0] 
                }
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
        
        // Adjust DataTable on window resize
        window.addEventListener('resize', () => {
            if (table) {
                table.columns.adjust().responsive.recalc();
            }
        });
    });
});

function updateUrgencyAutomaticly() {
    const dampak = document.getElementById('ubah-terjadi-siapa').value;
    const tampilanUrgensi = document.getElementById('tampilan-urgensi');
    const hiddenUrgensi = document.getElementById('ubah-urgensi');

    let level = "";
    let color = "";

    // Logika Otomatis Sistem
    switch (dampak) {
        case 'semua':
            level = "Very-High";
            color = "#d9534f"; // Merah
            break;
        case 'beberapa':
            level = "High";
            color = "#f0ad4e"; // Oranye
            break;
        case 'satu':
            level = "Medium";
            color = "#5bc0de"; // Biru
            break;
        case 'opsi_lain':
            level = "Low";
            color = "#5cb85c"; // Hijau
            break;
        default:
            level = "";
            color = "#666";
    }

    if (tampilanUrgensi && hiddenUrgensi) {
        tampilanUrgensi.value = level ? level.replace('-', ' ') : ""; 
        tampilanUrgensi.style.color = color;
        hiddenUrgensi.value = level; // Nilai ini yang akan masuk ke database
    }
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
    
    /* Modal show/hide animation */
    .modal {
        display: none;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .modal.show {
        display: flex;
        opacity: 1;
    }
`;
document.head.appendChild(style);

// JAVASCRIPT HOME (ROLE USER)

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() { 
    
    // Initialize unrated counter
    updateUnratedCount();
    
    // Initialize animations
    initAnimations(); 
    
    // Dashboard action dropdown functionality (jika ada di halaman)
    initActionDropdown();
    
    // Dashboard select all functionality (jika ada di halaman)
    initSelectAll();
    
    // Make table rows clickable (jika ada di halaman)
    initClickableTableRows();
});

function cancelRequest(requestId) {
    if (!confirm('Apakah Anda yakin ingin membatalkan request ini?')) {
        return;
    }

    const formData = new FormData();
    formData.append('request_id', requestId);  

    fetch('direct/process_cancel.php', {
        method: 'POST',
        body: formData  
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();  
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Terjadi kesalahan sistem.');
    });
}

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

// ====== ADAPTER DATA DARI PHP ======
let unratedRequests = [];

if (typeof unratedRequestsRaw !== 'undefined') {
    unratedRequests = unratedRequestsRaw.map(r => ({
        id: r.id,
        title: r.jenis_kendala || '-',
        staff: r.staff_name || '-',
        date: formatDate(r.input_datetime),
        deskripsi: r.description || '-',
        hasilService: r.hasil_it || '-',
        userRating: 0
    }));
}

// ====== HELPER ======
function formatDate(dt) {
    if (!dt) return '-';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID');
}

// ========== UNRATED MODAL FUNCTIONS ==========
function updateUnratedCount() {
    const badge = document.getElementById('unratedCount');
    if (!badge) return;

    badge.textContent = unratedRequests.length;

    if (unratedRequests.length > 0) {
        badge.classList.add('pulse');
        badge.style.display = 'inline-block';
    } else {
        badge.classList.remove('pulse');
        badge.style.display = 'none';
    }
}

function lihatUnrated() {
    openUnratedModal();
}

function openUnratedModal() {
    const modal = document.getElementById('unratedModal');
    if (!modal) return;

    document.body.classList.add('modal-open');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    renderUnratedList();
}

function closeUnratedModal() {
    const modal = document.getElementById('unratedModal');
    if (!modal) return;

    document.body.classList.remove('modal-open');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function renderUnratedList() {
    const listContainer = document.getElementById('unratedList');
    if (!listContainer) return;

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

    unratedRequests.forEach(request => {
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

                <div class="service-detail">
                    <div class="detail-header">
                        <i class="fas fa-exclamation-circle"></i>
                        <h5>Deskripsi Kendala</h5>
                    </div>
                    <div class="detail-content">
                        <p>${request.deskripsi}</p>
                    </div>
                </div>

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
                        <i class="fas fa-star" onclick="setRating(${request.id},1)"></i>
                        <i class="fas fa-star" onclick="setRating(${request.id},2)"></i>
                        <i class="fas fa-star" onclick="setRating(${request.id},3)"></i>
                        <i class="fas fa-star" onclick="setRating(${request.id},4)"></i>
                        <i class="fas fa-star" onclick="setRating(${request.id},5)"></i>
                    </div>

                    <div class="rating-comment">
                        <textarea id="comment-${request.id}"
                                  rows="2"
                                  placeholder="Tulis komentar atau feedback (opsional)..."></textarea>
                    </div>

                    <div class="rating-actions">
                        <button class="rating-btn submit"
                                id="submit-btn-${request.id}"
                                onclick="submitRating(${request.id})"
                                disabled>
                            <i class="fas fa-check"></i> Kirim Rating
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

function setRating(requestId, rating) {
    const request = unratedRequests.find(r => r.id === requestId);
    if (!request) return;

    request.userRating = rating;

    highlightStars(requestId, rating);
    enableSubmitButton(requestId);
}

function highlightStars(requestId, rating) {
    const starsContainer = document.getElementById(`stars-${requestId}`);
    if (!starsContainer) return;

    const stars = starsContainer.querySelectorAll('.fas.fa-star');

    stars.forEach((star, index) => {
        star.style.color = index < rating ? '#FFA726' : '#ddd';
    });
}

function enableSubmitButton(requestId) {
    const submitBtn = document.getElementById(`submit-btn-${requestId}`);
    if (submitBtn) {
        submitBtn.disabled = false;
    }
}

function submitRating(requestId) {
    const requestIndex = unratedRequests.findIndex(r => r.id === requestId);
    if (requestIndex === -1) return;

    const request = unratedRequests[requestIndex];
    const comment = document.getElementById(`comment-${requestId}`)?.value || '';
    const rating = request.userRating || 0;

    if (rating === 0) {
        alert('Pilih rating terlebih dahulu!');
        return;
    }

    if (!confirm(`Kirim rating ${rating} bintang untuk "${request.title}"?`)) {
        return;
    }

    // TODO: AJAX ke backend di sini

    showNotification(
        `Rating ${rating} bintang berhasil dikirim untuk: ${request.title}`,
        'success'
    );

    setTimeout(() => {
        removeUnratedRequest(requestId);
    }, 800);
}

function removeUnratedRequest(requestId) {
    unratedRequests = unratedRequests.filter(r => r.id !== requestId);
    updateUnratedCount();
    renderUnratedList();
}

// ====== INIT BADGE ======
document.addEventListener('DOMContentLoaded', updateUnratedCount);

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
    const group = document.getElementById('namaLainGroup');
    const inputSearch = document.getElementById('user_search');
    const inputId = document.getElementById('user_penerima_id');

    if (!group) return; // Proteksi awal

    if (show) {
        group.style.display = 'block';
        if (inputSearch) inputSearch.required = true;
    } else {
        group.style.display = 'none';
        if (inputSearch) {
            inputSearch.required = false;
            inputSearch.value = '';
        }
        if (inputId) inputId.value = ''; // Reset ID
    }
}

function toggleHardwareFields(isHardware) {
    const kodeHardwareGroup = document.getElementById('kodeHardwareGroup');
    const selectJenisKendala = document.getElementById('jenis_kendala');
    
    // 1. Tampilkan/Sembunyikan input Kode Hardware
    if (isHardware) {
        kodeHardwareGroup.style.display = 'block';
    } else {
        kodeHardwareGroup.style.display = 'none';
        const inputHW = document.getElementById('kode_hardware');
        if(inputHW) inputHW.value = '';
    }

    // 2. Isi Dropdown Jenis Kendala secara Dinamis
    const tipe = isHardware ? 'Hardware' : 'Software';  
    
    // Ambil data dari variabel global dataKendala
    const listKendala = dataKendala[tipe] || [];

    // Kosongkan dropdown sebelum diisi ulang
    selectJenisKendala.innerHTML = '<option value="">-- Pilih Jenis Kendala --</option>';

    // Loop data untuk membuat elemen <option>
    listKendala.forEach(item => {
        const option = document.createElement('option');
        // Gunakan nama_kendala sesuai struktur SQL kamu
        option.value = item.nama; 
        option.textContent = item.nama;
        selectJenisKendala.appendChild(option);
    });
}

// Tambahkan alias agar tombol radio di HTML tidak error saat memanggil updateJenisKendala
function updateJenisKendala(tipe) {
    const isHardware = (tipe.toLowerCase() === 'hardware');
    toggleHardwareFields(isHardware);
}

// Tambahkan inisialisasi agar saat halaman baru dibuka, dropdown Software langsung terisi
document.addEventListener('DOMContentLoaded', function() {
    if (typeof dataKendala !== 'undefined') {
        // Secara default pilih Software (sesuai atribut 'checked' di HTML kamu)
        toggleHardwareFields(false);
    } else {
        console.error("Variabel dataKendala belum terdefinisi. Pastikan PHP mengirim datanya.");
    }
});
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

function showAttachment(fileName) {
    const container = document.getElementById('attachment-container');
    if (!fileName) {
        container.innerHTML = `
            <div class="foto-placeholder">
                <i class="fas fa-file-excel" style="font-size: 24px; color: #ccc;"></i>
                <span>Tidak ada lampiran</span>
            </div>`;
        return;
    }

    const ext = fileName.split('.').pop().toLowerCase();
    const filePath = 'uploads/' + fileName;

    if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        // Jika Gambar, tampilkan preview dan link buka
        container.innerHTML = `
            <div class="foto-preview">
                <img src="${filePath}" style="max-width: 100%; border-radius: 8px; cursor: pointer;" onclick="window.open('${filePath}')">
                <p style="font-size: 12px; margin-top: 5px;">Klik gambar untuk memperbesar</p>
            </div>`;
    } else {
        // Jika Dokumen (PDF, Excel, dll), tampilkan icon dan tombol buka
        let icon = 'fa-file-alt';
        let color = '#555';
        
        if (ext === 'pdf') { icon = 'fa-file-pdf'; color = '#e74c3c'; }
        else if (['xls', 'xlsx'].includes(ext)) { icon = 'fa-file-excel'; color = '#27ae60'; }
        else if (['doc', 'docx'].includes(ext)) { icon = 'fa-file-word'; color = '#2980b9'; }

        container.innerHTML = `
            <div class="file-link-box" onclick="window.open('${filePath}')" 
                 style="display: flex; align-items: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; background: #f9f9f9;">
                <i class="fas ${icon}" style="font-size: 30px; color: ${color}; margin-right: 15px;"></i>
                <div>
                    <strong style="display: block;">${fileName}</strong>
                    <span style="font-size: 12px; color: #666;">Klik untuk membuka/unduh file</span>
                </div>
            </div>`;
    }
}

function resetForm() {
    const form = document.querySelector('.request-form');
    if (form) form.reset();

    // Reset preview gambar jika ada
    const fileName = document.getElementById('fileName');
    if (fileName) fileName.textContent = 'Masukkan SS/Image';
 
    const results = document.getElementById('search_results');
    if (results) {
        results.innerHTML = '';
        results.style.display = 'none';
    }
    
    // Sembunyikan kembali input "Orang Lain"
    const namaLainGroup = document.getElementById('namaLainGroup');
    if (namaLainGroup) namaLainGroup.style.display = 'none';
}

function submitRequestForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData();

    // 1. Validasi Toko (Gunakan querySelector yang spesifik ke form ini)
    const selectedCheckboxes = form.querySelectorAll('input[name="toko_id[]"]:checked');
    if (selectedCheckboxes.length === 0) {
        alert("Peringatan: Anda harus memilih minimal satu Toko!");
        return;
    }
    selectedCheckboxes.forEach(cb => formData.append('toko_id[]', cb.value));

    // 2. User Penerima
    const radioUntukSiapa = form.querySelector('input[name="untuk_siapa"]:checked');
    const untukSiapa = radioUntukSiapa ? radioUntukSiapa.value : 'diri_sendiri';
    let userPenerimaId = "";

    if (untukSiapa === 'orang_lain') {
        const elPenerima = document.getElementById('user_penerima_id');
        userPenerimaId = elPenerima ? elPenerima.value : "";
        if (!userPenerimaId) {
            alert("Pilih user penerima!"); return;
        }
    }

    formData.append('untuk_siapa', untukSiapa);
    formData.append('user_penerima', userPenerimaId);
    
    // 3. Ambil Data Lain (Pastikan ID-nya benar di HTML)
    formData.append('tipe_kendala', form.querySelector('input[name="tipe_kendala"]:checked').value);
    formData.append('jenis_kendala', document.getElementById('jenis_kendala').value);
    formData.append('kode_hardware', document.getElementById('kode_hardware').value);
    formData.append('terjadi_pada', document.getElementById('terjadi_di').value); 
    formData.append('deskripsi', document.getElementById('deskripsi').value);

    // 4. File
    const fileInput = document.getElementById('fileUpload');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('foto', fileInput.files[0]);
    } 

    fetch('direct/process_request.php', {
        method: 'POST',
        body: formData
    })
    .then(async res => { 
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            console.error("Server Response bukan JSON:", text);
            throw new Error("Server mengirim respon tidak valid (Error 500/HTML)");
        }
    })
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert("Gagal: " + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Terjadi kesalahan sistem: " + err.message);
    });
}

function searchUser(query) {
    const resultsContainer = document.getElementById('search_results');
    const inputId = document.getElementById('user_penerima_id');
    const inputSearch = document.getElementById('user_search');

    // Proteksi jika elemen tidak ditemukan di HTML
    if (!resultsContainer) return;

    // Jika input kosong atau cuma 1 huruf, tutup hasil
    if (query.length < 2) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        return;
    }

    // Ambil data dari get_users.php
    fetch(`direct/get_users.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultsContainer.innerHTML = '';
            
            if (data && data.length > 0) {
                data.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'search-item';
                    div.style.padding = '10px';
                    div.style.cursor = 'pointer';
                    div.style.borderBottom = '1px solid #eee';
                    div.textContent = user.username;
                    
                    // Saat salah satu nama di klik
                    div.onclick = function() {
                        if (inputSearch) inputSearch.value = user.username;
                        if (inputId) inputId.value = user.id;
                        resultsContainer.style.display = 'none';
                    };
                    resultsContainer.appendChild(div);
                });
                resultsContainer.style.display = 'block';
            } else {
                resultsContainer.innerHTML = '<div style="padding:10px;">User tidak ditemukan</div>';
                resultsContainer.style.display = 'block';
            }
        })
        .catch(err => {
            console.error("Gagal load user:", err);
        });
}

function selectUser(id, username) {
    console.log("Memilih User ID:", id); // Cek di console apakah ID muncul
    document.getElementById('user_search').value = username;
    document.getElementById('user_penerima_id').value = id; // Ini yang dikirim ke PHP
    document.getElementById('search_results').style.display = 'none';
}

// Menutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    if (e.target.id !== 'user_search') {
        document.getElementById('search_results').style.display = 'none';
    }
});
 
function closeDashboardModal() {
    const detailModal = document.getElementById('requestDetailModal');
    if (detailModal) {
        detailModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ========== ACTION DROPDOWN FUNCTIONS ==========
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
/**
 * MASTER DATA FUNCTIONALITY - FINAL OPTIMIZED
 * - EDIT MODAL DATASET BASED
 * - ROLE EDIT: MAPPING KODE TOKO (FIX PRELOAD)
 */

let currentTab = 'toko';
let dataTableInstance = null;
let currentEditId = null;

/* =========================
   INIT
========================= */
document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initModals();
    loadTabContent(currentTab);

    $('#btnAddMaster').on('click', openAddModal);
    $('#addMasterModal .btn-submit').on('click', saveAddData);
    $('#editModal .btn-submit').on('click', updateData);

    initRoleKeyHandlers();
    initMultiDropdown();
    initRoleDropdowns();
});

/* =========================
   TAB HANDLER
========================= */
function initTabs() {
    $('.tab-button').click(function(){
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        currentTab = $(this).data('tab');
        loadTabContent(currentTab);
    });
}

/* =========================
   LOAD DATA
========================= */
function loadTabContent(tab) {
    const container = $('.table-container');
    container.html('<p style="padding:20px;">Loading...</p>');

    $.getJSON('direct/get_master_data.php', { tab })
        .done(res => renderTable(tab, res || []))
        .fail(() => container.html('Gagal load data'));
}

/* =========================
   RENDER TABLE
========================= */
function renderTable(tab, data) {
    const container = $('.table-container');
    container.html(`<table id="table-${tab}" class="display nowrap"></table>`);

    if (dataTableInstance) dataTableInstance.destroy();

    let columns = [{ title: 'No', data: null, render: (_, __, ___, m) => m.row + 1 }];
    if (tab === 'toko') columns.push({ title: 'Nama', data: 'nama' }, { title: 'Kode', data: 'kode' });
    if (tab === 'karyawan') columns.push({ title: 'Nama', data: 'nama' }, { title: 'Toko', data: 'toko_text' });
    if (tab === 'jenis_kendala')
        columns.push(
            { title: 'Tipe', data: 'tipe' },
            { title: 'Jenis', data: 'jenis' },
            { title: 'Turunan', data: 'turunan' }
        );
    if (tab === 'role') columns.push({ title: 'Role', data: 'nama' });

    columns.push({
        title: 'Aksi',
        data: null,
        render: (_, __, r) => `
        <button class="action-edit edit-btn"
            data-id="${r.id}"
            data-tab="${tab}"
            data-nama="${r.nama}"
            data-kode="${r.kode || ''}"
            data-toko="${r.toko_id || ''}"
            data-tipe="${r.tipe || ''}"
            data-jenis="${r.jenis || ''}"
            data-turunan="${r.turunan || ''}">
            Edit
        </button>
        <button class="action-delete"
            onclick="deleteData('${tab}',${r.id})">
            Hapus
        </button>`
    });

    dataTableInstance = $(`#table-${tab}`).DataTable({
        data,
        columns,
        responsive: true,
        drawCallback: bindEdit
    });
}

/* =========================
   ADD MODAL
========================= */
function openAddModal() {
    $('#addMasterModal').addClass('show');
    $('.master-form').hide();

    const map = { toko:'formToko', karyawan:'formKaryawan', jenis_kendala:'formJenisKendala', role:'formRole' };
    $('#' + map[currentTab]).show();

    if(currentTab === 'karyawan') loadMasterOptions('toko', 'addTokoKaryawan');
}

/* =========================
   SAVE DATA (FIXED VERSION)
========================= */
function saveAddData() {
    let payload = { tab: currentTab };

    if (currentTab === 'toko') {
        payload.nama = $('#addNamaToko').val();
        payload.kode = $('#addKodeToko').val();
    }

    if (currentTab === 'karyawan') {
        payload.nama = $('#addNamaKaryawan').val();
        payload.toko = $('#addTokoKaryawan').val();
    }

    if (currentTab === 'jenis_kendala') {
        payload.tipe = $('#addTipeKendala').val();
        payload.jenis = $('#addJenisKendala').val();
        payload.turunan = $('input[name="addTurunan"]:checked').val() || 'Tidak';
    }

if(currentTab === 'role'){
        payload.nama = $('#addNamaRole').val();
        payload.keys = {};

        // 1. Debug Boolean
        console.log("Gathering Boolean Keys...");
        const boolKeys = ['enable', 'update_toko', 'update_role', 'handling_request'];
        boolKeys.forEach(k => {
            const isChecked = $(`#addMasterModal input[name="roleBoolKeys[]"][value="${k}"]`).prop('checked');
            payload.keys[k] = isChecked ? 1 : 0;
            console.log(`Bool [${k}]: ${payload.keys[k]}`);
        });

        // 2. Debug String (Toko)
        console.log("Gathering String Keys...");
        const stringKeys = ['manage_user','update_user_toko','input_request','cancel_request','update_request','delete_request'];
        stringKeys.forEach(k => {
            payload.keys[k] = getStringKeyValue(k, false);
        });

        payload.keys.expiration_date = $('#roleForever').prop('checked') ? 'forever' : $('#roleExpirationDate').val();
        console.log("Expiration:", payload.keys.expiration_date);
    }

    console.log("FINAL PAYLOAD TO SEND:", payload);

    $.post('direct/add_master_data.php', payload)
        .done((res) => { 
            console.log("SERVER RESPONSE:", res);
            alert('Saved'); 
            $('#addMasterModal').removeClass('show'); 
            loadTabContent(currentTab); 
        })
        .fail(e => {
            console.error("SAVE FAILED!");
            console.error("Status:", e.status);
            console.error("Response:", e.responseText);
            alert(e.responseText || 'Gagal simpan');
        });
}

/* =========================
   INIT ROLE DROPDOWN STRING KEYS
========================= */
function initRoleDropdowns() {
    const stringKeys = ['manage_user','update_user_toko','input_request','cancel_request','update_request','delete_request'];
    stringKeys.forEach(k=>{
        const cb = $('#key_'+k);
        const wrapper = $('#value_'+k);
        if(!cb.length || !wrapper.length) return;

        cb.change(()=>{
            wrapper.toggleClass('disabled', !cb.prop('checked'));
            if(cb.prop('checked')){
                wrapper.empty();
                const dropdown = $(`<div class="multi-dropdown"><div class="dropdown-header">Pilih Toko</div><div class="dropdown-list" id="${k}TokoList"></div></div>`);
                wrapper.append(dropdown);
                const list = dropdown.find('.dropdown-list');

                $.getJSON('direct/get_master_option.php',{type:'toko'}, res=>{
                    list.html('<label><input type="checkbox" value="all"><strong>Semua Toko</strong></label><hr>');
                    res.forEach(t=> list.append(`<label><input type="checkbox" value="${t.kode}">${t.text}</label>`));
                    bindMultiDropdownLogic(list[0]);
                });
            } else wrapper.html('');
        });
    });
}

/* =========================
   MULTI DROPDOWN LOGIC
========================= */
function bindMultiDropdownLogic(container){
    const allCb = container.querySelector('input[value="all"]');
    const tokoCbs = container.querySelectorAll('input[type="checkbox"]:not([value="all"])');
    allCb?.addEventListener('change', ()=>{ 
        tokoCbs.forEach(cb=>{ 
            cb.checked=false; 
            cb.disabled=allCb.checked; 
        }); 
    });
    tokoCbs.forEach(cb=>cb.addEventListener('change', ()=>{ if(cb.checked) allCb.checked=false; }));
}

/* =========================
   ROLE HANDLER BOOLEAN + EXPIRATION
========================= */
function initRoleKeyHandlers(){
    const roleForever = $('#editRoleForever');
    const roleExpirationDate = $('#editRoleExpirationDate');

    roleForever.change(()=>{
        roleExpirationDate.prop('disabled', roleForever.prop('checked'));
        if(roleForever.prop('checked')) roleExpirationDate.val('');
    });
}

/* =========================
   MULTI DROPDOWN TOGGLE
========================= */
function initMultiDropdown(){
    $(document).click(e=>{
        $('.multi-dropdown').each((i,dd)=>{ if(!dd.contains(e.target)) $(dd).removeClass('open'); });
        
        const header = e.target.closest('.multi-dropdown .dropdown-header');
        if(!header) return;
        e.stopPropagation();
        $(header).closest('.multi-dropdown').toggleClass('open');
    });
}

/* =========================
   BIND EDIT BUTTON
========================= */
function bindEdit(){
    $('.edit-btn').off('click').on('click', function(){
        const d = this.dataset;
        currentEditId = d.id;

        $('#editModal').addClass('show').data('edit-tab', d.tab);
        $('#editModal .master-form').hide();

        const map = { toko:'editFormToko', karyawan:'editFormKaryawan', jenis_kendala:'editFormJenisKendala', role:'editFormRole' };
        $('#'+map[d.tab]).show();

        if(d.tab==='toko'){
            $('#editNamaToko').val(d.nama);
            $('#editKodeToko').val(d.kode);
        }

        if(d.tab==='karyawan'){
            $('#editNamaKaryawan').val(d.nama);
            $.getJSON('direct/get_master_option.php',{type:'toko'}, res=>{
                const s = $('#editTokoKaryawan');
                s.html('<option value="">-- Pilih --</option>');
                res.forEach(i=> s.append(`<option value="${i.id}">${i.text}</option>`));
                s.val(d.toko);
            });
        }

        if(d.tab==='jenis_kendala'){
            $('#editTipeKendala').val(d.tipe);
            $('#editJenisKendala').val(d.jenis);
            $(`input[name="editTurunan"][value="${d.turunan}"]`).prop('checked', true);
        }

        if(d.tab==='role'){
            preloadEditRole(d.id, d.nama);
        }
    });
}

/* =========================
   PRELOAD EDIT ROLE (FIXED MAPPING)
========================= */
function preloadEditRole(roleId, roleName){
    const editForm = $('#editFormRole');
    $('#editNamaRole').val(roleName);

    // Reset State
    editForm.find('input[type="checkbox"]').prop('checked', false);
    editForm.find('.key-value-wrapper').addClass('disabled').empty();
    $('#editRoleForever').prop('checked', false);
    $('#editRoleExpirationDate').val('').prop('disabled', false);

    $.getJSON('direct/get_permission_role.php',{role_id:roleId}, res=>{
        // 1. BOOLEAN
        if(res.bool) {
            Object.entries(res.bool).forEach(([k, v]) => {
                const el = editForm.find(`input[name="roleBoolKeys[]"][value="${k}"]`);
                if(el.length) el.prop('checked', parseInt(v) === 1);
            });
        }

        // 2. STRING + MULTI-DROPDOWN
        const stringKeys = ['manage_user','update_user_toko','input_request','cancel_request','update_request','delete_request'];
        
        stringKeys.forEach(key => {
            const value = res.string[key] || 'none';
            const cb = $(`#edit_key_${key}`);
            const wrap = $(`#edit_value_${key}`);
            
            const isActive = value.toLowerCase() !== 'none' && value !== '';
            cb.prop('checked', isActive);

            if(isActive) {
                wrap.removeClass('disabled').empty();
                const dropdown = $(`
                    <div class="multi-dropdown">
                        <div class="dropdown-header">Pilih Toko</div>
                        <div class="dropdown-list" id="edit_${key}TokoList"></div>
                    </div>
                `);
                wrap.append(dropdown);
                const list = dropdown.find('.dropdown-list');

                $.getJSON('direct/get_master_option.php',{type:'toko'}, r => {
                    list.html('<label><input type="checkbox" value="all"><strong>Semua Toko</strong></label><hr>');
                    r.forEach(t => list.append(`<label><input type="checkbox" value="${t.kode}">${t.text}</label>`));

                    const valueMap = value.split(',').map(v => v.trim());
                    if(valueMap.includes('all')){
                        list.find('input[value="all"]').prop('checked', true).trigger('change');
                    } else {
                        valueMap.forEach(v => {
                            list.find(`input[value="${v}"]`).prop('checked', true);
                        });
                    }
                    bindMultiDropdownLogic(list[0]);
                });
            }

            // Bind manual change for edit mode
            cb.off('change').on('change', function() {
                if($(this).prop('checked')) {
                    // Logic mirip initRoleDropdowns
                    wrap.removeClass('disabled').empty();
                    const dropdown = $(`<div class="multi-dropdown"><div class="dropdown-header">Pilih Toko</div><div class="dropdown-list"></div></div>`);
                    wrap.append(dropdown);
                    $.getJSON('direct/get_master_option.php',{type:'toko'}, r => {
                        const l = dropdown.find('.dropdown-list');
                        l.html('<label><input type="checkbox" value="all"><strong>Semua Toko</strong></label><hr>');
                        r.forEach(t => l.append(`<label><input type="checkbox" value="${t.kode}">${t.text}</label>`));
                        bindMultiDropdownLogic(l[0]);
                    });
                } else {
                    wrap.addClass('disabled').empty();
                }
            });
        });

        // 3. EXPIRATION
        if(res.expiration === 'forever'){
            $('#editRoleForever').prop('checked', true);
            $('#editRoleExpirationDate').prop('disabled', true).val('');
        } else {
            $('#editRoleExpirationDate').val(res.expiration || '');
        }
    });
}

function getStringKeyValue(key, isEdit = false) {
    const prefix = isEdit ? 'edit_' : '';
    const wrap = $(`#${prefix}value_${key}`);
    const cb = $(`#${prefix}key_${key}`);
    
    console.log(`--- DEBUG GET STRING [${key}] ---`);
    console.log(`Target Checkbox: #${prefix}key_${key} | Found: ${cb.length}`);
    console.log(`Target Wrapper: #${prefix}value_${key} | Found: ${wrap.length}`);

    if(!cb.prop('checked')) {
        console.warn(`Key ${key} is NOT checked. Returning 'none'`);
        return 'none';
    }

    const allChecked = wrap.find('input[value="all"]').prop('checked');
    if(allChecked) {
        console.info(`Key ${key} is set to ALL`);
        return 'all';
    }

    const vals = [];
    const checkedCheckboxes = wrap.find('input[type="checkbox"]:checked');
    console.log(`Total toko dicentang di ${key}: ${checkedCheckboxes.length}`);

    checkedCheckboxes.each((i, c) => {
        let v = $(c).val();
        if(v && v !== 'all' && v !== 'undefined') {
            vals.push(v);
        }
    });
    
    const result = vals.length ? vals.join(',') : 'none';
    console.log(`Final String for ${key}: ${result}`);
    return result;
}

/* =========================
   UPDATE DATA
========================= */
function updateData() {
    const tab = currentTab;
    let payload = { tab: tab, id: currentEditId, keys: {} };

    if (tab === 'role') {
        payload.nama = $('#editNamaRole').val();

        // 1. Ambil Boolean Keys (Edit)
        const boolKeys = ['enable', 'update_toko', 'update_role', 'handling_request'];
        boolKeys.forEach(k => {
            // Gunakan ID modal edit (biasanya ada prefix edit_)
            payload.keys[k] = $(`#editModal input[value="${k}"]`).prop('checked') ? 1 : 0;
        });

        // 2. Ambil String Keys (Toko)
        const stringKeys = ['manage_user', 'update_user_toko', 'input_request', 'cancel_request', 'update_request', 'delete_request'];
        stringKeys.forEach(k => {
            // KUNCI: Harus TRUE supaya nyari ID #edit_key_...
            payload.keys[k] = getStringKeyValue(k, true); 
        });

        payload.keys.expiration_date = $('#editRoleForever').prop('checked') ? 'forever' : $('#editRoleExpirationDate').val();
    } else if(tab === 'toko') {
        payload.nama = $('#editNamaToko').val();
        payload.kode = $('#editKodeToko').val();
    } else if(tab === 'karyawan') {
        payload.nama = $('#editNamaKaryawan').val();
        payload.toko = $('#editTokoKaryawan').val();
    } else if(tab === 'jenis_kendala') {
        payload.tipe = $('#editTipeKendala').val();
        payload.jenis = $('#editJenisKendala').val();
        payload.turunan = $('input[name="editTurunan"]:checked').val();
    }

    console.log("SENDING UPDATE PAYLOAD:", payload); // Intip payloadnya sebelum kirim

    $.post('direct/update_master_data.php', payload)
        .done((res) => {
            console.log("UPDATE RESPONSE:", res);
            alert('Update berhasil');
            $('#editModal').removeClass('show');
            loadTabContent(tab);
        })
        .fail(e => alert(e.responseText || 'Gagal update'));
}

/* =========================
   DELETE & UTIL
========================= */
function deleteData(tab, id){
    if(!confirm('Hapus data?')) return;
    $.post('direct/delete_master.php', { tab, id }).done(() => loadTabContent(tab));
}

function loadMasterOptions(type, selectId){
    const s = $('#'+selectId);
    s.html('<option value="">-- Pilih --</option>');
    $.getJSON('direct/get_master_option.php', { type }, r => {
        r.forEach(i => s.append(`<option value="${i.id}">${i.text}</option>`));
    });
}

function initModals(){
    $('.close-btn, .btn-cancel').click(function(){
        $(this).closest('.modal').removeClass('show');
    });
}
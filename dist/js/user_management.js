/**
 * user_management.js
 * Handles: Roles Loading, User CRUD, Live Search Portal, & Accordion UI
 */

document.addEventListener('DOMContentLoaded', () => {

    const addModal      = document.getElementById('addUserModal');
    const editModal     = document.getElementById('editUserModal');
    const userContainer = document.getElementById('userContainer');

    // ==========================================
    // 1. LOAD ROLES (Dropdown Builder)
    // ==========================================
    function loadRoles() {
        $.getJSON('direct/get_roles.php', res => {
            const addSelect  = document.getElementById('add-role');
            const editSelect = document.getElementById('edit-role');

            let options = '<option value="">-- Pilih Role --</option>';
            res.forEach(r => {
                options += `<option value="${r.id}">${r.text}</option>`;
            });

            if (addSelect) addSelect.innerHTML = options;
            if (editSelect) editSelect.innerHTML = options;
        });
    }

    // ==========================================
    // 2. LOAD USERS (List Accordion)
    // ==========================================
    function loadUsers() {
        userContainer.innerHTML = '<p style="padding:20px">Loading users...</p>';
        $.getJSON('direct/get_all_users.php', users => {
            if (!users || !users.length) {
                userContainer.innerHTML = '<p style="padding:20px">Belum ada user</p>';
                return;
            }

            userContainer.innerHTML = '';
            users.forEach(u => {
                const statusText  = u.enable == 1 ? 'Active' : 'Disabled';
                const statusClass = u.enable == 1 ? 'status-active' : 'status-disabled';
                const statusColor = u.enable == 1 ? '#2ecc71' : '#e74c3c';

                userContainer.innerHTML += `
                <div class="accordion-item" 
                     data-id="${u.id}" 
                     data-username="${u.username}" 
                     data-role="${u.role_id}" 
                     data-active="${u.enable}">
                    <button class="accordion-header">
                        <span class="user-title">${u.username} — ${u.role_name} 
                            [<span style="color:${statusColor}">${statusText}</span>]
                        </span>
                        <span class="icon">⌄</span>
                    </button>
                    <div class="accordion-content">
                        <div class="accordion-body">
                            <p><strong>Username:</strong> ${u.username}</p>
                            <p><strong>Role:</strong> ${u.role_name}</p>
                            <p><strong>Status:</strong> ${u.enable == 1 ? 'Aktif' : 'Nonaktif'}</p>
                            <div class="actions">
                                <button class="action-btn edit-btn">Edit</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            bindAccordion();
            bindEdit();
            bindDelete();
        });
    }

    // ==========================================
    // 3. UI LOGIC (Accordion)
    // ==========================================
    function bindAccordion() {
        document.querySelectorAll('.accordion-header').forEach(h => {
            h.onclick = () => {
                const item = h.closest('.accordion-item');
                const isOpen = item.classList.contains('active');
                
                document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
                if (!isOpen) item.classList.add('active');
            };
        });
    }

    // ==========================================
    // 4. ADD USER & LIVE SEARCH
    // ==========================================
    const addUsername = $('#add-username');
    const resultBox   = $('<div id="portalUserResults" class="live-search-box"></div>');
    addUsername.after(resultBox);

    let portalUsersCache = {};

    // Open Modal Add
    document.querySelector('.add-user')?.addEventListener('click', () => {
        $('#add-username, #add-password, #add-retype').val('');
        $('#add-role').val('');
        $('#add-enable').prop('checked', true);
        resultBox.hide().empty();
        addModal.classList.add('show');
    });

    // Live Search Logic
    addUsername.on('input', function () {
        const keyword = $(this).val().trim();
        if (keyword.length < 2) { resultBox.hide(); return; }

        $.getJSON('direct/get_users_portal.php', { query: keyword })
            .done(users => {
                let html = '';
                if (users.length > 0) {
                    users.forEach(user => {
                        portalUsersCache[user.username] = user;
                        html += `
                            <div class="search-item" data-username="${user.username}">
                                <strong>${user.username}</strong><br>
                                <small>${user.nama || '-'}</small>
                            </div>`;
                    });
                } else {
                    html = `<div class="search-item disabled">User tidak ditemukan</div>`;
                }
                resultBox.html(html).show();
            });
    });

    // Pick User from Search Results
    $(document).on('click', '#portalUserResults .search-item:not(.disabled)', function () {
        const username = $(this).data('username');
        const user = portalUsersCache[username];
        if (user) {
            $('#add-username').val(user.username);
            $('#add-password, #add-retype').val(user.password);
            resultBox.hide();
        }
    });

    // Submit Add User
    document.getElementById('btn-save-user')?.addEventListener('click', () => {
        if ($('#add-password').val() !== $('#add-retype').val()) {
            alert("Password tidak cocok!"); return;
        }

        const payload = {
            username: $('#add-username').val(),
            password: $('#add-password').val(),
            role: parseInt($('#add-role').val(), 10),
            is_active: $('#add-enable').is(':checked') ? 1 : 0
        };

        $.post('direct/add_user.php', payload, res => {
            alert(res.message);
            if (res.status) { addModal.classList.remove('show'); loadUsers(); }
        }, 'json');
    });

    // ==========================================
    // 5. EDIT USER
    // ==========================================
    function bindEdit() {
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const item = btn.closest('.accordion-item');
                
                $('#edit-id').val(item.dataset.id);
                $('#edit-username').val(item.dataset.username);
                $('#edit-role').val(item.dataset.role);
                $('#edit-enable').prop('checked', item.dataset.active == 1);
                $('#edit-password, #edit-retype').val('');
                
                editModal.classList.add('show');
            };
        });
    }

    document.getElementById('btn-update-user')?.addEventListener('click', () => {
        const payload = {
            id: parseInt($('#edit-id').val(), 10),
            username: $('#edit-username').val(),
            password: $('#edit-password').val(),
            retype_password: $('#edit-retype').val(),
            role: parseInt($('#edit-role').val(), 10),
            is_active: $('#edit-enable').is(':checked') ? 1 : 0
        };

        $.post('direct/edit_user.php', payload, res => {
            alert(res.message);
            if (res.status) { editModal.classList.remove('show'); loadUsers(); }
        }, 'json');
    });

    // ==========================================
    // 6. DELETE USER
    // ==========================================
    function bindDelete() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const id = btn.closest('.accordion-item').dataset.id;
                if (!confirm('Yakin ingin menghapus user ini?')) return;

                $.post('direct/delete_user.php', { id: parseInt(id, 10) }, res => {
                    alert(res.message);
                    if (res.status) loadUsers();
                }, 'json');
            };
        });
    }

    // ==========================================
    // 7. UTILS (Close Modals)
    // ==========================================
    document.querySelectorAll('.close-btn').forEach(b => {
        b.onclick = () => {
            const modalId = b.dataset.modal;
            document.getElementById(modalId)?.classList.remove('show');
        };
    });

    window.onclick = (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
        }
        if (!$(event.target).closest('#add-username, #portalUserResults').length) {
            resultBox.hide();
        }
    };

    // INIT
    loadRoles();
    loadUsers();
});
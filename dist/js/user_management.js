document.addEventListener('DOMContentLoaded', () => {

    const addModal  = document.getElementById('addUserModal');
    const editModal = document.getElementById('editUserModal');
    const userContainer = document.getElementById('userContainer');

    // LOAD ROLES
    function loadRoles() {
        $.getJSON('direct/get_roles.php', res => {
            const addSelect  = document.getElementById('add-role');
            const editSelect = document.getElementById('edit-role');

            addSelect.innerHTML  = '<option value="">-- Pilih Role --</option>';
            editSelect.innerHTML = '<option value="">-- Pilih Role --</option>';

            res.forEach(r => {
                addSelect.innerHTML  += `<option value="${r.id}">${r.text}</option>`;
                editSelect.innerHTML += `<option value="${r.id}">${r.text}</option>`;
            });
        });
    }

    // LOAD USERS
    function loadUsers() {
        userContainer.innerHTML = '<p style="padding:20px">Loading users...</p>';
        $.getJSON('direct/get_users.php', users => {
            if (!users.length) {
                userContainer.innerHTML = '<p style="padding:20px">Belum ada user</p>';
                return;
            }

            userContainer.innerHTML = '';
            users.forEach(u => {
                const last = new Date(u.created_at).toLocaleString('id-ID');
                userContainer.innerHTML += `
                <div class="accordion-item" data-id="${u.id}" data-username="${u.username}" data-role="${u.role_id}">
                    <button class="accordion-header">
                        <span class="user-title">${u.username} — ${u.role_name}</span>
                        <span class="icon">⌄</span>
                    </button>
                    <div class="accordion-content">
                        <div class="accordion-body">
                            <p><strong>Username:</strong> ${u.username}</p>
                            <p><strong>Role:</strong> ${u.role_name}</p>
                            <p><strong>Last Modified:</strong> ${u.created}</p>
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

    function bindAccordion() {
        document.querySelectorAll('.accordion-header').forEach(h => {
            h.onclick = () => {
                const item = h.closest('.accordion-item');
                const open = item.classList.contains('active');
                document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
                if (!open) item.classList.add('active');
            };
        });
    }

    // ADD USER
    document.querySelector('.add-user')?.addEventListener('click', () => addModal.classList.add('show'));
    document.getElementById('btn-save-user')?.addEventListener('click', () => {
        $.post('direct/add_user.php', {
            username: $('#add-username').val(),
            password: $('#add-password').val(),
            retype_password: $('#add-retype').val(),
            role: $('#add-role').val()
        }, res => {
            alert(res.message);
            if (res.status) {
                addModal.classList.remove('show');
                loadUsers();
            }
        }, 'json');
    });

    // EDIT USER
    function bindEdit() {
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.onclick = () => {
                const item = btn.closest('.accordion-item');
                $('#edit-id').val(item.dataset.id);
                $('#edit-username').val(item.dataset.username);
                $('#edit-role').val(item.dataset.role); // auto pick previous role
                $('#edit-password').val('');
                $('#edit-retype').val('');
                editModal.classList.add('show');
            };
        });
    }

    document.getElementById('btn-update-user')?.addEventListener('click', () => {
        $.post('direct/edit_user.php', {
            id: parseInt($('#edit-id').val(), 10),
            username: $('#edit-username').val(),
            password: $('#edit-password').val(),
            retype_password: $('#edit-retype').val(),
            role: parseInt($('#edit-role').val(), 10)
        }, res => {
            alert(res.message);
            if (res.status) {
                editModal.classList.remove('show');
                loadUsers();
            }
        }, 'json');
    });

    // DELETE USER
    function bindDelete() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.onclick = () => {
                const id = parseInt(btn.closest('.accordion-item').dataset.id, 10);
                if (!confirm('Yakin hapus user ini?')) return;
                $.post('direct/delete_user.php', { id }, res => {
                    alert(res.message);
                    if (res.status) loadUsers();
                }, 'json');
            };
        });
    }

    // CLOSE MODAL
    document.querySelectorAll('.close-btn').forEach(b => {
        b.onclick = () => document.getElementById(b.dataset.modal)?.classList.remove('show');
    });

    // INIT
    loadRoles();
    loadUsers();
});

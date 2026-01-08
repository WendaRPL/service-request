document.addEventListener('DOMContentLoaded', () => {
    
    // 1. SELECTOR MODAL & TOMBOL (Sesuai HTML Kamu)
    const modal = document.getElementById('addUserModal');
    const btnOpen = document.querySelector('.add-user'); // Tombol + Add User
    const btnClose = document.querySelector('.close-btn[data-modal="addUserModal"]');  
    const editModal = document.getElementById('editUserModal');
    
    // FUNGSI BUKA MODAL
    if (btnOpen) {
        btnOpen.onclick = function () {
            modal.classList.add('show');
        }
    }

    // FUNGSI TUTUP MODAL (Tombol X)
    if (btnClose) {
        btnClose.onclick = function () {
            modal.classList.remove('show');
        }
    }

    // FUNGSI TUTUP MODAL (Klik Area Gelap)
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    }

    // FUNGSI TUTUP MODAL (Klik Tombol Cancel)
    const btnCancelList = document.querySelectorAll('.btn-cancel');

    btnCancelList.forEach(btn => {
        btn.addEventListener('click', function () {
            modal.classList.remove('show');
        });
    });

    // 2. LOGIKA ACCORDION (Buka Tutup Detail User)
    // Mencari semua header accordion di dalam list user
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Ambil parent-nya (.accordion-item)
            const item = this.parentElement;
            
            // Cek apakah item ini sudah terbuka
            const isOpen = item.classList.contains('active');

            // TUTUP SEMUA ACCORDION LAIN  
            document.querySelectorAll('.accordion-item').forEach(otherItem => {
                otherItem.classList.remove('active');
            });

            // Jika sebelumnya tertutup, maka buka. Jika sudah terbuka, maka tutup.
            if (!isOpen) {
                item.classList.add('active');
            }
        });
    });

    // 3. LOGIKA TOMBOL "FOREVER"
    const btnForever = document.querySelector('.btn-forever');
    const inputDate = document.querySelector('input[name="expiration_date"]');

    if (btnForever && inputDate) {
        btnForever.onclick = function(e) {
            e.preventDefault(); // Mencegah form submit/refresh
            inputDate.value = "2099-12-31"; // Set tanggal jauh ke depan
        }
    }

    // OPEN EDIT MODAL
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            // contoh ambil data dari accordion (dummy dulu)
            const accordionBody = this.closest('.accordion-body');

            const username = accordionBody.querySelector('p:nth-child(1)').innerText.split(': ')[1];
            const role = accordionBody.querySelector('p:nth-child(2)').innerText.split(': ')[1];
            const status = accordionBody.querySelector('p:nth-child(3)').innerText.includes('Active');
            const lastModified = accordionBody.querySelector('p:nth-child(4)').innerText.split(': ')[1];

            // SET VALUE KE FORM
            document.getElementById('edit-username').value = username;
            document.getElementById('edit-role').value = role.toLowerCase().replace(' ', '_');
            document.getElementById('edit-enable').checked = status;

            // reset password
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-retype-password').value = '';

            editModal.classList.add('show');
        });
    });

    // CLOSE MODAL
    document.querySelectorAll('[data-modal="editUserModal"]').forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.classList.remove('show');
        });
    });

    // CLICK OUTSIDE
    window.addEventListener('click', e => {
        if (e.target === editModal) {
            editModal.classList.remove('show');
        }
    });

    // FOREVER BUTTON
    document.getElementById('edit-forever').addEventListener('click', () => {
        document.getElementById('edit-expiration').value = '2099-12-31';
    });

    // UPDATE USER
    document.getElementById('btn-update-user').addEventListener('click', () => {
        alert('User berhasil diupdate (dummy)');
        editModal.classList.remove('show');
    });

});

 

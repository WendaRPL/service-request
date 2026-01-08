document.addEventListener('DOMContentLoaded', function () {

    const trigger = document.getElementById('userTrigger');
    const dropdown = document.getElementById('userDropdown');

    if (!trigger || !dropdown) {
        console.error('User dropdown element NOT FOUND');
        return;
    }

    // Toggle dropdown
    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    // Klik di luar → close
    document.addEventListener('click', function () {
        dropdown.classList.remove('show');
    });

    // Logout
    const logoutBtn = dropdown.querySelector('.logout-btn');
    logoutBtn.addEventListener('click', function () {
        alert('Logout clicked');
        window.location.href = 'login.html';
    });

});

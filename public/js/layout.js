// Satu kali dipasang di document, jadi tetap jalan setelah Turbo mengganti isi <body>
(function () {
    if (window.__layoutInit) return;
    window.__layoutInit = true;

    const $ = id => document.getElementById(id);

    document.addEventListener('click', function (e) {
        const sidebar = $('sidebar'), overlay = $('overlay');
        const profile = $('profile'), notif = $('notif');

        // Sidebar versi mobile
        if (e.target.closest('#burger') || e.target.closest('#overlay')) {
            sidebar && sidebar.classList.toggle('open');
            overlay && overlay.classList.toggle('show');
            return;
        }

        // Dropdown profil
        if (e.target.closest('#profileBtn')) {
            profile && profile.classList.toggle('open');
            notif && notif.classList.remove('open');
            return;
        }

        // Panel notifikasi
        if (e.target.closest('#notifBtn')) {
            notif && notif.classList.toggle('open');
            profile && profile.classList.remove('open');
            return;
        }

        // Klik di luar: tutup panel
        if (profile && !profile.contains(e.target)) profile.classList.remove('open');
        if (notif && !notif.contains(e.target)) notif.classList.remove('open');
    });
})();

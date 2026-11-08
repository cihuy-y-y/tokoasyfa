// Sidebar versi mobile
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const burger  = document.getElementById('burger');

function toggleSidebar() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}
burger.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

// Dropdown profil
const profile    = document.getElementById('profile');
const profileBtn = document.getElementById('profileBtn');

profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profile.classList.toggle('open');
});
document.addEventListener('click', (e) => {
    if (!profile.contains(e.target)) profile.classList.remove('open');
});

// Panel notifikasi
const notif    = document.getElementById('notif');
const notifBtn = document.getElementById('notifBtn');

if (notif && notifBtn) {
    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notif.classList.toggle('open');
        profile.classList.remove('open');   // tutup dropdown profil
    });

    document.addEventListener('click', (e) => {
        if (!notif.contains(e.target)) notif.classList.remove('open');
    });
}
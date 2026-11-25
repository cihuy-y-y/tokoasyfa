// Satu kali dipasang di document, jadi tetap jalan setelah Turbo mengganti isi <body>
(function () {
    if (window.__layoutInit) return;
    window.__layoutInit = true;

    const $ = id => document.getElementById(id);

    // ===== Konfirmasi custom global (aman untuk navigasi Turbo) =====
    // Jangan menyimpan referensi elemen modal karena Turbo dapat mengganti <body>.
    let confirmResolver = null;

    function getConfirmElements() {
        return {
            modal: $('appConfirmModal'),
            title: $('appConfirmTitle'),
            message: $('appConfirmMessage'),
            detail: $('appConfirmDetail'),
            ok: $('appConfirmOk'),
            cancel: $('appConfirmCancel')
        };
    }

    function closeAppConfirm(result) {
        const { modal } = getConfirmElements();
        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }
        const resolve = confirmResolver;
        confirmResolver = null;
        if (resolve) resolve(result);
    }

    window.appConfirm = function (options = {}) {
        const el = getConfirmElements();
        if (!el.modal || !el.title || !el.message || !el.ok) {
            // Fallback aman bila layout modal belum tersedia.
            return Promise.resolve(window.confirm(options.message || 'Apakah kamu yakin?'));
        }

        if (confirmResolver) closeAppConfirm(false);

        el.title.textContent = options.title || 'Konfirmasi';
        el.message.textContent = options.message || 'Apakah kamu yakin ingin melanjutkan?';
        if (el.detail) {
            el.detail.textContent = options.detail || '';
            el.detail.hidden = !options.detail;
        }
        el.ok.textContent = options.confirmText || 'Ya, Lanjutkan';
        el.ok.classList.toggle('is-danger', options.danger !== false);
        el.modal.classList.add('is-open');
        el.modal.setAttribute('aria-hidden', 'false');

        return new Promise(resolve => {
            confirmResolver = resolve;
            el.ok.focus();
        });
    };

    // Event delegation: tombol tetap berfungsi walaupun Turbo mengganti body.
    document.addEventListener('click', function (e) {
        if (e.target.closest('#appConfirmOk')) {
            e.preventDefault();
            closeAppConfirm(true);
            return;
        }
        if (e.target.closest('#appConfirmCancel')) {
            e.preventDefault();
            closeAppConfirm(false);
            return;
        }
        const { modal } = getConfirmElements();
        if (modal && e.target === modal) closeAppConfirm(false);
    });

    document.addEventListener('keydown', function (e) {
        const { modal } = getConfirmElements();
        if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeAppConfirm(false);
        }
    });

    // Form dengan data-confirm memakai modal custom.
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            return;
        }

        e.preventDefault();
        const ok = await window.appConfirm({
            title: form.dataset.confirmTitle || 'Konfirmasi',
            message: form.dataset.confirmMessage || form.dataset.confirm,
            detail: form.dataset.confirmDetail || '',
            confirmText: form.dataset.confirmButton || 'Ya, Lanjutkan',
            danger: form.dataset.confirmDanger !== 'false'
        });

        if (ok) {
            form.dataset.confirmed = '1';
            HTMLFormElement.prototype.submit.call(form);
        }
    }, true);

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

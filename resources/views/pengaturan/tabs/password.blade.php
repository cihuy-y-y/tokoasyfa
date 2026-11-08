<section class="set-card form-card">
    <div class="set-head">
        <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg></span>
        <div>
            <h2>Ganti Kata Sandi</h2>
            <p>Gunakan password minimal 8 karakter yang sulit ditebak</p>
        </div>
    </div>

    <form action="{{ route('pengaturan.password') }}" method="POST">
        @csrf @method('PUT')

        <div class="form-grid one">
            <div class="fg">
                <label for="pw_lama">Password Saat Ini</label>
                <input type="password" id="pw_lama" name="current_password" autocomplete="current-password">
            </div>
            <div class="fg">
                <label for="pw_baru">Password Baru</label>
                <input type="password" id="pw_baru" name="password" autocomplete="new-password">
            </div>
            <div class="fg">
                <label for="pw_konf">Konfirmasi Password Baru</label>
                <input type="password" id="pw_konf" name="password_confirmation" autocomplete="new-password">
            </div>
        </div>

        <div class="modal-foot left">
            <button type="submit" class="btn-add">Ganti Password</button>
        </div>
    </form>
</section>
@php $user = auth()->user(); @endphp

<section class="set-card form-card">
    <div class="set-head">
        <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span>
        <div>
            <h2>Profil Saya</h2>
            <p>Ubah data akun yang sedang login</p>
        </div>
    </div>

    <form action="{{ route('pengaturan.profil') }}" method="POST">
        @csrf @method('PUT')

        <div class="form-grid one">
            <div class="fg">
                <label for="p_name">Nama Lengkap</label>
                <input type="text" id="p_name" name="name" value="{{ old('name', $user->name) }}">
            </div>
            <div class="fg">
                <label for="p_username">Username</label>
                <input type="text" id="p_username" name="username" value="{{ old('username', $user->username) }}">
            </div>
            <div class="fg">
                <label for="p_email">Email</label>
                <input type="email" id="p_email" name="email" value="{{ old('email', $user->email) }}">
            </div>
            <div class="fg">
                <label>Role</label>
                <input type="text" value="{{ ucfirst($user->role) }}" disabled>
            </div>
        </div>

        <div class="modal-foot left">
            <button type="submit" class="btn-add">Simpan Perubahan</button>
        </div>
    </form>
</section>
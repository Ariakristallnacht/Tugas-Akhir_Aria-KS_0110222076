@csrf

<div class="pkm-form-grid">
    <div class="pkm-field">
        <label for="nip">NIP</label>
        <input id="nip" class="pkm-input" type="text" name="nip" value="{{ old('nip', $pegawai->nip ?? '') }}" required>
        @error('nip')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field">
        <label for="nama">Nama pegawai</label>
        <input id="nama" class="pkm-input" type="text" name="nama" value="{{ old('nama', $pegawai->nama ?? '') }}" required>
        @error('nama')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field">
        <label for="jabatan">Jabatan</label>
        <input id="jabatan" class="pkm-input" type="text" name="jabatan" value="{{ old('jabatan', $pegawai->jabatan ?? '') }}" required>
        @error('jabatan')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field">
        <label for="unit_kerja">Unit kerja</label>
        <input id="unit_kerja" class="pkm-input" type="text" name="unit_kerja" value="{{ old('unit_kerja', $pegawai->unit_kerja ?? '') }}" required>
        @error('unit_kerja')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field">
        <label for="no_hp">No. HP</label>
        <input id="no_hp" class="pkm-input" type="text" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp ?? '') }}">
        @error('no_hp')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field pkm-field--checkbox">
        <label for="is_aktif">Status aktif</label>
        <label class="pkm-checkbox">
            <input id="is_aktif" type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $pegawai->is_aktif ?? true))>
            <span>Pegawai aktif</span>
        </label>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="alamat">Alamat</label>
        <textarea id="alamat" class="pkm-input" name="alamat" rows="4">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
        @error('alamat')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field pkm-field--full">
        <div class="pkm-account-block">
            <div class="pkm-account-block__head">
                <div>
                    <strong>Akun Login</strong>
                    <p>Setiap pegawai wajib memiliki akun login untuk mengakses sistem.</p>
                </div>
            </div>

            <div class="pkm-form-grid pkm-account-fields">
                <div class="pkm-field">
                    <label for="email">Email login</label>
                    <input id="email" class="pkm-input" type="email" name="email" value="{{ old('email', $pegawai->user->email ?? '') }}" required>
                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <div class="pkm-field">
                    <label for="role_id">Role akun</label>
                    <select id="role_id" class="pkm-input" name="role_id" required>
                        <option value="">Pilih role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id', $pegawai->user->role_id ?? '') === (string) $role->id)>
                                {{ $role->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <div class="pkm-field">
                    <label for="password">Password {{ isset($pegawai) && $pegawai->user ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input id="password" class="pkm-input" type="password" name="password" {{ isset($pegawai) && $pegawai->user ? '' : 'required' }}>
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <div class="pkm-field">
                    <label for="password_confirmation">Konfirmasi password</label>
                    <input id="password_confirmation" class="pkm-input" type="password" name="password_confirmation" {{ isset($pegawai) && $pegawai->user ? '' : 'required' }}>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('admin.pegawai.index') }}" class="pkm-secondary-button">Kembali</a>
    <button type="submit" class="pkm-primary-button">{{ $submitLabel }}</button>
</div>

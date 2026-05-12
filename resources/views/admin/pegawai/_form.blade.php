@csrf

<div class="pkm-form-grid">
    <div class="pkm-field">
        <label for="jenis_pegawai">Jenis pegawai</label>
        <select id="jenis_pegawai" class="pkm-input" name="jenis_pegawai" required data-jenis-pegawai>
            <option value="">Pilih jenis pegawai</option>
            <option value="asn" @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? 'asn') === 'asn')>PNS</option>
            <option value="p3k" @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? 'asn') === 'p3k')>PPPK</option>
            <option value="honorer" @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? 'asn') === 'honorer')>Honorer</option>
        </select>
        @error('jenis_pegawai')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field" data-nip-field>
        <label for="nip">NIP <small>(opsional)</small></label>
        <input id="nip" class="pkm-input" type="text" name="nip" value="{{ old('nip', $pegawai->nip ?? '') }}" data-nip-input>
        <small data-nip-hint>NIP ditampilkan untuk pegawai PNS dan PPPK.</small>
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
                    <h3 style="font-weight: bold">Akun Login</h3>
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
                    <div class="relative">
                        <input id="password" class="pkm-input pr-14" type="password" name="password" {{ isset($pegawai) && $pegawai->user ? '' : 'required' }}>
                        <button
                            type="button"
                            class="password-toggle absolute inset-y-0 right-0 flex items-center justify-center px-4 opacity-70 transition hover:opacity-100"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                            data-password-toggle
                            data-target="password"
                        >
                            <svg class="password-toggle-icon-show size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.52 7.64 7.27 5 12 5s8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7C20.48 16.36 16.73 19 12 19s-8.48-2.64-9.94-6.65Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="password-toggle-icon-hide hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 5.09A9.76 9.76 0 0 1 12 5c4.73 0 8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7 10.46 10.46 0 0 1-4.24 5.1"></path>
                                <path d="M6.61 6.61A10.45 10.45 0 0 0 2.06 11.65a1 1 0 0 0 0 .7C3.52 16.36 7.27 19 12 19a9.8 9.8 0 0 0 5.39-1.61"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <div class="pkm-field">
                    <label for="password_confirmation">Konfirmasi password</label>
                    <div class="relative">
                        <input id="password_confirmation" class="pkm-input pr-14" type="password" name="password_confirmation" {{ isset($pegawai) && $pegawai->user ? '' : 'required' }}>
                        <button
                            type="button"
                            class="password-toggle absolute inset-y-0 right-0 flex items-center justify-center px-4 opacity-70 transition hover:opacity-100"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                            data-password-toggle
                            data-target="password_confirmation"
                        >
                            <svg class="password-toggle-icon-show size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.52 7.64 7.27 5 12 5s8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7C20.48 16.36 16.73 19 12 19s-8.48-2.64-9.94-6.65Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="password-toggle-icon-hide hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 5.09A9.76 9.76 0 0 1 12 5c4.73 0 8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7 10.46 10.46 0 0 1-4.24 5.1"></path>
                                <path d="M6.61 6.61A10.45 10.45 0 0 0 2.06 11.65a1 1 0 0 0 0 .7C3.52 16.36 7.27 19 12 19a9.8 9.8 0 0 0 5.39-1.61"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('admin.pegawai.index') }}" class="pkm-secondary-button">
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Kembali</span>
    </a>
    <button type="submit" class="pkm-primary-button">
        <i data-lucide="save" class="size-4"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const jenisPegawaiSelect = document.querySelector('[data-jenis-pegawai]');
            const nipField = document.querySelector('[data-nip-field]');
            const nipInput = document.querySelector('[data-nip-input]');
            const passwordToggles = document.querySelectorAll('[data-password-toggle]');

            if (jenisPegawaiSelect && nipField && nipInput) {
                const syncNipVisibility = () => {
                    const shouldShowNip = ['asn', 'p3k'].includes(jenisPegawaiSelect.value);

                    nipField.hidden = !shouldShowNip;
                    nipInput.disabled = !shouldShowNip;

                    if (!shouldShowNip) {
                        nipInput.value = '';
                    }
                };

                jenisPegawaiSelect.addEventListener('change', syncNipVisibility);
                syncNipVisibility();
            }

            passwordToggles.forEach(function (toggle) {
                const targetId = toggle.dataset.target;
                const input = targetId ? document.getElementById(targetId) : null;

                if (!input) {
                    return;
                }

                const showIcon = toggle.querySelector('.password-toggle-icon-show');
                const hideIcon = toggle.querySelector('.password-toggle-icon-hide');

                toggle.addEventListener('click', function () {
                    const isHidden = input.type === 'password';

                    input.type = isHidden ? 'text' : 'password';
                    toggle.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                    toggle.setAttribute('aria-pressed', String(isHidden));
                    showIcon?.classList.toggle('hidden', isHidden);
                    hideIcon?.classList.toggle('hidden', !isHidden);
                });
            });

        });
    </script>
@endpush

@csrf

<div class="pkm-form-grid">
    <div class="pkm-field">
        <label for="nama_kegiatan">Nama kegiatan</label>
        <input id="nama_kegiatan" class="pkm-input" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan ?? '') }}" maxlength="200" required>
        @error('nama_kegiatan')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field pkm-field--checkbox">
        <label for="is_aktif">Status aktif</label>
        <label class="pkm-checkbox">
            <input id="is_aktif" type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $kegiatan->is_aktif ?? true))>
            <span>Layanan aktif</span>
        </label>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" class="pkm-input" name="deskripsi" rows="4">{{ old('deskripsi', $kegiatan->deskripsi ?? '') }}</textarea>
        @error('deskripsi')
            <small>{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('pj.kegiatan.index') }}" class="pkm-secondary-button">
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Kembali</span>
    </a>
    <button type="submit" class="pkm-primary-button">
        <i data-lucide="save" class="size-4"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>

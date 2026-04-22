@php
    $tanggalMulai = old('tanggal_mulai', filled($pengajuan->tanggal_mulai) ? \Illuminate\Support\Carbon::parse($pengajuan->tanggal_mulai)->format('Y-m-d') : '');
    $tanggalSelesai = old('tanggal_selesai', filled($pengajuan->tanggal_selesai) ? \Illuminate\Support\Carbon::parse($pengajuan->tanggal_selesai)->format('Y-m-d') : '');
@endphp

@push('styles')
    <style>
        .pkm-upload-divider {
            grid-column: 1 / -1;
            margin: 6px 0 2px;
            padding-top: 18px;
            border-top: 1px solid var(--pkm-border);
        }

        .pkm-file-input {
            padding: 0.45rem 0.7rem;
        }

        .pkm-file-input::file-selector-button {
            margin-right: 0.95rem;
            padding: 0.7rem 1rem;
            border-right: 1px solid rgba(93, 143, 112, 0.24);
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            background: rgba(226, 239, 232, 0.9);
            color: var(--pkm-primary-strong);
            font-weight: 600;
        }
    </style>
@endpush

<div class="pkm-form-grid">
    <div class="pkm-field">
        <label for="tanggal_mulai">Tanggal mulai</label>
        <input id="tanggal_mulai" class="pkm-input" type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" required>
    </div>

    <div class="pkm-field">
        <label for="tanggal_selesai">Tanggal selesai</label>
        <input id="tanggal_selesai" class="pkm-input" type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" required>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="tujuan">Tujuan dinas luar</label>
        <input id="tujuan" class="pkm-input" type="text" name="tujuan" value="{{ old('tujuan', $pengajuan->tujuan) }}" maxlength="200" placeholder="Contoh: Desa Sukamaju, Aula Kecamatan, Posyandu Melati" required>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="kegiatan">Kegiatan</label>
        <textarea id="kegiatan" class="pkm-input" name="kegiatan" rows="4" placeholder="Jelaskan kegiatan dinas luar yang akan dilakukan." required>{{ old('kegiatan', $pengajuan->kegiatan) }}</textarea>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="keterangan">Keterangan tambahan</label>
        <textarea id="keterangan" class="pkm-input" name="keterangan" rows="4" placeholder="Opsional. Tambahkan informasi pendukung bila diperlukan.">{{ old('keterangan', $pengajuan->keterangan) }}</textarea>
    </div>


    <div class="pkm-field pkm-field--full">
        <label for="bukti_surat">Bukti surat panggilan dinas luar</label>
        <input id="bukti_surat" class="pkm-input pkm-file-input" type="file" name="bukti_surat" accept=".pdf,image/*">
        <input type="hidden" name="bukti_surat_existing" value="{{ $pengajuan->bukti_surat_path }}">
        @if ($pengajuan->bukti_surat_path)
            <small style="display: block; margin-top: 8px;">
                File saat ini:
                <a href="{{ $pengajuan->bukti_surat_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link" style="padding: 0; background: transparent;">
                    <i data-lucide="{{ $pengajuan->bukti_surat_is_pdf ? 'file-text' : 'image' }}" class="size-4"></i>
                    <span>{{ $pengajuan->bukti_surat_nama ?? 'Lihat lampiran' }}</span>
                </a>
            </small>
        @else
            <small style="display: block; margin-top: 8px;">Unggah file gambar atau PDF. Maksimal 5 MB.</small>
        @endif
        @error('bukti_surat')
            <small>{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('pegawai.pengajuan-dinas.index') }}" class="pkm-secondary-button">
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Kembali</span>
    </a>
    <button type="submit" class="pkm-primary-button">
        <i data-lucide="save" class="size-4"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>

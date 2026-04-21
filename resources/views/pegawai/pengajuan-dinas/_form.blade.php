@php
    $tanggalMulai = old('tanggal_mulai', filled($pengajuan->tanggal_mulai) ? \Illuminate\Support\Carbon::parse($pengajuan->tanggal_mulai)->format('Y-m-d') : '');
    $tanggalSelesai = old('tanggal_selesai', filled($pengajuan->tanggal_selesai) ? \Illuminate\Support\Carbon::parse($pengajuan->tanggal_selesai)->format('Y-m-d') : '');
@endphp

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
</div>

<div class="pkm-form-actions">
    <a href="{{ route('pegawai.pengajuan-dinas.index') }}" class="pkm-secondary-button">Kembali</a>
    <button type="submit" class="pkm-primary-button">{{ $submitLabel }}</button>
</div>

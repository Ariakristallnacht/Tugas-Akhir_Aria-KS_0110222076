<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengajuanDinasController extends Controller
{
    public function index(): View
    {
        $pegawai = $this->pegawai();
        $submissions = PengajuanDinas::query()
            ->where('pegawai_id', $pegawai->id)
            ->latest('tanggal_pengajuan')
            ->latest('created_at')
            ->paginate(10);

        return view('pegawai.pengajuan-dinas.index', [
            'submissions' => $submissions,
            'summary' => [
                'total' => $submissions->total(),
                'pending' => PengajuanDinas::where('pegawai_id', $pegawai->id)->where('status', 'diajukan')->count(),
                'approved' => PengajuanDinas::where('pegawai_id', $pegawai->id)->where('status', 'disetujui')->count(),
                'rejected' => PengajuanDinas::where('pegawai_id', $pegawai->id)->where('status', 'ditolak')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pegawai.pengajuan-dinas.create', [
            'pengajuan' => new PengajuanDinas([
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pegawai = $this->pegawai();
        $validated = $this->validateRequest($request);
        unset($validated['bukti_surat'], $validated['bukti_surat_existing']);
        $buktiSurat = $this->storeBuktiSurat($request);

        PengajuanDinas::create($validated + [
            'pegawai_id' => $pegawai->id,
            'tanggal_pengajuan' => now()->toDateString(),
            'bukti_surat_path' => $buktiSurat['path'] ?? null,
            'bukti_surat_nama' => $buktiSurat['name'] ?? null,
            'bukti_surat_mime' => $buktiSurat['mime'] ?? null,
            'status' => 'diajukan',
            'diverifikasi_oleh' => null,
            'diverifikasi_at' => null,
            'catatan_verifikasi' => null,
        ]);

        return redirect()
            ->route('pegawai.pengajuan-dinas.index')
            ->with('success', 'Pengajuan Dinas berhasil dikirim.');
    }

    public function edit(PengajuanDinas $pengajuanDina): View
    {
        $this->ensureOwnedByCurrentPegawai($pengajuanDina);

        abort_unless($this->isEditable($pengajuanDina), 403);

        return view('pegawai.pengajuan-dinas.edit', [
            'pengajuan' => $pengajuanDina,
        ]);
    }

    public function update(Request $request, PengajuanDinas $pengajuanDina): RedirectResponse
    {
        $this->ensureOwnedByCurrentPegawai($pengajuanDina);

        if (! $this->isEditable($pengajuanDina)) {
            return redirect()
                ->route('pegawai.pengajuan-dinas.index')
                ->with('error', 'Pengajuan yang sudah diverifikasi tidak dapat diubah lagi.');
        }

        $validated = $this->validateRequest($request);
        unset($validated['bukti_surat'], $validated['bukti_surat_existing']);
        $buktiSurat = $this->storeBuktiSurat($request);

        if ($buktiSurat && $pengajuanDina->bukti_surat_path) {
            Storage::disk('public')->delete($pengajuanDina->bukti_surat_path);
        }

        $pengajuanDina->update($validated + [
            'bukti_surat_path' => $buktiSurat['path'] ?? $pengajuanDina->bukti_surat_path,
            'bukti_surat_nama' => $buktiSurat['name'] ?? $pengajuanDina->bukti_surat_nama,
            'bukti_surat_mime' => $buktiSurat['mime'] ?? $pengajuanDina->bukti_surat_mime,
            'status' => 'diajukan',
            'diverifikasi_oleh' => null,
            'diverifikasi_at' => null,
            'catatan_verifikasi' => null,
        ]);

        return redirect()
            ->route('pegawai.pengajuan-dinas.index')
            ->with('success', 'Pengajuan Dinas berhasil diperbarui.');
    }

    public function destroy(PengajuanDinas $pengajuanDina): RedirectResponse
    {
        $this->ensureOwnedByCurrentPegawai($pengajuanDina);

        if (! $this->isEditable($pengajuanDina)) {
            return redirect()
                ->route('pegawai.pengajuan-dinas.index')
                ->with('error', 'Pengajuan yang sudah diverifikasi tidak dapat dihapus.');
        }

        if ($pengajuanDina->bukti_surat_path) {
            Storage::disk('public')->delete($pengajuanDina->bukti_surat_path);
        }

        $pengajuanDina->delete();

        return redirect()
            ->route('pegawai.pengajuan-dinas.index')
            ->with('success', 'Pengajuan Dinas berhasil dihapus.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tujuan' => ['required', 'string', 'max:200'],
            'kegiatan' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
            'bukti_surat' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'bukti_surat_existing' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['diajukan'])],
        ]);
    }

    private function storeBuktiSurat(Request $request): ?array
    {
        if (! $request->hasFile('bukti_surat')) {
            return null;
        }

        $file = $request->file('bukti_surat');
        $path = $file->store('pengajuan-dinas/bukti-surat', 'public');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
        ];
    }

    private function ensureOwnedByCurrentPegawai(PengajuanDinas $pengajuan): void
    {
        abort_unless($pengajuan->pegawai_id === $this->pegawai()->id, 403);
    }

    private function isEditable(PengajuanDinas $pengajuan): bool
    {
        return in_array($pengajuan->status, ['diajukan', 'dibatalkan'], true);
    }

    private function pegawai(): Pegawai
    {
        $pegawai = auth()->user()?->pegawai;

        abort_if(! $pegawai, 403, 'Akun pegawai belum terhubung dengan data pegawai.');

        return $pegawai;
    }
}

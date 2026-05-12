<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use Carbon\Carbon;
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
            'isEditMode' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pegawai = $this->pegawai();
        $validated = $this->validateRequest($request);
        unset($validated['bukti_surat'], $validated['bukti_surat_existing'], $validated['alasan_perubahan_tanggal']);
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

        abort_unless($this->canEdit($pengajuanDina), 403);

        return view('pegawai.pengajuan-dinas.edit', [
            'pengajuan' => $pengajuanDina,
            'isEditMode' => true,
        ]);
    }

    public function update(Request $request, PengajuanDinas $pengajuanDina): RedirectResponse
    {
        $this->ensureOwnedByCurrentPegawai($pengajuanDina);

        if (! $this->canEdit($pengajuanDina)) {
            return redirect()
                ->route('pegawai.pengajuan-dinas.index')
                ->with('error', 'Pengajuan dinas ini tidak dapat diubah.');
        }

        $validated = $this->validateRequest($request);
        unset($validated['bukti_surat'], $validated['bukti_surat_existing']);
        $buktiSurat = $this->storeBuktiSurat($request);
        $alasanPerubahanTanggal = $validated['alasan_perubahan_tanggal'] ?? null;
        unset($validated['alasan_perubahan_tanggal']);
        $tanggalMulaiSebelumnya = $pengajuanDina->tanggal_mulai?->copy();
        $tanggalSelesaiSebelumnya = $pengajuanDina->tanggal_selesai?->copy();
        $tanggalBerubah = $this->hasDateChanged(
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $tanggalMulaiSebelumnya,
            $tanggalSelesaiSebelumnya
        );

        if ($buktiSurat && $pengajuanDina->bukti_surat_path) {
            Storage::disk('public')->delete($pengajuanDina->bukti_surat_path);
        }

        $pengajuanDina->update($validated + [
            'keterangan' => $this->buildKeteranganUpdate(
                $validated['keterangan'] ?? null,
                $alasanPerubahanTanggal,
                $tanggalMulaiSebelumnya,
                $tanggalSelesaiSebelumnya,
                $tanggalBerubah
            ),
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

        if (! $this->canDelete($pengajuanDina)) {
            return redirect()
                ->route('pegawai.pengajuan-dinas.index')
                ->with('error', 'Pengajuan yang sudah disetujui atau diverifikasi tidak dapat dihapus.');
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
            'alasan_perubahan_tanggal' => [
                Rule::requiredIf(function () use ($request) {
                    if (! $request->routeIs('pegawai.pengajuan-dinas.update')) {
                        return false;
                    }

                    $pengajuan = $request->route('pengajuanDina');

                    if (! $pengajuan instanceof PengajuanDinas) {
                        return false;
                    }

                    return $this->hasDateChanged(
                        (string) $request->input('tanggal_mulai'),
                        (string) $request->input('tanggal_selesai'),
                        $pengajuan->tanggal_mulai,
                        $pengajuan->tanggal_selesai
                    );
                }),
                'nullable',
                'string',
                'max:500',
            ],
            'bukti_surat' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'bukti_surat_existing' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['diajukan'])],
        ]);
    }

    private function hasDateChanged(
        string $tanggalMulaiBaru,
        string $tanggalSelesaiBaru,
        ?Carbon $tanggalMulaiLama,
        ?Carbon $tanggalSelesaiLama
    ): bool {
        if (! $tanggalMulaiLama || ! $tanggalSelesaiLama) {
            return false;
        }

        return $tanggalMulaiLama->toDateString() !== $tanggalMulaiBaru
            || $tanggalSelesaiLama->toDateString() !== $tanggalSelesaiBaru;
    }

    private function buildKeteranganUpdate(
        ?string $keterangan,
        ?string $alasanPerubahanTanggal,
        ?Carbon $tanggalMulaiSebelumnya,
        ?Carbon $tanggalSelesaiSebelumnya,
        bool $tanggalBerubah
    ): ?string {
        if (! $tanggalBerubah) {
            $result = filled($keterangan) ? trim($keterangan) : null;

            return filled($result) ? $result : null;
        }

        $segments = collect([
            $this->stripAutoDateChangeNotes($keterangan),
        ])->filter(fn (?string $value) => filled($value));

        if ($tanggalBerubah) {
            if (filled($alasanPerubahanTanggal)) {
                $segments->push('Alasan perubahan tanggal: '.trim($alasanPerubahanTanggal));
            }

            $segments->push('Kegiatan ini akan dilaksanakan pada '.$this->formatPreviousDateRange($tanggalMulaiSebelumnya, $tanggalSelesaiSebelumnya).'.');
        }

        $result = $segments->implode("\n");

        return filled($result) ? $result : null;
    }

    private function stripAutoDateChangeNotes(?string $keterangan): ?string
    {
        if (! filled($keterangan)) {
            return null;
        }

        $lines = preg_split("/\r\n|\n|\r/", trim($keterangan)) ?: [];

        $filteredLines = collect($lines)
            ->reject(fn (string $line) => str_starts_with($line, 'Alasan perubahan tanggal: '))
            ->reject(fn (string $line) => str_starts_with($line, 'Kegiatan ini akan dilaksanakan pada '))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values();

        return $filteredLines->isNotEmpty() ? $filteredLines->implode("\n") : null;
    }

    private function formatPreviousDateRange(?Carbon $tanggalMulai, ?Carbon $tanggalSelesai): string
    {
        if (! $tanggalMulai || ! $tanggalSelesai) {
            return '-';
        }

        if ($tanggalMulai->isSameDay($tanggalSelesai)) {
            return $tanggalMulai->translatedFormat('d F Y');
        }

        return $tanggalMulai->translatedFormat('d F Y').' s.d. '.$tanggalSelesai->translatedFormat('d F Y');
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

    private function canEdit(PengajuanDinas $pengajuan): bool
    {
        return in_array($pengajuan->status, ['diajukan', 'dibatalkan', 'disetujui'], true);
    }

    private function canDelete(PengajuanDinas $pengajuan): bool
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

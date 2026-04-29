<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kegiatan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $kegiatanList = Kegiatan::query()
            ->where('jenis', 'layanan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama_kegiatan', 'like', '%'.$search.'%')
                        ->orWhere('deskripsi', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('jenis')
            ->orderBy('nama_kegiatan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kegiatan.index', [
            'kegiatanList' => $kegiatanList,
            'filters' => [
                'search' => $search,
            ],
            'summary' => [
                'total' => Kegiatan::where('jenis', 'layanan')->count(),
                'layanan' => Kegiatan::where('jenis', 'layanan')->count(),
                'aktif' => Kegiatan::where('jenis', 'layanan')->where('is_aktif', true)->count(),
                'nonaktif' => Kegiatan::where('jenis', 'layanan')->where('is_aktif', false)->count(),
                'jadwal' => Jadwal::whereHas('kegiatan', fn ($query) => $query->where('jenis', 'layanan'))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.kegiatan.create', [
            'kegiatan' => new Kegiatan([
                'is_aktif' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['jenis'] = 'layanan';
        $validated['is_aktif'] = $request->boolean('is_aktif');

        Kegiatan::create($validated);

        return redirect()
            ->route('pj.kegiatan.index')
            ->with('success', 'Data kegiatan berhasil ditambahkan.');
    }

    public function edit(Kegiatan $kegiatan): View
    {
        return view('admin.kegiatan.edit', [
            'kegiatan' => $kegiatan,
        ]);
    }

    public function update(Request $request, Kegiatan $kegiatan): RedirectResponse
    {
        $validated = $this->validateRequest($request, $kegiatan);
        $validated['jenis'] = 'layanan';
        $validated['is_aktif'] = $request->boolean('is_aktif');

        $kegiatan->update($validated);

        return redirect()
            ->route('pj.kegiatan.index')
            ->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        try {
            DB::transaction(function () use ($kegiatan) {
                $kegiatan->load('jadwal');

                foreach ($kegiatan->jadwal as $jadwal) {
                    $jadwal->delete();
                }

                $kegiatan->delete();
            });
        } catch (QueryException) {
            return redirect()
                ->route('pj.kegiatan.index')
                ->with('error', 'Data layanan tidak dapat dihapus karena masih dipakai pada data lain.');
        }

        return redirect()
            ->route('pj.kegiatan.index')
            ->with('success', 'Data layanan beserta jadwal terkait berhasil dihapus.');
    }

    private function validateRequest(Request $request, ?Kegiatan $kegiatan = null): array
    {
        return $request->validate([
            'nama_kegiatan' => [
                'required',
                'string',
                'max:200',
                Rule::unique('kegiatan', 'nama_kegiatan')->ignore($kegiatan?->id),
            ],
            'deskripsi' => ['nullable', 'string'],
            'is_aktif' => ['nullable', 'boolean'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function index(): View
    {
        return view('admin.pegawai.index', [
            'pegawaiList' => Pegawai::with('user.role')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.pegawai.create', [
            'roles' => Role::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:30', 'unique:pegawai,nip'],
            'nama' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'unit_kerja' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'is_aktif' => ['nullable', 'boolean'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');

        $pegawai = Pegawai::create([
            'nip' => $validated['nip'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'unit_kerja' => $validated['unit_kerja'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'is_aktif' => $validated['is_aktif'],
        ]);

        User::create([
            'name' => $pegawai->nama,
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'pegawai_id' => $pegawai->id,
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Data pegawai dan akun login berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai): View
    {
        return view('admin.pegawai.edit', [
            'pegawai' => $pegawai,
            'roles' => Role::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:30', Rule::unique('pegawai', 'nip')->ignore($pegawai->id)],
            'nama' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'unit_kerja' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'is_aktif' => ['nullable', 'boolean'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pegawai->user?->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');
        $request->validate([
            'password' => [$pegawai->user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        $pegawai->update([
            'nip' => $validated['nip'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'unit_kerja' => $validated['unit_kerja'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'is_aktif' => $validated['is_aktif'],
        ]);

        $userPayload = [
            'name' => $pegawai->nama,
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'pegawai_id' => $pegawai->id,
        ];

        if (filled($validated['password'] ?? null)) {
            $userPayload['password'] = $validated['password'];
        }

        if ($pegawai->user) {
            $pegawai->user->update($userPayload);
        } else {
            User::create($userPayload + [
                'password' => $validated['password'],
            ]);
        }

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Data pegawai dan akun login berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        try {
            if ($pegawai->user && auth()->id() === $pegawai->user->id) {
                return redirect()
                    ->route('admin.pegawai.index')
                    ->with('error', 'Data pegawai yang terhubung dengan akun Anda sendiri tidak dapat dihapus.');
            }

            $pegawai->user?->delete();
            $pegawai->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.pegawai.index')
                ->with('error', 'Data pegawai tidak dapat dihapus karena masih dipakai pada data lain.');
        }

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }
}

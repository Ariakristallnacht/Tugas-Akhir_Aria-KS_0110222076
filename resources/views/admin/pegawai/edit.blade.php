@extends('layouts.dashboard')

@php
    $title = 'Edit Pegawai | Puskesmas Bunar';
    $heading = 'Edit Pegawai';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2>Edit Pegawai</h2>
                <p>Perbarui identitas pegawai sekaligus akun login yang wajib terhubung ke pegawai tersebut.</p>
            </div>
        </div>

        @include('admin.partials.flash')

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3>Form Pegawai dan Akun</h3>
                    <p>Data ini menjadi referensi akun, jadwal, monitoring, dan laporan, sehingga akun login selalu melekat pada pegawai.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.pegawai.update', $pegawai) }}" class="pkm-form-stack">
                @method('PUT')
                @include('admin.pegawai._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </section>
    </section>
@endsection

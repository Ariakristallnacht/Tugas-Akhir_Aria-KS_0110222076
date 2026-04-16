@extends('layouts.dashboard')

@php
    $title = 'Tambah Pegawai | Puskesmas Bunar';
    $heading = 'Tambah Pegawai';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2>Tambah Pegawai</h2>
                <p>Buat data pegawai baru sekaligus akun login, karena setiap pegawai wajib memiliki akses akun.</p>
            </div>
        </div>

        @include('admin.partials.flash')

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3>Form Pegawai dan Akun</h3>
                    <p>Lengkapi identitas dasar, email login, role, dan password dalam satu form.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.pegawai.store') }}" class="pkm-form-stack">
                @include('admin.pegawai._form', ['submitLabel' => 'Simpan Pegawai'])
            </form>
        </section>
    </section>
@endsection

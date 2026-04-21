@extends('layouts.dashboard')

@php
    $title = 'Tambah Pengajuan Dinas Luar | Puskesmas Bunar';
    $heading = 'Tambah Pengajuan Dinas Luar';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2>Buat Pengajuan Dinas Luar</h2>
                    <p>Isi jadwal dan tujuan kegiatan lapangan agar PJ penjadwalan dapat menyesuaikan layanan.</p>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pegawai.pengajuan-dinas.store') }}" class="pkm-form-stack">
                @csrf
                @include('pegawai.pengajuan-dinas._form', ['submitLabel' => 'Kirim Pengajuan'])
            </form>
        </div>
    </section>
@endsection

@extends('layouts.dashboard')

@php
    $title = 'Edit Pengajuan Dinas Luar | Puskesmas Bunar';
    $heading = 'Edit Pengajuan Dinas Luar';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2>Edit Pengajuan Dinas Luar</h2>
                    <p>Perbarui detail pengajuan selama statusnya masih menunggu verifikasi.</p>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pegawai.pengajuan-dinas.update', $pengajuan) }}" class="pkm-form-stack">
                @csrf
                @method('PUT')
                @include('pegawai.pengajuan-dinas._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </div>
    </section>
@endsection

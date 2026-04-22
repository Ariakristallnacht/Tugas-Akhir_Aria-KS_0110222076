@extends('layouts.dashboard')

@php
    $title = 'Tambah Pengajuan Dinas| Puskesmas Bunar';
    $heading = 'Tambah Pengajuan Dinas';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight:bold">Buat Pengajuan Dinas</h2>
                    <br>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pegawai.pengajuan-dinas.store') }}" class="pkm-form-stack" enctype="multipart/form-data">
                @csrf
                @include('pegawai.pengajuan-dinas._form', ['submitLabel' => 'Kirim Pengajuan'])
            </form>
        </div>
    </section>
@endsection

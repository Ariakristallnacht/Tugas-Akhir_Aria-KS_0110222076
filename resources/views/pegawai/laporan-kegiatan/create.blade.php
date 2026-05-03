@extends('layouts.dashboard')

@php
    $title = 'Tambah Laporan Kegiatan | Puskesmas Bunar';
    $heading = 'Tambah Laporan Kegiatan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight: bold">Tambah Laporan Kegiatan</h2>
                    <br>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pegawai.laporan-kegiatan.store') }}" class="pkm-form-stack" enctype="multipart/form-data">
                @csrf
                @include('pj.laporan-kegiatan._form', ['submitLabel' => 'Simpan Laporan'])
            </form>
        </div>
    </section>
@endsection

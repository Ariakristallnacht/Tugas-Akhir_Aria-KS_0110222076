@extends('layouts.dashboard')

@php
    $title = 'Tambah Jadwal Kegiatan | Puskesmas Bunar';
    $heading = 'Tambah Jadwal Kegiatan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight: bold">Susun Jadwal Layanan</h2>
                    <br>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pj.jadwal-kegiatan.store') }}" class="pkm-form-stack">
                @csrf
                @include('pj.jadwal-kegiatan._form', ['submitLabel' => 'Simpan Jadwal'])
            </form>
        </div>
    </section>
@endsection

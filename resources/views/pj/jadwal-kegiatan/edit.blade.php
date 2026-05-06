@extends('layouts.dashboard')

@php
    $title = 'Edit Jadwal Kegiatan | Puskesmas Bunar';
    $heading = 'Edit Jadwal Kegiatan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2>Edit Jadwal Kegiatan</h2>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pj.jadwal-kegiatan.update', $jadwal) }}" class="pkm-form-stack">
                @csrf
                @method('PUT')
                @include('pj.jadwal-kegiatan._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </div>
    </section>
@endsection

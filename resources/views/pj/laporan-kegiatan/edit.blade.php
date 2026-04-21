@extends('layouts.dashboard')

@php
    $title = 'Edit Laporan Kegiatan | Puskesmas Bunar';
    $heading = 'Edit Laporan Kegiatan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2>Edit Laporan Kegiatan</h2>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pj.laporan-kegiatan.update', $report) }}" class="pkm-form-stack">
                @csrf
                @method('PUT')
                @include('pj.laporan-kegiatan._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </div>
    </section>
@endsection

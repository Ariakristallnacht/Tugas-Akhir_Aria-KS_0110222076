@extends('layouts.dashboard')

@php
    $title = 'Tambah Layanan | Puskesmas Bunar';
    $heading = 'Tambah Layanan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Tambah Layanan</h2>
            </div>
        </div>

        @include('admin.partials.flash')

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Form Layanan Poli</h3>
                    <br>
                </div>
            </div>

            <form method="POST" action="{{ route('pj.kegiatan.store') }}" class="pkm-form-stack">
                @include('admin.kegiatan._form', ['submitLabel' => 'Simpan Layanan'])
            </form>
        </section>
    </section>
@endsection

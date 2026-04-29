@extends('layouts.dashboard')

@php
    $title = 'Edit Layanan | Puskesmas Bunar';
    $heading = 'Edit Layanan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Edit Layanan</h2>
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

            <form method="POST" action="{{ route('pj.kegiatan.update', $kegiatan) }}" class="pkm-form-stack">
                @method('PUT')
                @include('admin.kegiatan._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </section>
    </section>
@endsection

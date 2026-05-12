@extends('layouts.dashboard')

@php
    $title = 'Edit Pengajuan Dinas | Puskesmas Bunar';
    $heading = 'Edit Pengajuan Dinas';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2>Edit Pengajuan Dinas</h2>
                </div>
            </div>

            @include('admin.partials.flash')

            <form method="POST" action="{{ route('pegawai.pengajuan-dinas.update', $pengajuan) }}" class="pkm-form-stack" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('pegawai.pengajuan-dinas._form', ['submitLabel' => 'Simpan Perubahan', 'isEditMode' => true])
            </form>
        </div>
    </section>
@endsection

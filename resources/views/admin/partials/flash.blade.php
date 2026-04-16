@if (session('success'))
    <div class="pkm-alert is-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="pkm-alert is-error">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="pkm-alert is-error">
        <strong>Periksa kembali data yang diisi.</strong>
        <div class="mt-2">{{ $errors->first() }}</div>
    </div>
@endif

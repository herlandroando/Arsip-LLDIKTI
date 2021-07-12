<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <link href="{{ mix('/css/index.css') }}" rel="stylesheet" />
    <script>
        const _sidebars = {!! json_encode($_sidebars) !!}
        const _bagianInstansi = {!! json_encode($_bagianInstansi) !!}
        const _sifatSurat = {!! json_encode($_sifat_surat) !!}
    </script>
    @routes
    <script src="{{ mix('/js/app.js') }}" defer></script>

</head>

<body>
    @inertia
</body>

</html>

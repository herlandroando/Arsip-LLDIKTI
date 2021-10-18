<!DOCTYPE html>
<html>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <link href="{{ mix('/css/index.css') }}" rel="stylesheet" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title??"Laporan"}}</title>
    <link rel="stylesheet" href="{{asset('css/pdf.css')}}">

</head>

<body>
    <div class="header-container">
        <div class="logo-container">
            <img class="logo-responsive " src="{{url("images/small-logo.png")}}">
        </div>
        <div class="header-information">
            <div class="header-title">
                <h2>LLDIKTI Wilayah XIV Papua - Papua Barat</h2>
            </div>
            <div class="header-description">
                <p>
                    <small>Tanggal Pembuatan Laporan : {{$report_created_at}}</small>
                    <br><small> Tipe Laporan : {{$report_metadata["type"]}} - {{$report_metadata['routine_type']}}
                        ({{$report_metadata['duration']}})</small>
                </p>
            </div>
        </div>
    </div>
    <hr />
    <div class="content-container">
        @switch($report_type)
            @case("sm_report")
                @switch($routine_type)
                    @case("daily")
                    <x-reports.surat-masuk-daily />
                    @break
                    @case("monthly")
                    <x-reports.surat-masuk-monthly />
                    @break
                    @default
                @endswitch
            @break
            @case("sk_report")
                @switch($routine_type)
                    @case("daily")
                    <x-reports.surat-keluar-daily />
                    @break
                    @case("monthly")
                    <x-reports.surat-keluar-monthly />
                    @break
                    @default
                @endswitch
            @break
            @default
        @endswitch
    </div>
</body>

</html>

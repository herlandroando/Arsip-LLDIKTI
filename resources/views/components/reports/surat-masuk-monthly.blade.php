<section class="filter-information-container" title="Informasi Filter">

    @if(count($report_data)>0)

    {{-- <h4></h4> --}}
    @foreach ( $report_data as $list_from_date )

    <table class="filter-joined-table">
        <tr>
            <th colspan="7" class="text-left">{{$list_from_date['duration_date']}}</th>
        </tr>
        <tr>
            <th class="no-border"><b>No.</b></th>
            <th><b>No. Agenda</b></th>
            <th><b>No. Surat</b></th>
            <th><b>Tanggal Diterima</b></th>
            <th><b>Tanggal Surat</b></th>
            <th><b>Asal Surat</b></th>
            <th><b>Perihal</b></th>
        </tr>
        @foreach ($list_from_date["data"] as $key => $report)

        <tr>
            <th class="text-center">{{$key+1}}</th>
            <td class="text-center">{{$report->no_agenda}}</td>
            <td class="text-center">{{$report->no_surat}}</td>
            <td class="text-center">
                {{\Carbon\Carbon::parse($report->created_at)->timezone("Asia/Jayapura")->toDayDateTimeString()}}
            </td>
            <td class="text-center">
                {{\Carbon\Carbon::parse($report->tanggal_surat)->toFormattedDateString()}}
            </td>
            <td class="text-center">{{$report->asal_surat}}</td>
            <td class="text-center">{{$report->perihal}}</td>

        </tr>
        @endforeach

    </table>
    <i class="page-break"></i>

    @endforeach
    @endif
    @empty($report_data)
    <p>Tidak ada surat masuk pada tanggal {{$report_metadata['duration']}}.</p>

    @endempty
</section>

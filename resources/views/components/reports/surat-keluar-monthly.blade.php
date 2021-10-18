<section class="filter-information-container" title="Informasi Filter">

    @if(count($report_data)>0)

    {{-- <h4></h4> --}}
    @foreach ( $report_data as $list_from_date )

    <table class="filter-joined-table">
        <tr>
            <th colspan="6" class="text-left">{{$list_from_date['duration_date']}}</th>
        </tr>
        <tr>
            <th class="no-border">No.</th>
            <th>No. Surat</th>
            <th>Tanggal Surat</th>
            <th>Perihal</th>
            <th>Bagian yang Membuat Surat</th>
            <th>Nama Pembuat</th>
        </tr>
        @foreach ($list_from_date["data"] as $key => $report)
        <tr>
            <th class="text-center">{{$key+1}}</th>
            <td class="text-center">{{$report['no_surat']}}</td>
            <td class="text-center">
                {{\Carbon\Carbon::parse($report['tanggal_surat'])->timezone("Asia/Jayapura")->toDayDateTimeString()}}
            </td>
            <td class="text-center">{{$report['perihal']}}</td>
            <td class="text-center">
                {{$report['nama_bagian']}}
            </td>
            <td class="text-center">{{$report['nama_pembuat']}}</td>

        </tr>
        @endforeach

    </table>
    <i class="page-break"></i>

    @endforeach
    @endif
    @empty($report_data)
    <p>Tidak ada surat keluar pada tanggal {{$report_metadata['duration']}}.</p>

    @endempty
</section>

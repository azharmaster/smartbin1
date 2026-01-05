<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Report - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; line-height: 1.4; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: center; }
        th { background-color: #1b5e20; color: white; }
        .section-title { font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>SmartBin Summary Report - {{ $month }}</h2>

    <p class="section-title">Capacity Stats</p>
    <table>
        <tr>
            <th>Empty</th>
            <th>Half Full</th>
            <th>Full</th>
        </tr>
        <tr>
            <td>{{ $capacityStats->empty_count }}</td>
            <td>{{ $capacityStats->half_count }}</td>
            <td>{{ $capacityStats->full_count }}</td>
        </tr>
    </table>

    <p class="section-title">Devices by Floor</p>
    <table>
        <tr>
            <th>Floor</th>
            <th>Total Devices</th>
        </tr>
        @foreach($devicesByFloor as $floor)
        <tr>
            <td>{{ $floor->floor_name }}</td>
            <td>{{ $floor->total }}</td>
        </tr>
        @endforeach
    </table>

    <p class="section-title">Full Bin Trend</p>
    <table>
        <tr>
            <th>Date</th>
            <th>Full Bins</th>
        </tr>
        @foreach($fullTrend as $trend)
        <tr>
            <td>{{ $trend->date }}</td>
            <td>{{ $trend->total }}</td>
        </tr>
        @endforeach
    </table>

    <p class="section-title">Full Counts per Bin</p>
    <table>
        <tr>
            <th>Asset</th>
            <th>Times Full</th>
        </tr>
        @foreach($fullCounts as $bin)
        <tr>
            <td>{{ $bin->asset_name }}</td>
            <td>{{ $bin->total_full }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>

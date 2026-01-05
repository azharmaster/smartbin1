<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        h2 { color: #1b5e20; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Summary Report - {{ $month }}</h2>

    <h3>Device Capacity</h3>
    <ul>
        <li>Empty: {{ $capacityStats->empty_count }}</li>
        <li>Half Full: {{ $capacityStats->half_count }}</li>
        <li>Full: {{ $capacityStats->full_count }}</li>
    </ul>

    <h3>Devices by Floor</h3>
    <table>
        <tr><th>Floor</th><th>Total Devices</th></tr>
        @foreach($devicesByFloor as $floor)
            <tr>
                <td>{{ $floor->floor_name }}</td>
                <td>{{ $floor->total }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Full Bin Trend (Daily)</h3>
    <table>
        <tr><th>Date</th><th>Full Bins</th></tr>
        @foreach($fullTrend as $trend)
            <tr>
                <td>{{ $trend->date }}</td>
                <td>{{ $trend->total }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Full Counts per Bin</h3>
    <table>
        <tr><th>Bin</th><th>Times Full</th></tr>
        @foreach($fullCounts as $count)
            <tr>
                <td>{{ $count->asset_name }}</td>
                <td>{{ $count->total_full }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>

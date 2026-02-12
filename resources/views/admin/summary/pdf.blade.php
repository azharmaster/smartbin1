<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2, h4 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 6px; text-align: center; }
    </style>
</head>
<body>

<h2>SmartBin Summary Report</h2>
<p style="text-align:center;">
    Period: {{ ucfirst($period) }} <br>
    Date: {{ $month }}
</p>

<hr>

<h4>Bin Capacity Distribution</h4>
<table>
    <tr>
        <th>Empty</th>
        <th>Half</th>
        <th>Full</th>
    </tr>
    <tr>
        <td>{{ $capacityStats->empty_count }}</td>
        <td>{{ $capacityStats->half_count }}</td>
        <td>{{ $capacityStats->full_count }}</td>
    </tr>
</table>

<h4>Devices by Floor</h4>
<table>
    <tr>
        <th>Floor</th>
        <th>Total Devices</th>
    </tr>
    @foreach($devicesByFloor as $row)
        <tr>
            <td>{{ $row->floor_name }}</td>
            <td>{{ $row->total }}</td>
        </tr>
    @endforeach
</table>

<h4>Bin Analytics (Per Asset)</h4>
<table>
    <tr>
        <th>Asset</th>
        <th>Times Full</th>
        <th>Avg Fill Time (hrs)</th>
        <th>Avg Clear Time (hrs)</th>
    </tr>
    @foreach($binAnalytics as $row)
        <tr>
            <td>{{ $row->asset_name }}</td>
            <td>{{ $row->times_full }}</td>
            <td>{{ $row->avg_fill_time }}</td>
            <td>{{ $row->avg_clear_time }}</td>
        </tr>
    @endforeach
</table>

<h4>Assets</h4>
<ul>
@foreach($assets as $asset)
    <li>{{ $asset->asset_name }} ({{ $asset->floor->floor_name ?? 'N/A' }})</li>
@endforeach
</ul>

</body>
</html>
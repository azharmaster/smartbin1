<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SmartBin Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 20px; }
        h4 { margin-bottom: 10px; }
        .chart { width: 100%; max-width: 600px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px 10px; text-align: center; }
        th { background-color: #f5f5f5; }
        .asset-img { width: 100px; height: 100px; object-fit: cover; border-radius: 6px; }
        .assets { display: flex; flex-wrap: wrap; gap: 15px; }
        .asset-card { text-align: center; font-size: 0.9rem; width: 120px; }
    </style>
</head>
<body>

<h2>SmartBin Summary</h2>

<p style="font-weight: bold;">
    {{ $reportTitle }}
</p>

<h4>Number of Times Each Bin Became Full</h4>
<img src="{{ $timesFullChartData }}" class="chart">

<h4>Average Time for Bin to Become Full (Hours)</h4>
<img src="{{ $avgFillChartData }}" class="chart">

<h4>Average Bin Clear Time (Hours)</h4>
<img src="{{ $avgClearChartData }}" class="chart">

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

</body>
</html>

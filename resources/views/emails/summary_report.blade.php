<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Summary Report - {{ $month }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 20px; }
        h2, h3 { margin-bottom: 10px; }
        .section { margin-bottom: 30px; }
        .chart { width: 100%; max-width: 600px; margin: 0 auto 20px; }
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

<h2>SmartBin Summary Report</h2>
<h3>Month: {{ $month }}</h3>

<!-- Capacity Stats -->
<div class="section">
    <h3>Device Capacity Distribution</h3>
    <img src="{{ $charts['capacityChart'] }}" class="chart">
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
</div>

<!-- Devices by Floor -->
<div class="section">
    <h3>Devices by Floor</h3>
    <img src="{{ $charts['floorChart'] }}" class="chart">
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
</div>

<!-- Full Bin Trend -->
<div class="section">
    <h3>Full Bin Trend (Daily)</h3>
    <img src="{{ $charts['trendChart'] }}" class="chart">
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
</div>

<!-- Full Counts per Bin -->
<div class="section">
    <h3>Number of Times Each Bin Was Full</h3>
    <img src="{{ $charts['fullCountsChart'] }}" class="chart">
    <table>
        <tr>
            <th>Asset Name</th>
            <th>Times Full</th>
        </tr>
        @foreach($fullCounts as $bin)
        <tr>
            <td>{{ $bin->asset_name }}</td>
            <td>{{ $bin->total_full }}</td>
        </tr>
        @endforeach
    </table>
</div>

<!-- Asset Images -->
@if($assets->count() > 0)
<div class="section">
    <h3>Asset Images</h3>
    <div class="assets">
        @foreach($assets as $asset)
        <div class="asset-card">
            <div>{{ $asset->asset_name }}</div>
            @if($asset->picture)
                <img src="{{ asset('storage/' . $asset->picture) }}" class="asset-img">
            @else
                <div style="color: #999; margin-top: 5px;">No image</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

</body>
</html>

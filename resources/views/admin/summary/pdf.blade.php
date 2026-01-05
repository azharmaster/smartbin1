<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Report - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; }
        h2, h5 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: center; }
        .chart { page-break-inside: avoid; margin-bottom: 25px; }
        .asset-img { height: 120px; object-fit: cover; }
    </style>
</head>
<body>

<h2>SmartBin Summary Report</h2>
<p style="text-align:center;">Month: {{ $month }}</p>

<h5>Device Capacity Distribution</h5>
<img src="data:image/png;base64,{{ $capacityChart }}" class="chart">

<h5>Devices by Floor</h5>
<img src="data:image/png;base64,{{ $floorChart }}" class="chart">

<h5>Full Bin Trend (Daily)</h5>
<img src="data:image/png;base64,{{ $trendChart }}" class="chart">

<h5>Full Counts per Bin</h5>
<img src="data:image/png;base64,{{ $fullCountsChart }}" class="chart">

<h5>Assets</h5>
@foreach($assets as $asset)
    <p>{{ $asset->asset_name }}</p>
    @if($asset->picture)
        <img src="{{ public_path('storage/' . $asset->picture) }}" class="asset-img">
    @else
        <p>No image</p>
    @endif
@endforeach

</body>
</html>

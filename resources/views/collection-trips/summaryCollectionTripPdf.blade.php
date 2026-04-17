<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Summary Collection Trip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; }
        .page { width: 100%; }
        .header { margin-bottom: 10px; }
        .header h1 { margin: 0 0 4px; font-size: 18px; }
        .meta { color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .metrics td { width: 20%; padding: 4px; vertical-align: top; }
        .metric-card {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            min-height: 72px;
        }
        .metric-title { font-size: 10px; color: #6b7280; margin-bottom: 6px; }
        .metric-value { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .metric-note { font-size: 9px; color: #6b7280; }
        .section-table td { width: 50%; padding: 5px; vertical-align: top; }
        .card { border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; }
        .card-header { background: #672d84; color: #fff; padding: 7px 9px; font-weight: 700; font-size: 11px; }
        .card-body { padding: 8px; }
        .chart { width: 100%; height: 170px; object-fit: contain; }
        .kpi-table th, .kpi-table td { border: 1px solid #e5e7eb; padding: 5px 6px; text-align: left; font-size: 9px; }
        .kpi-table th { background: #f3f4f6; }
        .insights { margin: 0; padding-left: 16px; }
        .insights li { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Summary Collection Trip</h1>
            <div class="meta">
                Range: {{ $rangeLabel }} |
                Asset: {{ $assetId ? (optional($assets->firstWhere('id', $assetId))->asset_name ?? 'Selected Asset') : 'All Assets' }} |
                Capacity Filter: {{ $capacityFilterTitle }}
            </div>
        </div>

        <table class="metrics">
            <tr>
                <td><div class="metric-card"><div class="metric-title">Total Collection Trips</div><div class="metric-value">{{ number_format($totalTrips) }}</div><div class="metric-note">Total trips in selected period.</div></div></td>
                <td><div class="metric-card"><div class="metric-title">Average Collection Rate</div><div class="metric-value">{{ $averageTripsMetric['value'] }}/{{ $averageTripsMetric['unit'] }}</div><div class="metric-note">Average collection frequency.</div></div></td>
                <td><div class="metric-card"><div class="metric-title">Peak Collection Day</div><div class="metric-value">{{ $weekdayPeakLabel }}</div><div class="metric-note">Most frequent day from Monday to Sunday.</div></div></td>
                <td><div class="metric-card"><div class="metric-title">Highest Collection Volume</div><div class="metric-value">{{ $mostUsedBin }}</div><div class="metric-note">Top bin by collection trips.</div></div></td>
                <td><div class="metric-card"><div class="metric-title">Highest Capacity</div><div class="metric-value">{{ $highestCapacityTile['value'] }}</div><div class="metric-note">{{ $highestCapacityTile['label'] }}</div></div></td>
            </tr>
        </table>

        <table class="section-table">
            <tr>
                <td>
                    <div class="card">
                        <div class="card-header">Collection Trip Trend</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['trend'] }}" alt="Trend"></div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-header">Collection Frequency by Bin</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['bin_frequency'] }}" alt="Bin Frequency"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="card">
                        <div class="card-header">Capacity Bins</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['capacity_bins'] }}" alt="Capacity Bins"></div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-header">Collection Frequency by Day</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['weekday'] }}" alt="Weekday Frequency"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="card">
                        <div class="card-header">Collection Frequency by Hour (7 AM - 7 PM)</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['hourly'] }}" alt="Hourly Frequency"></div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-header">Compartment Capacity by Asset & Device</div>
                        <div class="card-body"><img class="chart" src="{{ $pdfCharts['compartment'] }}" alt="Compartment Capacity"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="card">
                        <div class="card-header">Smart Bin System KPI</div>
                        <div class="card-body">
                            <table class="kpi-table">
                                <thead>
                                    <tr>
                                        <th>KPI</th>
                                        <th>Value</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($systemKpis as $kpi)
                                        <tr>
                                            <td>{{ $kpi['title'] }}</td>
                                            <td>{{ $kpi['value'] }}</td>
                                            <td>{{ $kpi['detail'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-header">{{ $period === 'monthly' ? 'Monthly' : ($period === 'weekly' ? 'Weekly' : 'Daily') }} Insights</div>
                        <div class="card-body">
                            @if(count($insights) > 0)
                                <ul class="insights">
                                    @foreach($insights as $insight)
                                        <li>{{ $insight }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>No insights available for this period.</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

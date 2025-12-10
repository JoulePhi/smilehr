<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Prevent rows from splitting across pages */
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Overtimes Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Position</th>
                <th>Total</th>
                <th>Fee</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($overtimes as $overtime)
                @php
                    if ($overtime->periode != null) {
                        $overtime->periode = new Carbon($overtime->periode);
                    }
                    $formattedDate = $overtime->periode ? $overtime->periode->format('M, Y') : '';
                    $overtime_fee = 0;
                    if ($overtime->user->financial) {
                        $overtime_fee = app(\App\Services\OvertimeService::class)->calculateFee(
                            $overtime->user->financial->overtime_calculation_method,
                            $overtime->user->financial->hourly_wages_based_on,
                            $overtime->user->financial->basic_salary,
                            $overtime->user->financial->fixed_allowance,
                            $overtime->total_hours,
                            $overtime->user->financial->overtime_multiplier,
                        );
                    }
                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $overtime->user ? Str::title($overtime->user->name) : '' }}</td>
                    <td>{{ $overtime->user && $overtime->user->branch ? Str::title($overtime->user->branch->name) : '' }}
                    </td>
                    <td>{{ $overtime->user && $overtime->user->department ? Str::title($overtime->user->department->name) : '' }}
                    </td>
                    <td>{{ $overtime->user && $overtime->user->position ? Str::title($overtime->user->position->name) : '' }}
                    </td>
                    <td>{{ $overtime->total_overtimes ?? 0 }}</td>
                    <td>{{ $overtime_fee ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

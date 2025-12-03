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
        <h2>Schedule Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Shift In</th>
                <th>Shift Out</th>
                <th>Approoved</th>
                <th>Validated</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($schedules as $schedule)
                @php
                    if ($schedule->shift_in_date != null) {
                        $schedule->shift_in_date = new Carbon($schedule->shift_in_date);
                    }
                    if ($schedule->shift_out_date != null) {
                        $schedule->shift_out_date = new Carbon($schedule->shift_out_date);
                    }

                    if ($schedule->shift_in != null) {
                        $schedule->shift_in = Carbon::createFromFormat('H:i:s', $schedule->shift_in);
                    }
                    if ($schedule->shift_out != null) {
                        $schedule->shift_out = Carbon::createFromFormat('H:i:s', $schedule->shift_out);
                    }
                    $formattedShiftInDate = $schedule->shift_in_date
                        ? $schedule->shift_in_date->format('D, Y-m-d')
                        : '';
                    $formattedShiftOutDate = $schedule->shift_out_date
                        ? $schedule->shift_out_date->format('D, Y-m-d')
                        : '';

                    $formattedShiftIn = $schedule->shift_in ? $schedule->shift_in->format('H:i') : '';
                    $formattedShiftOut = $schedule->shift_out ? $schedule->shift_out->format('H:i') : '';
                @endphp
                <tr>
                    <td>{{ $schedule->user->name ?? 'N/A' }}</td>
                    <td>{{ $formattedShiftInDate . ' ' . $formattedShiftIn }}</td>
                    <td>{{ $formattedShiftOutDate . ' ' . $formattedShiftOut }}</td>
                    <td>{{ $schedule->is_approved ? 'Yes' : 'No' }}</td>
                    <td>{{ $schedule->is_validated ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

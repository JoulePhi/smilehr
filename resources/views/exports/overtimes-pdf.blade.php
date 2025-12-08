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
                <th>Name</th>
                <th>Type</th>
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
            @foreach ($overtimes as $overtime)
                @php
                    if ($overtime->shift_in_date != null) {
                        $overtime->shift_in_date = new Carbon($overtime->shift_in_date);
                    }
                    if ($overtime->shift_out_date != null) {
                        $overtime->shift_out_date = new Carbon($overtime->shift_out_date);
                    }

                    if ($overtime->shift_in != null) {
                        $overtime->shift_in = Carbon::createFromFormat('H:i:s', $overtime->shift_in);
                    }
                    if ($overtime->shift_out != null) {
                        $overtime->shift_out = Carbon::createFromFormat('H:i:s', $overtime->shift_out);
                    }
                    $formattedShiftInDate = $overtime->shift_in_date
                        ? $overtime->shift_in_date->format('D, Y-m-d')
                        : '';
                    $formattedShiftOutDate = $overtime->shift_out_date
                        ? $overtime->shift_out_date->format('D, Y-m-d')
                        : '';

                    $formattedShiftIn = $overtime->shift_in ? $overtime->shift_in->format('H:i') : '';
                    $formattedShiftOut = $overtime->shift_out ? $overtime->shift_out->format('H:i') : '';
                @endphp
                <tr>
                    <td>{{ $overtime->user->name ?? 'N/A' }}</td>
                    <td>{{ $overtime->is_special ? 'Special Overtime' : 'Overtime' }}</td>
                    <td>{{ $formattedShiftInDate . ' ' . $formattedShiftIn }}</td>
                    <td>{{ $formattedShiftOutDate . ' ' . $formattedShiftOut }}</td>
                    <td>{{ $overtime->is_approved ? 'Yes' : 'No' }}</td>
                    <td>{{ $overtime->is_validated ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

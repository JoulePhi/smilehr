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
        <h2>Attendances Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Date In</th>
                <th>Date Out</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($attendances as $attendance)
                @php
                    if ($attendance->date_in != null) {
                        $attendance->date_in = new Carbon($attendance->date_in);
                    }
                    if ($attendance->date_out != null) {
                        $attendance->date_out = new Carbon($attendance->date_out);
                    }
                    if ($attendance->time_in != null) {
                        $attendance->time_in = new Carbon($attendance->time_in);
                    }
                    if ($attendance->time_out != null) {
                        $attendance->time_out = new Carbon($attendance->time_out);
                    }

                    $formattedDateIn = $attendance->date_in ? $attendance->date_in->format('D, Y-m-d') : '';
                    $formattedDateOut = $attendance->date_out ? $attendance->date_out->format('D, Y-m-d') : '';
                    $formattedTimeIn = $attendance->time_in ? $attendance->time_in->format('H:i:s') : '';
                    $formattedTimeOut = $attendance->time_out ? $attendance->time_out->format('H:i:s') : '';
                @endphp
                <tr>
                    <td>{{ $attendance->user?->name }}</td>
                    <td>
                        {{ $formattedDateIn }}
                        @if ($formattedTimeIn)
                            <br>
                            {{ $formattedTimeIn }}
                        @endif
                    </td>
                    <td>
                        {{ $formattedDateOut }}
                        @if ($formattedTimeOut)
                            <br>
                            {{ $formattedTimeOut }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

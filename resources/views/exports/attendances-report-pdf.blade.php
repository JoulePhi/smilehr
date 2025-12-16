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
                <th>Periode</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Position</th>
                <th>Cost Center</th>
                <th>Total Worked</th>
                <th>Total Visit</th>
                <th>Total Invalid</th>
                <th>Total Late</th>
                <th>Total Early Leave</th>
                <th>Total Overtime</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($attendances as $attendance)
                @php
                    if ($attendance->periode != null) {
                        $attendance->periode = new Carbon($attendance->periode);
                    }
                    $formattedDate = $attendance->periode ? $attendance->periode->format('M, Y') : '';

                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $attendance->user ? Str::title($attendance->user->name) : '' }}</td>
                    <td>{{ $attendance->user && $attendance->user->branch ? Str::title($attendance->user->branch->name) : '' }}
                    </td>
                    <td>{{ $attendance->user && $attendance->user->department ? Str::title($attendance->user->department->name) : '' }}
                    </td>
                    <td>{{ $attendance->user && $attendance->user->position ? Str::title($attendance->user->position->name) : '' }}
                    </td>
                    <td>{{ $attendance->user && $attendance->user->costCenter ? Str::title($attendance->user->costCenter->name) : '' }}
                    </td>
                    <td>{{ $attendance->total_worked_days ?? 0 }}</td>
                    <td>{{ $attendance->total_visit ?? 0 }}</td>
                    <td>{{ $attendance->total_invalid_count ?? 0 }}</td>
                    <td>{{ $attendance->total_late_minutes ?? 0 }}</td>
                    <td>{{ $attendance->total_early_minutes ?? 0 }}</td>
                    <td>{{ $attendance->total_overtime_minutes ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

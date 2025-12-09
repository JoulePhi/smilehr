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
        <h2>Leaves Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Position</th>
                <th>Leave</th>
                <th>Sick</th>
                <th>Permission</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($leaves as $leave)
                @php
                    if ($leave->periode != null) {
                        $leave->periode = new Carbon($leave->periode);
                    }
                    $formattedDate = $leave->periode ? $leave->periode->format('M, Y') : '';
                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $leave->user ? Str::title($leave->user->name) : '' }}</td>
                    <td>{{ $leave->user && $leave->user->branch ? Str::title($leave->user->branch->name) : '' }}</td>
                    <td>{{ $leave->user && $leave->user->department ? Str::title($leave->user->department->name) : '' }}
                    </td>
                    <td>{{ $leave->user && $leave->user->position ? Str::title($leave->user->position->name) : '' }}
                    </td>
                    <td>{{ $leave->total_leave ?? 0 }}</td>
                    <td>{{ $leave->total_sick ?? 0 }}</td>
                    <td>{{ $leave->total_permission ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

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
                <th>Name</th>
                <th>Date</th>
                <th>Type</th>
                <th>Remark</th>
                <th>Approoved</th>
                <th>Validated</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($leaves as $leave)
                @php
                    if ($leave->date != null) {
                        $leave->date = new Carbon($leave->date);
                    }
                    $formattedDate = $leave->date ? $leave->date->format('D, Y-m-d') : '';
                @endphp
                <tr>
                    <td>{{ $leave->user->name ?? 'N/A' }}</td>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $leave->type }}</td>
                    <td>{{ $leave->remarks }}</td>
                    <td>{{ $leave->is_approved ? 'Yes' : 'No' }}</td>
                    <td>{{ $leave->is_validated ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

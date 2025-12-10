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
        <h2>Debts Report</h2>
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
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($debts as $debt)
                @php
                    if ($debt->periode != null) {
                        $debt->periode = new Carbon($debt->periode);
                    }
                    $formattedDate = $debt->periode ? $debt->periode->format('M, Y') : '';

                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $debt->user ? Str::title($debt->user->name) : '' }}</td>
                    <td>{{ $debt->user && $debt->user->branch ? Str::title($debt->user->branch->name) : '' }}
                    </td>
                    <td>{{ $debt->user && $debt->user->department ? Str::title($debt->user->department->name) : '' }}
                    </td>
                    <td>{{ $debt->user && $debt->user->position ? Str::title($debt->user->position->name) : '' }}
                    </td>
                    <td>{{ $debt->total_debt_amount ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

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
            @foreach ($reimburses as $reimburse)
                @php
                    if ($reimburse->periode != null) {
                        $reimburse->periode = new Carbon($reimburse->periode);
                    }
                    $formattedDate = $reimburse->periode ? $reimburse->periode->format('M, Y') : '';

                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $reimburse->user ? Str::title($reimburse->user->name) : '' }}</td>
                    <td>{{ $reimburse->user && $reimburse->user->branch ? Str::title($reimburse->user->branch->name) : '' }}
                    </td>
                    <td>{{ $reimburse->user && $reimburse->user->department ? Str::title($reimburse->user->department->name) : '' }}
                    </td>
                    <td>{{ $reimburse->user && $reimburse->user->position ? Str::title($reimburse->user->position->name) : '' }}
                    </td>
                    <td>{{ $reimburse->total_reimburse_amount ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

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
                <th>Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Remark</th>
                <th>Approoved</th>
                <th>Paid</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
            @endphp
            @foreach ($debts as $debt)
                @php
                    if ($debt->date != null) {
                        $debt->date = new Carbon($debt->date);
                    }
                    $formattedDate = $debt->date ? $debt->date->format('D, Y-m-d') : '';
                @endphp
                <tr>
                    <td>{{ $debt->user->name ?? 'N/A' }}</td>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $debt->amount }}</td>
                    <td>{{ $debt->remarks }}</td>
                    <td>{{ $debt->is_approved ? 'Yes' : 'No' }}</td>
                    <td>{{ $debt->is_paid ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

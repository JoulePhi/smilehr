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
        <h2>Branches Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Longitude</th>
                <th>Latitude</th>
                <th>Radius</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($branches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->address }}</td>
                    <td>{{ $branch->longitude }}</td>
                    <td>{{ $branch->latitude }}</td>
                    <td>{{ $branch->radius }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

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
        <h2>Employees Report</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NIK</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->nik }}</td>
                    <td>{{ Str::title($employee->name) }}</td>
                    <td>{{ $employee->branch ? Str::title($employee->branch->name) : '-' }}</td>
                    <td>{{ $employee->department ? Str::title($employee->department->name) : '-' }}</td>
                    <td>{{ $employee->position ? Str::title($employee->position->name) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

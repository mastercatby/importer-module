<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Imports</h1>

    <form action="/importers/fromfile" method="post" enctype="multipart/form-data">
        @csrf
        Choice file to import (such as work_orders.html)</br>
        <input type="file" name="importfile" id="importfile" />
        <input type="submit" text="Submit" />
        </br></br>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>id</th>
                <th>type</th>
                <th>run_at</th>
                <th>entries_processed</th>
                <th>entries_created</th>
                <th>entries_skipped</th>
                <th>errors</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($list as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->run_at }}</td>
                <td>{{ $item->entries_processed }}</td>
                <td>{{ $item->entries_created }}</td>
                <td>{{ $item->entries_skipped }}</td>
                <td>{{ $item->errors }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import from file</title>
</head>
<body>
    <h1>Import from file</h1> 
    @if($msg)
        <p>{{ $msg }}</p>
    @endif
    <p><a href="/importers/">Back</a></p>
    <textarea style="width:100%;" rows="25">{{ $csv }}</textarea>
</body>
</html>
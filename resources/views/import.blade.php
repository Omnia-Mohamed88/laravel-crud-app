<!-- resources/views/import.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Import Categories</title>
</head>
<body>
    <h1>Import Categories</h1>
    <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.csv" />
        <button type="submit">Import</button>
    </form>
</body>
</html>

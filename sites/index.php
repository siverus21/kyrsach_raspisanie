<?php
require './vendor/autoload.php';

use App\Controllers\ScheduleController;

// Создание контроллера
$controller = new ScheduleController();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка CSV в базу данных</title>
</head>

<body>
    <h1>Загрузите CSV-файл</h1>
    <form action="/ajax/add_db_data.php" method="post" enctype="multipart/form-data">
        <label for="csv_file">Выберите файл CSV:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <button type="submit">Загрузить</button>
    </form>
</body>
<script src="./js/script.js" defer></script>

</html>
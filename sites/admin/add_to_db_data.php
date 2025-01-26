<?php
require './vendor/autoload.php';

use App\Controllers\ScheduleController;

$controller = new ScheduleController();

$TablesData = $controller->GetTablesFromWriteData();
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
        <div>
            <label for="selectTable">Выберите таблицу</label>
            <select name="selectTable" id="selectTable">
                <? foreach ($TablesData as $name): ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                <? endforeach; ?>
            </select>
        </div>
        <div>
            <label for="csv_file">Выберите файл CSV:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        </div>
        <button type="submit">Загрузить</button>
    </form>
</body>
<script src="./js/script.js" defer></script>

</html>
<?php
require 'vendor/autoload.php';
require 'excel_processor.php';

// Проверка параметра
$courseNumber = filter_input(INPUT_GET, 'course_number', FILTER_VALIDATE_INT);
if (!$courseNumber) {
    die('Некорректный параметр курса');
}

// Замеряем время начала скрипта
$start_time = microtime(true);

$cacheDir = 'cache';
$cacheFile = $cacheDir . '/schedule_cache_' . $courseNumber . '.php';

// Указываем путь к файлу Excel
$inputFileName = 'excel/test' . $courseNumber . '.xlsm';

// Вызываем функцию для обработки файла Excel
$arSchedule = processExcelFile($inputFileName, $cacheFile);

// Замеряем время окончания обработки данных
$end_time = microtime(true);

// Считаем общее время обработки
$execution_time = $end_time - $start_time;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/table/style.css">
    <title>Расписание занятий</title>
</head>

<body>
    <? renderTable($arSchedule); ?>
</body>

</html>
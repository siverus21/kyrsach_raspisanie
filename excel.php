<?php
require 'vendor/autoload.php';
require 'excel_processor.php';

// Замеряем время начала скрипта
$start_time = microtime(true);

$cacheDir = 'cache';
$cacheFile = $cacheDir . '/schedule_cache_' . $_GET["course_number"] . '.php';

// Проверяем, существует ли директория для кэша
if (!is_dir($cacheDir)) {
    // Если директория не существует, создаем ее
    mkdir($cacheDir, 0777, true);
}

// Указываем путь к файлу Excel
$inputFileName = 'excel/test' . $_GET["course_number"] . '.xlsm';

// Вызываем функцию для обработки файла Excel
$arSchedule = processExcelFile($inputFileName, $cacheFile);

// Функция для вывода массива в таблицу с добавлением пустых строк
function renderTable($arSchedule)
{
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
    echo '<thead><tr><th>Время</th><th>Дисциплина</th><th>Ф.И.О Преподавателя</th><th>Аудитория</th></tr></thead>';
    echo '<tbody>';

    // Перебор дней недели
    foreach ($arSchedule['schedule'] as $day => $times) {
        // Добавляем отступ перед каждым днем
        echo '<tr><td colspan="4" style="padding:10px; font-weight:bold; background-color:#f0f0f0; text-align: center; font-size: 20px;">' . htmlspecialchars($day) . '</td></tr>';

        // Для каждого времени занятий
        foreach ($arSchedule['timeSlots'][$day] as $time) {
            // Если есть занятия в этот временной интервал
            if (isset($times[$time])) {
                $info = $times[$time];
                $rowSpan = count($info['Дисциплина']); // Количество строк для объединения

                for ($i = 0; $i < $rowSpan; $i++) {
                    echo '<tr>';

                    // Вывод времени только для первой строки этого времени
                    if ($i == 0) {
                        echo '<td rowspan="' . $rowSpan . '">' . htmlspecialchars($time) . '</td>';
                    }

                    // Вывод дисциплины, преподавателя и аудитории
                    echo '<td>' . htmlspecialchars($info['Дисциплина'][$i]) . '</td>';
                    echo '<td>' . htmlspecialchars($info['Ф.И.О Преподавателя'][$i]) . '</td>';
                    echo '<td>' . htmlspecialchars($info['Аудитория'][$i]) . '</td>';

                    echo '</tr>';
                }
            } else {
                // Если занятий нет, добавляем пустую строку
                echo '<tr>';
                echo '<td>' . htmlspecialchars($time) . '</td>';
                echo '<td colspan="3" style="text-align:center;">-</td>';
                echo '</tr>';
            }
        }
    }

    echo '</tbody>';
    echo '</table>';
}

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
    <?php
    // Выводим таблицу с расписанием
    renderTable($arSchedule);
    ?>

    <!-- Время выполнения -->
    <p>Время выполнения скрипта: <?= round($execution_time, 2) ?> секунд</p>
</body>

</html>
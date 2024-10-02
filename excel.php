<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;

// Указываем путь к файлу Excel

$inputFileName = 'excel/test' . $_GET["course_number"] . '.xls';

// Загружаем файл Excel
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getActiveSheet();

// Переменные для хранения данных
$schedule = [];
$days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница'];

// Переменные для отслеживания текущего дня и времени
$currentDay = '';
$currentTime = '';

// Массив для сбора временных слотов по дням
$timeSlots = [];

// Функция для обработки RichText объектов
function getCellText($cellValue)
{
    if ($cellValue instanceof RichText) {
        $text = '';
        foreach ($cellValue->getRichTextElements() as $element) {
            $text .= $element->getText();
        }
        return $text;
    }
    return $cellValue;
}

// Перебор строк и колонок
foreach ($sheet->getRowIterator(11) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false); // Все ячейки, даже пустые

    $rowData = [];
    foreach ($cellIterator as $cell) {
        $cellValue = $cell->getValue();
        // Используем функцию для обработки RichText
        $rowData[] = getCellText($cellValue);
    }

    $dayOfWeek = trim($rowData[0]);
    $time = trim($rowData[1]);
    $discipline = trim($rowData[3]);
    $professor = trim($rowData[5]);
    $audience = trim($rowData[6]);

    // Если ячейка с днем недели не пуста, обновляем текущий день
    if (!empty($dayOfWeek)) {
        $currentDay = $dayOfWeek; // Прямое сохранение дня недели

        // Если день не существует в $timeSlots, инициализируем массив
        if (!isset($timeSlots[$currentDay])) {
            $timeSlots[$currentDay] = [];
        }
    }

    // Если ячейка с временем не пуста, обновляем текущее время
    if (!empty($time)) {
        $currentTime = $time;

        // Добавляем уникальное время в массив $timeSlots для текущего дня
        if (!in_array($time, $timeSlots[$currentDay])) {
            $timeSlots[$currentDay][] = $time; // Добавляем уникальное время
        }
    }

    // Проверка и обработка данных для дисциплины, преподавателя и аудитории
    if (!empty($discipline)) {
        // Если день недели пустой, сохраняем данные в массив по текущему дню
        if (!empty($currentDay)) {
            // Если в это время уже есть записи, добавляем новую дисциплину в массив
            if (!isset($schedule[$currentDay][$currentTime])) {
                $schedule[$currentDay][$currentTime] = [
                    'Дисциплина' => [],
                    'Ф.И.О Преподавателя' => [],
                    'Аудитория' => []
                ];
            }

            // Добавляем информацию по дисциплине и преподавателю
            $schedule[$currentDay][$currentTime]['Дисциплина'][] = $discipline;
            $schedule[$currentDay][$currentTime]['Ф.И.О Преподавателя'][] = $professor;

            // Добавляем аудиторию (если она не пустая)
            if (!empty(trim($audience))) {
                $schedule[$currentDay][$currentTime]['Аудитория'][] = $audience;
            } else {
                // Если аудитория не указана, сохраняем "Не указано" только для этой дисциплины
                $schedule[$currentDay][$currentTime]['Аудитория'][] = 'Не указано';
            }
        }
    }
}

// Функция для вывода массива в таблицу с добавлением пустых строк
function renderTable($schedule, $timeSlots)
{
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
    echo '<thead><tr><th>Время</th><th>Дисциплина</th><th>Ф.И.О Преподавателя</th><th>Аудитория</th></tr></thead>';
    echo '<tbody>';

    // Перебор дней недели
    foreach ($schedule as $day => $times) {
        // Добавляем отступ перед каждым днем
        echo '<tr><td colspan="4" style="padding:10px; font-weight:bold; background-color:#f0f0f0; text-align: center; font-size: 20px;">' . htmlspecialchars($day) . '</td></tr>';

        // Для каждого времени занятий
        foreach ($timeSlots[$day] as $time) {
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
    <?
    renderTable($schedule, $timeSlots);
    ?>
</body>

</html>
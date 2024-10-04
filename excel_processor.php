<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;

/**
 * Загружает Excel файл и возвращает объект Spreadsheet
 * @param string $inputFileName Путь к файлу Excel
 * @return \PhpOffice\PhpSpreadsheet\Spreadsheet Объект Excel Spreadsheet
 */
function loadSpreadsheet($inputFileName)
{
    return IOFactory::load($inputFileName);
}

/**
 * Парсит данные из листа Excel и возвращает расписание и временные слоты
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Лист Excel
 * @return array Массив с расписанием и временными слотами
 */
function parseSheet($sheet)
{
    $schedule = [];
    $timeSlots = [];
    $currentDay = '';
    $currentTime = '';

    foreach ($sheet->getRowIterator(11) as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = getCellText($cell->getValue());
        }

        // Обработка текущей строки
        list($currentDay, $currentTime) = processRow($rowData, $schedule, $timeSlots, $currentDay, $currentTime);
    }

    return [
        'schedule' => $schedule,
        'timeSlots' => $timeSlots
    ];
}

/**
 * Извлекает текст из ячейки, учитывая RichText
 * @param mixed $cellValue Значение ячейки Excel
 * @return string Текстовое значение ячейки
 */
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

/**
 * Обрабатывает одну строку Excel и обновляет расписание и временные слоты
 * @param array $rowData Массив данных строки Excel
 * @param array &$schedule Массив расписания
 * @param array &$timeSlots Массив временных слотов
 * @param string $currentDay Текущий день недели
 * @param string $currentTime Текущее время
 * @return array Обновленные текущий день и время
 */
function processRow($rowData, &$schedule, &$timeSlots, $currentDay, $currentTime)
{
    $dayOfWeek = trim($rowData[0] ?? '');
    $time = trim($rowData[1] ?? '');
    $discipline = trim($rowData[3] ?? '');
    $professor = trim($rowData[5] ?? '');
    $audience = trim($rowData[6] ?? '');

    // Логируем входящие данные
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Обработка строки: " . print_r($rowData, true) . "\n", FILE_APPEND);

    // Обновляем текущий день, если указан новый день недели
    if (!empty($dayOfWeek)) {
        $currentDay = $dayOfWeek;
        initializeDay($currentDay, $timeSlots);
        file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Новый день недели: $currentDay\n", FILE_APPEND);
    }

    // Обновляем текущее время, если указано новое время
    if (!empty($time)) {
        $currentTime = $time;
        updateTimeSlots($currentDay, $currentTime, $timeSlots);
        file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Новое время: $currentTime\n", FILE_APPEND);
    }

    // Добавляем информацию о занятии, если указана дисциплина
    if (!empty($discipline) && !empty($currentDay)) {
        if (!isset($schedule[$currentDay][$currentTime])) {
            $schedule[$currentDay][$currentTime] = [
                'Дисциплина' => [],
                'Ф.И.О Преподавателя' => [],
                'Аудитория' => []
            ];
            file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Инициализация записи для $currentDay на время $currentTime.\n", FILE_APPEND);
        }

        $schedule[$currentDay][$currentTime]['Дисциплина'][] = $discipline;
        $schedule[$currentDay][$currentTime]['Ф.И.О Преподавателя'][] = $professor;
        $schedule[$currentDay][$currentTime]['Аудитория'][] = !empty($audience) ? $audience : 'Не указано';

        // Логируем добавленные данные
        file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Добавлены данные: Дисциплина: $discipline, Преподаватель: $professor, Аудитория: $audience\n", FILE_APPEND);
    }

    return [$currentDay, $currentTime];
}

/**
 * Инициализирует день недели в массиве временных слотов
 * @param string $currentDay Текущий день недели
 * @param array &$timeSlots Массив временных слотов
 */
function initializeDay($currentDay, &$timeSlots)
{
    if (!isset($timeSlots[$currentDay])) {
        $timeSlots[$currentDay] = [];
    }
}

/**
 * Обновляет временные слоты для конкретного дня
 * @param string $currentDay Текущий день недели
 * @param string $currentTime Текущее время
 * @param array &$timeSlots Массив временных слотов
 */
function updateTimeSlots($currentDay, $currentTime, &$timeSlots)
{
    if (!in_array($currentTime, $timeSlots[$currentDay])) {
        $timeSlots[$currentDay][] = $currentTime;
    }
}

/**
 * Главная функция для обработки файла Excel
 * @param string $inputFileName Путь к файлу Excel
 * @param string $cacheFile Путь к файлу кэша
 * @param bool $flagUpdateChache Флаг обновления кэша
 * @return array Расписание и временные слоты
 */
function processExcelFile($inputFileName, $cacheFile, $flagUpdateChache = false)
{
    $logFile = __DIR__ . '/log.txt'; // Полный путь к логам

    // Логируем входящие параметры
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Входные параметры: inputFileName = $inputFileName, cacheFile = $cacheFile, flagUpdateChache = $flagUpdateChache \n", FILE_APPEND);

    // Проверяем, существует ли кэш
    if (file_exists($cacheFile) && !$flagUpdateChache) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Используем кэш: $cacheFile\n", FILE_APPEND);
        return include($cacheFile); // Возвращаем кэшированные данные
    }

    try {
        // Загружаем файл Excel
        $spreadsheet = loadSpreadsheet($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        // Парсим лист и возвращаем расписание
        $scheduleData = parseSheet($sheet);
    } catch (Exception $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка при загрузке или обработке файла: " . $e->getMessage() . "\n", FILE_APPEND);
        return []; // Возвращаем пустой массив в случае ошибки
    }

    // Кэшируем данные в файл
    if (!empty($cacheFile)) {
        file_put_contents($cacheFile, '<?php return ' . var_export($scheduleData, true) . ';');
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Кэш успешно обновлён: $cacheFile\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Путь к кэшу пустой.\n", FILE_APPEND);
    }

    return $scheduleData;
}

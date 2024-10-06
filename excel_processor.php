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
    try {
        return IOFactory::load($inputFileName);
    } catch (Exception $e) {
        throw $e;
    }
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

    // Обновляем текущий день, если указан новый день недели
    if (!empty($dayOfWeek)) {
        $currentDay = $dayOfWeek;
        initializeDay($currentDay, $timeSlots);
    }

    // Обновляем текущее время, если указано новое время
    if (!empty($time)) {
        $currentTime = $time;
        updateTimeSlots($currentDay, $currentTime, $timeSlots);
    }

    // Добавляем информацию о занятии, если указана дисциплина
    if (!empty($discipline) && !empty($currentDay)) {
        if (!isset($schedule[$currentDay][$currentTime])) {
            $schedule[$currentDay][$currentTime] = [
                'Дисциплина' => [],
                'Ф.И.О Преподавателя' => [],
                'Аудитория' => []
            ];
        }

        $schedule[$currentDay][$currentTime]['Дисциплина'][] = $discipline;
        $schedule[$currentDay][$currentTime]['Ф.И.О Преподавателя'][] = $professor;
        $schedule[$currentDay][$currentTime]['Аудитория'][] = !empty($audience) ? $audience : 'Не указано';
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
 * Обработка файла Excel с использованием кэша
 * @param string $inputFileName Путь к файлу Excel
 * @param string $cacheFile Путь к файлу кэша
 * @param bool $flagUpdateCache Флаг обновления кэша
 * @return array Расписание и временные слоты
 */
function processExcelFile($inputFileName, $cacheFile, $flagUpdateCache = false)
{
    // Если кэш существует и обновление не требуется, возвращаем кэшированные данные
    if (file_exists($cacheFile) && !$flagUpdateCache) {
        return include($cacheFile);
    }

    // Обновляем и записываем кэш
    return updateCache($inputFileName, $cacheFile);
}

/**
 * Обновление кэша с данными из файла Excel
 * @param string $inputFileName Путь к файлу Excel
 * @param string $cacheFile Путь к файлу кэша
 * @return array Расписание и временные слоты или пустой массив в случае ошибки
 */
function updateCache($inputFileName, $cacheFile)
{
    $logFile = __DIR__ . '/webhook/webhook_logs.txt';
    try {
        // Проверка на существование файла Excel
        if (!file_exists($inputFileName)) {
            // Логируем начало парсинга
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Файл Excel не найден: $inputFileName\n", FILE_APPEND);
            throw new Exception("Файл Excel не найден: $inputFileName");
        }

        // Загружаем и парсим файл Excel
        $spreadsheet = loadSpreadsheet($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        // Логируем начало парсинга
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Начинаем парсинг листа Excel.\n", FILE_APPEND);

        $scheduleData = parseSheet($sheet);

        // Проверяем, что данные были успешно распарсены
        if (empty($scheduleData)) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Расписание не найдено в файле: $inputFileName\n", FILE_APPEND);
            throw new Exception("Ошибка: Расписание не найдено в файле: $inputFileName");
        }

        // Записываем кэш
        writeCache(
            $cacheFile,
            $scheduleData
        );

        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Кэш обновлен: $cacheFile\n", FILE_APPEND);
        return $scheduleData;
    } catch (Exception $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка обновления кэша: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}


/**
 * Запись данных в кэш
 * @param string $cacheFile Путь к файлу кэша
 * @param array $data Данные для кэширования
 * @return string Статус операции ('success' или 'error')
 */
function writeCache($cacheFile, $data)
{
    $logFile = __DIR__ . '/webhook/webhook_logs.txt';
    if (!empty($cacheFile)) {
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';');
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Кэш успешно перезаписан.\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Путь к кэшу пустой.\n", FILE_APPEND);
    }
}

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

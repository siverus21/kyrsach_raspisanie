<?

require FUNCTIONS_PATH . 'chache.php';
require AUTOLOAD_PATH;

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
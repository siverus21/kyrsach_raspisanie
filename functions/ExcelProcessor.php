<?php

namespace App\Schedule;

require '../vendor/autoload.php'; // Подключаем автозагрузчик Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;
use Exception;

class ExcelProcessor
{
    private $cacheManager;
    private $inputFileName;
    private $cacheFile;

    public function __construct($inputFileName, $cacheFile)
    {
        $this->inputFileName = $inputFileName;
        $this->cacheFile = $cacheFile;
        $this->cacheManager = new CacheManager($cacheFile);
    }

    /**
     * Загружает Excel файл и возвращает объект Spreadsheet
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet Объект Excel Spreadsheet
     * @throws Exception Если файл не может быть загружен
     */
    public function loadSpreadsheet()
    {
        try {
            return IOFactory::load($this->inputFileName);
        } catch (Exception $e) {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка загрузки файла: {$this->inputFileName} - " . $e->getMessage() . "\n", FILE_APPEND);
            throw new Exception("Ошибка загрузки файла: {$this->inputFileName}");
        }
    }

    /**
     * Парсит данные из листа Excel и возвращает расписание и временные слоты
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Лист Excel
     * @return array Массив с расписанием и временными слотами
     */
    public function parseSheet($sheet)
    {
        $schedule = [];
        $timeSlots = [];
        $currentDay = '';
        $currentTime = '';

        foreach ($sheet->getRowIterator(11) as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $this->getCellText($cell->getValue());
            }

            // Обработка текущей строки
            list($currentDay, $currentTime) = $this->processRow($rowData, $schedule, $timeSlots, $currentDay, $currentTime);
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
    private function getCellText($cellValue)
    {
        if ($cellValue instanceof RichText) {
            $text = '';
            foreach ($cellValue->getRichTextElements() as $element) {
                $text .= $element->getText();
            }
            return $text;
        }
        return (string)$cellValue; // Убедитесь, что возвращаемое значение - строка
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
    private function processRow($rowData, &$schedule, &$timeSlots, $currentDay, $currentTime)
    {
        $dayOfWeek = trim($rowData[0] ?? '');
        $time = trim($rowData[1] ?? '');
        $discipline = trim($rowData[3] ?? '');
        $professor = trim($rowData[5] ?? '');
        $audience = trim($rowData[6] ?? '');

        // Обновляем текущий день, если указан новый день недели
        if (!empty($dayOfWeek)) {
            $currentDay = $dayOfWeek;
            $this->initializeDay($currentDay, $timeSlots);
        }

        // Обновляем текущее время, если указано новое время
        if (!empty($time)) {
            $currentTime = $time;
            $this->updateTimeSlots($currentDay, $currentTime, $timeSlots);
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
    private function initializeDay($currentDay, &$timeSlots)
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
    private function updateTimeSlots($currentDay, $currentTime, &$timeSlots)
    {
        if (!in_array($currentTime, $timeSlots[$currentDay])) {
            $timeSlots[$currentDay][] = $currentTime;
        }
    }

    /**
     * Обработка файла Excel с использованием кэша
     * @param bool $flagUpdateCache Флаг обновления кэша
     * @return array Расписание и временные слоты
     */
    public function processExcelFile($flagUpdateCache = false)
    {
        $cacheFilePath = '../cache/' . $this->cacheFile;
        // Проверяем кэш
        if (!$flagUpdateCache) {
            $cacheData = CacheManager::checkCache($cacheFilePath);
            if (is_array($cacheData)) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Получены данные из кэша.\n", FILE_APPEND);
                return $cacheData; // Кэш найден, возвращаем данные
            }
        }
        // Если кэш отсутствует или требуется обновление
        $inputFilePath = EXCEL_PATH . $this->inputFileName;
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Обновление кэша для файла: $inputFilePath\n", FILE_APPEND);

        return $this->cacheManager->updateCache($inputFilePath);
    }


}

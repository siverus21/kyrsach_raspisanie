<?php

namespace App\Schedule;

use App\Schedule\ExcelProcessor;

class CacheManager
{
    private $cacheFile;
    private $excelProcessor;

    public function __construct($cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    /**
     * Обновление кэша с данными из файла Excel
     * @param string $inputFileName Путь к файлу Excel
     * @return array Расписание и временные слоты или пустой массив в случае ошибки
     */
    public function updateCache($inputFileName)
    {
        try {
            // Проверка на существование файла Excel
            if (!file_exists($inputFileName)) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Файл Excel не найден: $inputFileName\n", FILE_APPEND);
                throw new \Exception("Файл Excel не найден: $inputFileName");
            }

            // Создаем объект ExcelProcessor для работы с Excel файлом
            $this->excelProcessor = new ExcelProcessor($inputFileName, $this->cacheFile);

            // Загружаем и парсим файл Excel
            $spreadsheet = $this->excelProcessor->loadSpreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Начинаем парсинг листа Excel.\n", FILE_APPEND);

            $scheduleData = $this->excelProcessor->parseSheet($sheet);

            // Проверяем, что данные были успешно распарсены
            if (empty($scheduleData)) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Расписание не найдено в файле: $inputFileName\n", FILE_APPEND);
                throw new \Exception("Ошибка: Расписание не найдено в файле: $inputFileName");
            }

            // Генерация пути к файлу кэша
            $cacheFilePath = $this->generateCacheFilePath($inputFileName);

            // Записываем кэш
            $this->writeCache($scheduleData, $cacheFilePath);

            // Логируем успешное обновление кэша
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Кэш обновлен: {$cacheFilePath}\n", FILE_APPEND);

            return $scheduleData;
        } catch (\Exception $e) {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка обновления кэша: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Генерация пути к файлу кэша на основе имени Excel-файла
     * @param string $inputFileName Путь к файлу Excel
     * @return string Путь к файлу кэша с расширением .php
     */
    private function generateCacheFilePath($inputFileName)
    {
        // Меняем расширение файла на .php
        return preg_replace('/\.xls(x)?$/', '.php', $inputFileName);
    }

    /**
     * Запись данных в кэш
     * @param array $data Данные для кэширования
     * @param string $cacheFilePath Путь к файлу для кэша
     */
    private function writeCache($data, $cacheFilePath)
    {
        if (!empty($cacheFilePath)) {

            $cacheFilePath = str_replace('excel', 'cache', $cacheFilePath);

            // Проверка и создание директорий, если они не существуют
            $dir = dirname($cacheFilePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($cacheFilePath, '<?php return ' . var_export($data, true) . ';');
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Кэш успешно записан в файл: {$cacheFilePath}\n", FILE_APPEND);
        } else {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Путь к кэшу пустой.\n", FILE_APPEND);
        }
    }

    /**
     * Проверка файла и возвращение массива
     * @return array|string Массив данных или 'error'
     */
    public function checkCache()
    {
        // Проверяем, существует ли файл
        if (file_exists($this->cacheFile)) {
            // Проверяем, можно ли его прочитать
            if (is_readable($this->cacheFile)) {
                // Получаем содержимое файла
                $data = include $this->cacheFile;
                // Проверяем, действительно ли данные являются массивом
                if (is_array($data)) {
                    return $data;
                } else {
                    return 'Содержимое файла - не массив.';
                }
            } else {
                return 'Содержимое файла - не читаются';
            }
        } else {
            return 'Такого файла нет';
        }
    }
}

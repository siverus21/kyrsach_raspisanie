<?php

namespace App\Schedule;

use App\Schedule\ExcelProcessor;
use App\Schedule\WebSocketNotifier;

class CacheManager
{
    private $cacheFile;
    private $excelProcessor;
    private $webSocketNotifier;

    public function __construct($cacheFile, WebSocketNotifier $webSocketNotifier)
    {
        $this->cacheFile = $cacheFile;
        $this->webSocketNotifier = $webSocketNotifier;
    }

    public function updateCache($inputFileName)
    {
        try {
            if (!file_exists($inputFileName)) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Файл Excel не найден: $inputFileName\n", FILE_APPEND);
                throw new \Exception("Файл Excel не найден: $inputFileName");
            }

            $this->excelProcessor = new ExcelProcessor($inputFileName, $this->cacheFile);
            $spreadsheet = $this->excelProcessor->loadSpreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Начинаем парсинг листа Excel.\n", FILE_APPEND);

            $scheduleData = $this->excelProcessor->parseSheet($sheet);

            if (empty($scheduleData)) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Расписание не найдено в файле: $inputFileName\n", FILE_APPEND);
                throw new \Exception("Ошибка: Расписание не найдено в файле: $inputFileName");
            }

            $cacheFilePath = $this->generateCacheFilePath($inputFileName);
            $this->writeCache($scheduleData, $cacheFilePath);

            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Кэш обновлен: {$cacheFilePath}\n", FILE_APPEND);

            // Отправка уведомления через WebSocket
            $this->webSocketNotifier->sendNotification($inputFileName);

            return $scheduleData;
        } catch (\Exception $e) {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка обновления кэша: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    private function generateCacheFilePath($inputFileName)
    {
        return str_replace(['.xls', '.xlsm'], '.php', $inputFileName);
    }

    private function writeCache($data, $cacheFilePath)
    {
        if (!empty($cacheFilePath)) {
            $cacheFilePath = str_replace('excel', 'cache', $cacheFilePath);
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

    public function checkCache()
    {
        if (file_exists($this->cacheFile)) {
            if (is_readable($this->cacheFile)) {
                $data = include $this->cacheFile;
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

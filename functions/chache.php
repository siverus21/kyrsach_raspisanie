<?

require FUNCTIONS_PATH . '/web_socket.php';
require FUNCTIONS_PATH . '/excel_processor.php';

/**
 * Обновление кэша с данными из файла Excel
 * @param string $inputFileName Путь к файлу Excel
 * @param string $cacheFile Путь к файлу кэша
 * @return array Расписание и временные слоты или пустой массив в случае ошибки
 */
function updateCache($inputFileName, $cacheFile)
{
    try {
        // Проверка на существование файла Excel
        if (!file_exists($inputFileName)) {
            // Логируем начало парсинга
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Файл Excel не найден: $inputFileName\n", FILE_APPEND);
            throw new Exception("Файл Excel не найден: $inputFileName");
        }

        // Загружаем и парсим файл Excel
        $spreadsheet = loadSpreadsheet($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        // Логируем начало парсинга
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Начинаем парсинг листа Excel.\n", FILE_APPEND);

        $scheduleData = parseSheet($sheet);

        // Отправляем обновленные данные через WebSocket
        $socket = fsockopen('kyrsach', 8081);
        if ($socket) {
            $message = json_encode(['schedule' => $scheduleData]);
            fwrite($socket, $message);
            fclose($socket);
        }

        // Проверяем, что данные были успешно распарсены
        if (empty($scheduleData)) {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Расписание не найдено в файле: $inputFileName\n", FILE_APPEND);
            throw new Exception("Ошибка: Расписание не найдено в файле: $inputFileName");
        }

        // Записываем кэш
        writeCache(
            $cacheFile,
            $scheduleData
        );

        // Логируем успешное обновление кэша
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Кэш обновлен: $cacheFile\n", FILE_APPEND);

        // Отправляем обновление по WebSocket
        sendWebSocketNotification($scheduleData);

        return $scheduleData;
    } catch (Exception $e) {
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка обновления кэша: " . $e->getMessage() . "\n", FILE_APPEND);
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
    if (!empty($cacheFile)) {
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';');
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Кэш успешно перезаписан.\n", FILE_APPEND);
    } else {
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Путь к кэшу пустой.\n", FILE_APPEND);
    }
}

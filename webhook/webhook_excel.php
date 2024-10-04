<?php
// Путь к файлу для записи логов вебхука
$logFile = __DIR__ . '/webhook_logs.txt'; // Полный путь к логам
$cacheDir = __DIR__ . '/../cache'; // Полный путь к директории кэша

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $webhookData = file_get_contents('php://input');
    $webhookData = html_entity_decode($webhookData);
    $dataArray = json_decode($webhookData, true);

    // Логируем полученные данные
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Получены данные:\n" . print_r($dataArray, true) . "\n", FILE_APPEND);

    if (!empty($dataArray)) {
        // Извлекаем путь к файлу
        $filePath = $dataArray['File-Path'] ?? null;
        $fileName = str_replace(array("test", ".xlsm"), "", basename($filePath));

        if ($filePath) {
            $cacheFile = $cacheDir . '/schedule_cache_' . $fileName . '.php';

            // Проверяем, существует ли директория для кэша
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Создана директория кэша: $cacheDir\n", FILE_APPEND);
            }

            require __DIR__ . '/../vendor/autoload.php';
            require __DIR__ . '/../excel_processor.php';

            try {
                // Задержка в 1 секунду
                sleep(1);

                // Обрабатываем файл Excel и обновляем кэш
                $arSchedule = processExcelFile($filePath, $cacheFile, true);

                // Логируем успешную обработку и обновление кэша
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Успешно обработан файл: $filePath\n", FILE_APPEND);

                // Проверяем, создался ли новый кэш
                if (file_exists($cacheFile)) {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Кэш успешно обновлён: $cacheFile\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Кэш не обновлён!\n", FILE_APPEND);
                }
            } catch (Exception $e) {
                // Логируем ошибки при обработке файла
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка при обработке файла: {$e->getMessage()}\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Не указан путь к файлу в данных вебхука.\n", FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка обработки, исходные данные:\n" . htmlspecialchars($webhookData) . "\n", FILE_APPEND);
    }
} else {
    echo "<h2>Метод запроса не поддерживается. Используйте POST для отправки вебхука.</h2>";
}

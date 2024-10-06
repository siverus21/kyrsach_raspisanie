<?php
// Путь к файлу для записи логов вебхука
$logFile = __DIR__ . '/webhook_logs.txt'; // Полный путь к логам
$cacheDir = __DIR__ . '/../cache'; // Полный путь к директории кэша

// Проверка прав на запись в директорию логов
if (!is_writable(__DIR__)) {
    error_log("Ошибка: Директория для логов не доступна для записи.");
    exit('Ошибка: Директория для логов не доступна для записи.');
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Получен POST запрос.\n", FILE_APPEND);

    $webhookData = file_get_contents('php://input');
    $webhookData = html_entity_decode($webhookData);

    // Логируем входные данные вебхука для отладки
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Данные вебхука: $webhookData\n", FILE_APPEND);

    $dataArray = json_decode($webhookData, true);

    // Проверяем корректность данных
    if (!empty($dataArray) && isset($dataArray['File-Path'])) {
        $filePath = $dataArray['File-Path'];

        // Логируем путь к файлу
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Путь к файлу: $filePath\n", FILE_APPEND);

        // Проверяем наличие файла
        if (file_exists($filePath)) {
            $fileName = str_replace(array("test", ".xlsm"), "", basename($filePath));
            $cacheFile = $cacheDir . '/schedule_cache_' . $fileName . '.php';

            try {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Начинаем обработку файла: $filePath\n", FILE_APPEND);

                require_once __DIR__ . '/../excel_processor.php';
                require_once __DIR__ . '/../vendor/autoload.php';

                // Обновление кэша
                $scheduleData = updateCache($filePath, $cacheFile);

                // Логируем успешное обновление
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Успешно обработан файл: $filePath\n", FILE_APPEND);
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка при обработке файла: {$e->getMessage()}\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Файл не найден $filePath\n", FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Некорректные данные вебхука.\n", FILE_APPEND);
    }
} else {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Ошибка: Метод запроса не поддерживается.\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Метод запроса не поддерживается']);
}

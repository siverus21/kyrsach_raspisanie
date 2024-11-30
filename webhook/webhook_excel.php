<?php
require '../vendor/autoload.php';
require '../config.php';

use App\Schedule\CacheManager;

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Получен POST запрос.\n", FILE_APPEND);

    $webhookData = file_get_contents('php://input');
    $webhookData = html_entity_decode($webhookData);

    // Логируем входные данные вебхука для отладки
    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Данные вебхука: $webhookData\n", FILE_APPEND);

    $dataArray = json_decode($webhookData, true);

    // Проверяем корректность данных
    if (!empty($dataArray) && isset($dataArray['File-Path'])) {
        $filePath = $dataArray['File-Path'];

        // Логируем путь к файлу
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Путь к файлу: $filePath\n", FILE_APPEND);

        // Проверяем наличие файла
        if (file_exists($filePath)) {
            try {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Начинаем обработку файла: $filePath\n", FILE_APPEND);

                $cache = new CacheManager($filePath);
                $cache->updateCache($filePath);

                // Логируем успешное обновление
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Успешно обработан файл: $filePath\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка при обработке файла: {$e->getMessage()}\n", FILE_APPEND);
            }
        } else {
            file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Файл не найден $filePath\n", FILE_APPEND);
        }
    } else {
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Некорректные данные вебхука.\n", FILE_APPEND);
    }
} else {
    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Ошибка: Метод запроса не поддерживается.\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Метод запроса не поддерживается']);
}

<?

/**
 * Отправляет сообщение через WebSocket с обновленными данными
 * @param array $scheduleData Обновленные данные расписания
 */
function sendWebSocketNotification($scheduleData)
{
    try {
        // Подключаемся к WebSocket серверу
        $wsClient = new WebSocket\Client("ws://kyrsach:8081");

        // Формируем сообщение с обновлёнными данными
        $message = json_encode([
            'status' => 'updated',
            'schedule' => $scheduleData
        ]);

        // Отправляем сообщение
        $wsClient->send($message);
        $wsClient->close();
    } catch (Exception $e) {
        // Логируем ошибку при отправке сообщения по WebSocket
        file_put_contents(__DIR__ . '/webhook/webhook_logs.txt', date('Y-m-d H:i:s') . " - Ошибка WebSocket: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

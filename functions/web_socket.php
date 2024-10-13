<?php

namespace App\Schedule;

class WebSocketNotifier
{
    private $wsClient;

    public function __construct($url)
    {
        $this->connect($url);
    }

    /**
     * Устанавливает соединение с WebSocket сервером
     * @param string $url URL WebSocket сервера
     */
    private function connect($url)
    {
        try {
            $this->wsClient = new WebSocket\Client($url);
        } catch (Exception $e) {
            $this->logError("Ошибка подключения к WebSocket: " . $e->getMessage());
        }
    }

    /**
     * Отправляет сообщение через WebSocket с обновленными данными
     * @param array $scheduleData Обновленные данные расписания
     */
    public function sendNotification($scheduleData)
    {
        try {
            // Формируем сообщение с обновлёнными данными
            $message = json_encode([
                'status' => 'updated',
                'schedule' => $scheduleData
            ]);

            // Отправляем сообщение
            $this->wsClient->send($message);
            $this->wsClient->close();
        } catch (Exception $e) {
            // Логируем ошибку при отправке сообщения по WebSocket
            $this->logError("Ошибка при отправке сообщения по WebSocket: " . $e->getMessage());
        }
    }

    /**
     * Логирует ошибки в файл
     * @param string $errorMessage Сообщение об ошибке
     */
    private function logError($errorMessage)
    {
        file_put_contents(__DIR__ . '/webhook/webhook_logs.txt', date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    }
}

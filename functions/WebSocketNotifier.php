<?php

namespace App\Schedule;

require __DIR__ . '/../vendor/autoload.php';  // Подключаем автозагрузчик Composer

use WebSocket\Client;
use Exception;

class WebSocketNotifier
{
    private $wsClient;

    public function __construct($url)
    {
        // Подключаемся к WebSocket серверу
        $this->connect($url);
    }

    // Подключение к WebSocket серверу
    private function connect($url)
    {
        try {
            $this->wsClient = new Client($url);
            echo "Подключено к WebSocket серверу: {$url}\n";
        } catch (Exception $e) {
            $this->logError("Ошибка подключения к WebSocket: " . $e->getMessage());
        }
    }

    public function sendUpdateFile($filePath)
    {
        try {
            // Формируем сообщение для отправки
            $message = json_encode(['action' => 'sendUpdateFile',
                'status' => 'updated',
                'message' => 'updated file',
                'file' => $filePath
            ]);

            // Отправляем сообщение на сервер
            $this->wsClient->send($message);
            echo "Сообщение 'Hello world' отправлено на сервер: {$message}\n";
        } catch (Exception $e) {
            $this->logError("Ошибка при отправке сообщения Hello World: " . $e->getMessage());
        }
    }

    // Логирование ошибок
    private function logError($errorMessage)
    {
        file_put_contents(__DIR__ . '/../logs/error_log.txt', date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    }
}

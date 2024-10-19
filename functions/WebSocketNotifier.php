<?php

namespace App\Schedule;

require '../vendor/autoload.php';

use WebSocket\Client;
use Exception;

class WebSocketNotifier
{
    private $wsClient;

    public function __construct($url)
    {
        $this->connect($url);
    }

    private function connect($url)
    {
        try {
            $this->wsClient = new Client($url);
        } catch (Exception $e) {
            $this->logError("Ошибка подключения к WebSocket: " . $e->getMessage());
        }
    }

    /**
     * Отправляет уведомление с путем к файлу Excel
     * @param string $filePath Путь к файлу Excel
     */
    public function sendNotification($filePath)
    {
        try {
            $message = json_encode([
                'status' => 'updated',
                'file_path' => $filePath
            ]);

            $this->wsClient->send($message);
            $this->wsClient->close();
        } catch (Exception $e) {
            $this->logError("Ошибка при отправке сообщения по WebSocket: " . $e->getMessage());
        }
    }

    private function logError($errorMessage)
    {
        file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    }
}

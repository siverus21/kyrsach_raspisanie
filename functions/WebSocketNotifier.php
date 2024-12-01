<?php

namespace App\Schedule;

require __DIR__ . '/../vendor/autoload.php';

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

    public function sendNotification($filePath)
    {
        try {
            $relativePath = str_replace("D:\\OSPanel\\domains\\kyrsach\\excel\\", "", $filePath);
            $relativePath = str_replace("\\", "/", $relativePath);

            $message = json_encode([
                'status' => 'updated',
                'file_path' => $relativePath
            ]);

            try {
                // Логируем сообщение перед отправкой
                file_put_contents(
                    LOG_PATH,
                    date('Y-m-d H:i:s') . " - Отправляемое сообщение: " . print_r($message, true) . "\n",
                    FILE_APPEND
                );

                $this->wsClient->send($message);  // Отправка сообщения

                // Логирование после успешной отправки
                file_put_contents(
                    LOG_PATH,
                    date('Y-m-d H:i:s') . " - Сообщение отправлено: " . print_r($message, true) . "\n",
                    FILE_APPEND
                );
            } catch (Exception $e) {
                // Логируем ошибку, если произошла проблема при отправке
                $this->logError("Ошибка при отправке сообщения по WebSocket: " . $e->getMessage());
                file_put_contents(
                    LOG_PATH,
                    date('Y-m-d H:i:s') . " - Ошибка при отправке уведомления для файла $filePath: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }
        } catch (Exception $e) {
            $this->logError("Ошибка при отправке сообщения по WebSocket: " . $e->getMessage());
            file_put_contents(
                LOG_PATH,
                date('Y-m-d H:i:s') . " - Ошибка при отправке уведомления для файла $filePath: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }
    }

    public function sendHelloWorld()
    {
        try {
            $message = json_encode([
                'status' => 'info',
                'message' => 'Hello world'
            ]);

            // Логируем сообщение перед отправкой
            file_put_contents(
                LOG_PATH,
                date('Y-m-d H:i:s') . " - Отправляемое сообщение: " . print_r($message, true) . "\n",
                FILE_APPEND
            );

            // Отправляем сообщение всем клиентам
            foreach ($this->clients as $client) {
                $client->send($message);
            }

            // Логируем успешную отправку
            file_put_contents(
                LOG_PATH,
                date('Y-m-d H:i:s') . " - Сообщение отправлено: " . print_r($message, true) . "\n",
                FILE_APPEND
            );
        } catch (Exception $e) {
            // Логируем ошибку
            $this->logError("Ошибка при отправке сообщения Hello world: " . $e->getMessage());
            file_put_contents(
                LOG_PATH,
                date('Y-m-d H:i:s') . " - Ошибка при отправке сообщения Hello world: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }
    }


    private function logError($errorMessage)
    {
        file_put_contents(__DIR__ . '/../logs/websocket_errors.log', date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
    }
}

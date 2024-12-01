<?php
require '../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ScheduleWebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $userFiles;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userFiles = []; // Ассоциативный массив для хранения открытых файлов
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'openFile':
                    $file = $data['file'];
                    $this->userFiles[$from->resourceId] = $file;
                    echo "Пользователь {$from->resourceId} открыл файл: {$file}\n";
                    break;

                case 'closeFile':
                    unset($this->userFiles[$from->resourceId]);
                    echo "Пользователь {$from->resourceId} закрыл файл\n";
                    break;
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->userFiles[$conn->resourceId]); // Удаляем записи об открытых файлах
        echo "Соединение закрыто: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ошибка: {$e->getMessage()}\n";
        $conn->close();
    }

    public function notifyFileUpdate($filePath)
    {
        $message = json_encode([
            'status' => 'updated',
            'file_path' => $filePath
        ]);

        // Отправляем сообщение всем клиентам
        foreach ($this->clients as $client) {
            $client->send($message);
            echo "Уведомлен пользователь {$client->resourceId} об изменении файла: {$filePath}\n";
        }
    }

    // Пример Ratchet WebSocket-сервера
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        // Присваиваем уникальный идентификатор (например, номер подключения)
        $userId = spl_object_id($conn); // Генерация уникального ID
        $conn->send(json_encode(['action' => 'setId', 'id' => $userId]));

        echo "Новое соединение: {$userId}\n";
    }

}

// Порт, на котором будет работать WebSocket-сервер
$port = 8081;

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new ScheduleWebSocketServer()
        )
    ),
    $port
);

echo "WebSocket-сервер успешно запущен на порту {$port}\n";
$server->run();

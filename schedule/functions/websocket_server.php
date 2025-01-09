<?php
require __DIR__ . '/../vendor/autoload.php';  // Подключаем автозагрузчик Composer
require __DIR__ . '/../config.php';  // Путь к файлу конфигурации с параметрами (например, URL для WebSocket)

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface
{
    // Хранение клиентов и других данных
    protected $clients;
    protected $userFiles;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();  // Для хранения подключенных клиентов
        $this->userFiles = [];  // Массив для хранения открытых файлов пользователя
    }

    // Метод для обработки нового подключения
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Новое соединение: {$conn->resourceId}\n";

        // Отправляем уникальный ID клиенту
        $conn->send(json_encode([
            'action' => 'setId',
            'id' => $conn->resourceId
        ]));
    }

    // Метод для обработки полученных сообщений
    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Получено сообщение от {$from->resourceId}: $msg\n";

        // Декодируем сообщение
        $data = json_decode($msg, true);

        if (isset($data['action'])) {
            // Обрабатываем различные действия
            switch ($data['action']) {
                case 'openFile':
                    // Сохраняем информацию о файле, который открыл пользователь
                    $this->userFiles[$from->resourceId] = $data['file'];
                    echo "Пользователь {$from->resourceId} открыл файл: {$data['file']}\n";
                    break;

                case 'closeFile':
                    // Удаляем информацию о закрытом файле
                    unset($this->userFiles[$from->resourceId]);
                    echo "Пользователь {$from->resourceId} закрыл файл\n";
                    break;

                case 'getConnectedUsers':
                    // Отправляем список подключенных пользователей
                    $connectedUsers = [];
                    foreach ($this->clients as $client) {
                        $connectedUsers[] = ['id' => $client->resourceId];
                    }

                    $response = json_encode(['users' => $connectedUsers]);
                    $from->send($response);
                    echo "Отправлен список подключенных пользователей пользователю {$from->resourceId}: $response\n";
                    break;

                case 'sendHelloWorld':
                    // Отправляем сообщение "Hello World" всем подключенным пользователям
                    $this->sendHelloWorld();
                    break;

                case 'sendUpdateFile':
                    // Отправляем сообщение "Hello World" всем подключенным пользователям
                    $this->sendUpdateFile($data['file']);
                    break;


                default:
                    echo "Неизвестное действие: {$data['action']}\n";
                    break;
            }
        } else {
            echo "Получены некорректные данные: $msg\n";
        }
    }

    // Метод для обработки закрытия соединения
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->userFiles[$conn->resourceId]);
        echo "Соединение закрыто: {$conn->resourceId}\n";
    }

    // Метод для обработки ошибок
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ошибка: {$e->getMessage()}\n";
        $conn->close();
    }

    // Метод для отправки сообщения всем подключенным пользователям
    public function sendHelloWorld()
    {
        $message = json_encode(['status' => 'info',
            'message' => 'Hello world'
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
            echo "Сообщение 'Hello world' отправлено пользователю {$client->resourceId}\n";
        }
    }

    // Метод для отправки сообщения всем подключенным пользователям
    public function sendUpdateFile($pathFile)
    {
        $message = json_encode([
            'status' => 'updated',
            'file_path' => $pathFile
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
            echo "Сообщение update отправлено пользователю {$client->resourceId}\n";
        }
    }
}

// Запуск WebSocket сервера
$port = 8081;

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new WebSocketServer()
        )
    ),
    $port
);

echo "WebSocket-сервер успешно запущен на порту {$port}\n";
$server->run();

<?
require '../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ScheduleWebSocketServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Добавляем новое соединение
        $this->clients->attach($conn);
        echo "Новое соединение: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Передаем сообщение всем клиентам
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Отключаем клиента
        $this->clients->detach($conn);
        echo "Соединение закрыто: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ошибка: {$e->getMessage()}\n";
        $conn->close();
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

// Сообщение об успешном запуске сервера
echo "WebSocket-сервер успешно запущен на порту {$port}\n";

// Запуск сервера
$server->run();

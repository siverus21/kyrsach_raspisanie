<?php
require_once 'backend/db/database.php';
require_once 'backend/controllers/ScheduleController.php';

// Подключение к базе данных
$db = getDatabaseConnection();

// Создание контроллера
$controller = new ScheduleController($db);

// Маршрутизация
$action = $_GET['action'] ?? 'list';

// switch ($action) {
//     case 'create':
//         $controller->createSchedule();
//         break;
//     case 'store':
//         $controller->storeSchedule();
//         break;
//     case 'view':
//         $id = $_GET['id'] ?? null;
//         $controller->viewSchedule($id);
//         break;
//     default:
//         $controller->listSchedules();
//         break;
// }

// Закрытие соединения с базой данных
pg_close($db);

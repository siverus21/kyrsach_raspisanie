<?
require '../vendor/autoload.php';
require '../config.php';

use App\Schedule\DirectoryManager;

// Получаем имя директории из запроса
$data = json_decode(file_get_contents('php://input'), true);
if (!empty($data['LINK'])) {
    $link = realpath(EXCEL_PATH . $data['LINK']);

    // Проверяем, существует ли такой путь
    if ($link && is_dir($link)) {
        // Создаем объект DirectoryManager с этим путём
        $directoryManager = new \App\Schedule\DirectoryManager($link);

        // Получаем имена вложенных папок
        $subdirectories = $directoryManager->getSubdirectories();

        // Возвращаем массив вложенных папок в формате JSON
        echo json_encode($subdirectories, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Directory not found'], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['error' => 'No link provided'], JSON_UNESCAPED_UNICODE);
}

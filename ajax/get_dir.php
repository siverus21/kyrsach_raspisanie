<?php
require '../config.php';
require FUNCTIONS_PATH . '/dir.php';

// Получаем имя директории из запроса
$data = json_decode(file_get_contents('php://input'), true);
if (!empty($data['LINK'])) {
    $link = realpath(EXCEL_PATH . $data['LINK']);

    // Проверяем, существует ли такой путь
    if ($link && is_dir($link)) {
        // Получаем имена вложенных папок
        $subdirectories = getSubdirectories($link);

        // Возвращаем массив вложенных папок в формате JSON
        echo json_encode($subdirectories, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Directory not found'], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['error' => 'No link provided'], JSON_UNESCAPED_UNICODE);
}

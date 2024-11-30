<?php
require '../vendor/autoload.php';
require '../excel.php';

use App\Schedule\ExcelProcessor;
use App\Schedule\Render;
// use App\Schedule\WebSocketNotifier; // Не забудьте подключить WebSocketNotifier

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['PATH'])) {
    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Данные ajax: " . print_r($data, true) . "\n", FILE_APPEND);

    $link = str_replace(['.xlsm', '.xls'], '.php', $data['PATH']);
    $cacheFile = $link;

    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . " - Данные cacheFile: " . print_r($cacheFile, true) . "\n", FILE_APPEND);

    // $webSocketNotifier = new WebSocketNotifier(WS_SERVER_URL); // Создаем экземпляр WebSocketNotifier

    $excel = new ExcelProcessor($data['PATH'], $link, $cacheFile);

    $arSchedule = $excel->processExcelFile();

    $render = new Render($arSchedule);

    echo $render->renderTable();
} else {
    echo json_encode(['status' => 'error', 'message' => 'PATH не указан']);
}

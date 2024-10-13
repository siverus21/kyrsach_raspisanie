<?
require '../vendor/autoload.php'; // Подключаем автозагрузчик Composer

use App\Schedule\ExcelProcessor;
use App\Schedule\Render;

$data = json_decode(file_get_contents('php://input'), true);
if (!empty($data['PATH'])) {
    $link = str_replace('.xls', '.php', $data['PATH']);

    $excel = new ExcelProcessor($data['PATH'], $link);

    $arSchedule = $excel->processExcelFile();

    $render = new Render($arSchedule);

    return $render->renderTable();
}

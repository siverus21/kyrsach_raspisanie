<?

use App\Controllers\ScheduleController;

$controller = new ScheduleController();

$arResult = $controller->GetAllRoom();

include 'template.php';

<?
require '../vendor/autoload.php';

use App\Controllers\ScheduleController;

$controller = new ScheduleController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    print_r($_POST);
}

<?php
require '../vendor/autoload.php';

use App\Controllers\ScheduleController;

$controller = new ScheduleController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        if ($_POST["selectTable"]) {
            $tmpName = $_FILES['csv_file']['tmp_name'];
            $response = $controller->UploadInfoDB($tmpName, $_POST["selectTable"]);
            echo $response;
        } else {
            echo "Ошибка не выбрана таблица.";
        }
    } else {
        echo "Ошибка загрузки файла.";
    }
}
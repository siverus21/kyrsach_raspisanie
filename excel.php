<?php
require 'vendor/autoload.php';
require 'excel_processor.php';

// Проверка параметра
$courseNumber = filter_input(INPUT_GET, 'course_number', FILTER_VALIDATE_INT);
if (!$courseNumber) {
    die('Некорректный параметр курса');
}

// Замеряем время начала скрипта
$start_time = microtime(true);

$cacheDir = 'cache';
$cacheFile = $cacheDir . '/schedule_cache_' . $courseNumber . '.php';

// Указываем путь к файлу Excel
$inputFileName = 'excel/test' . $courseNumber . '.xlsm';

// Вызываем функцию для обработки файла Excel
$arSchedule = processExcelFile($inputFileName, $cacheFile);

// Замеряем время окончания обработки данных
$end_time = microtime(true);

// Считаем общее время обработки
$execution_time = $end_time - $start_time;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/table/style.css">
    <title>Расписание занятий</title>
</head>

<body>
    <div class="">
    </div>
    <div class="schedule-table">
        <? renderTable($arSchedule);
        ?>
    </div>
    <script>
        var socket = new WebSocket('ws://kyrsach:8081');

        socket.onopen = function() {
            console.log("Успешное подключение к WebSocket-серверу");
        };

        socket.onmessage = function(event) {
            console.log("Получено сообщение: " + event.data);
        };

        socket.onclose = function(event) {
            console.log("WebSocket соединение закрыто");
        };

        socket.onerror = function(error) {
            console.log("Ошибка WebSocket: " + error.message);
        };


        socket.onmessage = function(event) {
            const data = JSON.parse(event.data);

            // Обновляем содержимое таблицы с расписанием
            updateTable(data.schedule);
        };

        function updateTable() {
            // Извлекаем параметры из URL
            const urlParams = new URLSearchParams(window.location.search);
            const courseNumber = urlParams.get('course_number');

            // Проверяем, получили ли мы course_number
            if (!courseNumber) {
                console.error('course_number не найден в GET запросе.');
                return;
            }

            // AJAX-запрос для получения обновленной таблицы с сервера
            fetch(`/excel.php/?course_number=${courseNumber}`, {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(html => {
                    // Заменяем содержимое <div class="schedule-table">
                    document.querySelector('.schedule-table').innerHTML = html;
                })
                .catch(error => {
                    console.error('Ошибка при обновлении таблицы:', error);
                });
        }
    </script>


</body>

</html>
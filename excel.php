<?
require 'template/header.php';
require FUNCTIONS_PATH . '/excel_processor.php'
?>
<div class="schedule-table">
    <? renderTable($arSchedule); ?>
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
<? require 'template/footer.php'; ?>
let socket = null;
let currentOpenedFile = null;
let userId = null; // Переменная для хранения уникального ID

function openWebSocket() {
    if (socket && (socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CONNECTING)) {
        console.log("WebSocket уже подключен или в процессе подключения.");
        return;
    }

    socket = new WebSocket('ws://kyrsach:8081'); // Инициализация должна быть внутри функции

    socket.onopen = function () {
        console.log("Успешное подключение к WebSocket-серверу");
    };

    socket.onmessage = function (event) {
        console.log('Сообщение от WebSocket:', event.data); // Проверка всех сообщений

        try {
            const message = JSON.parse(event.data);
            console.log('Получено сообщение:', message);

            // Если сообщение содержит "Hello world", выводим соответствующий лог
            if (message.status === 'info' && message.message === 'Hello world') {
                console.log('Получено сообщение Hello world');
            }

            // Если сообщение содержит ID пользователя, сохраняем его
            if (message.action === 'setId') {
                userId = message.id;
                console.log(`Уникальный ID пользователя установлен: ${userId}`);
            }

            // Обработка обновления файла
            if (message.status === 'updated' && message.file_path) {
                updateTable(message.file_path);
            }

        } catch (e) {
            console.error('Ошибка при разборе сообщения:', e);
        }
    };

    socket.onclose = function () {
        console.log("WebSocket соединение закрыто. Повторное подключение...");
        setTimeout(openWebSocket, 5000); // Попытка переподключения через 5 секунд
    };

    socket.onerror = function (error) {
        console.error("Ошибка WebSocket:", error);
    };
}

// Отправка данных на сервер для открытия файла
function sendFileToServer(filePath) {
    if (socket && socket.readyState === WebSocket.OPEN) {
        if (currentOpenedFile !== filePath) {
            const message = JSON.stringify({ action: 'openFile', file: filePath });
            socket.send(message);
            currentOpenedFile = filePath;
            console.log(`Файл открыт: ${filePath}`);
        }
    } else {
        console.warn("WebSocket не подключен. Ожидание подключения...");
    }
}

// Обновление таблицы через AJAX
function updateTable(filePath) {
    console.log(`Обновление таблицы для файла: ${filePath}`);
    fetch('/ajax/render.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ PATH: filePath })
    })
        .then(response => response.text())
        .then(data => {
            const tableElement = document.querySelector('.schedule-table');
            if (tableElement) {
                tableElement.innerHTML = data;
                console.log('Таблица успешно обновлена');
            } else {
                console.error('Элемент .schedule-table не найден');
            }
        })
        .catch(error => console.error('Ошибка обновления таблицы:', error));
}

function checkWebSocketStatus() {
    const status = socket ? socket.readyState : -1;
    console.log(`WebSocket статус: ${status} (0 - соединение, 1 - открыто, 2 - закрывается, 3 - закрыто)`);
    if (status !== 1) {
        console.warn("WebSocket не открыт, текущий статус: " + status);
    }
}

// Инициализация WebSocket при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    openWebSocket();

    // Проверка статуса WebSocket каждые 5 секунд
    setInterval(checkWebSocketStatus, 5000);
});

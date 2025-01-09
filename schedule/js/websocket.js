let socket = null;
let userId = null; // Переменная для хранения уникального ID

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

// Инициализация WebSocket при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    const socket = new WebSocket('ws://kyrsach:8081'); // Адрес вашего WebSocket сервера

    socket.onopen = () => {
        console.log('Соединение открыто');
    };

    socket.onmessage = (event) => {
        console.log('Сообщение от сервера:', event.data);
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
                let pathToFile = message.file_path;
                // Убираем "D:\\OSPanel\\domains\\kyrsach\\excel\\" из пути
                let relativePath = pathToFile.replace(/^D:\\OSPanel\\domains\\kyrsach\\excel\\/, '').replace(/\\/g, '/');
                updateTable(relativePath);
            }

        } catch (e) {
            console.error('Ошибка при разборе сообщения:', e);
        }
    };

    socket.onclose = () => {
        console.log('Соединение закрыто');
    };

    socket.onerror = (error) => {
        console.error('Ошибка WebSocket:', error);
    };
});
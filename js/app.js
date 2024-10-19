document.addEventListener('DOMContentLoaded', function () {
    let getSelect = document.querySelectorAll('select.choise__select');

    // Функция для отправки AJAX-запроса
    function sendAjaxRequest() {
        let allLinks = Array.from(getSelect).map(select => select.value).join('/');

        // Проверка на наличие значений
        if (allLinks.trim() !== '') {
            // Отправляем запрос с помощью fetch
            fetch('/ajax/get_dir.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ LINK: allLinks })
            })
            .then(async response => {
                // Проверяем, был ли ответ успешным
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    // Находим блок choise, чтобы создать новый select
                    const choiseDiv = document.querySelector('.choise');

                    // Создаем новый select с классом choise__select
                    const newSelect = document.createElement('select');
                    newSelect.classList.add('choise__select');

                    // Создаем пустой option
                    const emptyOption = document.createElement('option');
                    emptyOption.value = '';
                    emptyOption.textContent = ''; // Добавляем пустой option
                    newSelect.appendChild(emptyOption);

                    // Проверяем и добавляем уникальные значения в новый select
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item;
                            option.textContent = item;
                            newSelect.appendChild(option);
                        });
                    } else {
                        const errorOption = document.createElement('option');
                        errorOption.value = '';
                        errorOption.textContent = data.error || 'Произошла ошибка';
                        newSelect.appendChild(errorOption);
                    }

                    choiseDiv.appendChild(newSelect); // Добавляем новый select в блок choise

                    // Обновляем список всех select элементов с классом choise__select
                    getSelect = document.querySelectorAll('select.choise__select');

                    // Добавляем обработчик события изменения для нового select
                    newSelect.addEventListener('change', handleChange);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
        } else {
            console.log('LINK is empty');
        }
    }

    // Функция для обработки изменения select
    function handleChange(event) {
        // Находим индекс текущего select
        const index = Array.from(getSelect).indexOf(event.target);

        // Удаляем все select, которые находятся после текущего
        const selectsToRemove = Array.from(getSelect).slice(index + 1);
        selectsToRemove.forEach(select => select.remove());

        // Обновляем список select элементов после удаления
        getSelect = document.querySelectorAll('select.choise__select');

        // Отправляем AJAX-запрос для получения новых данных
        if (event.target.value !== '') { // Только если выбрано значение
            sendAjaxRequest();
        }

        // Проверяем, является ли текущий select последним и содержит ли его значение ".xls"
        if (index === getSelect.length - 1 && event.target.value.includes('.xls')) {
            sendAjaxRenderRequest();
        }
    }

    // Функция для отправки AJAX-запроса на /ajax/render.php
    function sendAjaxRenderRequest() {
        let allLinks = Array.from(getSelect).map(select => select.value).join('/');
        
        fetch('/ajax/render.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ PATH: allLinks }) // Отправляем необходимые данные
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка в запросе на render.php');
            }
            return response.text(); // Получаем ответ как текст (HTML)
        })
        .then(data => {            
            // Вставляем HTML в элемент с классом schedule-table
            document.querySelector('.schedule-table').innerHTML = data;
        })
        .catch(error => {
            console.error('Ошибка при отправке запроса на render.php:', error);
        });
    }

    // Добавляем обработчик события изменения для каждого select с классом choise__select
    getSelect.forEach(function (selectElement) {
        selectElement.addEventListener('change', handleChange);
    });
});

let socket;  // Переменная для хранения WebSocket-соединения

// Функция для открытия WebSocket-соединения
function openWebSocket() {
    // Если соединение уже открыто, не создаем новое
    if (socket && socket.readyState === WebSocket.OPEN) {
        return;
    }
    
    socket = new WebSocket('ws://kyrsach:8081');
    
    socket.onopen = function() {
        console.log("Успешное подключение к WebSocket-серверу");
    };
    
    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        // Исправляем путь, заменяя обратные слеши на прямые и удаляя лишние символы
        let fixedFilePath = data.file_path
            .replace(/\\/g, '/')  // Заменяем все обратные слеши на прямые
            .replace('D:/OSPanel/domains/kyrsach/excel', '')  // Удаляем начальный путь
            .trim();  // Убираем лишние пробелы
        
        console.log(fixedFilePath);
        
        // Обновляем содержимое таблицы с расписанием
        updateTable(fixedFilePath);
    };
    
    socket.onclose = function(event) {
        console.log("WebSocket соединение закрыто");
    };
    
    socket.onerror = function(error) {
        console.log("Ошибка WebSocket: " + error.message);
    };
}

// Открываем WebSocket один раз при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    openWebSocket();
});

// Функция для обновления таблицы
function updateTable(linkAjax) {
    fetch('/ajax/render.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ PATH: linkAjax })  // Отправляем необходимые данные
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка в запросе на render.php');
        }
        return response.text();  // Получаем ответ как текст (HTML)
    })
    .then(data => {            
        // Вставляем HTML в элемент с классом schedule-table
        document.querySelector('.schedule-table').innerHTML = data;
    })
    .catch(error => {
        console.error('Ошибка при отправке запроса на render.php:', error);
    });
}

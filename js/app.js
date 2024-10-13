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
        console.log(allLinks);
        
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
            console.log('Ответ от /ajax/render.php:', data);
            
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

document.addEventListener('DOMContentLoaded', () => {
    let getSelect = document.querySelectorAll('select.choise__select');
    let currentOpenedFile = null; // Для WebSocket взаимодействия

    // Функция отправки AJAX-запроса для загрузки новых опций
    function sendAjaxRequest() {
        const allLinks = Array.from(getSelect).map(select => select.value).join('/');

        if (allLinks.trim() === '') {
            console.log('LINK is empty');
            return;
        }

        fetch('/ajax/get_dir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ LINK: allLinks })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    addNewSelect(data);
                } else {
                    console.error('Ошибка данных:', data.error);
                }
            })
            .catch(error => {
                console.error('Ошибка запроса:', error);
            });
    }

    // Добавление нового select элемента
    function addNewSelect(data) {
        const choiseDiv = document.querySelector('.choise');
        const newSelect = document.createElement('select');
        newSelect.classList.add('choise__select');

        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        newSelect.appendChild(emptyOption);

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item;
            option.textContent = item;
            newSelect.appendChild(option);
        });

        choiseDiv.appendChild(newSelect);
        getSelect = document.querySelectorAll('select.choise__select');
        newSelect.addEventListener('change', handleChange);
    }

    // Обработчик изменения select
    function handleChange(event) {
        const index = Array.from(getSelect).indexOf(event.target);

        // Удаляем все select, которые находятся после текущего
        const selectsToRemove = Array.from(getSelect).slice(index + 1);
        selectsToRemove.forEach(select => select.remove());

        // Обновляем список всех select
        getSelect = document.querySelectorAll('select.choise__select');

        // Если значение пустое, закрываем файл на сервере
        if (event.target.value === '') {
            closeFileOnServer();
            return;
        }

        // Составляем полный путь из значений всех выбранных select
        const fullPath = Array.from(getSelect).map(select => select.value).join('/');

        // Если выбранный элемент является файлом Excel
        if (index === getSelect.length - 1 && /\.(xls|xlsm)$/.test(event.target.value)) {
            sendFileToServer(fullPath); // Логируем открытие файла
            sendAjaxRenderRequest(fullPath); // Рендерим таблицу
        } else {
            sendAjaxRequest();
        }
    }

    function sendFileToServer(filePath) {
        if (socket && socket.readyState === WebSocket.OPEN) {
            const message = JSON.stringify({
                action: 'openFile',
                file: filePath,
            });
            socket.send(message);
        } else {
            console.error('WebSocket соединение не установлено.');
        }
    }


    // AJAX-запрос для отрисовки таблицы
    function sendAjaxRenderRequest(filePath) {        
        fetch('/ajax/render.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ PATH: filePath })
        })
            .then(response => response.text())
            .then(data => {
                document.querySelector('.schedule-table').innerHTML = data;
                currentOpenedFile = filePath;
            })
            .catch(error => {
                console.error('Ошибка отрисовки таблицы:', error);
            });
    }

    getSelect.forEach(select => select.addEventListener('change', handleChange));
});

document.addEventListener('DOMContentLoaded', function() {
    let getSelect = document.querySelectorAll('select');

    // Функция для отправки AJAX-запроса
    function sendAjaxRequest() {
        let allLinks = Array.from(getSelect).map(select => select.value).join(',');
        console.log(allLinks);
        
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
            .then(response => {
                // Проверяем, был ли ответ успешным
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Parsed data:', data);

                // Находим блок choise, чтобы создать новый select
                const choiseDiv = document.querySelector('.choise');

                // Создаем новый select
                const newSelect = document.createElement('select');

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

                // Добавляем обработчик события изменения для нового select
                newSelect.addEventListener('change', function() {
                    if (this.value) { // Проверяем, выбран ли элемент
                        sendAjaxRequest(); // Отправляем AJAX-запрос только при выборе элемента
                    }
                });
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
        } else {
            console.log('LINK is empty');
        }
    }

    // Добавляем обработчик события изменения для каждого select
    getSelect.forEach(function(selectElement) {
        selectElement.addEventListener('change', sendAjaxRequest);
    });
});

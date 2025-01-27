document.addEventListener("DOMContentLoaded", function () {

    document.querySelector('form').addEventListener('submit', async function(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('/ajax/add_db_data.php', {
                method: 'POST',
                body: formData, // Отправляем данные формы
            });

            if (response.ok) {
                const result = await response.text(); // Предполагаем, что сервер возвращает текст
                console.log(result);
            } else {
                console.error('Ошибка загрузки файла:', response.statusText);
            }
        } catch (error) {
            console.error('Ошибка сети:', error);
        }
    });

});
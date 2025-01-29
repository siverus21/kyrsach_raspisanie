document.addEventListener("DOMContentLoaded", function () {

    document.querySelector('form').addEventListener('submit', async function(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('/ajax/add_db_data.php', {
                method: 'POST',
                body: formData,
            });

            if (response.ok) {
                const result = await response.text();
            } else {
                console.error('Ошибка загрузки файла:', response.statusText);
            }
        } catch (error) {
            console.error('Ошибка сети:', error);
        }
    });

});
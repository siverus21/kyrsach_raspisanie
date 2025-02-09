document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll('input');

    inputs.forEach(input => {
        const blockContext = input.closest('.modal-content__item');
        if (!blockContext) return;

        const searchBlock = blockContext.querySelector(".search-block");
        if (!searchBlock) return;

        const listItems = Array.from(searchBlock.querySelectorAll('.search-block__li'));

        // Отображение всех элементов, если input пуст и в фокусе
        input.addEventListener("focus", function () {
            if (!input.value.trim()) {
                searchBlock.style.opacity = "1";
                searchBlock.style.visibility = "visible";
                listItems.forEach(item => item.style.display = "block");
            }
        });

        input.addEventListener("input", function () {
            const query = input.value.toLowerCase().trim();

            if (query) {
                searchBlock.style.opacity = "1";
                searchBlock.style.visibility = "visible";

                listItems.forEach(item => {
                    const itemValue = item.dataset.value.toLowerCase();
                    item.style.display = itemValue.includes(query) ? "block" : "none";
                });
            } else {
                // Показать все элементы, если поле пустое
                listItems.forEach(item => item.style.display = "block");
            }
        });

        listItems.forEach(item => {
            item.addEventListener("click", function () {
                input.value = item.dataset.value;
                input.setAttribute('data-id', item.dataset.id);
                searchBlock.style.opacity = "0";
                searchBlock.style.visibility = "hidden";
            });
        });

        input.addEventListener("blur", function () {
            setTimeout(() => {
                searchBlock.style.opacity = "0";
                searchBlock.style.visibility = "hidden";
            }, 200); // Задержка для кликов по элементам списка
        });
    });

    const form = document.querySelector('.modal-content__form');
    const saveButton = document.getElementById('save-block');

    if (form && saveButton) {
        saveButton.addEventListener("click", function (event) {
            event.preventDefault();
            console.log("Форма не отправлена, данные сохранены локально.");
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {

    const blockList = document.getElementById('block-list');
    const modal = document.getElementById('modal');
    const blockContentInput = document.getElementById('block-content');
    const timeInput = document.getElementById('time');
    const fioInput = document.getElementById('fio');
    const saveBlockButton = document.getElementById('save-block');

    const columns = document.querySelectorAll('.column');

    let draggedBlock = null;
    let isNewBlock = false;
    let dropTargetColumn = null;

    // Создание нового блока с данными
    function createBlock(data = { content: 'New Block', time: '', fio: '' }) {
        const block = document.createElement('div');
        block.classList.add('block');
        block.draggable = true;
        block.dataset.content = data.content;
        block.dataset.time = data.time;
        block.dataset.fio = data.fio;

        updateBlockContent(block);
        addDeleteButton(block);
        attachBlockListeners(block); // Привязываем обработчики событий для нового блока
        return block;
    }

    // Функция обновления отображения контента блока
    function updateBlockContent(block) {
        const content = block.dataset.content || '';
        const time = block.dataset.time || '';
        const fio = block.dataset.fio || '';
        block.innerHTML = `
            <div>${content}</div>
            <div>${time}</div>
            <div>${fio}</div>
        `;
        addDeleteButton(block);
    }

    // Функция добавления кнопки удаления
    function addDeleteButton(block) {
        if (!block.querySelector('.delete')) {
            const deleteButton = document.createElement('button');
            deleteButton.className = 'delete';
            deleteButton.textContent = '×';
            deleteButton.addEventListener('click', () => block.remove());
            block.appendChild(deleteButton);
        }
    }

    // Привязка слушателей событий для блоков
    function attachBlockListeners(block) {
        block.addEventListener('dblclick', () => {
            // Если это блок из блока-списка, не разрешаем редактировать
            if (block.parentElement && block.parentElement.id === 'block-list') return;

            modal.style.display = 'flex';

            blockContentInput.value = block.dataset.content || '';
            timeInput.value = block.dataset.time || '';
            fioInput.value = block.dataset.fio || '';

            saveBlockButton.onclick = () => {
                block.dataset.content = blockContentInput.value;
                block.dataset.time = timeInput.value;
                block.dataset.fio = fioInput.value;

                updateBlockContent(block);
                modal.style.display = 'none';
                resetVariables(); // Сброс всех переменных после редактирования
            };
        });

        block.addEventListener('dragstart', (event) => {
            draggedBlock = block;
            isNewBlock = false; // Это уже существующий блок
        });
    }

    // Обработчики для создания нового блока при перетаскивании
    blockList.addEventListener('dragstart', (event) => {
        if (event.target.classList.contains('block')) {
            draggedBlock = event.target.cloneNode(true);
            isNewBlock = true; // Новый блок
        }
    });

    // Обработчики для колонок (перетаскивание и сброс)
    columns.forEach(column => {
        column.addEventListener('dragover', (event) => {
            event.preventDefault();
            column.classList.add('drop-target');
            dropTargetColumn = column;
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('drop-target');
            dropTargetColumn = null;
        });

        column.addEventListener('drop', (event) => {
            event.preventDefault();
            column.classList.remove('drop-target');

            if (draggedBlock) {
                if (isNewBlock) {
                    if (column.id !== 'block-list') {
                        modal.style.display = 'flex';
                        blockContentInput.value = '';
                        timeInput.value = '';
                        fioInput.value = '';

                        saveBlockButton.onclick = () => {
                            const data = {
                                content: blockContentInput.value.trim(),
                                time: timeInput.value.trim(),
                                fio: fioInput.value.trim(),
                            };

                            if (!data.content) return;

                            const newBlock = createBlock(data);
                            column.appendChild(newBlock);

                            modal.style.display = 'none';
                            resetVariables(); // Сброс всех переменных после добавления блока
                        };
                    }
                } else {
                    if (column.id !== 'block-list') {
                        column.appendChild(draggedBlock);
                    }
                }
            }
        });
    });

    // Закрытие модального окна по клику
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            resetVariables(); // Сброс всех переменных при закрытии окна
        }
    });

    // Инициализация блоков при загрузке
    document.querySelectorAll('.block').forEach(block => {
        attachBlockListeners(block);
    });

    // Функция сброса всех переменных
    function resetVariables() {
        draggedBlock = null;
        isNewBlock = false;
        dropTargetColumn = null;
    }
});
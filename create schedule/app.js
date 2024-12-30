const blockList = document.getElementById('block-list');
const columns = document.querySelectorAll('.column');
const modal = document.getElementById('modal');
const blockContentInput = document.getElementById('block-content');
const saveBlockButton = document.getElementById('save-block');

let draggedBlock = null;
let isNewBlock = false;
let dropTargetColumn = null;

// Создание нового блока
function createBlock(content = 'New Block') {
    const block = document.createElement('div');
    block.classList.add('block');
    block.textContent = content;
    block.draggable = true;
    addDeleteButton(block);
    attachBlockListeners(block);
    return block;
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
        blockContentInput.value = block.textContent.replace('×', '').trim();

        saveBlockButton.onclick = () => {
            block.textContent = blockContentInput.value;
            addDeleteButton(block); // Повторно добавляем кнопку удаления
            modal.style.display = 'none';
            resetVariables();  // Сброс всех переменных после редактирования
        };
    });

    block.addEventListener('dragstart', (event) => {
        draggedBlock = block;
        isNewBlock = false; // Это не новый блок
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
                // Новый блок, показываем модальное окно
                if (column.id !== 'block-list') {
                    modal.style.display = 'flex';
                    blockContentInput.value = '';
                }
            } else {
                // Перемещаем существующий блок
                if (column.id !== 'block-list') {
                    column.appendChild(draggedBlock);
                }
            }
        }
    });
});

// Сохранение нового блока или изменения в уже существующем
saveBlockButton.onclick = () => {
    if (!dropTargetColumn || dropTargetColumn.id === 'block-list') return;

    const newBlockContent = blockContentInput.value.trim();
    if (!newBlockContent) return;

    let newBlock;
    if (isNewBlock) {
        newBlock = createBlock(newBlockContent);
    } else if (draggedBlock) {
        draggedBlock.textContent = newBlockContent;
        addDeleteButton(draggedBlock); // Повторно добавляем кнопку удаления
        newBlock = draggedBlock;
    }

    dropTargetColumn.appendChild(newBlock);

    modal.style.display = 'none';
    resetVariables(); // Сброс всех переменных после добавления блока
};

// Закрытие модального окна по клику
modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        modal.style.display = 'none';
        resetVariables();  // Сброс всех переменных при закрытии окна
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

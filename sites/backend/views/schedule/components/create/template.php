<?
// Get data
$viewSchedule = new ViewSchedule();
$arResult = $viewSchedule->ResultData();
?>

<div class="view">
    <div class="view__container">
        <div class="column" id="block-list">
            <div class="block" draggable="true" data-template="New Block">New Block</div>
        </div>
        <div class="column" id="column-1"></div>
        <div class="column" id="column-2"></div>
        <div class="column" id="column-3"></div>
        <div class="column" id="column-4"></div>
        <div class="column" id="column-5"></div>
        <div class="column" id="column-6"></div>
    </div>

    <div class="view__modal" id="modal">
        <div class="modal-content">
            <h3>Enter Block Details</h3>
            <label for="block-content">Название предмета</label>
            <input type="text" id="block-content" placeholder="Enter content">
            <label for="time">Время</label>
            <input type="text" id="time" placeholder="Enter content">
            <label for="fio">ФИО препода</label>
            <input type="text" id="fio" placeholder="Enter content">
            <button id="save-block">Save</button>
        </div>
    </div>
</div>
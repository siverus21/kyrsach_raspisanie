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
            <form class="modal-content__form" action="">
                <div class="modal-content__item">
                    <label for="block-content">Название предмета</label>
                    <input type="text" id="block-content" placeholder="Enter content">
                    <div class="search-block">
                        <ul class="search-block__ul">
                            <? foreach ($arResult["ALL_DISCIPLINE"] as $arItem): ?>
                                <li class="search-block__li" data-id="<?= $arItem["id"] ?>" data-value="<?= $arItem["name"]; ?>">
                                    <?= $arItem["name"]; ?>
                                </li>
                            <? endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="modal-content__item">
                    <label for="time">Время</label>
                    <input type="text" id="time" placeholder="Enter content">
                </div>
                <div class="modal-content__item">
                    <label for="fio">ФИО препода</label>
                    <input type="text" id="fio" placeholder="Enter content">
                    <div class="search-block">
                        <ul class="search-block__ul">
                            <? foreach ($arResult["ALL_LECTOR"] as $arItem): ?>
                                <li class="search-block__li" data-id="<?= $arItem["id"] ?>" data-value="<?= $arItem["FIO"]; ?>">
                                    <?= $arItem["FIO"]; ?>
                                </li>
                            <? endforeach; ?>
                        </ul>
                    </div>
                </div>
                <button id="save-block">Save</button>
            </form>
        </div>
    </div>
</div>
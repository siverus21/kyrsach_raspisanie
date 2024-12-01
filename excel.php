<?php
require 'vendor/autoload.php';
require 'config.php';
require 'template/header.php';
?>
<div class="choise">
    <select class="choise__select" id="fileSelector">
        <option value=""></option>
        <option value="Очное">Очное</option>
        <option value="Заочное">Заочное</option>
    </select>
</div>
<div class="schedule-table"></div>
<?php require 'template/footer.php'; ?>
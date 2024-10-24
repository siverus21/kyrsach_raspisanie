<?php
require 'vendor/autoload.php';
require 'config.php';

use App\Schedule\WebSocketNotifier;

// Инициализация WebSocketNotifier
$webSocketNotifier = new WebSocketNotifier(WS_SERVER_URL);

require 'template/header.php';
?>
<div class="choise">
    <select class="choise__select" name="" id="">
        <option value=""></option>
        <option value="Очное">Очное</option>
        <option value="Заочное">Заочное</option>
    </select>
</div>
<div class="schedule-table">

</div>
<?php require 'template/footer.php'; ?>
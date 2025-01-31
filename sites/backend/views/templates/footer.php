<?php

use App\Controllers\ShowComponents;
use App\Config\Config;

Config::init();

?>
</main>
<footer>
    <p>&copy; 2025 Электронное расписание</p>
</footer>
<? ShowComponents::renderFooterScripts(); ?>
<script src="<?= Config::$MAIN_SCRIPT_PATH ?>"></script>
<? if ($_SERVER["DOCUMENT_URI"] == "/create.php"): ?>
    <script src="<?= Config::$CREATE_SCRIPT_PATH ?>"></script>
<? endif; ?>
</body>

</html>
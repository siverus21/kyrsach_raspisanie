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
</body>

</html>
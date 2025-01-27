<?
require './vendor/autoload.php';

use App\Config\Config;
use App\Controllers\ShowComponents;

Config::init();
require Config::$HEADER_PATH;
?>
<main class="container">
    <?= ShowComponents::ShowComponent("view"); ?>
</main>

<?
require Config::$FOOTER_PATH;
?>
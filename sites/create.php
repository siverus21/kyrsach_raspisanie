<?
require './vendor/autoload.php';

use App\Config\Config;
use App\Controllers\ShowComponents;

Config::init();
?>

<? ShowComponents::RenderHeader(); ?>
<div class="container">
    <?= ShowComponents::RenderComponent("create"); ?>
</div>

<? ShowComponents::RenderFooter(); ?>
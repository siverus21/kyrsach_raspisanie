<?
require './vendor/autoload.php';

use App\Config\Config;
use App\Controllers\ShowComponents;

Config::init();
?>

<? ShowComponents::RenderHeader(); ?>

<? ShowComponents::RenderFooter(); ?>
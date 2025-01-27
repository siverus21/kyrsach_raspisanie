<?

namespace App\Controllers;

use App\Config\Config;

Config::init();

class ShowComponents
{
    public static function ShowComponent($nameComponent)
    {
        $componentPath = Config::$COMPONENT_PATH . $nameComponent . "/data.php";
        if (file_exists($componentPath)) {
            include $componentPath;
        } else {
            if (file_exists(str_replace("data", "template", $componentPath))) {
                include $componentPath;
            } else {
                echo "Компонент не найден: " . $componentPath;
            }
        }
    }
}

<?php

namespace App\Controllers;

use App\Config\Config;

Config::init();

class ShowComponents
{
    private static array $footerScripts = [];
    private static array $headerStyles = [];

    public static function RenderComponent($nameComponent)
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

    public static function RenderHeader()
    {
        include Config::$HEADER_PATH;
    }

    public static function RenderFooter()
    {
        include Config::$FOOTER_PATH;
    }

    /**
     * Добавляет путь к скрипту для footer
     * @param string $scriptPath Путь к скрипту
     */
    public static function addFooterScript(string $scriptPath)
    {
        if (!in_array($scriptPath, self::$footerScripts)) {
            self::$footerScripts[] = $scriptPath;
        }
    }

    /**
     * Добавляет путь к стилю для header
     * @param string $stylePath Путь к файлу стилей
     */
    public static function addHeaderStyle(string $stylePath)
    {
        if (!in_array($stylePath, self::$headerStyles)) {
            self::$headerStyles[] = $stylePath;
        }
    }

    /**
     * Выводит все добавленные скрипты в footer
     */
    public static function renderFooterScripts()
    {
        foreach (self::$footerScripts as $script) {
            echo "<script src='" . htmlspecialchars($script) . "'></script>\n";
        }
    }

    /**
     * Выводит все добавленные стили в header
     */
    public static function renderHeaderStyles()
    {
        foreach (self::$headerStyles as $style) {
            echo "<link rel='stylesheet' href='" . htmlspecialchars($style) . "'>\n";
        }
    }

    /**
     * Вызов этой функции внутри компонента добавляет скрипт в footer
     * @param string $scriptPath Путь к скрипту
     */
    public static function AddScript(string $scriptPath)
    {
        self::addFooterScript($scriptPath);
    }

    /**
     * Вызов этой функции внутри компонента добавляет стиль в header
     * @param string $stylePath Путь к файлу стилей
     */
    public static function AddStyle(string $stylePath)
    {
        self::addHeaderStyle($stylePath);
    }
}

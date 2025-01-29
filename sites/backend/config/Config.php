<?php

namespace App\Config;

class Config
{
    public static $SITE_NAME = "localhost";
    public static $LOCAL_SITE_PATH = "/var/www";
    public static $LOCAL_TEMPLATE_PATH;
    public static $AJAX_PATH;
    public static $CACHE_PATH;
    public static $EXCEL_PATH;
    public static $FUNCTIONS_PATH;
    public static $TEMPLATE_PATH;
    public static $VENDOR_PATH;
    public static $AUTOLOAD_PATH;
    public static $WEBHOOK_PATH;
    public static $LOG_PATH;
    public static $WS_SERVER_URL = 'ws://kyrsach:8081';
    public static $DIR = __DIR__;

    public static $HEADER_PATH;
    public static $FOOTER_PATH;

    public static $STYLE_PATH = '/css/';
    public static $MAIN_STYLE_PATH;
    public static $HEADER_STYLE_PATH;
    public static $FOOTER_STYLE_PATH;

    public static $SCRIPT_PATH = '/js/';
    public static $MAIN_SCRIPT_PATH;


    public static $COMPONENT_PATH;

    // Статический блок для инициализации путей
    public static function init()
    {
        self::$COMPONENT_PATH = self::$LOCAL_SITE_PATH . '/backend/views/schedule/components/';
        self::$LOCAL_TEMPLATE_PATH = self::$LOCAL_SITE_PATH . '/backend/views/templates';


        self::$MAIN_STYLE_PATH = self::$STYLE_PATH . 'style.css';
        self::$MAIN_SCRIPT_PATH = self::$SCRIPT_PATH . 'script.js';

        self::$HEADER_STYLE_PATH = self::$STYLE_PATH . 'header.css';
        self::$FOOTER_STYLE_PATH = self::$STYLE_PATH . 'footer.css';

        self::$HEADER_PATH = self::$LOCAL_TEMPLATE_PATH . '/header.php';
        self::$FOOTER_PATH = self::$LOCAL_TEMPLATE_PATH . '/footer.php';

        self::$AJAX_PATH = self::$SITE_NAME . '/ajax/';
        self::$CACHE_PATH = self::$SITE_NAME . '/cache/';
        self::$EXCEL_PATH = self::$SITE_NAME . '/excel/';
        self::$FUNCTIONS_PATH = self::$SITE_NAME . '/functions/';
        self::$TEMPLATE_PATH = self::$SITE_NAME . '/template/';
        self::$VENDOR_PATH = self::$SITE_NAME . '/vendor/';
        self::$AUTOLOAD_PATH = self::$SITE_NAME . '/vendor/autoload.php';
        self::$WEBHOOK_PATH = self::$SITE_NAME . '/webhook/';
        self::$LOG_PATH = self::$SITE_NAME . '/log/log.log';
    }
}

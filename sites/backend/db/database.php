<?php
function getDatabaseConnection()
{
    // Параметры подключения
    $connectionString = "host=postgres port=5432 dbname=schedule user=habrpguser password=pgpwd4habr";

    // Установка соединения
    $connection = pg_connect($connectionString);

    if (!$connection) {
        die('Ошибка подключения к PostgreSQL: ' . pg_last_error());
    }

    return $connection;
}

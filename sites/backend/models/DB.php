<?php

namespace App\Models;

class DB
{
    private $host = "postgres";
    private $port = "5432";
    private $dbname = "schedule";
    private $user = "habrpguser";
    private $password = "pgpwd4habr";

    public function __construct() {}

    public function getDatabaseConnection()
    {
        // Параметры подключения
        $connectionString = sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s",
            $this->host,
            $this->port,
            $this->dbname,
            $this->user,
            $this->password
        );

        // Установка соединения
        $connection = pg_connect($connectionString);

        if (!$connection) {
            throw new Exception('Ошибка подключения к PostgreSQL: ' . pg_last_error());
        }

        return $connection;
    }
}

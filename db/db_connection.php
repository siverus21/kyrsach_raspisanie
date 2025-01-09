<?php
// Настройки подключения к PostgreSQL
$host = "127.0.0.1";
$dbname = "schedule";
$user = "schedule";
$password = "";

// Подключение к PostgreSQL
$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(json_encode(["error" => "Не удалось подключиться к базе данных."]));
}

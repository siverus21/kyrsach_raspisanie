<?php

namespace App\Models;

use App\Models\DB;

class Schedule
{
    private $db;
    private $connection;

    public function __construct()
    {
        // Создаём объект DB
        $this->db = new DB();
        // Устанавливаем подключение к базе данных
        $this->connection = $this->db->getDatabaseConnection();
    }

    // Получить расписание по ID
    public function getScheduleById($id)
    {
        $query = "SELECT * FROM schedules WHERE id = $1";
        $result = pg_query_params($this->connection, $query, [$id]);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_assoc($result); // Возвращает одну строку
    }

    public function GetAllProgram()
    {
        $result = pg_query($this->connection, "SELECT * FROM program");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function GetAllRoom()
    {
        $result = pg_query($this->connection, "SELECT * FROM room");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function GetAllLector()
    {
        $result = pg_query($this->connection, "SELECT * FROM lector");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function GetAllDiscipline()
    {
        $result = pg_query($this->connection, "SELECT * FROM decsipline");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function UploadInfoDB($file, $nameTable)
    {
        if (($handle = fopen($file, 'r')) !== false) {
            fgetcsv($handle, 1000, ',');

            // Чтение строк из CSV-файла
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                $name = trim($data[0]);
                $code = trim($data[1]);

                // SQL-запрос на вставку данных
                $query = "INSERT INTO $nameTable (name, code) VALUES ($1, $2)";
                // $result = pg_query_params($this->connection, $query, [$name, $code]);

                // if (!$result) {
                // echo "Ошибка вставки данных: " . pg_last_error($this->connection) . "<br>";
                // }
            }

            fclose($handle);
            return "Данные успешно загружены в базу данных.";
        } else {
            return "Ошибка открытия файла.";
        }
    }
}

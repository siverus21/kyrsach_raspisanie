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
        $result = pg_query($this->connection, "SELECT * FROM discipline");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function UploadInfoDB($file, $nameTable)
    {
        if (($handle = fopen($file, 'r')) !== false) {
            $dataFieldsName = fgetcsv($handle, 1000, ',');
            $dataFieldsNameString = implode(', ', $dataFieldsName);

            fgetcsv($handle, 1000, ',');
            // Чтение строк из CSV-файла
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                foreach ($data as $k => $v)
                    $data[$k] = trim($v);

                // SQL-запрос на вставку данных
                $query = "INSERT INTO $nameTable (" . $dataFieldsNameString . ") VALUES (";
                for ($i = 1; $i <= count($dataFieldsName); $i++) {
                    $query .= "$" . $i;
                }
                $query .= ")";

                $result = pg_query_params($this->connection, $query, $data);
                if (!$result) {
                    echo "Ошибка вставки данных: " . pg_last_error($this->connection) . "<br>";
                }
            }
            fclose($handle);
            return "Данные успешно загружены в базу данных.";
        } else {
            return "Ошибка открытия файла.";
        }
    }

    public function GetAllTablesName()
    {
        $query = "SELECT n.nspname, c.relname
                  FROM pg_class c
                  JOIN pg_namespace n ON n.oid = c.relnamespace
                  WHERE c.relkind = 'r'
                  AND n.nspname NOT IN('pg_catalog', 'information_schema')";
        $result = pg_query($this->connection, $query);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function GetTablesFromWriteData()
    {
        $allowedTables = [
            'room' => "Аудитории",
            'lector' => "Преподаватели",
            'program' => "Программы",
            'discipline' => "Дисциплины"
        ];

        $allowedTablesString = "'" . implode("','", array_keys($allowedTables)) . "'";

        $query = "SELECT n.nspname, c.relname
              FROM pg_class c
              JOIN pg_namespace n ON n.oid = c.relnamespace
              WHERE c.relkind = 'r'
              AND n.nspname NOT IN ('pg_catalog', 'information_schema')
              AND c.relname IN ($allowedTablesString)";

        $result = pg_query($this->connection, $query);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }

        $arResult = pg_fetch_all($result);

        if ($arResult) {
            foreach ($arResult as $key => $arItem) {
                $arResult[$key] = $allowedTables[$arItem["relname"]];
            }
        }

        return $arResult ?: [];
    }

}

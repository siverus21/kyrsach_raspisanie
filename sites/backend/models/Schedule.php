<?php

namespace App\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
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

    public function GetAllInfoTable($name)
    {
        $result = pg_query($this->connection, "SELECT * FROM $name");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->connection));
        }
        return pg_fetch_all($result) ?: [];
    }

    public function UploadInfoDB($file, $nameTable)
    {
        // Определение типа файла
        $fileType = IOFactory::identify($file);
        $reader = IOFactory::createReader($fileType);

        try {
            // Чтение файла
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet();

            // Получение всех данных из первого листа
            $dataFieldsName = [];
            $data = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Итерируем по всем ячейкам строки

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Первая строка содержит имена полей
                if ($rowIndex == 1) {
                    $dataFieldsName = $rowData;
                    $dataFieldsNameString = implode(', ', $dataFieldsName);
                    continue;
                }

                // После первой строки - данные
                $data[] = $rowData;
            }

            // Если данные есть, начинаем вставку
            if (!empty($data)) {
                foreach ($data as $row) {
                    // Преобразование данных в массив с удалёнными пробелами
                    $row = array_map('trim', $row);

                    // Преобразование типов данных в соответствии с типами столбцов
                    foreach ($row as $key => $value) {
                        // Если столбец id_training_format, приводим к integer
                        if ($dataFieldsName[$key] === 'id_training_format' && is_numeric($value)) {
                            $row[$key] = (int)$value;
                        }
                        // Здесь можно добавить другие преобразования для других типов данных
                    }

                    // Генерация SQL-запроса с уникальными параметрами для каждого столбца
                    $query = "INSERT INTO \"{$nameTable}\" (" . $dataFieldsNameString . ") VALUES (";
                    $query .= implode(", ", array_map(function ($i) {
                        return '$' . ($i + 1);
                    }, range(0, count($row) - 1)));
                    $query .= ")";

                    // Выполнение запроса с уникальными параметрами для каждого столбца
                    $result = pg_query_params($this->connection, $query, $row);
                    if (!$result) {
                        echo "Ошибка вставки данных: " . pg_last_error($this->connection) . "<br>";
                    }
                }

                return "Данные успешно загружены в базу данных.";
            } else {
                return "Нет данных для загрузки.";
            }
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return "Ошибка чтения файла: " . $e->getMessage();
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
            'discipline' => "Дисциплины",
            'group' => "Группа"
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
                $arResult[$key] = array(
                    "eng" => $arItem["relname"],
                    "ru" => $allowedTables[$arItem["relname"]]
                );
            }
        }

        return $arResult ?: [];
    }

}

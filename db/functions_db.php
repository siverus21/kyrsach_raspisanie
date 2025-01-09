<?php
include('db_connection.php');  // Подключение к базе данных

// Функция для выполнения SQL-запроса
function executeQuery($sql)
{
    global $conn;

    if (empty($sql)) {
        return json_encode(["error" => "SQL-запрос не может быть пустым."]);
    }

    $result = @pg_query($conn, $sql);

    if (!$result) {
        return json_encode(["error" => "Ошибка выполнения запроса: " . pg_last_error($conn)]);
    }

    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    return json_encode(["success" => true, "data" => $data]);
}

// Функция для получения данных из таблицы
function fetchTableData($tableName)
{
    global $conn;

    if (empty($tableName)) {
        return json_encode(["error" => "Не указана таблица."]);
    }

    $query = "SELECT * FROM $tableName LIMIT 100";
    $result = pg_query($conn, $query);

    if (!$result) {
        return json_encode(["error" => "Ошибка при получении данных из таблицы: " . pg_last_error($conn)]);
    }

    $data = [];
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }

    return json_encode(["success" => true, "data" => $data]);
}

// Функция для получения списка таблиц
function fetchTables()
{
    global $conn;

    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';";
    $result = pg_query($conn, $query);

    if (!$result) {
        return json_encode(["error" => "Ошибка при получении списка таблиц: " . pg_last_error($conn)]);
    }

    $tables = [];
    while ($row = pg_fetch_assoc($result)) {
        $tables[] = $row['table_name'];
    }

    return json_encode(["tables" => $tables]);
}

// Функция для загрузки данных из CSV в базу данных
function uploadCSVToDB($csvFile, $tableName)
{
    global $conn;

    if (empty($csvFile) || empty($tableName)) {
        return json_encode(["error" => "Пожалуйста, загрузите файл и выберите таблицу."]);
    }

    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $columns = fgetcsv($handle, 1000, ",");

        if ($columns === FALSE) {
            return json_encode(["error" => "Ошибка при чтении файла CSV."]);
        }

        $placeholders = implode(", ", array_fill(0, count($columns), '$' . '%s'));
        $query = "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES ($placeholders)";

        $count = 0;
        $error = false;
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $params = [];
            foreach ($row as $key => $value) {
                $params[] = pg_escape_literal($value);
            }
            $result = pg_query_params($conn, $query, $params);
            if (!$result) {
                $error = true;
                break;
            }
            $count++;
        }

        fclose($handle);

        if ($error) {
            return json_encode(["error" => "Ошибка при загрузке данных."]);
        } else {
            return json_encode(["success" => true, "rows_inserted" => $count]);
        }
    } else {
        return json_encode(["error" => "Не удалось открыть файл CSV."]);
    }
}

pg_close($conn);  // Закрытие соединения с базой данных

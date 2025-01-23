<?php
// Данные подключения к PostgreSQL
$host = "postgres"; // Имя сервиса PostgreSQL в docker-compose
$port = "5432";     // Порт PostgreSQL
$dbname = "schedule"; // Имя базы данных
$user = "habrpguser"; // Пользователь PostgreSQL
$password = "pgpwd4habr"; // Пароль PostgreSQL

// Обработчик загрузки файла
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Проверяем, был ли загружен файл
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['csv_file']['tmp_name'];

        // Подключение к базе данных
        $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
        $conn = pg_connect($conn_string);

        if (!$conn) {
            die("Ошибка подключения к базе данных");
        }

        // Открытие загруженного файла
        if (($handle = fopen($tmpName, 'r')) !== false) {
            // Пропускаем первую строку (заголовок, если он есть)
            fgetcsv($handle, 1000, ',');

            // Чтение строк из CSV-файла
            while (($data = fgetcsv($handle, 1000, "\n")) !== false) {
                // Строка CSV состоит из одного столбца, его нужно получить и обрезать лишние пробелы
                $name = trim($data[0]);

                // SQL-запрос на вставку данных
                $query = "INSERT INTO room (name) VALUES ($1)";
                $result = pg_query_params($conn, $query, [$name]);

                if (!$result) {
                    echo "Ошибка вставки данных: " . pg_last_error($conn) . "<br>";
                }
            }

            fclose($handle);
            echo "Данные успешно загружены в базу данных.";
        } else {
            echo "Ошибка открытия файла.";
        }

        // Закрытие соединения с базой данных
        pg_close($conn);
    } else {
        echo "Ошибка загрузки файла.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка CSV в базу данных</title>
</head>

<body>
    <h1>Загрузите CSV-файл</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="csv_file">Выберите файл CSV:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <button type="submit">Загрузить</button>
    </form>
</body>

</html>
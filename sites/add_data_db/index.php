<?
$conn = pg_connect("host=postgres port=5432 dbname=schedule user=habrpguser password=pgpwd4habr");
if (!$conn) {
    die("Ошибка подключения к базе данных");
}

// SQL-запрос для получения всех данных из таблицы `room`
$query = "SELECT * FROM room";

// Выполнение запроса
$result = pg_query($conn, $query);

if (!$result) {
    die("Ошибка выполнения запроса: " . pg_last_error($conn));
}

// Вывод данных из таблицы
echo "<h1>Данные из таблицы room:</h1>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th></tr>"; // Заголовки таблицы

// Обработка и вывод каждой строки результата
while ($row = pg_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Закрытие соединения с базой данных
pg_close($conn);

<?php
require './vendor/autoload.php';

use App\Controllers\ScheduleController;

$controller = new ScheduleController();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Электронное расписание</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Электронное расписание</h1>
            <nav>
                <ul>
                    <li><a href="#schedule">Расписание</a></li>
                    <li><a href="#tasks">Задачи</a></li>
                    <li><a href="#settings">Настройки</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="schedule" class="card">
            <h2>Календарь</h2>
            <div class="calendar"></div>
        </section>

        <section id="tasks" class="card">
            <h2>Задачи на сегодня</h2>
            <ul class="task-list">
                <!-- Пример задачи -->
                <li>Собрание с командой в 10:00</li>
            </ul>
            <form id="task-form">
                <input type="text" id="task-input" placeholder="Введите задачу" required>
                <input type="time" id="task-time" required>
                <button type="submit">Добавить</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Электронное расписание</p>
    </footer>

    <script src="script.js"></script>
</body>

</html>
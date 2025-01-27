<?php
require './vendor/autoload.php';

use App\Config\Config;

Config::init();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Электронное расписание</title>
    <link rel="stylesheet" href="<?= Config::$MAIN_STYLE_PATH; ?>">
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
    <main>
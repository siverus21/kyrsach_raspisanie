<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/styles/main/style.css">
</head>

<body>
    <h1 class="title">Расписание ИСИТ</h1>
    <div class="change-block">
        <? for ($i = 1; $i < 5; $i++): ?>
            <div class="change-block__item">
                <a href="table.php/?course_number=<?= $i ?>"><?= $i ?> курс</a>
            </div>
        <? endfor; ?>
    </div>

    <h1>Это текст</h1>
</body>

</html>
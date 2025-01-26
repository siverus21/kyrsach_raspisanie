<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Просмотр расписания</title>
</head>

<body>
    <?php include 'views/templates/header.php'; ?>
    <h1><?= htmlspecialchars($schedule['title']); ?></h1>
    <p><strong>Дата:</strong> <?= htmlspecialchars($schedule['date']); ?></p>
    <p><strong>Время:</strong> <?= htmlspecialchars($schedule['time']); ?></p>
    <p><strong>Описание:</strong> <?= htmlspecialchars($schedule['description']); ?></p>
    <a href="/">Вернуться к списку</a>
    <?php include 'views/templates/footer.php'; ?>
</body>

</html>
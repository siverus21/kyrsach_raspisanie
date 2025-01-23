<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Список расписаний</title>
</head>

<body>
    <?php include 'views/templates/header.php'; ?>
    <h1>Список расписаний</h1>
    <a href="?action=create">Добавить расписание</a>
    <ul>
        <?php foreach ($schedules as $schedule): ?>
            <li>
                <strong><?= htmlspecialchars($schedule['title']); ?></strong>
                (<?= htmlspecialchars($schedule['date'] . ' ' . $schedule['time']); ?>)
                <a href="?action=view&id=<?= $schedule['id']; ?>">Подробнее</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php include 'views/templates/footer.php'; ?>
</body>

</html>
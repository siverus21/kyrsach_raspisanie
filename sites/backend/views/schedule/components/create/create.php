<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Создать расписание</title>
</head>

<body>
    <?php include 'views/templates/header.php'; ?>
    <h1>Создать расписание</h1>
    <form method="POST" action="?action=store">
        <label>Заголовок:</label>
        <input type="text" name="title" required>
        <br>
        <label>Описание:</label>
        <textarea name="description" required></textarea>
        <br>
        <label>Дата:</label>
        <input type="date" name="date" required>
        <br>
        <label>Время:</label>
        <input type="time" name="time" required>
        <br>
        <button type="submit">Сохранить</button>
    </form>
    <?php include 'views/templates/footer.php'; ?>
</body>

</html>
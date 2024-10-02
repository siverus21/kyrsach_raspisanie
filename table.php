<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/styles/table/style.css">
    <link rel="stylesheet" href="/styles/main/style.css">
</head>

<body style="height: 80vh;">
    <h1 class="title">Рассписание ИСИТ <?= $_GET["course_number"] ?> курса</h1>
    <div class="iframe-block">
        <iframe src="http://kyrsach/excel.php/?course_number=<?= $_GET["course_number"] ?>" frameborder="0" width="80%" height="100%"
            style="margin: 0 auto;"></iframe>
    </div>
</body>

</html>
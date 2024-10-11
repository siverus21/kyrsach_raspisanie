<?
function getDirContents($dir, &$results = array(), $baseDir = "")
{
    if (empty($baseDir)) {
        $baseDir = realpath($dir);
    }

    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            // Убираем базовый путь из результата и заменяем \ на /
            $results[] = str_replace('\\', '/', str_replace($baseDir . DIRECTORY_SEPARATOR, "", $path));
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results, $baseDir);
        }
    }

    return $results;
}

function getSubdirectories($dir)
{
    $subdirectories = [];

    // Проверяем, существует ли директория
    if (is_dir($dir)) {
        // Получаем все элементы в директории
        $items = scandir($dir);

        foreach ($items as $item) {
            // Игнорируем текущую и родительскую директории
            if ($item !== '.' && $item !== '..') {
                $path = realpath($dir . DIRECTORY_SEPARATOR . $item);
                // Проверяем, является ли элемент директорией
                if (is_dir($path)) {
                    $subdirectories[] = $item; // Добавляем имя директории в массив
                }
            }
        }
    }

    return $subdirectories; // Возвращаем массив имен поддиректорий
}

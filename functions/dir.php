<?
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
                // if (is_dir($path)) {
                    $subdirectories[] = $item; // Добавляем имя директории в массив
                // } else {
                //     $subdirectories[] = getDirContents();
                // }
            }
        }
    }

    return $subdirectories; // Возвращаем массив имен поддиректорий
}

function replaceSpacesInNames($dir)
{
    // Открываем директорию
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $oldPath = $dir . DIRECTORY_SEPARATOR . $entry;
                $newEntry = str_replace(' ', '_', $entry); // Замена пробела на _
                $newPath = $dir . DIRECTORY_SEPARATOR . $newEntry;

                // Переименовываем файл или папку
                if ($oldPath !== $newPath) {
                    rename($oldPath, $newPath);
                }

                // Если это директория, продолжаем рекурсивно
                if (is_dir($newPath)) {
                    replaceSpacesInNames($newPath);
                }
            }
        }
        closedir($handle);
    }
}
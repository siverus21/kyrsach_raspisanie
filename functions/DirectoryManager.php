<?php

namespace App\Schedule;

class DirectoryManager
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = realpath($path); // Преобразуем в абсолютный путь
    }

    public function getSubdirectories(): array
    {
        $subdirectories = [];

        // Проверяем, существует ли директория и можно ли её прочитать
        if (is_dir($this->path) && is_readable($this->path)) {
            // Получаем все элементы в директории
            $items = scandir($this->path);

            if ($items === false) {
                // Возвращаем пустой массив, если не удалось прочитать директорию
                return $subdirectories;
            }

            foreach ($items as $item) {
                // Игнорируем текущую и родительскую директории
                if ($item !== '.' && $item !== '..') {
                    $subdirectories[] = $item; // Добавляем имя директории в массив
                }
            }
        }

        return $subdirectories; // Возвращаем массив имен поддиректорий
    }

    public function replaceSpacesInNames(): void
    {
        // Проверяем, можно ли открыть директорию
        if (is_dir($this->path) && is_readable($this->path) && $handle = opendir($this->path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $oldPath = $this->path . DIRECTORY_SEPARATOR . $entry;
                    $newEntry = str_replace(' ', '_', $entry); // Замена пробела на _
                    $newPath = $this->path . DIRECTORY_SEPARATOR . $newEntry;

                    // Переименовываем файл или папку, только если имена различаются
                    if ($oldPath !== $newPath && is_writable($oldPath)) {
                        rename($oldPath, $newPath);
                    }

                    // Если это директория, продолжаем рекурсивно
                    if (is_dir($newPath)) {
                        $this->replaceSpacesInNames($newPath);
                    }
                }
            }
            closedir($handle);
        }
    }
}

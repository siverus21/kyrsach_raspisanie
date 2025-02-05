import os
import pandas as pd

# Слова и символы для удаления из названий файлов
replace_terms = ["27.09", " 2 курс", " 3 курс", " 4 курс", "_2022", "_2021", "1 сем", "2 сем", " 5 курс", "_2024", "_2023", " 27.09", "_2020", "_2019", " 6 курс"]

# Путь к корневой папке
root_folder = "./excel"

# Список для хранения данных
data = []

# Уникальный ID для каждой записи
record_id = 1

# Множество для отслеживания уникальных имен файлов
unique_names = set()

# Открытие файла для логирования путей
log_file_path = "file_paths.log"
with open(log_file_path, "w") as log_file:
    # Обход папки и подпапок
    for root, dirs, files in os.walk(root_folder):
        print(f"Обход директории: {root}")  # Лог текущей папки
        for file in files:
            file_path = os.path.join(root, file)
            file_name = os.path.splitext(file)[0]  # Убираем расширение файла

            # Удаление нежелательных частей из имени файла
            for term in replace_terms:
                file_name = file_name.replace(term, "")

            file_name = file_name.strip()  # Удаление лишних пробелов

            # Проверка на уникальность имени файла
            original_name = file_name
            counter = 1
            while file_name in unique_names:
                file_name = f"{original_name}_{counter}"
                counter += 1

            unique_names.add(file_name)

            # Логирование пути файла
            log_file.write(f"{file_path}\n")
            print(f"Найден файл: {file_path}")  # Отладочный вывод

            # Определение значения id_training_format
            if "Очное" in root:
                id_training_format = 2
            elif "Заочное" in root:
                id_training_format = 1
            else:
                id_training_format = None

            # Добавление записи в список
            data.append([
                record_id,
                file_name,
                id_training_format,
                1,  # id_direction фиксированное значение
                None  # id_program оставляем пустым
            ])
            record_id += 1

# Создание DataFrame
columns = ["id", "name", "id_training_format", "id_direction", "id_program"]
df = pd.DataFrame(data, columns=columns)

# Сохранение в Excel
output_file = "result.xlsx"
df.to_excel(output_file, index=False)

print(f"Данные успешно сохранены в файл {output_file}")
print(f"Логи путей сохранены в файл {log_file_path}")

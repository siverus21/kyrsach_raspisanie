import os
import win32com.client as win32

# Путь к директории с файлами Excel
directory = r'D:/OSPanel/domains/kyrsach/excel'

# Путь к файлу с макросом
macro_file = r'D:/OSPanel/domains/kyrsach/other/python/macro.bas'


# Обход всех файлов в директории и подпапках
def process_files(directory):
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.xls'):
                filepath = os.path.join(root, file)
                print(f"Обрабатываем файл: {filepath}")  # Для отладки
                new_filepath = os.path.splitext(filepath)[0] + '_new.xlsm'  # Изменяем расширение на .xlsm
                convert_and_add_macro(filepath, new_filepath)


# Конвертация файла и добавление макроса
def convert_and_add_macro(filepath, new_filepath):
    # Нормализуем путь
    filepath = os.path.normpath(filepath)
    new_filepath = os.path.normpath(new_filepath)

    if not os.path.exists(filepath):
        print(f"Файл не найден: {filepath}")
        return  # Пропускаем дальнейшую обработку

    try:
        excel = win32.Dispatch("Excel.Application")
        excel.Visible = False
        excel.DisplayAlerts = False

        # Открываем файл
        workbook = excel.Workbooks.Open(filepath)

        # Сохраняем файл как .xlsm
        workbook.SaveAs(new_filepath, FileFormat=52)  # 52 - формат .xlsm

        # Импортируем макрос из файла .bas
        workbook.VBProject.VBComponents.Import(macro_file)

        # Сохраняем и закрываем файл
        workbook.Save()
        workbook.Close(SaveChanges=True)

        print(f"Файл конвертирован и макрос добавлен: {new_filepath}")
    except Exception as e:
        print(f"Ошибка при обработке файла {filepath}: {str(e)}")
    finally:
        excel.Quit()


if __name__ == "__main__":
    process_files(directory)

import os
import win32com.client as win32


# Функция для чтения макроса из файла с указанием кодировки
def read_macro_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as file:
            return file.read()
    except Exception as e:
        print(f"Ошибка при чтении макроса: {str(e)}")
        return None


# Функция для добавления макроса напрямую в рабочую книгу Excel
def add_macro_directly(workbook, macro_code):
    try:
        module = workbook.VBProject.VBComponents.Add(1)  # Добавляем новый стандартный модуль
        module.CodeModule.AddFromString(macro_code)  # Вставляем код макроса
    except Exception as e:
        print(f"Ошибка при добавлении макроса: {str(e)}")


# Основная функция для конвертации файла в .xlsm, добавления макросов и удаления исходного файла
def convert_and_add_macro(filepath, new_filepath, thisbook_macro_file, module_macro_file):
    filepath = os.path.normpath(filepath)
    new_filepath = os.path.normpath(new_filepath)

    if not os.path.exists(filepath):
        print(f"Файл не найден: {filepath}")
        return

    try:
        # Инициализация приложения Excel
        excel = win32.Dispatch("Excel.Application")
        excel.Visible = False
        excel.DisplayAlerts = False

        # Открываем Excel файл
        workbook = excel.Workbooks.Open(filepath)

        # Сохраняем как .xlsm (FileFormat=52)
        workbook.SaveAs(new_filepath, FileFormat=52)

        # Чтение макросов из файлов
        thisbook_macro_code = read_macro_file(thisbook_macro_file)
        module_macro_code = read_macro_file(module_macro_file)

        if thisbook_macro_code:
            # Добавляем макрос в ThisWorkbook
            workbook.VBProject.VBComponents("ThisWorkbook").CodeModule.AddFromString(thisbook_macro_code)

        if module_macro_code:
            # Добавляем макрос в модуль
            add_macro_directly(workbook, module_macro_code)

        # Сохраняем изменения и закрываем книгу
        workbook.Save()
        workbook.Close(SaveChanges=True)

        # Удаляем исходный .xls файл
        if os.path.exists(filepath):
            os.remove(filepath)
            print(f"Исходный файл удалён: {filepath}")

        print(f"Файл конвертирован и макросы добавлены: {new_filepath}")

    except Exception as e:
        print(f"Ошибка при обработке файла {filepath}: {str(e)}")

    finally:
        # Завершаем работу с Excel
        excel.Quit()


# Пример использования
if __name__ == "__main__":
    source_folder = "D:/OSPanel/domains/kyrsach/excel"  # Укажите путь к исходной папке с файлами .xls
    thisbook_macro_file = "D:/OSPanel/domains/kyrsach/other/python/ThisBook.bas"  # Путь к файлу с макросом для ThisWorkbook
    module_macro_file = "D:/OSPanel/domains/kyrsach/other/python/Module.bas"  # Путь к файлу с макросом для Module

    for root, dirs, files in os.walk(source_folder):
        for file in files:
            if file.endswith(".xls"):
                filepath = os.path.join(root, file)
                new_filepath = filepath.replace(".xls", ".xlsm")

                convert_and_add_macro(filepath, new_filepath, thisbook_macro_file, module_macro_file)

import requests
from bs4 import BeautifulSoup
import csv
import re

from requests.compat import chardet


# --- Первая задача: Парсинг данных сотрудников ---
def parse_employees():
    url = 'https://www.arcticsu.ru/sveden/employees/'
    target_columns = [
        'Фамилия, Имя, Отчество(при наличии)',
        'Перечень преподаваемых дисциплин',
        'Наименование образовательных программ, в реализации которых участвует педагогический работник'
    ]

    response = requests.get(url)
    response.encoding = 'utf-8'

    soup = BeautifulSoup(response.text, 'html.parser')
    header = soup.find('h4',
                       string='Информация о персональном составе педагогических работников образовательной программы')
    table = header.find_next('table') if header else None

    if table:
        headers = [th.get_text(strip=True) for th in table.find_all('th')]
        target_indices = [headers.index(col) for col in target_columns if col in headers]
        rows = table.find_all('tr')[1:]  # Пропускаем заголовок

        data = []
        for row in rows:
            cells = row.find_all('td')
            if len(cells) >= max(target_indices) + 1:
                entry = {}
                for i in target_indices:
                    raw_content = cells[i]
                    text = ",".join(part.strip() for part in raw_content.stripped_strings)
                    entry[headers[i]] = text
                data.append(entry)

        # Запись в оба файла
        write_csv('employees_utf8.csv', target_columns, data, 'utf-8')
        write_csv('employees_windows1251.csv', target_columns, data, 'windows-1251')

        print(f"Данные сотрудников успешно сохранены в файлы: employees_utf8.csv и employees_windows1251.csv")
    else:
        print('Таблица с данными сотрудников не найдена.')


# --- Вторая задача: Парсинг адресов ---
def write_csv(filename, header, data, encoding):
    """
    Записывает данные в CSV-файл.
    :param filename: имя файла.
    :param header: заголовки столбцов.
    :param data: список словарей с данными.
    :param encoding: кодировка для записи файла.
    """
    with open(filename, mode='w', encoding=encoding, newline='') as file:
        writer = csv.DictWriter(file, fieldnames=header)
        writer.writeheader()
        writer.writerows(data)


def normalize_address(address):
    """
    Приводит адрес к сокращённому формату.
    :param address: полный адрес.
    :return: сокращённый адрес.
    """
    # Удаление начала строки с индексом, областью и городом
    address = re.sub(r'^\d{6},\s*(Мурманская область,?\s*|Мурманская обл\.,?\s*|г\.\s*(Апатиты|Мончегорск),?\s*)', '', address).strip()

    address = address.replace("г.Апатиты, ", "")
    address = address.replace("г. Апатиты, ", "")
    address = address.replace("г. Мончегорск, ", "")

    # Замена "ул. Лесная, д.29" и "ул. Энергетическая, д.19" на "К.7" и "К.2"
    address = address.replace("ул. Лесная, д.29", "К.7")
    address = address.replace("ул. Энергетическая, д.19", "К.2")
    address = address.replace("ул.Энергетическая, д.19", "К.2")

    # Замена "Корпус №2" на "К.2" и "Корпус №7" на "К.7"
    address = address.replace("Корпус №2", "К.2")
    address = address.replace("Корпус №7", "К.7")

    # Удаление лишних слов и информации после слов "Договор", "Государственное", "Федеральный"
    address = re.sub(r'\s*(Договор.*|Государственное.*|Федеральный.*)', '', address)

    # Удаление лишних пробелов и символов
    address = re.sub(r'\s*,\s*', ', ', address)  # Убираем лишние пробелы после запятых
    address = re.sub(r'\s+', ' ', address)  # Убираем лишние пробелы между словами
    address = address.strip(" .,;")
    address = address.replace("К.7, К.7,", "К.7,")
    address = address.replace("К.7 К.7,", "К.7,")
    address = address.replace("К.7 К.7", "К.7,")
    address = address.replace("К.2, К.2,", "К.2,")
    address = address.replace("К.2 К.2,", "К.2,")
    address = address.replace("К.2", "К.2,")
    address = address.replace("К.2К.2ауд", "К.2 ауд")

    return address


def split_addresses(address):
    """
    Разделяет адрес с множеством элементов на несколько.
    :param address: адрес, содержащий перечисление элементов.
    :return: список разделённых адресов.
    """
    print(address)
    # Убираем повторяющиеся части (например "К.7, К.7")
    address = re.sub(r'(К\.\d+),\s*\1', r'\1', address)

    # Убираем дублирующиеся "ауд." в адресах
    address = re.sub(r'(ауд\.\s*\d+),\s*\1', r'\1', address)

    # Разбиваем строку только по запятым, за исключением случаев с диапазоном аудиторий (например, "106-106а")
    address_parts = re.split(r',\s*(?!\d+-\d+[а-яА-Я]*)', address)

    # Убираем лишние пробелы в каждой части
    address_parts = [part.strip() for part in address_parts if part.strip()]

    print(address_parts)
    print("\n")

    # Соединяем части обратно, если их не нужно разделять
    return [', '.join(address_parts)]

def parse_addresses():
    url = 'https://www.arcticsu.ru/sveden/objects/'
    response = requests.get(url)
    response.encoding = 'utf-8'

    soup = BeautifulSoup(response.text, 'html.parser')

    # Поиск всех элементов с itemprop="addressPrac" или itemprop="addressCab"
    address_elements = soup.find_all(attrs={"itemprop": ["addressPrac", "addressCab"]})

    # Сбор и нормализация адресов
    addresses = set()  # Используем set для исключения дубликатов
    for element in address_elements:
        raw_address = element.get_text(strip=True)
        normalized_address = normalize_address(raw_address)
        split_result = split_addresses(normalized_address)
        addresses.update(split_result)

    # Сортируем адреса для удобства чтения
    unique_addresses = sorted(addresses)

    if not unique_addresses:
        print("Не удалось найти адреса с itemprop 'addressPrac' или 'addressCab'. Проверьте структуру страницы.")
    else:
        # Подготовка данных для записи
        header = ['Адрес']
        data = [{'Адрес': address} for address in unique_addresses]

        # Запись в оба файла
        write_csv('addresses_utf8.csv', header, data, 'utf-8')
        write_csv('addresses_windows1251.csv', header, data, 'windows-1251')

        print(f"Адреса успешно сохранены в файлы: addresses_utf8.csv и addresses_windows1251.csv")


def parse_employees_data():
    url = 'https://www.arcticsu.ru/sveden/employees/'
    # Заголовки HTTP-запроса
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }

    # Запрос страницы
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        soup = BeautifulSoup(response.text, 'html.parser')

        # Найти таблицу по заголовку <h3>
        section = soup.find('h3',
                            text='Информация о персональном составе педагогических работников каждой реализуемой образовательной программы')
        if section:
            table = section.find_next('table')

            if table:
                rows = table.find_all('tr')[1:]  # Пропускаем первую строку (заголовок таблицы)
                tempCode = []
                # Собираем данные
                data = []
                for row in rows:
                    columns = row.find_all('td')
                    if len(columns) >= 2:
                        code = columns[0].get_text(strip=True)
                        if code not in tempCode and not bool(re.search('[а-яА-Я]', code)):
                            tempCode.append(code)
                            name = columns[1].get_text(strip=True)
                            data.append([name, code])

                # Записываем в CSV файл
                with open('programs.csv', 'w', newline='', encoding='utf-8') as file:
                    writer = csv.writer(file)
                    writer.writerow(['name', 'code'])  # Заголовок столбцов
                    writer.writerows(data)  # Запись данных

                print("Данные успешно записаны в файл 'programs.csv'")
            else:
                print("Не удалось найти таблицу после заголовка.")
        else:
            print(
                "Не удалось найти заголовок с текстом 'Информация о персональном составе педагогических работников каждой реализуемой образовательной программы'.")
    else:
        print("Ошибка при запросе страницы:", response.status_code)


def parse_correct_table_to_csv():
    url = 'https://www.arcticsu.ru/sveden/employees/'

    # Отправляем GET-запрос к указанному URL
    response = requests.get(url)
    response.raise_for_status()

    # Парсим HTML-контент
    soup = BeautifulSoup(response.content, 'html.parser')

    # Находим заголовок h4
    header = soup.find('h4', string='Информация о персональном составе педагогических работников образовательной программы')
    if not header:
        print('Заголовок не найден.')
        return

    # Находим таблицу сразу после заголовка
    table = header.find_next('table')
    if not table:
        print('Таблица не найдена.')
        return

    # Определяем индекс столбца "Перечень преподаваемых дисциплин"
    headers = table.find_all('th')
    discipline_index = None
    for i, th in enumerate(headers):
        if 'Перечень преподаваемых дисциплин' in th.get_text(strip=True):
            discipline_index = i
            break

    if discipline_index is None:
        print('Столбец "Перечень преподаваемых дисциплин" не найден.')
        return

    # Извлекаем данные из таблицы
    disciplines = []
    tmp = []
    for row in table.find_all('tr')[1:]:  # Пропускаем заголовок таблицы
        cells = row.find_all('td')
        if len(cells) > discipline_index:
            # Извлекаем содержимое нужной ячейки
            cell_content = cells[discipline_index]
            # Разбиваем текст по тегам <br> и <span>
            items = []
            for part in cell_content.find_all(['br', 'span']):
                if part.name == 'br' and part.previous_sibling:
                    if(not part.previous_sibling.strip() in tmp):
                        tmp.append(part.previous_sibling.strip())
                        items.append(part.previous_sibling.strip())
                elif part.name == 'span':
                    span_text = part.get_text(strip=True)
                    if span_text:
                        if (not span_text in tmp):
                            tmp.append(span_text)
                            items.append(span_text)
            # Убираем пустые элементы и добавляем в итоговый список
            for item in filter(None, items):
                disciplines.append({'name': item})

    if not disciplines:
        print('Данные для записи отсутствуют.')
        return

    # Записываем данные в CSV-файл
    with open("discipline.csv", 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['name']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(disciplines)

    print(f'Данные успешно сохранены в файл discipline.csv')


def remove_duplicates_from_csv(input_csv="discipline2.csv", output_csv="discipline_clean.csv"):
    """
    Убирает дубликаты строк из CSV-файла и сохраняет результат в новый файл.

    :param input_csv: Путь к исходному CSV-файлу
    :param output_csv: Путь к выходному CSV-файлу без дубликатов
    """
    try:
        # Определяем кодировку файла
        with open(input_csv, 'rb') as f:
            result = chardet.detect(f.read())
            encoding = result['encoding']

        # Читаем исходный CSV-файл
        with open(input_csv, 'r', encoding=encoding) as infile:
            reader = csv.DictReader(infile)
            # Используем множество для хранения уникальных строк
            unique_rows = set()
            fieldnames = reader.fieldnames  # Получаем названия столбцов
            for row in reader:
                # Преобразуем строку в неизменяемый формат (tuple) для хранения в множестве
                row_tuple = tuple(row.items())
                unique_rows.add(row_tuple)

        # Записываем уникальные строки в новый CSV-файл
        with open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
            writer = csv.DictWriter(outfile, fieldnames=fieldnames)
            writer.writeheader()
            for row_tuple in unique_rows:
                writer.writerow(dict(row_tuple))

        print(f'Дубликаты удалены. Результат сохранен в {output_csv}')

    except Exception as e:
        print(f'Произошла ошибка: {e}')



def parse_table_to_csv(url='https://www.arcticsu.ru/sveden/employees/', output_file="lector.csv"):
    try:
        # Отправляем GET-запрос на указанный URL
        response = requests.get(url)
        response.raise_for_status()  # Проверяем на наличие ошибок

        # Создаем объект BeautifulSoup для анализа HTML
        soup = BeautifulSoup(response.text, 'html.parser')

        # Ищем заголовок таблицы
        header = soup.find('h4', text='Информация о персональном составе педагогических работников образовательной программы')
        if not header:
            print("Не удалось найти заголовок таблицы.")
            return

        # Ищем таблицу после заголовка
        table = header.find_next('table')
        if not table:
            print("Не удалось найти таблицу после заголовка.")
            return

        # Ищем строки таблицы
        rows = table.find_all('tr')
        if not rows:
            print("Таблица пуста.")
            return

        # Открываем CSV-файл для записи
        with open(output_file, mode='w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=['family', 'name', 'patronymic'])
            writer.writeheader()

            # Обрабатываем строки таблицы
            for row in rows[1:]:  # Пропускаем заголовок таблицы
                cols = row.find_all('td')
                if cols:
                    full_name = cols[0].get_text(strip=True)  # Извлекаем ФИО из первой колонки
                    name_parts = full_name.split()

                    # Разделяем на фамилию, имя и отчество
                    family = name_parts[0] if len(name_parts) > 0 else ''
                    name = name_parts[1] if len(name_parts) > 1 else ''
                    patronymic = name_parts[2] if len(name_parts) > 2 else ''

                    # Записываем данные в CSV
                    writer.writerow({'family': family, 'name': name, 'patronymic': patronymic})

        print(f"Данные успешно сохранены в файл {output_file}")

    except requests.RequestException as e:
        print(f"Ошибка при выполнении запроса: {e}")
    except Exception as e:
        print(f"Произошла ошибка: {e}")

# --- Выполнение функций ---
if __name__ == "__main__":
    # parse_employees()
    # parse_addresses()
    # parse_employees_data()
    # parse_correct_table_to_csv()
    # remove_duplicates_from_csv()
    parse_table_to_csv()
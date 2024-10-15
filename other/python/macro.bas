Private Sub Workbook_Open()
    Dim ws As Worksheet
    Dim domain As String

    ' Поиск листа Config
    On Error Resume Next
    Set ws = ThisWorkbook.Sheets("Config")
    On Error GoTo 0

    ' Если нет листа, создаем его
    If ws Is Nothing Then
        Set ws = ThisWorkbook.Sheets.Add
        ws.Name = "Config"
        ws.Visible = xlSheetVeryHidden  ' Прячем конфиг
    End If

    ' Установка домена
    domain = "http://kyrsach/webhook/webhook_excel.php" ' Указание домена сразу

    ' Сохранение домена в ячейке A1 листа Config
    ws.Range("A1").Value = domain
End Sub

Private Sub Workbook_AfterSave(ByVal Success As Boolean)
    If Success Then
        ' Вызываем функцию отправки вебхука при сохранении файла
        Call SendWebhookRequest
    End If
End Sub

Sub SendWebhookRequest()
    Dim ws As Worksheet
    Dim domain As String
    Dim jsonData As String
    Dim http As Object
    Dim currentFileName As String
    Dim currentFilePath As String

    ' Получение Config из файла Excel
    Set ws = ThisWorkbook.Sheets("Config")
    domain = ws.Range("A1").Value

    ' Проверяем, установлен ли домен
    If domain = "" Then
        MsgBox "Домен не установлен. Вебхуки не будут отправляться."
        Exit Sub
    End If

    ' Данные для отправки
    currentFileName = ThisWorkbook.Name
    currentFilePath = ThisWorkbook.FullName

    ' Формируем JSON-строку с правильным использованием значений
    jsonData = "{""message"":""Файл Excel был сохранен"", ""File-Path"":""" & Replace(currentFilePath, "\", "\\") & """, ""File-Name"":""" & currentFileName & """}"

    ' Создание HTTP-запроса
    Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")

    On Error GoTo ErrorHandler
    http.Open "POST", domain, False
    http.setRequestHeader "Content-Type", "application/json"

    ' Отправка данных и получение ответа
    http.send jsonData

    ' Проверка статуса ответа
    If http.Status = 200 Then
        MsgBox "Запрос успешно отправлен! Вебхук был отправлен."
    Else
        MsgBox "Не удалось отправить запрос. Код ошибки: " & http.Status
    End If
    Exit Sub

ErrorHandler:
    MsgBox "Ошибка при отправке запроса: " & Err.Description
End Sub

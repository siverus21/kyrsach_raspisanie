Sub SendWebhookRequest()
    Dim jsonData As String
    Dim http As Object
    Dim currentFileName As String
    Dim currentFilePath As String
    Dim status As Variant
    Dim errMsg As String

    ' Жёсткая привязка к домену
    Dim domain As String
    domain = "http://kyrsach/webhook/webhook_excel.php"

    ' Получение текущего имени и пути файла
    currentFileName = ThisWorkbook.Name
    currentFilePath = ThisWorkbook.FullName

    ' Формирование JSON-строки с данными
    jsonData = "{""message"":""Файл Excel был сохранён"", ""File-Path"":""" & Replace(currentFilePath, "\", "\\") & """, ""File-Name"":""" & currentFileName & """}"

    ' Создание HTTP-запроса
    Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")

    On Error GoTo ErrorHandler
    http.Open "POST", domain, False
    http.setRequestHeader "Content-Type", "application/json"

    ' Отправка данных и получение ответа
    http.send jsonData

    ' Проверка статуса ответа
    status = http.status
    If status = 200 Then
        MsgBox "Запрос успешно отправлен! Файл был сохранён."
    Else
        errMsg = "Не удалось отправить запрос. Код ответа: " & status & vbNewLine & "Ответ сервера: " & http.responseText
        MsgBox errMsg
    End If
    Exit Sub

ErrorHandler:
    MsgBox "Произошла ошибка при отправке запроса: " & Err.Description & vbNewLine & "Код ошибки: " & Err.Number
End Sub

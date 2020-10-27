Attribute VB_Name = "Word"
'元文字列から指定の文字数で区切った文字列を生成
Function createWord(originalColumn As String, insertColumn As String, ByVal startRowCount As Integer, ByVal endRowCount As Integer, delimiter As String, ByVal wordLength As Integer, isTruncation As Boolean)

'生成する文字の長さ
Dim searchWordLen As Integer

'値がある行を数える
Dim n As Integer
n = IIf(endRowCount > 0, startRowCount + endRowCount - 1, Cells(Rows.Count, originalColumn).End(xlUp).Row)

'値がある行すべて検索文字列を生成
For rowIndex = startRowCount To n

    '検索文字列を生成
    Dim cellvalue As String
    cellvalue = WorksheetFunction.Clean(Cells(rowIndex, originalColumn))
    strlen = Len(cellvalue)

    Dim index As Integer
    Dim arr() As String
    ReDim arr(strlen)
    
    '決められた長さで分割
    For index = 0 To IIf(isTruncation, strlen - wordLength, strlen)
        arr(index) = Mid(cellvalue, index + 1, wordLength)
    Next index

    '代入
    Cells(rowIndex, insertColumn) = Join(arr, delimiter)

Next rowIndex

End Function

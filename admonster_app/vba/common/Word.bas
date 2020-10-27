Attribute VB_Name = "Word"
'�������񂩂�w��̕������ŋ�؂���������𐶐�
Function createWord(originalColumn As String, insertColumn As String, ByVal startRowCount As Integer, ByVal endRowCount As Integer, delimiter As String, ByVal wordLength As Integer, isTruncation As Boolean)

'�������镶���̒���
Dim searchWordLen As Integer

'�l������s�𐔂���
Dim n As Integer
n = IIf(endRowCount > 0, startRowCount + endRowCount - 1, Cells(Rows.Count, originalColumn).End(xlUp).Row)

'�l������s���ׂČ���������𐶐�
For rowIndex = startRowCount To n

    '����������𐶐�
    Dim cellvalue As String
    cellvalue = WorksheetFunction.Clean(Cells(rowIndex, originalColumn))
    strlen = Len(cellvalue)

    Dim index As Integer
    Dim arr() As String
    ReDim arr(strlen)
    
    '���߂�ꂽ�����ŕ���
    For index = 0 To IIf(isTruncation, strlen - wordLength, strlen)
        arr(index) = Mid(cellvalue, index + 1, wordLength)
    Next index

    '���
    Cells(rowIndex, insertColumn) = Join(arr, delimiter)

Next rowIndex

End Function

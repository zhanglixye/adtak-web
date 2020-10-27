Attribute VB_Name = "AbbeyMaster"
' for abbey table
Sub createInsertSQL()
' Generate search word
Call createSearchWord
' Add the first line
endRow = Cells(Rows.Count, "B").End(xlUp).Row
insertColumnSQL = "INSERT INTO `abbey` (`abbey_id`, `specification`, `specification_2`, `purpose`, `width`, `hight`, `file_size`, `file_size_unit`, `file_format`, `total_bit_rate`, `animation`, `alt_text`, `link`, `target_Loudness`, `text`, `title_text`, `branding_text`, `search_full_text`, `search_full_text_2`) VALUES"
Cells(3, "X") = insertColumnSQL
Call generateInsertStatement("B", "W", 4, endRow, "X")

MsgBox "OK"
End Sub

Function createSearchWord()

Dim startRow, endRow, wordLen As Integer
Dim delimiter As String
Dim isTruncation As Boolean

startRow = 4
endRow = -1 ' to end
wordLen = 2
isTruncation = False
delimiter = " "

' "abc" -> "ab bc"
Call createWord("C", "V", startRow, endRow, delimiter, wordLen, isTruncation)

Call createWord("D", "W", startRow, endRow, delimiter, wordLen, isTruncation)

End Function


Function generateInsertStatement(startColumn, endColumn, startRowCount, endRowCount, insertColumn)

Dim distance As Integer

distance = Columns(endColumn).Column - Columns(startColumn).Column

' Processing of each row
For rowIndex = startRowCount To endRowCount

    ' Remove extra file units
    arrayLength = distance - 3
    Dim arr() As String
    ReDim arr(arrayLength)

    Dim fileFormat As String
    fileFormat = ""
    Dim arrIndex As Integer
    arrIndex = 0
    For columnIndex = Columns(startColumn).Column To Columns(endColumn).Column

        '1,1,1,1 -> 1111
        If 10 <= columnIndex And columnIndex <= 13 Then
            fileFormat = fileFormat & Cells(rowIndex, columnIndex).Value

            If columnIndex = 13 Then
                arr(arrIndex) = "'" & fileFormat & "'"
                arrIndex = arrIndex + 1
            End If

        ' 1:*** -> 1
        ElseIf 5 = columnIndex Or 9 = columnIndex Then
            Dim strIndex As Integer
            strIndex = InStr(1, Cells(rowIndex, columnIndex).Value, ":", vbTextCompare)
            valueStr = Cells(rowIndex, columnIndex).Value
            If strIndex > 0 And Not IsNull(strIndex) Then
                valueStr = "'" & Left(Cells(rowIndex, columnIndex).Value, strIndex - 1) & "'"

            ElseIf Len(valueStr) > 0 Then
                valueStr = "'" & valueStr & "'"
            Else
                valueStr = "Null"

            End If

            arr(arrIndex) = valueStr
            arrIndex = arrIndex + 1

        Else
            arr(arrIndex) = IIf(Cells(rowIndex, columnIndex).Value = "", "Null", "'" & Cells(rowIndex, columnIndex).Value & "'")
            arrIndex = arrIndex + 1
        End If

    Next columnIndex

    ' Generate value rows
    If rowIndex = endRowCount Then
        Cells(rowIndex, insertColumn) = "(" & Join(arr, ",") & ")"
    Else
        Cells(rowIndex, insertColumn) = "(" & Join(arr, ",") & "),"
    End If

Next rowIndex

End Function

Sub removeDupulicateValue()
Dim startRowCount, endRowCount As Integer

startRowCount = 4
endRowCount = -1 ' to end

Dim selectColumn As String
selectColumn = "X"

' Count row values
Dim n As Integer
n = IIf(endRowCount > 0, startRowCount + endRowCount - 1, Cells(Rows.Count, selectColumn).End(xlUp).Row)

' RemoveDuplicate
For rowIndex = startRowCount To n Step 1
    For tmpRI = rowIndex - 1 To startRowCount Step -1
        If StrComp(Cells(rowIndex, selectColumn).Value, Cells(tmpRI, selectColumn).Value) = 0 Then
            Cells(rowIndex, selectColumn).Value = ""
            Exit For
        End If
    Next
Next

MsgBox "OK"

End Sub

Sub removeNotAbbeyIDValue()

Dim startRowCount, endRowCount As Integer

startRowCount = 4
endRowCount = -1 ' to end

Dim selectColumn, watchColumn As String
selectColumn = "X"
watchColumn = "B"

' Count row values
Dim n As Integer
n = IIf(endRowCount > 0, startRowCount + endRowCount - 1, Cells(Rows.Count, selectColumn).End(xlUp).Row)

' RemoveDuplicate
For rowIndex = startRowCount To n Step 1
    cellvalue = Cells(rowIndex, watchColumn).Value
    If Not IsNumeric(cellvalue) Or cellvalue = "" Then
        Cells(rowIndex, selectColumn).Value = ""
    End If
Next

MsgBox "OK"

End Sub

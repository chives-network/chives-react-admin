<?php
header("Content-Type: application/json"); 
require_once('cors.php');
require_once('include.inc.php');

//$externalId = 16;

//CheckAuthUserLoginStatus();
$DATA = DecryptID($_GET['DATA']);
$DATA = unserialize($DATA);

$Action         = $DATA['Action'];
$TableName      = $DATA['TableName'];
$FileName       = $DATA['FileName'];
$FormId         = $DATA['FormId'];

if($Action=="export_template"&&$FormId!="")              {

    $sql        = "select * from form_formfield where FormId='$FormId' and IsEnable='1' order by SortNumber asc, id asc";
    $rs         = $db->Execute($sql);
    $AllFieldsFromTable   = $rs->GetArray();
    $AllFields = [];
    foreach($AllFieldsFromTable as $Item)  {
        $AllFields[0][] = $Item['FieldName'];
        $AllFields[1][] = "";
        $AllFields[2][] = "";
    }

    $filename = $FileName."-".__("ImportTemplate").".xlsx";
    $filetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();

    //Make Body Data
    $row = 1;
    foreach ($AllFields as $rowData) {
        $col = 1;
        foreach ($rowData as $value) {
            //$worksheet->setCellValueByColumnAndRow($col, $row, $value);
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $cell->setValue($value);
            $cell->getStyle()->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $cell->getStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $cell->getStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);   
            if($row==1)  {
                $worksheet->getColumnDimensionByColumn($col)->setWidth(15);
                $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $cell->getStyle()->getFill()->getStartColor()->setRGB('D2EAF2');
            }
            $col++;
        }
        $worksheet->getRowDimension($row)->setRowHeight(20);
        $row++;
    }

    
    $worksheet->getColumnDimensionByColumn(1)->setWidth(15);

    header('Content-Type: ' . $filetype);
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}


        

?>
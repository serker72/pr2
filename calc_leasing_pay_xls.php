<?
// Проверка и получение значений переменной из $_GET или $_POST
// $var_type = 's' - string, 'i' - integer, 'f' - float
function ksk_get_var_from_get_post($name, $var_type='s', $value_default='') {
    if (!isset($name) || ($name == '')) {
        $value = $value_default;
    } elseif (isset($_GET[$name])){
        if ($var_type == 's') { $value = $_GET[$name]; }
        elseif ($var_type == 'i') { $value = abs((int)$_GET[$name]); }
        elseif ($var_type == 'f') { $value = abs((float)$_GET[$name]); }
        else { $value = $value_default; }
    } elseif (isset($_POST[$name])){
        if ($var_type == 's') { $value = $_POST[$name]; }
        elseif ($var_type == 'i') { $value = abs((int)$_POST[$name]); }
        elseif ($var_type == 'f') { $value = abs((float)$_POST[$name]); }
        else { $value = $value_default; }
    } else {
        $value = $value_default;
    }
    
    return $value;
}

session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');
require_once('classes/PHPExcel.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Рассчитать лизинговые платежи');


$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

// Получим переменные из запроса
$action = ksk_get_var_from_get_post('action', 'i', 0);
$equipment_cost = ksk_get_var_from_get_post('equipment_cost', 'f', 0);
$additional_services_cost = ksk_get_var_from_get_post('additional_services_cost', 'f', 0);
$prepayment_cost = ksk_get_var_from_get_post('prepayment_cost', 'f', 0);
$contract_time = ksk_get_var_from_get_post('contract_time', 'i', 3);
$equipment_service_life = ksk_get_var_from_get_post('equipment_service_life', 'i', 7);
$k_amortiz = ksk_get_var_from_get_post('k_amortiz', 'f', 2);
$credit_rate = ksk_get_var_from_get_post('credit_rate', 'f', 0);
$nds_rate = ksk_get_var_from_get_post('nds_rate', 'f', 18);
$leasing_rate = ksk_get_var_from_get_post('leasing_rate', 'f', 4.5);

// Первоначальные значения
$Ha = 0;
$S = 0;
$Y = 0;
$Y_m = 0;
$u_amortiz = array();
$sy_cost = array();
$lead_pay = array();


// Рассчет
if($action == 1) {
    require_once('calc_leasing_pay_inc.php');
    
              // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            
            // заголовок
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', iconv('windows-1251', 'utf-8', 'График начислений лизинговых платежей'))
                    
                ->setCellValue('A3', iconv('windows-1251', 'utf-8', '№ п/п'))
                ->setCellValue('B3', iconv('windows-1251', 'utf-8', 'Дата начисления платежа'))
                ->setCellValue('C3', iconv('windows-1251', 'utf-8', 'Лизинговый платеж к начислению с НДС'))
                ->setCellValue('D3', iconv('windows-1251', 'utf-8', 'Зачет аванса лизинговых платежей с НДС'))
                ->setCellValue('E3', iconv('windows-1251', 'utf-8', 'Итого к начислению, включая НДС'))
            ;


            $styleArray1 = array(
                'font' => array(
                    'bold' => true,
                    'size'=>11
                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A1:E3')->applyFromArray($styleArray1);
            
            $index = 4;
            for($i=1;$i<=count($lead_pay);$i++) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(0, $index, $lead_pay[$i][0], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $index, $lead_pay[$i][8], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $index, $lead_pay[$i][7], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $index, $lead_pay[$i][6], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                
                $index++;
            }
            
            $styleArray2 = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                ),	

                'font' => array(
                    'size' => 11
                ),
                
                'numberformat' => array(
                    'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                )
            );

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, iconv('windows-1251', 'utf-8', 'Итого'));
            $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $index, '=SUM(C4:C'.($index-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
            $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $index, '=SUM(D4:D'.($index-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
            $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $index, '=SUM(E4:E'.($index-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
            
            $objPHPExcel->getActiveSheet()->getStyle('A3:E'.$index.'')->applyFromArray($styleArray2);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$index.':E'.$index.'')->applyFromArray($styleArray1);
            
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(12);	
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(17);	
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(17);	
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(17);	
            
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:E3')->getAlignment()->setWrapText(true);
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:E3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8', "График начислений"));
            
            // Redirect output to a clientвЂ™s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','КП-лизинг.xls"'));
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');	
}
?>
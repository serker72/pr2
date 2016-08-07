<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/pl_posgroup_excel.php');

require_once('classes/posgroupgroup.php');
require_once('classes/posgroup.php');
require_once('classes/price_kind_item.php');
require_once('classes/PHPExcel.php');

$prefix='_1';
	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Ёкспорт');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;

/*
if(!$au->user_rights->CheckAccess('w',600)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
*/



//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
$sql='select c.name as c_name, cv.value as c_value, br.name as br_name
	from supplier as p
	inner join supplier_contact as c on c.supplier_id=p.id
	inner join supplier_contact_data as cv on cv.contact_id=c.id
	left join supplier_branches as br on p.branch_id=br.id
	where p.is_org=0 and cv.kind_id=5
	order by br.name asc, cv.value asc';
	
	//echo $sql;
	
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
 
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$alls[]=$f;
		}
	
	 
	  
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();
	
	//echo $llg;
// заголовок
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('C1', iconv('windows-1251', 'utf-8','ќтрасль '))
			->setCellValue('A1', iconv('windows-1251', 'utf-8','Ёл. почта '))
			
            ->setCellValue('B1', iconv('windows-1251', 'utf-8','»м€'))
			 
			 
			 
			
			;


$styleArray = array(
	'font' => array(
		'bold' => true,
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArray);

$index=2;

foreach($alls as $k=>$v){
	foreach($v as $k1=>$v1) $v[$k1]=iconv('windows-1251', 'utf-8',$v1);
	
	//print_r($v);
	
	 
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, $v['c_value']);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  $v['c_name']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  $v['br_name']);
			/*
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $index,  (float)$v2['discount_base']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  (float)$v2['discount_add']);
			
			 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  (float)$v2['price_ddpm']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $index,  (float)$v2['profit_exw']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $index, (float)$v2['max_discount_manager']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $index, (float)$v2['max_discount_ruk']);
			
			*/
	 
			$index++;
		
		 
	
	
	
}
$styleArray = array(
'borders' => array(
		'allborders'=> array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
		
	),	
	
 
	'font' => array(
		 
		'size'=>9
	)

	);
/*	
$objPHPExcel->getActiveSheet()->getStyle('A1:I'.($index-1).'')->applyFromArray($styleArray);


$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(9);	
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(16);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(7);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(11);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(7);

//$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(11);


$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:I1')->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('B1:B'.$index)->getAlignment()->setWrapText(true);


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8',"ExW"));
*/


//******************************************************************************************
 
	
// Redirect output to a clientвАЩs web browser (Excel2007)
/*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','Export.xlsx"'));
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
	*/
	header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
?>
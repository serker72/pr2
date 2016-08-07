<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


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
require_once('classes/supplier_responsible_user_group.php');

$prefix='_1';
	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Экспорт');


$log=new ActionLog;

 


//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
$sql='select p.*, opf.name as opf_name
	from supplier as p
	left join opf on opf.id=p.opf_id
	where p.is_org=0 
	and p.is_supplier=0
	and p.is_customer=0
	and p.is_partner=0
	order by p.full_name asc';
	
	//echo $sql;
	
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		$_sr=new SupplierResponsibleUserGroup;
		
 
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['resps']=$_sr->GetUsersArr($f['id'], $ids);
			
			$alls[]=$f;
		}
	
	 
	  
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();
	
	//echo $llg;
// заголовок
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A1', iconv('windows-1251', 'utf-8','Код '))
			->setCellValue('B1', iconv('windows-1251', 'utf-8','Название'))
			
            ->setCellValue('C1', iconv('windows-1251', 'utf-8','ОПФ'))
            ->setCellValue('D1', iconv('windows-1251', 'utf-8','Ответственные'))			
			 
			 
			 
			
			;


$styleArray = array(
	'font' => array(
		'bold' => true,
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArray);

$index=2;

foreach($alls as $k=>$v){
	foreach($v as $k1=>$v1) if($k1!='resps') $v[$k1]=iconv('windows-1251', 'utf-8',$v1);
	
	//print_r($v);
	
	 		$resps=array();
			foreach($v['resps'] as $kk=>$vv) $resps[]=iconv('windows-1251', 'utf-8',$vv['name_s']);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, $v['code']);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  $v['full_name']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  $v['opf_name']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $index,  implode('; ', $resps));
			
			
			
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
 
	
// Redirect output to a clientвЂ™s web browser (Excel2007)
 /*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','Export.xlsx"'));
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
	
	die();
	*/
	 
	header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
?>
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

$prefix='_1';
	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Прайс-лист');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',600)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}




//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//покажем лог
	$log=new PlPosGroupExcel;
	$prefix='_1';
	
	 
	//url='pricelist_pdf.php?price_kind_id%{$prefix}%=%{$price_kind_id}%&group_id%{$prefix}%=%{$group_id}%&two_group_id%{$prefix}%=%{$two_group_id}%&producer_id%{$prefix}%=%{$producer_id}%&id%{$prefix}%=%{$id}%&with_options='+with_options;
			
	
	//$sql='select distinct code, name from catalog_position where code<>"" and parent_id<>0 and group_id in(10,11) order by code';
	
	$sql='select distinct code, name from catalog_position where  parent_id<>0 and producer_id=5 and is_install=0 and name_en="" order by code';
	$set=new mysqlset($sql);
	$rs=$set->getResult();
	$rc=$set->getResultNumRows();
	
	
	
	
	
	
	
	  
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();
	
	//echo $llg;
// заголовок
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A1', iconv('windows-1251', 'utf-8','Коды по пр-ме '))
			->setCellValue('B1', iconv('windows-1251', 'utf-8','Код  '))
			->setCellValue('C1', iconv('windows-1251', 'utf-8','Наименование  (англ.)'))
			
             
			->setCellValue('D1', iconv('windows-1251', 'utf-8','Наименование  (рус.)'))
			 
			 
			/*->setCellValue('J1', iconv('windows-1251', 'utf-8','Цена ExW'))*/
			
		 
			
			;


$styleArray = array(
	'font' => array(
		'bold' => true,
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArray);

$index=2;

for($j=0; $j<$rc; $j++){
	$f=mysqli_fetch_array($rs);
	
	 $sql2='select distinct id from catalog_position where name="'.SecStr($f['name']).'" order by id asc ';
	 $set2=new mysqlset($sql2);
	$rs2=$set2->getResult();
	$rc2=$set2->getResultNumRows();
	
	$codes=array();
	for($k=0; $k<$rc2; $k++){
		$g=mysqli_fetch_array($rs2);
		$codes[]=$g[0];		
	}
	
	
//	$supplier=iconv('windows-1251', 'utf-8',$v['name']);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, implode('; ',$codes));
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  iconv('windows-1251', 'utf-8//translit', $f['code']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  iconv('windows-1251', 'utf-8//translit', $f['name']));
			
		
			
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
	
$objPHPExcel->getActiveSheet()->getStyle('A1:D'.($index-1).'')->applyFromArray($styleArray);

/*
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(9);	
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(16);


$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(7);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(11);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(7);

//$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(11);




*/
// Rename worksheet
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(10);	
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(24);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(24);


$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:D1')->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('B1:D'.$index)->getAlignment()->setWrapText(true);



	
	
// Redirect output to a clientвЂ™s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','Наименования для перевода.xlsx"'));
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
	
?>
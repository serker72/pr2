<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������


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
$smarty->assign("SITETITLE",'�����-����');

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




//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//������� ���
	$log=new PlPosGroupExcel;
	$prefix='_1';
	
	 
	//url='pricelist_pdf.php?price_kind_id%{$prefix}%=%{$price_kind_id}%&group_id%{$prefix}%=%{$group_id}%&two_group_id%{$prefix}%=%{$two_group_id}%&producer_id%{$prefix}%=%{$producer_id}%&id%{$prefix}%=%{$id}%&with_options='+with_options;
			
	
	
	
	
	$llg=$log->ShowPrint( 1,  3,0,0,0, 0,'pl_pdf/body.html' ,$result, $alls, 2 	   	   
	  );
	  
	  
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();
	
	//echo $llg;
// ���������
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A1', iconv('windows-1251', 'utf-8','������������� '))
			->setCellValue('B1', iconv('windows-1251', 'utf-8','������������ ������������ '))
			
            ->setCellValue('C1', iconv('windows-1251', 'utf-8','������� ����'))
			->setCellValue('D1', iconv('windows-1251', 'utf-8','������ �-�� �������'))
			->setCellValue('E1', iconv('windows-1251', 'utf-8','������ �-�� ���.'))
			->setCellValue('F1', iconv('windows-1251', 'utf-8','�������� ����'))
			->setCellValue('G1', iconv('windows-1251', 'utf-8','����-�� ExW, %'))
			
			->setCellValue('H1', iconv('windows-1251', 'utf-8','����. ������ ���������, %'))
			->setCellValue('I1', iconv('windows-1251', 'utf-8','����. ������ ���-��, %'))
			 
			/*->setCellValue('J1', iconv('windows-1251', 'utf-8','���� ExW'))*/
			
		 
			
			;


$styleArray = array(
	'font' => array(
		'bold' => true,
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArray);

$index=2;

foreach($alls as $k=>$v){
	$supplier=iconv('windows-1251', 'utf-8',$v['name']);
	
	//print_r($v);
	
	foreach($v['cats'] as $k1=>$v1){
		$cat=iconv('windows-1251', 'utf-8',$v1['name']); 
		
		foreach($v1['goods'] as $k2=>$v2){
			$good=iconv('windows-1251', 'utf-8',$v2['name']); 	
			
			$index_good=$index;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, $supplier);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  $good);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  (float)$v2['price_f']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $index,  (float)$v2['discount_base']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  (float)$v2['discount_add']);
			
			/*$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $index,  '=C'.$index.'*((100-D'.$index.')/100)*((100-E'.$index.')/100)');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$index)->getNumberFormat()->setFormatCode('0.00');*/
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  (float)$v2['price_ddpm']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $index,  (float)$v2['profit_exw']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $index, (float)$v2['max_discount_manager']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $index, (float)$v2['max_discount_ruk']);
			
			
		/*	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $index,  '=F'.$index.'*((100+G'.$index.')/100)*((100-H'.$index.')/100)*((100-I'.$index.')/100)');
			$objPHPExcel->getActiveSheet()->getStyle('J'.$index)->getNumberFormat()->setFormatCode('0.00');
			*/
			
			$index++;
		
		}
		
	}
	
	
	
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



//******************************************************************************************
//worksheet for ddpm

$llg=$log->ShowPrint( 1,   3,0,0,0, 0,'pl_pdf/body.html' ,$result, $alls, 1 	   	   
	  );
	  

$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex(1);
// ���������
$objPHPExcel->setActiveSheetIndex(1)
            
            ->setCellValue('A1', iconv('windows-1251', 'utf-8','������������� '))
			->setCellValue('B1', iconv('windows-1251', 'utf-8','������������ ������������ '))
			
            ->setCellValue('C1', iconv('windows-1251', 'utf-8','������� ����'))
			->setCellValue('D1', iconv('windows-1251', 'utf-8','������ �-�� �������'))
			->setCellValue('E1', iconv('windows-1251', 'utf-8','������ �-�� ���.'))
			->setCellValue('F1', iconv('windows-1251', 'utf-8','�������� ����'))
			->setCellValue('G1', iconv('windows-1251', 'utf-8','����-�� DDPM, %'))
			
			->setCellValue('H1', iconv('windows-1251', 'utf-8','����. ������ ���������, %'))
			->setCellValue('I1', iconv('windows-1251', 'utf-8','����. ������ ���-��, %'))
		/*	->setCellValue('J1', iconv('windows-1251', 'utf-8','���� DDPM'))*/
			
			
		
			
			;


$styleArray = array(
	'font' => array(
		'bold' => true,
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArray);

$index=2;

foreach($alls as $k=>$v){
	$supplier=iconv('windows-1251', 'utf-8',$v['name']);
	
	//print_r($v);
	
	foreach($v['cats'] as $k1=>$v1){
		$cat=iconv('windows-1251', 'utf-8',$v1['name']); 
		
		foreach($v1['goods'] as $k2=>$v2){
			$good=iconv('windows-1251', 'utf-8',$v2['name']); 	
			
			$index_good=$index;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index, $supplier);
		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  $good);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  (float)$v2['price_f']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $index,  (float)$v2['discount_base']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  (float)$v2['discount_add']);
			
			/*$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $index,  '=C'.$index.'*((100-D'.$index.')/100)*((100-E'.$index.')/100)');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$index)->getNumberFormat()->setFormatCode('0.00');*/
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $index,  (float)$v2['price_ddpm']);
			
			
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $index,  (float)$v2['profit_ddpm']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $index, (float)$v2['max_discount_manager']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $index, (float)$v2['max_discount_ruk']);
			
			
		 
			//���� exw, ddpm
		 
		 /*
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $index,  '=F'.$index.'*((100+G'.$index.')/100)*((100-H'.$index.')/100)*((100-I'.$index.')/100)');
			$objPHPExcel->getActiveSheet()->getStyle('J'.$index)->getNumberFormat()->setFormatCode('0.00');
			*/ 
			
			$index++;
		
		}
		
	}
	
	
	
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
	
$objPHPExcel->getActiveSheet()->getStyle('A1:I'.($index-1).'')->applyFromArray($styleArray);


$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('A')->setWidth(9);	
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('B')->setWidth(16);

$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('C')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('D')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('E')->setWidth(7);

$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('F')->setWidth(11);

$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('G')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('H')->setWidth(7);
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('I')->setWidth(7);

//$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('J')->setWidth(11);



$objPHPExcel->setActiveSheetIndex(1)->getStyle('A1:I1')->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(1)->getStyle('B1:B'.$index)->getAlignment()->setWrapText(true);


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8',"DDPM"));

	
	
// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','�����-����.xlsx"'));
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
	
?>
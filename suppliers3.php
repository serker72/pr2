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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/suppliersgroup2.php');

require_once('classes/supplier_to_user.php');
require_once('classes/PHPExcel.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр контрагентов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['suppliers_from'])){
		$from=abs((int)$_SESSION['suppliers_from']);
	}else $from=0;
	$_SESSION['suppliers_from']=$from;




if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 


if(!$au->user_rights->CheckAccess('w',1)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$log=new ActionLog;
/*if($print==0){
	$log->PutEntry($result['id'],'открыл раздел Контрагенты',NULL,91);
}else{
	$log->PutEntry($result['id'],'открыл раздел Контрагенты: версия для печати',NULL,91);
}*/


//  $smarty->display('top_print.html');
 

 
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	 
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
	
	
	$decorator=new DBDecorator;
	
	
	//только Москва и МО
	 $decorator->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  supplier_sprav_city where city_id=1 or city_id in (select id from sprav_city where region_id=62)', SqlEntry::IN_SQL));
	 
	
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=3;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
		break;
		
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::DESC));
		break;
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::ASC));
		break;
		
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::DESC));
		break;
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::ASC));
		break;
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::DESC));
		break;
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);


 
	
	
	$ug=new SuppliersGroup2;
	 $uug= $ug->GetItems('suppliers/suppliers_export_xl.html',$decorator,0,1000000,false,$au->user_rights->CheckAccess('w',543), $limited_supplier,  $result, $au->user_rights->CheckAccess('w',914),$alls);
	
	//echo $uug;
	
	
	//эксель
	// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setLastModifiedBy(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setTitle(iconv('windows-1251', 'utf-8',"Экспорт "))
							 ->setSubject(iconv('windows-1251', 'utf-8',"Экспорт  Office 2007 XLSX Test Document"))
							 ->setDescription(iconv('windows-1251', 'utf-8',"Экспорт  Office 2007 XLSX, "))
							 ->setKeywords("")
							 ->setCategory(iconv('windows-1251', 'utf-8',"Отчет"));




$objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(9);
	
	
	
	$working_row=1;
	
	$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Полное наименование '))		
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','ОПФ'))
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8','Страна'))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','Регион'))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Город'))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','Отрасль'))
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8','Подотрасль'))
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8','Подотрасль 1'))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8','ФИО'))
			->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8','Должность'))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','День рождения'))
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8','Контакты'))
			
			;
	
	
	foreach($alls as $k=>$sup){
		$working_row++;
		
		$country=""; $region=""; $city="";
		foreach($sup['cities'] as $kk=>$cities){
			
			$country.=$cities['country_name']." \n";
			$region.=$cities['region_name'].', '.$cities['okrug_name']." \n";
			$city.=$cities['name']." \n";
		}
		
		$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$sup['full_name']))		
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$sup['opf_name']))
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',$country))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$region))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',$city))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$sup['branch_name']))
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8',$sup['subbranch_name']))
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$sup['subbranch_name1']))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',''))
			->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8',''))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',''))
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',''))
			
			;
		//итерация по контактам
		foreach($sup['contacts'] as $kk=>$contacts){
			$objPHPExcel->setActiveSheetIndex(0)
            
            
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',$contacts['name']))
			->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8',$contacts['position']))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',$contacts['birthdate']))
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',''))
			
			;
			
			foreach($contacts['data'] as $k2=>$data){
				$objPHPExcel->setActiveSheetIndex(0)
            
					->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',$data['pc_name'].': '.$data['value'].' '.$data['value1']))
					
					;
				$working_row++;	
			}
			
			$working_row++;	
		}
	}
	
	
	$objPHPExcel->setActiveSheetIndex(0);

 

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Отчет.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
die(); 
	 
	//  echo $content;
//	unset($smarty);

 
?>
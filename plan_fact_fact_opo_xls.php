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

require_once('classes/plan_fact_fact_group.class.php');
require_once('classes/user_pos_item.php');


require_once('classes/PHPExcel.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from']))
	{
		 $from=abs((int)$_GET['from']);
	//elseif(isset($_SESSION['bills_from'])){
		//$from=abs((int)$_SESSION['bills_from']);
	}else $from=0;
	//$_SESSION['bills_from']=$from;

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(($print==1)&&!$au->user_rights->CheckAccess('w',823)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!$au->user_rights->CheckAccess('w',820)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


 
			
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	

	
	
/*********** OPO ****************************************************************************/		
	
	//покажем лог
	$log=new PlanFactFactGroup;
	//Разбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	//echo $result['org_id'];
	
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	if(isset($_GET['month'])&&(abs((int)$_GET['month'])>0)){
		$decorator->AddEntry(new SqlEntry('p.month',SecStr($_GET['month']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('month',$_GET['month']));
	}
	
	if(isset($_GET['year'])&&(abs((int)$_GET['year'])>0)){
		$decorator->AddEntry(new SqlEntry('p.year',SecStr($_GET['year']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('year',$_GET['year']));
	}
	
	if(isset($_GET['us_name'])&&(strlen($_GET['us_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new SqlEntry('us.name_s',SecStr(iconv('utf-8','windows-1251',$_GET['us_name'])), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('us_name',iconv('utf-8','windows-1251',$_GET['us_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('us.name_s',SecStr($_GET['us_name']), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('us_name',$_GET['us_name']));
		}
	}
	
	
	if(isset($_GET['price_kind_id'])&&(abs((int)$_GET['price_kind_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id']));
	}
	
	
	if(isset($_GET['eq_name'])&&(strlen($_GET['eq_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new SqlEntry('p.eq_name',SecStr(iconv('utf-8','windows-1251',$_GET['eq_name'])), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('eq_name',iconv('utf-8','windows-1251',$_GET['eq_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.eq_name',SecStr($_GET['eq_name']), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name']));
		}
	}
	if(isset($_GET['prod_name'])&&(strlen($_GET['prod_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('prod.name',SecStr(iconv('utf-8','windows-1251',$_GET['prod_name'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('prod_name',iconv('utf-8','windows-1251',$_GET['prod_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('prod.name',SecStr($_GET['prod_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('prod_name',$_GET['prod_name']));
		}
	}
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr(iconv('utf-8','windows-1251',$_GET['supplier_name'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',iconv('utf-8','windows-1251',$_GET['supplier_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		}
	}
	
	if(isset($_GET['city'])&&(strlen($_GET['city'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('c.name',SecStr(iconv('utf-8','windows-1251',$_GET['city'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('city',iconv('utf-8','windows-1251',$_GET['city'])));
		}else{
			$decorator->AddEntry(new SqlEntry('c.name',SecStr($_GET['city']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('city',$_GET['city']));
		}
	}
	
	if(isset($_GET['contract_no'])&&(strlen($_GET['contract_no'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('p.contract_no',SecStr(iconv('utf-8','windows-1251',$_GET['contract_no'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('contract_no',iconv('utf-8','windows-1251',$_GET['contract_no'])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.contract_no',SecStr($_GET['contract_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('contract_no',$_GET['contract_no']));
		}
	}
	
	
	if(isset($_GET['status_id'])){
		if($_GET['status_id']>0){
					$decorator->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['status_id']), SqlEntry::E));
				}
		$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id']));
	}else{
	
	  if(isset($_COOKIE['fact_opo_status_id'])){
			  $status_id=$_COOKIE['fact_opo_status_id'];
	  }else $status_id=0;
	  
	  if($status_id>0) $decorator->AddEntry(new SqlEntry('p.status_id',$status_id, SqlEntry::E));
	  $decorator->AddEntry(new UriEntry('status_id',$status_id));
	}
	
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr(iconv('utf-8','windows-1251',$_GET['manager_name'])), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',iconv('utf-8','windows-1251',$_GET['manager_name'])));
		}else{
			$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
		}
	}
	
	
	//отбор по себе и подчиненным сотр-кам
	if(!$au->user_rights->CheckAccess('w',784)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.user_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.user_id','select id from user where manager_id="'.$result['id'].'" and is_in_plan_fact_sales=1 and is_active=1', SqlEntry::IN_SQL));
		
		//если тек. сотр-к является руководителем отдела - добавить ему руководимых сотрудников
		$_pos=new UserPosItem;
		$pos=$_pos->Getitembyid($result['position_id']);
		if($pos['is_ruk_otd']){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
			$decorator->AddEntry(new SqlEntry('p.user_id','select id from user where is_active=1 and is_in_plan_fact_sales=1 and department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"', SqlEntry::IN_SQL));
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	 
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.month',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.month',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.year',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.year',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('us_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('us_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('prod.name',SqlOrdEntry::DESC));
		break;
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('prod.name',SqlOrdEntry::ASC));
		break;
		
	
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	$decorator->AddEntry(new UriEntry('print',$print));
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	
	 $llg=$log->ShowPos('plan_fact_sales/plan_fact_sales_opo_list'.$print_add.'.html', 
	  $decorator, 
	  0, 
	  1000000,   
	  
		$au->user_rights->CheckAccess('w',793) , 
		$au->user_rights->CheckAccess('w',791), 
		true,
		false ,
		$au->user_rights->CheckAccess('w',794),
		$au->user_rights->CheckAccess('w',792), 
		$au->user_rights->CheckAccess('w',788), 
		$au->user_rights->CheckAccess('w',813),
		$au->user_rights->CheckAccess('w',823),
		$alls
	);
	 
	
	
	$sm->assign('log',$llg);
	$sm->assign('has_kps', $au->user_rights->CheckAccess('w',783));
	
	
	
	
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
 
	
	$content=$sm->fetch('plan_fact_sales/plan_fact_sales_opo'.$print_add.'.html');
	
	
	
	  
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();
	
	//echo $llg;
// заголовок
$objPHPExcel->setActiveSheetIndex(0)
            
       
			->setCellValue('A1', iconv('windows-1251', 'utf-8','Номер '))
			->setCellValue('B1', iconv('windows-1251', 'utf-8','Месяц'))
        
			->setCellValue('C1', iconv('windows-1251', 'utf-8','Год'))
			->setCellValue('D1', iconv('windows-1251', 'utf-8','Сотрудник'))
			
			->setCellValue('E1', iconv('windows-1251', 'utf-8','Вид цен'))
			->setCellValue('F1', iconv('windows-1251', 'utf-8','Оборудование'))
			->setCellValue('G1', iconv('windows-1251', 'utf-8','Производитель'))
			->setCellValue('I1', iconv('windows-1251', 'utf-8','Сумма договора '))
			->setCellValue('H1', iconv('windows-1251', 'utf-8','№ договора'))
			->setCellValue('J1', iconv('windows-1251', 'utf-8','Клиент'))
			->setCellValue('K1', iconv('windows-1251', 'utf-8','Город'))
			->setCellValue('L1', iconv('windows-1251', 'utf-8','Статус'))
			->setCellValue('M1', iconv('windows-1251', 'utf-8','Примечания'))
			->setCellValue('N1', iconv('windows-1251', 'utf-8','Создал'))
			
			 
			/*->setCellValue('J1', iconv('windows-1251', 'utf-8','Цена ExW'))*/
			
		 
			
			;
	
$index=2;	 
foreach($alls as $k=>$v){
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $index,  iconv('windows-1251', 'utf-8',$v['id']));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $index,  iconv('windows-1251', 'utf-8',$v['month_name']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $index,  iconv('windows-1251', 'utf-8',$v['year']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $index,  iconv('windows-1251', 'utf-8',$v['us_name'].' '.$v['us_login']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $index,  iconv('windows-1251', 'utf-8',$v['price_kind_name']));
	
	$eq=$v['eq_name'];
	if($v['eq_is_new']==1) $eq.=' новое оборудование';
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $index,  iconv('windows-1251', 'utf-8',$eq));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $index,  iconv('windows-1251', 'utf-8',$v['prod_name']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $index,  iconv('windows-1251', 'utf-8',$v['contract_sum']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $index,  iconv('windows-1251', 'utf-8',$v['contract_no']));
	
	$eq=$v['supplier_name'];
	if($v['supplier_is_new']==1) $eq.=' новый клиент';
	
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $index,  iconv('windows-1251', 'utf-8',$eq));
	
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $index,  iconv('windows-1251', 'utf-8',$v['city']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $index,  iconv('windows-1251', 'utf-8',$v['status_name']));
	
	
	$notes='';
	foreach($v['notes'] as $k1=>$v1) $notes.=$v1['pdate'].' '.$v1['user_name_s'].' '.$v1['user_login'].' '.$v1['note'].' '."; ";
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $index,  iconv('windows-1251', 'utf-8',$notes));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $index,  iconv('windows-1251', 'utf-8',$v['manager_name'].' '.$v['manager_login']));
	
	
	
	$index++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','Факт продаж.xlsx"'));
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
	
 ?>
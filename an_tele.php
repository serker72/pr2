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



require_once('classes/an_kp.class.php');


require_once('classes/an_kp_rent.class.php');

require_once('classes/user_to_user.php');

require_once('classes/an_tele.class.php');
require_once('classes/an_tele_monitor.class.php');
//require_once('classes/an_working_time.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Тендеры/Лиды');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',967)&&!$au->user_rights->CheckAccess('w',969)&&!$au->user_rights->CheckAccess('w',1002)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',968)&&!$au->user_rights->CheckAccess('w',970)&&!$au->user_rights->CheckAccess('w',1003)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет Тендеры/Лиды',NULL,967,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));

if($print==0) $smarty->display('top.html');
//else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=76;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
//******************************************************************************************************************
//вкладка отчет Тендеры
 
	
	$an1= new AnTele_Tenders;
	
	$prefix='_1';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	//контроль видимости
	if(!$au->user_rights->CheckAccess('w',934)){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$an1->GetAvailableIds($result['id'])));	
	}
	
	
	
	
	$decorator->AddEntry(new UriEntry('prefix',$prefix));

	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	
	if(!isset($_GET['pdate_placing_1'.$prefix])){
	 		$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
	}else $pdate1 = $_GET['pdate_placing_1'.$prefix];
	
	if(!isset($_GET['pdate_placing_2'.$prefix])){
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_placing_2'.$prefix];

	$decorator->AddEntry(new UriEntry('pdate_placing_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_placing_2',$pdate2));
	
	$decorator->AddEntry(new SqlEntry('p.pdate_placing',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	 
	
	
	if(!isset($_GET['pdate_claiming_1'.$prefix])){
	 		$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
	}else $pdate1 = $_GET['pdate_claiming_1'.$prefix];
	
	if(!isset($_GET['pdate_claiming_2'.$prefix])){
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_claiming_2'.$prefix];

	$decorator->AddEntry(new UriEntry('pdate_claiming_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_claiming_2',$pdate2));
	
	$decorator->AddEntry(new SqlEntry('p.pdate_claiming',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	
	
	if(!isset($_GET['pdate_finish_1'.$prefix])){
	 		$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
	}else $pdate1 = $_GET['pdate_finish_1'.$prefix];
	
	if(!isset($_GET['pdate_finish_2'.$prefix])){
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_finish_2'.$prefix];

	$decorator->AddEntry(new UriEntry('pdate_finish_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_finish_2',$pdate2));
	
	$decorator->AddEntry(new SqlEntry('p.pdate_finish',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	
	
	//фильтр по статусам
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	   
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//вид
	if(isset($_GET['kinds'.$prefix])&&(abs((int)$_GET['kinds'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('kind',$_GET['kinds'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.kind_id',abs((int)$_GET['kinds'.$prefix]), SqlEntry::E));		 
	} 
	
	
	//тип обор-я
	if(isset($_GET['eq_type'.$prefix])&&(abs((int)$_GET['eq_type'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('eq_type',$_GET['eq_type'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_GET['eq_type'.$prefix]), SqlEntry::E));		 
	}
	
	//фз
	if(isset($_GET['fz'.$prefix])&&(abs((int)$_GET['fz'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('fz',$_GET['fz'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.fz_id',abs((int)$_GET['fz'.$prefix]), SqlEntry::E));		 
	}
	
	//название
	if(isset($_GET['topic'.$prefix])&&(strlen(SecStr($_GET['topic'.$prefix]))>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('p.topic',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['topic'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('topic',iconv("utf-8","windows-1251",$_GET['topic'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('p.topic',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['topic'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
			
		}
	}
	
	
	//фильтры по отв сотруднику
	 if(isset($_GET['manager_name'.$prefix])&&(strlen(SecStr($_GET['manager_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['manager_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('manager_name',  $_GET['manager_name'.$prefix]));
	}
	 
	//фильтр по контрагенту
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen(SecStr($_GET['supplier_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  tender_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
	}*/
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
		 
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  tender_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  tender_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.holding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  tender_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_users1).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предприятия субхолдингов заданного предприятия
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  tender_suppliers as ps 
			inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1  /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where  ss.holding_id in(  '.implode(', ',$_users1).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг = заданному
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  tender_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  tender_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));	
		}
	}
	
	
	
	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		 
		
	 
		 $decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from   tender_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		 
		 
		
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
		
	} 
	
	
	
	
	 
	 
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		
	 
		
		$decorator->AddEntry(new SqlEntry('p.id',' select distinct  sched_id from  tender_suppliers where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_country as sc on sc.id=c.country_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct  sched_id from  tender_suppliers where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_district as sc on sc.id=c.district_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}
	
	
	 //поиск по содержанию 
	if(isset($_GET['content'.$prefix])&&(strlen(SecStr($_GET['content'.$prefix]))>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['content'.$prefix]);
		}else{
			$crit= $_GET['content'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from tender_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from tender_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		 //поиск по ИМЕНАМ ФАЙЛОВ  ленты
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from tender_history as h inner join tender_history_file as f on f.history_id=h.id    WHERE f.orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		
		//поиск по СОДЕРЖАНИЮ файла  ленты
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from tender_history as h inner join tender_history_file as f on f.history_id=h.id    WHERE MATCH (f.text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		//поиск по комментариям
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select sched_id from tender_history where txt LIKE "%'.SecStr($crit).'%"', SqlEntry::IN_SQL));
		
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('content', $crit));
	 }
	 
	//расширенная форма
	
	  if(isset($_GET['has_content'.$prefix])&&($_GET['has_content'.$prefix]==1)){
	 	$decorator->AddEntry(new UriEntry('has_content', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_content', 0));
	 
	 
	 
	 
	//сортировка данных
	if(!isset($_GET['sortmode'.$prefix])||(strlen($_GET['sortmode'.$prefix])==0)){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	//$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
	
		
		
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
			
		break;
		
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_placing',SqlOrdEntry::DESC));
			 
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_placing',SqlOrdEntry::ASC));
		break;
		
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_claiming',SqlOrdEntry::DESC));
			 
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_claiming',SqlOrdEntry::ASC));
		break;
		
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::DESC));
			 
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::ASC));
		break;
		
		
		default:
				
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			 
		break;	
		
	}
	
	 
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	 
	 
	
	$filetext=$an1->ShowData(
		'an_tele/an_tele_tenders'.$print_add.'.html',
		$decorator,
		'an_tele.php',
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)), 
		$au->user_rights->CheckAccess('w',970), 
		$au->user_rights->CheckAccess('w',931),
		$data,
		$result);
	
	 
	 
		$sm->assign('log',$filetext);
	 
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		 
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Тендеры',NULL,970,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Тендеры',NULL,969,NULL, NULL);		
	}
	
	
	$sm->assign('has_tenders',$au->user_rights->CheckAccess('w',969));	
	
	
	
	
	
	
	
	
	
	
	
//*********************************************************************************************

//вкладка отчет лиды

	$an= new AnTele_Leads;
	
	$prefix='_2';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	//контроль видимости
	if(!$au->user_rights->CheckAccess('w',953)){
		$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$an->GetAvailableIds($result['id'])));	
	}
	
	
	
	
	$decorator->AddEntry(new UriEntry('prefix',$prefix));

	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	//$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	if(!isset($_GET['pdate1_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate1_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate1_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate1_2',$pdate2));
	
	
	$decorator->AddEntry(new SqlEntry('p.pdate_finish',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	
	
	
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($pdate1) , SqlEntry::BETWEEN, DateFromdmY($pdate2)));
	 
	 
	
	//состояния
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'states'])&&is_array($_GET[$prefix.'states'])) $cou_stat=count($_GET[$prefix.'states']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'states'];
	   
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_states',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_states',1));
		}else{
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			foreach($status_ids as $k=>$v) {
				$decorator->AddEntry(new UriEntry('state_'.$v,1));
				$decorator->AddEntry(new UriEntry($prefix.'states[]',$v));
				if($v==0){
					$decorator->AddEntry(new SqlEntry('p.probability',0, SqlEntry::BETWEEN,29.99));
				}elseif($v==1){
					$decorator->AddEntry(new SqlEntry('p.probability',30, SqlEntry::BETWEEN,69.99));
				}elseif($v==2){
					$decorator->AddEntry(new SqlEntry('p.probability',70, SqlEntry::GE));
				}
				if($k!=(count($status_ids)-1)) $decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
				
			}
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			
		 
		 
		}
	} 
	
	
	//фильтр по статусам
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	   
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	 
	
	
	
	
	//вид
	if(isset($_GET['kinds'.$prefix])&&(abs((int)$_GET['kinds'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('kind',$_GET['kinds'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.kind_id',abs((int)$_GET['kinds'.$prefix]), SqlEntry::E));		 
	} 
	
	
	//тип обор-я
	if(isset($_GET['eq_type'.$prefix])&&(abs((int)$_GET['eq_type'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('eq_type',$_GET['eq_type'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_GET['eq_type'.$prefix]), SqlEntry::E));		 
	}
	
	
	//название
	if(isset($_GET['topic'.$prefix])&&(strlen(SecStr($_GET['topic'.$prefix]))>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('p.topic',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['topic'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('topic',iconv("utf-8","windows-1251",$_GET['topic'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('p.topic',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['topic'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
			
		}
	}
	
	
	
	 
	//фильтры по отв сотруднику
	 
	if(isset($_GET['manager_name'.$prefix])&&(strlen(SecStr($_GET['manager_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['manager_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('manager_name',  $_GET['manager_name'.$prefix]));
	}
	 
	//фильтр по контрагенту
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen(SecStr($_GET['supplier_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  lead_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
	}*/
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
		 
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  lead_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  lead_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.holding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  lead_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_users1).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предприятия субхолдингов заданного предприятия
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  lead_suppliers as ps 
			inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1  /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where  ss.holding_id in(  '.implode(', ',$_users1).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг = заданному
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  lead_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  lead_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));	
		}
	}
	
	
	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		 
		
	 
		 $decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  lead_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		 
		 
		
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
		
	} 
	
	
	
	
	 
	 
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		
	 
		
		$decorator->AddEntry(new SqlEntry('p.id',' select distinct  sched_id from  lead_suppliers where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_country as sc on sc.id=c.country_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct  sched_id from  lead_suppliers where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_district as sc on sc.id=c.district_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}
	
	
	//по пр-лю
	if(isset($_GET['wo_producer'.$prefix])){
		$decorator->AddEntry(new SqlEntry('p.wo_producer', 1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('wo_producer',  1));
		
	}elseif(isset($_GET['producer_name'.$prefix])&&(strlen(SecStr($_GET['producer_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['producer_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('producer_name',  $_GET['producer_name'.$prefix]));
		$decorator->AddEntry(new UriEntry('wo_producer',  0));
	}
	
	
	 //поиск по содержанию 
	if(isset($_GET['content'.$prefix])&&(strlen(SecStr($_GET['content'.$prefix]))>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['content'.$prefix]);
		}else{
			$crit= $_GET['content'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from lead_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from lead_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		 //поиск по ИМЕНАМ ФАЙЛОВ  ленты
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from lead_history as h inner join lead_history_file as f on f.history_id=h.id    WHERE f.orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		
		//поиск по СОДЕРЖАНИЮ файла  ленты
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from lead_history as h inner join lead_history_file as f on f.history_id=h.id    WHERE MATCH (f.text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		//поиск по комментариям
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select sched_id from lead_history where txt LIKE "%'.SecStr($crit).'%"', SqlEntry::IN_SQL));
		
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('content', $crit));
	 }
	 
	//расширенная форма
	
	 if(isset($_GET['has_content'.$prefix])&&($_GET['has_content'.$prefix]==1)){
	 	$decorator->AddEntry(new UriEntry('has_content', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_content', 0));
	 
	 
	 
	//сортировка данных
	if(!isset($_GET['sortmode'.$prefix])||(strlen($_GET['sortmode'.$prefix])==0)){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	//$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
	
		
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
		break; 
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
			
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('p.producer_id',SqlOrdEntry::DESC));
			
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('p.producer_id',SqlOrdEntry::ASC));
			
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('p.probability',SqlOrdEntry::DESC));
			
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('p.probability',SqlOrdEntry::ASC));
			
		break;
		
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::DESC));
			 
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::ASC));
		break;
		
		
		default:
				
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			 
		break;	
		
	}
	
	 
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		 
	 
	
	$filetext=$an->ShowData(
		'an_tele/an_tele_leads'.$print_add.'.html',
		$decorator,
		'an_tele.php',
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),
		$au->user_rights->CheckAccess('w',968), 
		$au->user_rights->CheckAccess('w',950),
		$data,
		$result);
	
	 
	 
	
	 
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		 
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Лиды',NULL,968,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Лиды',NULL,903,NULL, NULL);	
	}
	
	
	 
	
	
	$sm->assign('log2',$filetext);	
	$sm->assign('has_leads',$au->user_rights->CheckAccess('w',967));	
	
	
	
	 
/******************************************************************************************************/
//вкладка ЧТО МОНИТОРИТЬ?	
	
	$an3= new AnTenderMonitor;
	
	$prefix='_3';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
 
	$decorator->AddEntry(new UriEntry('prefix',$prefix));

	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',3));
	 
	
	
	/*if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else{
		 $pdate1 = $_GET['pdate_1'.$prefix];
  		 $_pdate1=DateFromdmY( $_GET['pdate_1'.$prefix]);	 
	}
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else{
		 $pdate2 = $_GET['pdate_2'.$prefix];
		 $_pdate2=DateFromdmY( $_GET['pdate_2'.$prefix]);	 
		 
	}
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	$decorator->AddEntry(new SqlEntry('p.given_pdate',$_pdate1, SqlEntry::BETWEEN,$_pdate2));*/
	
	
	 if(isset($_GET['has_1'.$prefix])&&($_GET['has_1'.$prefix]==1)){
	 	$decorator->AddEntry(new UriEntry('has_1', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_1', 0));
	 
	  if(isset($_GET['has_2'.$prefix])&&($_GET['has_2'.$prefix]==1)){
	 	$decorator->AddEntry(new UriEntry('has_2', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_2', 0));
		
		
		
	//ограничения на просмотр контрагентов
	$limited_supplier=NULL;

	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		//$decorator->AddEntry(new SqlEntry('sup.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
	}
	
	
	$filetext3=$an3->ShowData(
		
		'an_tele/an_tele_monitor'.$print_add.'.html',
		$decorator, 
		'an_tele.php',
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3)), 
		$au->user_rights->CheckAccess('w',1003),
		false,
		$data, 
		$result,
		$limited_supplier);
	
	 
	 
	
	 
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3))){
		 
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Что мониторить?',NULL,1003,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Что мониторить?',NULL,1002,NULL, NULL);	
	}
	
	 
	$sm->assign('log3',$filetext3);	
	
	$sm->assign('has_monitor',$au->user_rights->CheckAccess('w',1002));	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_tele/an_tele_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else {
		//echo $content;
		
		$sm2=new SmartyAdm;
		
		//$content=$sm2->fetch('plan_pdf/pdf_header_lo.html').$content;
		
		
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		
		//скомпилируем подвал
		$sm=new SmartyAdm;
		$sm->assign('print_pdate', date("d.m.Y H:i:s"));
			//$username=$result['login'];
			$username=stripslashes($result['name_s']); //.' '.$username;	
			$sm->assign('print_username',$username);
		$foot=$sm->fetch('plan_pdf/pdf_footer_unlogo.html');
		$ftmp='f'.time();
		
		$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
		fputs($f, $foot);
		fclose($f);
		
		//die();
		 
		 
		if( isset($_GET['doSub_3'])||isset($_GET['doSub_3_x'])){
			$orient='--orientation Portrait';
			
		}else $orient='--orientation Landscape ';
		
		
		 
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm ".$orient."  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_Тендеры_Лиды.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	 
		
	
	
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$ftmp.'.html');  
		 
		exit;
	}
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
//else $smarty->display('bottom_print.html');
unset($smarty);
?>
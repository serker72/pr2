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


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Коммерческие предложения');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',760)&&!$au->user_rights->CheckAccess('w',764)){
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
	if(!$au->user_rights->CheckAccess('w',761)&&!$au->user_rights->CheckAccess('w',765)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет Коммерческие предложения',NULL,760,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=62;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
//******************************************************************************************************************
//вкладка отчет КП
	
	
	$an=new AnKp;
	$prefix='_1';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	
	

	
	
	//фильтры по доступу сотрудника
	//отбор по тому, кто создал КП
	$podd=array();
	if(!$au->user_rights->CheckAccess('w',763)){
		//1. свои КП
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$an->view_rules->GetManagers($result)));	
		
		
		
		//3. КП, подпадающие под правила из kp_rights
		//echo $log->view_rules->GetListSql($result);
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position where position_id in('.$an->view_rules->GetListSql($result).')', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		//добавить подчиненных менеджеров, если они есть
		//подчиненные
		$_usg=new UsersSGroup;
		
		$_usg->GetSubordinates($result['id'], $podd);
		
		/* if(count($podd)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		 	$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$podd));	
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		 
			$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		}else $decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		 */
	}
	
	
		//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	//print_r($limited_user);
	
	
	
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	//вид кп
	
	if(isset($_GET['price_kind_id'.$prefix])&&(abs((int)$_GET['price_kind_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	
	//группа, подгруппа, пр-ль
	if(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('b.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	if(isset($_GET['producer_id'.$prefix])&&(abs((int)$_GET['producer_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('producer_id',$_GET['producer_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('eq.producer_id',SecStr($_GET['producer_id'.$prefix]), SqlEntry::E));		 
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where producer_id='.abs((int)$_GET['producer_id'.$prefix]).' and parent_id=0)', SqlEntry::IN_SQL));
	} 
	
	if(isset($_GET['two_group_id'.$prefix])&&(abs((int)$_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
		
		//$decorator->AddEntry(new SqlEntry('eq.group_id',SecStr($_GET['two_group_id'.$prefix]), SqlEntry::E));	
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where group_id='.abs((int)$_GET['two_group_id'.$prefix]).' and parent_id=0)', SqlEntry::IN_SQL));	 
		
	}elseif(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		 
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			 
			//$decorator->AddEntry(new SqlEntry('eq.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));
			$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where group_id in('.implode(', ',$arg).') and parent_id=0)', SqlEntry::IN_SQL));		
		}
	}
	
	//наименование оборудования
	
	if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
		 
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $arr=explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
			 $arr1=array();
			 foreach($arr as $k=>$v) if(strlen(trim($v))>0)  $arr1[$k]= ' name like "%'.$v.'%" ';
			 
			
			 $decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where ('.implode(' or ',$arr1).') and parent_id=0)', SqlEntry::IN_SQL));
			
			 $decorator->AddEntry(new UriEntry('eq_name',iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
		}else{
			$arr=explode(';',SecStr(($_GET['eq_name'.$prefix])));
			 $arr1=array();
			 foreach($arr as $k=>$v) if(strlen(trim($v))>0) $arr1[$k]= ' name like "%'.$v.'%" ';
			  
			 $decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where ('.implode(' or ',$arr1).') and parent_id=0)', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));	
		}
	}
	
	
	//в компанию
	 
	//фильтр по контрагенту
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		 
		$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));	
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
	}*/
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier_name'.$prefix]);
		
		
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
		
		
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.holding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss  where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_suppliers).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предприятия субхолдингов заданного предприятия
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss    /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where  ss.is_active=1  and ss.holding_id in(  '.implode(', ',$_suppliers).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг = заданному
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
		}
	} 
	
	//менеджер
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('u.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('manager_name',iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('u.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr($_GET['manager_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
			
		}
	}
	 
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('eq_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('eq_name',SqlOrdEntry::ASC));
		break;
		
		

		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$filetext=$an->ShowData($result['org_id'], 
	 DateFromdmY($pdate1),
	  DateFromdmY($pdate2),
	  $prefix,
	  'an_kp/an_kp'.$print_add.'.html',
	  $decorator,
	  'an_kp.php',
	  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)),
	  $au->user_rights->CheckAccess('w',761),
	  $result,
	  $au->user_rights->CheckAccess('w',701),
	   $au->user_rights->CheckAccess('w',763) 
	  );
	
	 
	
	$sm->assign('log',$filetext);
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		$log->PutEntry($result['id'],'открыл Отчет Коммерческие предложения',NULL,760,NULL, NULL);	
	}
	
		
	
	 
	$sm->assign('has_kp',$au->user_rights->CheckAccess('w',760));	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
//*********************************************************************************************

//вкладка отчет Рентабельность КП
	
	
	$an=new AnKpRent;
	$prefix='_2';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	
	

	
	
	//фильтры по доступу сотрудника
	//отбор по тому, кто создал КП
	$podd=array();
	if(!$au->user_rights->CheckAccess('w',763)){
		//1. свои КП
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$an->view_rules->GetManagers($result)));	
		
		
		
		//3. КП, подпадающие под правила из kp_rights
		//echo $log->view_rules->GetListSql($result);
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position where position_id in('.$an->view_rules->GetListSql($result).')', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		//добавить подчиненных менеджеров, если они есть
		//подчиненные
		$_usg=new UsersSGroup;
		
		$_usg->GetSubordinates($result['id'], $podd);
		
		 
		/*if(count($podd)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		 	$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$podd));	
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		 
			$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		}else $decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		 */
	}
	
		//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	//print_r($limited_user);
	
	
	
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	//вид кп
	
	if(isset($_GET['price_kind_id'.$prefix])&&(abs((int)$_GET['price_kind_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	
	//группа, подгруппа, пр-ль
	if(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('b.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	if(isset($_GET['producer_id'.$prefix])&&(abs((int)$_GET['producer_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('producer_id',$_GET['producer_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('eq.producer_id',SecStr($_GET['producer_id'.$prefix]), SqlEntry::E));		 
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where producer_id='.abs((int)$_GET['producer_id'.$prefix]).' and parent_id=0)', SqlEntry::IN_SQL));
	} 
	
	if(isset($_GET['two_group_id'.$prefix])&&(abs((int)$_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
		
		//$decorator->AddEntry(new SqlEntry('eq.group_id',SecStr($_GET['two_group_id'.$prefix]), SqlEntry::E));	
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where group_id='.abs((int)$_GET['two_group_id'.$prefix]).' and parent_id=0)', SqlEntry::IN_SQL));	 
		
	}elseif(isset($_GET['group_id'.$prefix])&&(abs((int)$_GET['group_id'.$prefix])>0)){
		 
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			 
			//$decorator->AddEntry(new SqlEntry('eq.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));
			$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where group_id in('.implode(', ',$arg).') and parent_id=0)', SqlEntry::IN_SQL));		
		}
	}
	
	//наименование оборудования
	
	if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
		 
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $arr=explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
			 $arr1=array();
			 foreach($arr as $k=>$v)   if(strlen(trim($v))>0)  $arr1[$k]= ' name like "%'.$v.'%" ';
			 
			
			 $decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where ('.implode(' or ',$arr1).') and parent_id=0)', SqlEntry::IN_SQL));
			
			 $decorator->AddEntry(new UriEntry('eq_name',iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
		}else{
			$arr=explode(';',SecStr(($_GET['eq_name'.$prefix])));
			 
			 $arr1=array();
			 foreach($arr as $k=>$v) if(strlen(trim($v))>0)  $arr1[$k]= ' name like "%'.$v.'%" ';
			  
			 $decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where ('.implode(' or ',$arr1).') and parent_id=0)', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));	
		}
	}
	
	
	//в компанию
	 
	//фильтр по контрагенту
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		//$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));	
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
	}*/
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier_name'.$prefix]);
		
		
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
		
		
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.holding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss  where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_suppliers).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предприятия субхолдингов заданного предприятия
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss    /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where  ss.is_active=1  and ss.holding_id in(  '.implode(', ',$_suppliers).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг = заданному
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
		}
	} 
	
	
	//менеджер
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $decorator->AddEntry(new SqlEntry('u.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('manager_name',iconv("utf-8","windows-1251",$_GET['manager_name'.$prefix])));
		}else{
		  $decorator->AddEntry(new SqlEntry('u.name_s',NULL, SqlEntry::E_SET, NULL, explode(';',SecStr($_GET['manager_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
			
		}
	}
	
	if(isset($_GET['extended_form'.$prefix])&&($_GET['extended_form'.$prefix]==1)){
		$decorator->AddEntry(new UriEntry('extended_form',1));
	}else $decorator->AddEntry(new UriEntry('extended_form',0));
	
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('eq_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('eq_name',SqlOrdEntry::ASC));
		break;
		
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('profit_value',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('profit_value',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('profit_percent',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('profit_percent',SqlOrdEntry::ASC));
		break;
		

		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$filetext=$an->ShowData($result['org_id'], 
	 DateFromdmY($pdate1),
	  DateFromdmY($pdate2),
	  $prefix,
	  'an_kp/an_kp_rent'.$print_add.'.html',
	  $decorator,
	  'an_kp.php',
	  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),
	  $au->user_rights->CheckAccess('w',765), 
	  $result, 
	  $au->user_rights->CheckAccess('w',701),
	  $au->user_rights->CheckAccess('w',824),
	   $au->user_rights->CheckAccess('w',763) 
	  );
	
	 
	
	 
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		$log->PutEntry($result['id'],'открыл Отчет Рентабельность коммерческих предложений',NULL,764,NULL, NULL);	
	}
	

	//$filetext='Раздел находится в разработке.';
	
	$sm->assign('log2',$filetext);	
	$sm->assign('has_rent',$au->user_rights->CheckAccess('w',764));	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_kp/an_kp_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>
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




require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');
 

require_once('classes/tender.class.php');

require_once('classes/tender_monitor.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","Тендеры");

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



if($result!==NULL){
$smarty = new SmartyAdm;

$_menu_id=72;
	
	  include('inc/menu.php');
 
  
 
 
 	if(!$au->user_rights->CheckAccess('w',929)){
		$content='<h1>GYDEX.В работе!</h1>';
		
	}else{
  
	    $sm1 = new SmartyAdm;
		
		 
		$_plans=new Tender_Group; 
		$_plans->SetAuthResult($result);
		 

/***************************************************************************************************/
 
		$prefix='';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',934)){
			//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableTenderIds($result['id'])));	
		}
	 	
		//var_dump($_plans->GetAvailableTenderIds($result['id']));
		
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
		
		
		if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_GET['eq_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
		} 
		
		
		if(isset($_GET['kind_name'.$prefix])&&(strlen($_GET['kind_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.kind_id',abs((int)$_GET['kind_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('kind_name',$_GET['kind_name'.$prefix]));
		} 
		
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
	 	
		  
		
	
		 
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate_finish',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate2',''));
		} 
		
	 	
		 //дата  размещения
		 
		 if(!isset($_GET['pdate_placing1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate_placing1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate_placing1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate_placing2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate_placing2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate_placing2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate_placing1'.$prefix])&&isset($_GET['pdate_placing2'.$prefix])&&($_GET['pdate_placing2'.$prefix]!="")&&($_GET['pdate_placing2'.$prefix]!="-")&&($_GET['pdate_placing1'.$prefix]!="")&&($_GET['pdate_placing1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate_placing1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate_placing2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate_placing',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate_placing1',''));
				$decorator->AddEntry(new UriEntry('pdate_placing2',''));
		} 
		  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tender_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tender_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'tender_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		} 
		
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
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
					
				//
				
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				if($au->user_rights->CheckAccess('w',936)) $decorator->AddEntry(new SqlOrdEntry('p.is_fulfiled',SqlOrdEntry::ASC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		
	 	$docs1=$_plans->ShowPos(
		

			'tender/table.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',930), //2
			  $au->user_rights->CheckAccess('w',931), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',972), //8
			  $au->user_rights->CheckAccess('w',931),  //9
			  $au->user_rights->CheckAccess('w',931), //10
			  $au->user_rights->CheckAccess('w',962), //11
			  $au->user_rights->CheckAccess('w',931), //12
			  $au->user_rights->CheckAccess('w',931), //13
			  $au->user_rights->CheckAccess('w',935), //14
			  $au->user_rights->CheckAccess('w',936), //15
			 
	 
			  $au->user_rights->CheckAccess('w',937), //16
			  $au->user_rights->CheckAccess('w',938), //17
			  $au->user_rights->CheckAccess('w',1116) //18
			
			 );


		
		$sm1->assign('log1', $docs1);
		  



/*****************************************************************************************************/
//вкладка Мониторинг

/*****************************************************************************************************/
//вкладка Типы оборудования
		$prefix=1;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tenderm1_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tenderm1_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'tenderm1_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		 if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.eq_type_id',abs((int)$_GET['eq_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
		} 
		
		
		if(isset($_GET['crea'.$prefix])&&(strlen($_GET['crea'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('cr.name_s',SecStr($_GET['crea'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('crea',$_GET['crea'.$prefix]));
		}
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			 
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
				
			break;	
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
				
			break;
			
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
				
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
				
			break;
			
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
				
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
				
			break;
			
			
		 
			
			default:
					
				//
				
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
			 
				$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		$_list=new TenderMonitor_Resolver($prefix);
		$_list->SetAuthResult($result);
		 
		 
		
		$docs2=$_list->group_instance->ShowPos(  
			  'tender_monitor/table1.html',  //0
			  $decorator, //1
			  $au->user_rights->CheckAccess('w',993), //2
			  
			  $from, //3
			  $to_page, //4
			  true, //5
			  false,  //6
			  $au->user_rights->CheckAccess('w',995), //7
			  $au->user_rights->CheckAccess('w',997),  //8
			  $au->user_rights->CheckAccess('w',999),  //9
			  $au->user_rights->CheckAccess('w',939)  //10
			  
			 );
	
		$sm1->assign('log2', $docs2);

/*****************************************************************************************************/
//вкладка Контрагенты
		
		$prefix=2;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tenderm2_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tenderm2_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'tenderm2_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		 
		
		if(isset($_GET['crea'.$prefix])&&(strlen($_GET['crea'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('cr.name_s',SecStr($_GET['crea'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('crea',$_GET['crea'.$prefix]));
		}
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('sup.full_name',SecStr($_GET['supplier'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('supplier',$_GET['supplier'.$prefix]));
		}
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
				
			break;	
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
				
			break;
			 
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
				
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
				
			break;
			
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
				
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
				
			break;
			
			
		 
			
			default:
					
				//
				
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
			 
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
		//ограничения на просмотр контрагентов
		$limited_supplier=NULL;
	
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('sup.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		
		$_list=new TenderMonitor_Resolver($prefix);
		$_list->SetAuthResult($result);
		
		$docs3=$_list->group_instance->ShowPos(  
			  'tender_monitor/table2.html',  //0
			  $decorator, //1
			  $au->user_rights->CheckAccess('w',992), //2
			  
			  $from, //3
			  $to_page, //4
			  true, //5
			  false,  //6
			  $au->user_rights->CheckAccess('w',994), //7
			  $au->user_rights->CheckAccess('w',996),  //8
			  $au->user_rights->CheckAccess('w',998), //9
			  $au->user_rights->CheckAccess('w',87)  //10
			  
			 );
		
///


		
		$sm1->assign('log3', $docs3);

		$sm1->assign('can_monitor', $au->user_rights->CheckAccess('w',991));




		  
		  
		  
	
		
		
		$content=$sm1->fetch('tender/tenders.html'); 
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'открыл раздел Тендеры',NULL,929, NULL);
	 

	}


	  $smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
 

 }
 
$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>
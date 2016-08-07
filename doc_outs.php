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
 

require_once('classes/doc_out.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","Исходящие документы");

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


$_menu_id=80;	
	  include('inc/menu.php');
 
 
 
 
 	if(!$au->user_rights->CheckAccess('w',1061)){
		$content='<p>У Вас нет доступа в этот раздел.</p><p>Просьба обратиться к администраторам программы.</p>';
		
	}else{
  		$sm1=new SmartyAdm;
	 
		 
		
		 

/***************************************************************************************************/
//исх. письма
		
		$_plans=new DocOut_Group;
		$_plans->SetAuthResult($result);
		
		$prefix=1;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=30;
		  
		$decorator=new DBDecorator;
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',1068)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
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
				
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate2',''));
		}
		 
		 
		 
		if(!isset($_GET['reg_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['reg_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['reg_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['reg_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['reg_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['reg_pdate2'.$prefix]);
		}
		
		if(isset($_GET['reg_pdate1'.$prefix])&&isset($_GET['reg_pdate2'.$prefix])&&($_GET['reg_pdate2'.$prefix]!="")&&($_GET['reg_pdate2'.$prefix]!="-")&&($_GET['reg_pdate1'.$prefix]!="")&&($_GET['reg_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('reg_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('reg_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.reg_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('reg_pdate1',''));
				$decorator->AddEntry(new UriEntry('reg_pdate2',''));
		} 
		
		
		if(!isset($_GET['send_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['send_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['send_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['send_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['send_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['send_pdate2'.$prefix]);
		}
		
		if(isset($_GET['send_pdate1'.$prefix])&&isset($_GET['send_pdate2'.$prefix])&&($_GET['send_pdate2'.$prefix]!="")&&($_GET['send_pdate2'.$prefix]!="-")&&($_GET['send_pdate1'.$prefix]!="")&&($_GET['send_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('send_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('send_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.send_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('send_pdate1',''));
				$decorator->AddEntry(new UriEntry('send_pdate2',''));
		} 
		
		
		if(!isset($_GET['received_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['received_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['received_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['received_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['received_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['received_pdate2'.$prefix]);
		}
		
		if(isset($_GET['received_pdate1'.$prefix])&&isset($_GET['received_pdate2'.$prefix])&&($_GET['received_pdate2'.$prefix]!="")&&($_GET['received_pdate2'.$prefix]!="-")&&($_GET['received_pdate1'.$prefix]!="")&&($_GET['received_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('received_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('received_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.received_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('received_pdate1',''));
				$decorator->AddEntry(new UriEntry('received_pdate2',''));
		} 
	  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
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
		
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		
		
		if(isset($_GET['reg_no'.$prefix])&&(strlen($_GET['reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.reg_no',SecStr($_GET['reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('reg_no',$_GET['reg_no'.$prefix]));
		}
		
		//фильтр по lidu
		if(isset($_GET['lead'.$prefix])&&(strlen($_GET['lead'.$prefix])>0)){
			$names=explode(';', trim($_GET['lead'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('ld.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('lead',$_GET['lead'.$prefix]));
		}
		
		
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
		if(isset($_GET['send_user'.$prefix])&&(strlen($_GET['send_user'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('send.name_s',SecStr($_GET['send_user'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('send_user',$_GET['send_user'.$prefix]));
		}
		
		if(isset($_GET['received_who'.$prefix])&&(strlen($_GET['received_who'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_who',SecStr($_GET['received_who'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_who',$_GET['received_who'.$prefix]));
		}
		
		if(isset($_GET['received_reg_no'.$prefix])&&(strlen($_GET['received_reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_reg_no',SecStr($_GET['received_reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_reg_no',$_GET['received_reg_no'.$prefix]));
		}
		
		if(isset($_GET['description'.$prefix])&&(strlen($_GET['description'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['description'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('description',$_GET['description'.$prefix]));
		}
		
		
		//блок поиска по содержимому (включая файлы)
		//include('inc/shedule_contents_inc.php'); 
	 
		
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
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
			break;
			
			 
			 case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::DESC));
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::ASC));
			break;
			
			case 14:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::DESC));
			break;	
			case 15:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::ASC));
			break;
			
			
			case 16:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::DESC));
			break;	
			case 17:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::ASC));
			break;
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::DESC));
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::ASC));
			break;
			
			case 20:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::DESC));
			break;	
			case 21:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::ASC));
			break;
			
			case 22:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::DESC));
			break;	
			case 23:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::ASC));
			break;
			
			case 24:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::DESC));
			break;	
			case 25:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::ASC));
			break;
		 
			 
			
			default:
					
				 
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				  
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
	  
		
		 $docs1=$_plans->ShowPos(
		 
			'doc_out/table1.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',1064), //2
			   $au->user_rights->CheckAccess('w',1065), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1070), //8
			  $au->user_rights->CheckAccess('w',1071),  //9
			  $au->user_rights->CheckAccess('w',1065), //10
			  $au->user_rights->CheckAccess('w',1072), //11
			  $au->user_rights->CheckAccess('w',1065), //12
			  $prefix //13
	 
			
			 );
 


/***************************************************************************************************/
//информац. письма

 
		
		$_plans=new DocOut_InfGroup;
		$_plans->SetAuthResult($result);
		
		$prefix=2;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=30;
		  
		$decorator=new DBDecorator;
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',1068)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
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
				
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate2',''));
		}
		 
		 
		 
		if(!isset($_GET['reg_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['reg_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['reg_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['reg_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['reg_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['reg_pdate2'.$prefix]);
		}
		
		if(isset($_GET['reg_pdate1'.$prefix])&&isset($_GET['reg_pdate2'.$prefix])&&($_GET['reg_pdate2'.$prefix]!="")&&($_GET['reg_pdate2'.$prefix]!="-")&&($_GET['reg_pdate1'.$prefix]!="")&&($_GET['reg_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('reg_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('reg_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.reg_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('reg_pdate1',''));
				$decorator->AddEntry(new UriEntry('reg_pdate2',''));
		} 
		
		
		if(!isset($_GET['send_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['send_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['send_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['send_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['send_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['send_pdate2'.$prefix]);
		}
		
		if(isset($_GET['send_pdate1'.$prefix])&&isset($_GET['send_pdate2'.$prefix])&&($_GET['send_pdate2'.$prefix]!="")&&($_GET['send_pdate2'.$prefix]!="-")&&($_GET['send_pdate1'.$prefix]!="")&&($_GET['send_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('send_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('send_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.send_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('send_pdate1',''));
				$decorator->AddEntry(new UriEntry('send_pdate2',''));
		} 
		
		
		if(!isset($_GET['received_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['received_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['received_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['received_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['received_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['received_pdate2'.$prefix]);
		}
		
		if(isset($_GET['received_pdate1'.$prefix])&&isset($_GET['received_pdate2'.$prefix])&&($_GET['received_pdate2'.$prefix]!="")&&($_GET['received_pdate2'.$prefix]!="-")&&($_GET['received_pdate1'.$prefix]!="")&&($_GET['received_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('received_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('received_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.received_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('received_pdate1',''));
				$decorator->AddEntry(new UriEntry('received_pdate2',''));
		} 
	  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
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
		
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		
		
		if(isset($_GET['reg_no'.$prefix])&&(strlen($_GET['reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.reg_no',SecStr($_GET['reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('reg_no',$_GET['reg_no'.$prefix]));
		}
		
		//фильтр по lidu
		if(isset($_GET['lead'.$prefix])&&(strlen($_GET['lead'.$prefix])>0)){
			$names=explode(';', trim($_GET['lead'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('ld.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('lead',$_GET['lead'.$prefix]));
		}
		
		
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
		if(isset($_GET['send_user'.$prefix])&&(strlen($_GET['send_user'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('send.name_s',SecStr($_GET['send_user'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('send_user',$_GET['send_user'.$prefix]));
		}
		
		if(isset($_GET['received_who'.$prefix])&&(strlen($_GET['received_who'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_who',SecStr($_GET['received_who'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_who',$_GET['received_who'.$prefix]));
		}
		
		if(isset($_GET['received_reg_no'.$prefix])&&(strlen($_GET['received_reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_reg_no',SecStr($_GET['received_reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_reg_no',$_GET['received_reg_no'.$prefix]));
		}
		
		if(isset($_GET['description'.$prefix])&&(strlen($_GET['description'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['description'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('description',$_GET['description'.$prefix]));
		}
		
		
		//блок поиска по содержимому (включая файлы)
		//include('inc/shedule_contents_inc.php'); 
	 
		
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
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
			break;
			
			 
			 case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::DESC));
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::ASC));
			break;
			
			case 14:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::DESC));
			break;	
			case 15:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::ASC));
			break;
			
			
			case 16:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::DESC));
			break;	
			case 17:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::ASC));
			break;
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::DESC));
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::ASC));
			break;
			
			case 20:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::DESC));
			break;	
			case 21:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::ASC));
			break;
			
			case 22:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::DESC));
			break;	
			case 23:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::ASC));
			break;
			
			case 24:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::DESC));
			break;	
			case 25:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::ASC));
			break;
		 
			 
			
			default:
					
				 
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				  
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
	  
		
		 $docs2=$_plans->ShowPos(
		 
			'doc_out/table2.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',1064), //2
			   $au->user_rights->CheckAccess('w',1065), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1070), //8
			  $au->user_rights->CheckAccess('w',1071),  //9
			  $au->user_rights->CheckAccess('w',1065), //10
			  $au->user_rights->CheckAccess('w',1072), //11
			  $au->user_rights->CheckAccess('w',1065), //12
			  $prefix //13
	 
			
			 );
 

 
/***************************************************************************************************/
//сопр. письма
	 	
		$_plans=new DocOut_SoprGroup;
		$_plans->SetAuthResult($result);
		
		$prefix=3;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=30;
		  
		$decorator=new DBDecorator;
		
		//контроль видимости
		if(!$au->user_rights->CheckAccess('w',1068)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
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
				
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate2',''));
		}
		 
		 
		 
		if(!isset($_GET['reg_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['reg_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['reg_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['reg_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['reg_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['reg_pdate2'.$prefix]);
		}
		
		if(isset($_GET['reg_pdate1'.$prefix])&&isset($_GET['reg_pdate2'.$prefix])&&($_GET['reg_pdate2'.$prefix]!="")&&($_GET['reg_pdate2'.$prefix]!="-")&&($_GET['reg_pdate1'.$prefix]!="")&&($_GET['reg_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('reg_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('reg_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.reg_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('reg_pdate1',''));
				$decorator->AddEntry(new UriEntry('reg_pdate2',''));
		} 
		
		
		if(!isset($_GET['send_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['send_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['send_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['send_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['send_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['send_pdate2'.$prefix]);
		}
		
		if(isset($_GET['send_pdate1'.$prefix])&&isset($_GET['send_pdate2'.$prefix])&&($_GET['send_pdate2'.$prefix]!="")&&($_GET['send_pdate2'.$prefix]!="-")&&($_GET['send_pdate1'.$prefix]!="")&&($_GET['send_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('send_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('send_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.send_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('send_pdate1',''));
				$decorator->AddEntry(new UriEntry('send_pdate2',''));
		} 
		
		
		if(!isset($_GET['received_pdate1'.$prefix])){
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
		}else{
			 $given_pdate1 = $_GET['received_pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['received_pdate1'.$prefix]);
		}
		
		if(!isset($_GET['received_pdate2'.$prefix])){
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
		}else{
			 $given_pdate2 = $_GET['received_pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['received_pdate2'.$prefix]);
		}
		
		if(isset($_GET['received_pdate1'.$prefix])&&isset($_GET['received_pdate2'.$prefix])&&($_GET['received_pdate2'.$prefix]!="")&&($_GET['received_pdate2'.$prefix]!="-")&&($_GET['received_pdate1'.$prefix]!="")&&($_GET['received_pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('received_pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('received_pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.received_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)+24*60*60-1));
		}else{
					$decorator->AddEntry(new UriEntry('received_pdate1',''));
				$decorator->AddEntry(new UriEntry('received_pdate2',''));
		} 
	  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
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
		
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		
		
		if(isset($_GET['reg_no'.$prefix])&&(strlen($_GET['reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.reg_no',SecStr($_GET['reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('reg_no',$_GET['reg_no'.$prefix]));
		}
		
		//фильтр по lidu
		if(isset($_GET['lead'.$prefix])&&(strlen($_GET['lead'.$prefix])>0)){
			$names=explode(';', trim($_GET['lead'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('ld.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('lead',$_GET['lead'.$prefix]));
		}
		
		
		
		//фильтр по контрагенту
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
		if(isset($_GET['send_user'.$prefix])&&(strlen($_GET['send_user'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('send.name_s',SecStr($_GET['send_user'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('send_user',$_GET['send_user'.$prefix]));
		}
		
		if(isset($_GET['received_who'.$prefix])&&(strlen($_GET['received_who'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_who',SecStr($_GET['received_who'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_who',$_GET['received_who'.$prefix]));
		}
		
		if(isset($_GET['received_reg_no'.$prefix])&&(strlen($_GET['received_reg_no'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.received_reg_no',SecStr($_GET['received_reg_no'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('received_reg_no',$_GET['received_reg_no'.$prefix]));
		}
		
		if(isset($_GET['description'.$prefix])&&(strlen($_GET['description'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['description'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('description',$_GET['description'.$prefix]));
		}
		
		
		//блок поиска по содержимому (включая файлы)
		//include('inc/shedule_contents_inc.php'); 
	 
		
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
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
				
			break;
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
			break;
			
			 
			 case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::DESC));
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_pdate',SqlOrdEntry::ASC));
			break;
			
			case 14:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::DESC));
			break;	
			case 15:
				$decorator->AddEntry(new SqlOrdEntry('p.reg_no',SqlOrdEntry::ASC));
			break;
			
			
			case 16:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::DESC));
			break;	
			case 17:
				$decorator->AddEntry(new SqlOrdEntry('ld.code',SqlOrdEntry::ASC));
			break;
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::DESC));
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('p.send_pdate',SqlOrdEntry::ASC));
			break;
			
			case 20:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::DESC));
			break;	
			case 21:
				$decorator->AddEntry(new SqlOrdEntry('p.received_pdate',SqlOrdEntry::ASC));
			break;
			
			case 22:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::DESC));
			break;	
			case 23:
				$decorator->AddEntry(new SqlOrdEntry('p.received_reg_no',SqlOrdEntry::ASC));
			break;
			
			case 24:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::DESC));
			break;	
			case 25:
				$decorator->AddEntry(new SqlOrdEntry('p.description',SqlOrdEntry::ASC));
			break;
		 
			 
			
			default:
					
				 
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				  
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
	  
		
		 $docs3=$_plans->ShowPos(
		 
			'doc_out/table3.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',1064), //2
			   $au->user_rights->CheckAccess('w',1065), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1070), //8
			  $au->user_rights->CheckAccess('w',1071),  //9
			  $au->user_rights->CheckAccess('w',1065), //10
			  $au->user_rights->CheckAccess('w',1072), //11
			  $au->user_rights->CheckAccess('w',1065), //12
			  $prefix //13
	 
			
			 );
 


 
 




 
		
		
		
		$sm1->assign('log1', $docs1);
		$sm1->assign('log2', $docs2);
		$sm1->assign('log3', $docs3);
	 
	 
		
		$content=$sm1->fetch('doc_out/doc_out.html'); 
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'открыл раздел Исходящие документы',NULL,1061, NULL);
	 

	}

$smarty->assign('fast_menu', $menu_arr_fast);
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
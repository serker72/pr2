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




require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');
 

require_once('classes/lead.class.php');

require_once('classes/tz.class.php');

require_once('classes/kp_in.class.php');
require_once('classes/bdr.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","����");

$au=new AuthUser();
$result=$au->Auth();

//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

 

if($result!==NULL){
$smarty = new SmartyAdm;

$_menu_id=75;
	
	  include('inc/menu.php');
 
 
 
 
 	if(!$au->user_rights->CheckAccess('w',948)&&!$au->user_rights->CheckAccess('w',1005)&&!$au->user_rights->CheckAccess('w',1017)){
		$content='<h1>GYDEX.� ������!</h1>';
		
	}else{
  
	    $sm1 = new SmartyAdm;
		
		 
		$_plans=new Lead_Group;
		$_plans->SetAuthResult($result);
		 

/***************************************************************************************************/
//������� ���� 		
		
		$prefix='_leads';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		//�������� ���������
		if(!$au->user_rights->CheckAccess('w',953)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableLeadIds($result['id'])));	
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
		
		if(isset($_GET['prod_name'.$prefix])&&(strlen($_GET['prod_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('p.producer_id',abs((int)$_GET['prod_name'.$prefix]), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('prod_name',$_GET['prod_name'.$prefix]));
		} 
		
		
		if(isset($_GET['state_name'.$prefix])&&(strlen($_GET['state_name'.$prefix])>0)){
			//$decorator->AddEntry(new SqlEntry('p.producer_id',abs((int)$_GET['prod_name'.$prefix]), SqlEntry::E));
			$state_name=abs((int)$_GET['state_name'.$prefix]);
			if($state_name==0){
				$decorator->AddEntry(new SqlEntry('p.probability',0, SqlEntry::BETWEEN,29.99));
			}elseif($state_name==1){
				$decorator->AddEntry(new SqlEntry('p.probability',30, SqlEntry::BETWEEN,69.99));
			}elseif($state_name==2){
				$decorator->AddEntry(new SqlEntry('p.probability',70, SqlEntry::GE));
			}
			
			$decorator->AddEntry(new UriEntry('state_name',$_GET['state_name'.$prefix]));
		} 
		
		
		//������ �� �����������
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
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 //����  ����������
		 
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
			$decorator->AddEntry(new SqlEntry('tender.pdate_placing',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate_placing1',''));
				$decorator->AddEntry(new UriEntry('pdate_placing2',''));
		} 
		  
		
		//���� �������� �������
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'lead_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'lead_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'lead_'.$prefix.'status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
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
			
			
			/*case 14:
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
			*/
			
			case 18:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::DESC));
				 
			break;	
			case 19:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_finish',SqlOrdEntry::ASC));
			break;
			
			case 20:
				$decorator->AddEntry(new SqlOrdEntry('tender.pdate_placing',SqlOrdEntry::DESC));
				 
			break;	
			case 21:
				$decorator->AddEntry(new SqlOrdEntry('tender.pdate_placing',SqlOrdEntry::ASC));
			break;
			
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				if($au->user_rights->CheckAccess('w',957)) $decorator->AddEntry(new SqlOrdEntry('p.is_fulfiled',SqlOrdEntry::ASC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$docs1=$_plans->ShowPos(
		

			'lead/table.html',  //0
			 $decorator, //1
			  $au->user_rights->CheckAccess('w',949), //2
			  $au->user_rights->CheckAccess('w',950), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',950), //8
			  $au->user_rights->CheckAccess('w',961),  //9
			  $au->user_rights->CheckAccess('w',950), //10
			  $au->user_rights->CheckAccess('w',965), //11
			  $au->user_rights->CheckAccess('w',950), //12
			  $au->user_rights->CheckAccess('w',950), //13
			  $au->user_rights->CheckAccess('w',956), //14
			  $au->user_rights->CheckAccess('w',957), //15
			 
	 
			  $au->user_rights->CheckAccess('w',958), //16
			  $au->user_rights->CheckAccess('w',959), //17
			  $prefix //18
			
			 );

		
		$sm1->assign('log1', $docs1);
		  


/***************************************************************************************************/
//������� �� 
		$prefix='_tzs';
		
		$_plans=new TZ_Group;
		$_plans->SetAuthResult($result);
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		//�������� ���������
		if(!$au->user_rights->CheckAccess('w',1008)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableTZIds($result['id'])));	
		}
	 	
		  
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//������ �� �����������
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
	 	 //������ �� ����������
		if(isset($_GET['suppliertz_name'.$prefix])&&(strlen($_GET['suppliertz_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['suppliertz_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup1.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('suppliertz_name',$_GET['suppliertz_name'.$prefix]));
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
			$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//���� �������� �������
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tz_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'tz_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'tz_'.$prefix.'status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
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
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
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
			
			
			/*case 14:
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
			*/
			
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$docs11=$_plans->ShowPos(
				
			'tz/table.html',  //0
			 $decorator, //1
			  false, //2
			  $au->user_rights->CheckAccess('w',1009), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1012), //8
			  $au->user_rights->CheckAccess('w',1013),  //9
			  $au->user_rights->CheckAccess('w',1010), //10
			  $au->user_rights->CheckAccess('w',1011), //11
			  $au->user_rights->CheckAccess('w',1014), //12
			  
			  
			$prefix //13
			 );


 




	


 
		
		
		
		
		$sm1->assign('log11', $docs11);
		  
		$sm1->assign('has_tzs', $au->user_rights->CheckAccess('w',1005) );
		  
		  
		
		
		
/*****************************************************************************************************/
//��������� ��		
		
		
		 
		 $_tzs=new KpIn_Out_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		
		
 
		$prefix='_kp_outs';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
	
	
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('kind_id',1));
		 
		 //�������� ���������
		if(!$au->user_rights->CheckAccess('w',1020)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_tzs->GetAvailableKpInIds($result['id'])));	
		}
	 	
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//������ �� �����������
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		
	 	 //������ �� ����������
		if(isset($_GET['suppliertz_name'.$prefix])&&(strlen($_GET['suppliertz_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['suppliertz_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup1.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('suppliertz_name',$_GET['suppliertz_name'.$prefix]));
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
			$decorator->AddEntry(new SqlEntry('p.given_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//���� �������� �������
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'kp_in_'.$prefix.'status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
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
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
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
			
			
			 
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$docs2=$_tzs->ShowPos(
				
			'kp_in/table.html',  //0
			 $decorator, //1
			  ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id']))))&&($editing_user['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1021), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			  
			  
				$prefix, //13
			  1
			 );
		
		
		
		$sm1->assign('log2', $docs2);
		

/*****************************************************************************************************/
//�������� ��		
		
		 $_tzs=new KpIn_Group;
		 
		 
		$_tzs->SetAuthResult($result);
		
		
 
		$prefix='_kp_ins';
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
	
	
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('kind_id',0));
		 
		 //�������� ���������
		if(!$au->user_rights->CheckAccess('w',1020)){
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_tzs->GetAvailableKpInIds($result['id'])));	
		}
	 	
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//������ �� �����������
		if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['supplier_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
		
		//������ �� ����������
		if(isset($_GET['suppliertz_name'.$prefix])&&(strlen($_GET['suppliertz_name'.$prefix])>0)){
			$names=explode(';', trim($_GET['suppliertz_name'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('sup1.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('suppliertz_name',$_GET['suppliertz_name'.$prefix]));
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
			$decorator->AddEntry(new SqlEntry('p.given_pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//���� �������� �������
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'kp_in_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'kp_in_'.$prefix.'status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
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
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
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
			
			
			 
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$docs3=$_tzs->ShowPos(
				
			'kp_in/table.html',  //0
			 $decorator, //1
			  ($au->user_rights->CheckAccess('w',1019)||($au->user_rights->CheckAccess('w',1018)&&(($result['id']==$editing_user['manager_id'])||($result['id']==$editing_user['created_id']))))&&($editing_user['is_confirmed']==1), //2
			  $au->user_rights->CheckAccess('w',1021), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1024), //8
			  $au->user_rights->CheckAccess('w',1025),  //9
			  $au->user_rights->CheckAccess('w',1022), //10
			  $au->user_rights->CheckAccess('w',1023), //11
			  $au->user_rights->CheckAccess('w',1026), //12
			  
			  
				$prefix, //13
			  0
			 );
		
		
		$sm1->assign('log3', $docs3);
		  
		  
		$sm1->assign('has_kps', $au->user_rights->CheckAccess('w',1017) );
		  
		  


/***************************************************************************************************/
//������� ��� 
		$prefix='_bdrs';
		
		$_plans=new BDR_Group;
		$_plans->SetAuthResult($result);
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		
		//�������� ���������
		if(!$au->user_rights->CheckAccess('w',1040)){
			$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableBDRIds($result['id'])));	
		}
	 	
		  
	 
		 
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('t.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		 
		
		//������ �� �����������
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
			$decorator->AddEntry(new SqlEntry('t.pdate',  DateFromdmY($given_pdate1), SqlEntry::BETWEEN, DateFromdmY($given_pdate2)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		} 
		
	 	
		 
		  
		
		//���� �������� �������
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bdr_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bdr_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'bdr_'.$prefix.'status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
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
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
			break;
		 	case 2:
				$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
			break; 
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('lead.code',SqlOrdEntry::ASC));
				
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('t.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('t.status_id',SqlOrdEntry::ASC));
				
			break;
			
		
		
			 
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				
				$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		 
		
		$docs4=$_plans->ShowPos(
				
			'bdr/table.html',  //0
			 $decorator, //1
			  false, //2
			  $au->user_rights->CheckAccess('w',1041), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1044), //8
			  $au->user_rights->CheckAccess('w',1045),  //9
			  $au->user_rights->CheckAccess('w',1042), //10
			  $au->user_rights->CheckAccess('w',1043), //11
			  $au->user_rights->CheckAccess('w',1050), //12
			   $au->user_rights->CheckAccess('w',1051), //13
			    $au->user_rights->CheckAccess('w',1046), //14
			  
			  
			$prefix //15
			 );


 




	


 
		
		
		
		
		$sm1->assign('log4', $docs4);
		  
		$sm1->assign('has_bdrs', $au->user_rights->CheckAccess('w',1038) );
		  
		  
		

		  
	
		
		
		$content=$sm1->fetch('lead/leads.html'); 
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'������ ������ ����',NULL,948, NULL);
	 

	}

$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
 

 }
 
$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>
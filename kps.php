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

require_once('classes/kpgroup.php');



require_once('classes/user_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'������ ������������ �����������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['bills_from'])){
		$from=abs((int)$_SESSION['bills_from']);
	}else $from=0;
	$_SESSION['bills_from']=$from;



if(!$au->user_rights->CheckAccess('w',695)&&!$au->user_rights->CheckAccess('w',712)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//������ ������� 
$log=new ActionLog;
$log->PutEntry($result['id'],'������ ������ ������������ �����������',NULL,695);



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3)));

$smarty->display('top.html');
unset($smarty);


	
$_menu_id=51;

	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	

	
	
/*********** KP ****************************************************************************/		
	
	//������� ���
	$log=new KpGroup;
	//������ ���������� �������
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	//����� �� ����, ��� � ������ ���������� ��
	$podd=array();
	if(!$au->user_rights->CheckAccess('w',763)){
		//1. ���� ��
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		
		
		
		
		//2. �� ����������� ���������� ������������ ������ (���� ��� ���-�� =������������ � ���� ����� ���������)
		/*$_pos=new UserPosItem;
		$pos=$_pos->Getitembyid($result['position_id']);
		if($pos['is_ruk_otd']){*/
		/*if(eregi('������������', $result['position_name'])){
			 
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.user_manager_id','select id from user where department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"', SqlEntry::IN_SQL));
		}*/
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$log->view_rules->GetManagers($result)));	
		
		
		
		//3. ��, ����������� ��� ������� �� kp_rights
		//echo $log->view_rules->GetListSql($result);
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position where position_id in('.$log->view_rules->GetListSql($result).')', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		//�������� ����������� ����������, ���� ��� ����
		//�����������
		/* $_usg=new UsersSGroup;
		
		$dec1=new DbDecorator;
		$dec1->AddEntry(new SqlEntry('u.manager_id',$result['id'], SqlEntry::E));
		$dec1->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
		
		$pod=$_usg->GetItemsByDecArr($dec1);
		//$sm1->assign('pod', $pod);
		$podd=array();
		foreach($pod as $k=>$v){
			$podd[]=$v['id'];
		}
		
		if(count($podd)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		 	$decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$podd));	
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		 
			$decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		}else $decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
		 */
		
		$_usg=new UsersSGroup;
		$_usg->GetSubordinates($result['id'], $podd);
		
		
		/*$dec1=new DbDecorator;
		$dec1->AddEntry(new SqlEntry('u.manager_id',$result['id'], SqlEntry::E));
		$dec1->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
		
		$pod=$_usg->GetItemsByDecArr($dec1);
		//$sm1->assign('pod', $pod);
		$podd=array();
		foreach($pod as $k=>$v){
			$podd[]=$v['id'];
		}
		
		$podd[]=$result['id'];*/
	}
	
	 
	
	//����������� �� ����������
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
		
	}
	//print_r($limited_user);
	
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	
 
	
	//���� �������� �������
	
	
	$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kp_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^kp_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kp_status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('statuses[]',$v));	
			  }
		  } 
		
	

	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	if(isset($_GET['user_manager_name'])&&(strlen($_GET['user_manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('umn.name_s',SecStr($_GET['user_manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('user_manager_name',$_GET['user_manager_name']));
	}
	
	if(isset($_GET['eq_name'])&&(strlen($_GET['eq_name'])>0)){
	 
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where name like "%'.SecStr($_GET['eq_name']).'%" and parent_id=0)', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select id from  catalog_position where code like "%'.SecStr($_GET['eq_name']).'%" and parent_id=0)', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name']));

	}
	
	if(isset($_GET['sp_name'])&&(strlen($_GET['sp_name'])>0)){
			$decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position  where position_id in(select t.id from  catalog_position as t inner join pl_producer as pp on t.producer_id=pp.id where pp.name like "%'.SecStr($_GET['sp_name']).'%" and t.parent_id=0)', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new UriEntry('sp_name',$_GET['sp_name']));
	}
	
	
	if(isset($_GET['price_kind_id'])&&(strlen($_GET['price_kind_id'])>0)&&(abs((int)$_GET['price_kind_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.price_kind_id',abs((int)$_GET['price_kind_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id']));
		//$sortmode=5;
	}
	
	if(isset($_GET['supply_pdate_id'])&&(strlen($_GET['supply_pdate_id'])>0)&&(abs((int)$_GET['supply_pdate_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.supply_pdate_id',abs((int)$_GET['supply_pdate_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('supply_pdate_id',$_GET['supply_pdate_id']));
		//$sortmode=5;
	}
	
	
	if(!isset($_GET['supplier_bill_pdate1'])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['supplier_bill_pdate1'];
		 $_given_pdate1= DateFromdmY($_GET['supplier_bill_pdate1']);
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate2'])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['supplier_bill_pdate2'];
		  $_given_pdate2= DateFromdmY($_GET['supplier_bill_pdate2']);
	}
	
	if(isset($_GET['supplier_bill_pdate1'])&&isset($_GET['supplier_bill_pdate2'])&&($_GET['supplier_bill_pdate2']!="")&&($_GET['supplier_bill_pdate2']!="-")&&($_GET['supplier_bill_pdate1']!="")&&($_GET['supplier_bill_pdate1']!="-")){
		
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
		
		$decorator->AddEntry(new SqlEntry('p.valid_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',''));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',''));
	}
	
	
	
	//���������� ����� ��������� ��� �������������� �������� ��� UriEntry
	
	
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
		/*case 6:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('eq.name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sp.name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sp.name',SqlOrdEntry::ASC));
		break;*/
		
		
	 
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	
	$llg=$log->ShowPos('kp/kps_list.html', //0
	  $decorator, //1
	  $from, //2
	  $to_page, //3
	  $au->user_rights->CheckAccess('w',696), //4
	  $au->user_rights->CheckAccess('w',701)||$au->user_rights->CheckAccess('w',712), //5
	  $au->user_rights->CheckAccess('w',713), //6
	  '', //7
	  $au->user_rights->CheckAccess('w',709), //8
	  $au->user_rights->CheckAccess('w',96), //9
	  true, //10
	  false, //11
	  $au->user_rights->CheckAccess('w',714), //12
	   NULL, //13
	  $au->user_rights->CheckAccess('w',711), //14
	  $au->user_rights->CheckAccess('w',843), //15
	  $au->user_rights->CheckAccess('w',762), //16
	   $au->user_rights->CheckAccess('w',712),  //17
	    $au->user_rights->CheckAccess('w',816),  //18
		$au->user_rights->CheckAccess('w',763),  //19
	  $au->user_rights->CheckAccess('w',824), //20
	  $au->user_rights->CheckAccess('w',842), //21
	$sortmode  //22	
	 
	);
	
	
	$sm->assign('log',$llg);
	$sm->assign('has_kps', $au->user_rights->CheckAccess('w',695));
	
	
	
	
	
	
	$content=$sm->fetch('kp/kps.html');
	
	
	

	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>
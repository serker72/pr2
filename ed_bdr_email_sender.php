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
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

 require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
 

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

 
require_once('classes/lead.class.php');
require_once('classes/tz.class.php');
require_once('classes/bdr.class.php');
require_once('classes/kp_in.class.php');



 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

 

require_once('classes/pl_positem.php');
require_once('classes/kp_supply_pdate_item.php');

require_once('classes/kpitem.php');

require_once('classes/currency/currency_solver.class.php');

require_once('classes/user_dep_item.php');
require_once('classes/user_main_dep_item.php');

require_once('classes/usercontactdataitem.php'); 
require_once('classes/phpmailer/class.phpmailer.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'БДР/БДДС');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new BDR_AbstractItem;

$_plan1=new BDR_Group;
$available_users=$_plan1->GetAvailableBDRIds($result['id']);

$_plan=new BDR_Group;


$_supplier=new SupplierItem;
 $log=new ActionLog;
 $_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

$action=1;

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;

$object_id=array();
switch($action){
	case 0:
	$object_id[]=1039;
	break;
	case 1:
	$object_id[]=1041;
	break;
	case 2:
	$object_id[]=1041;
	break;
	default:
	$object_id[]=1041;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 

	
  if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);


$print=1;

/*if(!isset($_GET['print_mode'])){
	if(!isset($_POST['print_mode'])){
		$print_mode=0;
	}else $print_mode=abs((int)$_POST['print_mode']); 
}else $print_mode=abs((int)$_GET['print_mode']);*/

$file_ids=explode(',', $_GET['file_ids']);

//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
} 

 
	


	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	
	if(!isset($_GET['version_id'])){
		if(!isset($_POST['version_id'])){
			$version_id=NULL;
		}else $version_id=abs((int)$_POST['version_id']);	
	}else $version_id=abs((int)$_GET['version_id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->getitembyid($id, $version_id); 
	
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$_tg=new BDR_Group;
	
	if(!$au->user_rights->CheckAccess('w',1040)){	
		$available_tenders=$_tg->GetAvailableBDRIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
	
	//найти родительский лид
	$_lead=new Lead_Item;
	$lead=$_lead->getitembyid($editing_user['lead_id']);
	
	
	$_kp_in=new KpIn_Item;
	$kp_in=$_kp_in->GetItemById($editing_user['kp_in_id']);
	
	
	$_tz=new TZ_Item;
	$tz=$_tz->GetItemById($editing_user['tz_id']);
		 
	$_kp_out=new KpOut_Item;
	$kp_out=$_kp_out->GetItemById($editing_user['kp_out_id']); 
 
 
 
  
$filenames_to_send=array();
 foreach($file_ids as $file_k=>$print_mode){

	if($print==0) $print_add='';
		else $print_add='_print';
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		 
	 
	 	$_wg=new BDR_WorkingGroup;
		 
		
		$working_time_unf=$_wg->CalcWorkingTime($id, 5, $zz, $times, $is_working );
		$editing_user['times_5']=$times;
		$editing_user['working_time_unf_5']=$working_time_unf;
		$editing_user['5_is_working']=$is_working;
		
		
		 
		$working_time_unf=$_wg->CalcWorkingTime($id, 6, $zz, $times,$is_working);
		$editing_user['times_6']=$times;
		$editing_user['working_time_unf_6']=$working_time_unf;
		$editing_user['6_is_working']=$is_working;
		
		$working_time_unf=$_wg->CalcTotalWorkingTime($id, $zz, $times,$is_working );
		$editing_user['times_total']=$times;
		$editing_user['working_time_unf_total']=$working_time_unf;
		$editing_user['total_is_working']=$is_working;
	 
		//echo $zz;
		
		
		
		//echo $editing_user['vid'];	 
			 
		$sm1->assign('lead', $lead);
		$sm1->assign('tz', $tz);
		$sm1->assign('kp_in', $kp_in);
		$sm1->assign('kp_out', $kp_out);
			 
		 
		
		
		
		$_res=new BDR_Resolver();
		
		
		
		
		//станок, срок поставки, валюта - из ВХ КП
		$_supply=new KpSupplyPdateItem;
		$_pl=new Tender_EqTypeItem; $_curr=new PlCurrItem; 
		$supply=$_supply->GetItemById($kp_in['supply_pdate_id']);
		$pl=$_pl->GetItemById($kp_in['eq_type_id']);
		//$curr=$_curr->GetItemById($kp_in['currency_id']);
		$sm1->assign('eq_str', $pl['name']); 
		$sm1->assign('srok_str', $supply['name']); 
		//$sm1->assign('curr_str', $curr['signature']); 
		
		
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($editing_user['currency_id']));
		
		
		 //контрагенты
		$_suppliers1=new Lead_SupplierGroup;
		$sup1=$_suppliers1->GetItemsByIdArr($lead['id']);
		$sup2=array();
		if(count($sup1)>0) $sup2[]=$sup1[0];
		$sm1->assign('suppliers', $sup2);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		 
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$sm1->assign('manager_string', $user_s['name_s']);
		
		//подгрузим данные по прибыли 
		$_pb=new BDR_P_Block;
		$pdata=$_pb->ConstructById($id,$editing_user['vid']);
		foreach($pdata as $k=>$v){
			$pdata[$k]['cost_wo_nds']=number_format($v['cost_wo_nds'], 2,'.', ' ');
			$pdata[$k]['nds_cost']=number_format($v['nds_cost'], 2,'.', ' ');
			$pdata[$k]['cost']=number_format($v['cost'], 2,'.', ' ');
				
		}
		
		$sm1->assign('pdata',  $pdata);
		
		
		 //подгрузим данные по затратм 
		$_mb=new BDR_M_Block;
		$mdata=$_mb->ConstructById($editing_user['kp_in_id'], $id,$editing_user['vid']);
		foreach($mdata as $k=>$v){
			 
			$mdata[$k]['cost']=number_format($v['cost'], 2,'.', ' ');
				
		}
		$sm1->assign('mdata',  $mdata);
		
		//var_dump($mdata);
		
		$editing_user['gain_val']=number_format($editing_user['gain_val'], 2,'.', ' ');
		$editing_user['gain_ebitda']=number_format($editing_user['gain_ebitda'], 2,'.', ' ');
		$editing_user['gain_chp']=number_format($editing_user['gain_chp'], 2,'.', ' ');
		
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id)
			&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id))
		;
		
		//список версий
		$versions=$_res->instance->GetVersions($editing_user['id']);
		$sm1->assign('versions', $versions);
		$sm1->assign('can_make_version', $au->user_rights->CheckAccess('w',1052)
			&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id))
		);
		
		
		//var_dump($versions); 
		
		$editing_user['pdate_d']=date('d.m.Y', $editing_user['pdate']);
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		$editing_user['pdate_course']=date('d.m.Y', $editing_user['pdate_course']);
		
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',1012);
		if(!$au->user_rights->CheckAccess('w',1012)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',1013);
			if(!$au->user_rights->CheckAccess('w',1013)) $reason='недостаточно прав для данной операции';
		
		
		
			//получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1043);
		$sm1->assign('can_unconfirm_users',$usg1);
		$sm1->assign('can_unconfirm',$au->user_rights->CheckAccess('w',1043));
		
		 
		 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_version']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',1043)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1041)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$can_confirm_price=$can_confirm_price&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmPrice($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//блок утв. фин службой
		if(($editing_user['is_confirmed_version']==1)&&($editing_user['user_confirm_version_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_version_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_version_pdate']);
			
			$sm1->assign('is_confirmed_version_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_version']==1){
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1051)||($result['main_department_id']==5) );
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=($au->user_rights->CheckAccess('w',1050)||($result['main_department_id']==5) );
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1)&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		
		$sm1->assign('can_modify_v',$can_confirm_shipping);
		
		
		//блок утв. ген дир.
		if(($editing_user['is_confirmed_version1']==1)&&($editing_user['user_confirm_version_id1']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_version_id1']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_version_pdate1']);
			
			$sm1->assign('is_confirmed_version1_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_version1']==1){
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1056)||($result['position_id']==8) );
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=($au->user_rights->CheckAccess('w',1055)||($result['position_id']==8) );
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1)&&($editing_user['vid']==$_res->instance->GetActiveVersionId($id));
		
		$sm1->assign('can_modify_v1',$can_confirm_shipping);
		
		 
		
		
		
	 
		//Примечания
		$rg=new BDRNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',1048), $au->user_rights->CheckAccess('w',1049), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',1047)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('can_modify', $can_modify);  
		$sm1->assign('can_calc_gain',  $au->user_rights->CheckAccess('w',1042) && 
									 ($editing_user['is_confirmed']==0)&&
									 in_array($editing_user['status_id'], $_editable_status_id)
									  
									 );	
		
		//var_dump($lead);
		 
	 
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		
		
	 
		
		$sm1->assign('files', $filetext);
	
	$user_form=$sm1->fetch('bdr/edit_bdr'.$print_add.'.html');
		  
		  
 		
/*******************************************************************************************************/		
		//построим форму БДДС
		//$sm1=new SmartyAdm;
		$_bdds=new BDDS_Block($editing_user['id'], $editing_user['vid']);
		$bddsdata=$_bdds->ConstructById($balance, $check);
		$sm1->assign('bddsdata',$bddsdata );
		$sm1->assign('check', $check);
		 $sm1->assign('check_formatted', number_format($check,2, '.',' ')); 
		 
		
		 
		 $bdds_form=$sm1->fetch('bdr/edit_bdds'.$print_add.'.html');
				  
		  
		  
	if($print_mode==0) $sm->assign('users',$user_form);
	elseif($print_mode==1) $sm->assign('users',$bdds_form);
	 
	 
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username); 
	 
	$html=$sm->fetch('bdr/ed_bdr_page'.$print_add.'.html');
	
	//var_dump( $print_mode);
	//echo $html; die();
	 

	
		  $tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
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
	
	if($print_mode==0){
		 $orient='--orientation Portrait';	
	}elseif($print_mode==1){
		 $orient='--orientation Landscape';
	}
	
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 ".$orient." --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

exec($comand);
	
	$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		 
		
		
		if($print_mode==0) $filenames_to_send[]=array(
								'fullname'=>$filename,
								'name'=>'БДР_'.$editing_user['code'].'.pdf'
							
							);
		elseif($print_mode==1) $filenames_to_send[]=array(
								'fullname'=>$filename,
								'name'=>'БДДС_'.$editing_user['code'].'.pdf'
							
							);							
		
	
		 
	
		
		
	

 }
 
 /*
 echo '<pre>';
 var_dump($filenames_to_send);
 echo '</pre>';*/
 
 if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_addresses=explode(',',$emails);
		
		 
		
		 
		
		$_filenames=array();
		foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
		
		//var_dump($_filenames); 
		 
		$org=$_orgitem->Getitembyid($result['org_id']);
		$opf=$_opf->getitembyid($org['opf_id']);
		
		
		$was_sent_to_supplier=false;
		 
		
		//использовать класс отправки сообщения
		foreach($_addresses as $k=>$email){
			
			//найти ФИО по адресу эл.почты...
			//1) в карте к-та
			$has_cont=false; $user_name='контрагент';
			 
			
			//2) в карте сотр-ка
			if(!$has_cont){
				$_uci=new UserContactDataItem;
				$_ui=new UserItem;
				$uci=$_uci->GetItemByFields(array('value'=>$email));
				$ui=$_ui->GetItemById($uci['user_id']);
				if($ui!==false) $user_name=$ui['name_s'];
				
			}
			
			
			
			
			
			$mail = new PHPMailer();
			$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div><div>Просим Вас рассмотреть возможность поставки в наш адрес следующего оборудования.</div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div>   <div>С уважением, %manager% <br> Компания %opf_name% %company_name%.</div>
 "; 
 
 			
			 $_mi=new UserSItem;
			 $mi=$_mi->GetItemById($editing_user['manager_id']);
			 	
			 $contact_str='';
			 
			 $contact_str.=$mi['name_s'];
			 
			 
			
			 
			 $_di=new UserDepItem; $_mdi=new UserMainDepItem;
			 $mdi=$_mdi->GetItemById($mi['main_department_id']);
			 $di=$_di->GetItemById($mi['department_id']);
			 if($mdi!==false) $contact_str.='<br>'.$mdi['name'];
			 if($di!==false) $contact_str.='<br>'.$di['name'];
			  $contact_str.='<br>'.$mi['position_s'];
			 
			 $rg=new UserContactDataGroup;
			 foreach($rg->GetItemsByIdArr($editing_user['manager_id']) as $k=>$v){
				$contact_str.='<div>'.$v['pc_name'].': '.$v['value']."</div>"; 
			 }
 			$body=str_replace('%manager%',  $contact_str,$body);
			
			$body=str_replace('%contact_name%',  $user_name,$body);
			$body=str_replace('%docs%', implode(', ',$_filenames),  $body);
			$body=str_replace('%company_name%', $org['full_name'],  $body);
			$body=str_replace('%opf_name%', $opf['name'],  $body);
			
			
		
			$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$org['full_name']);
		
			  
		
			$mail->AddAddress(trim($email),  $email);
		
			$mail->Subject = "документы от ".$opf['name'].' '.$org['full_name']; 
			$mail->Body=$body;
			
			//echo $body; die();
			
			foreach($filenames_to_send as $k=>$v) $mail->AddAttachment($v['fullname'], $v['name']);  
			 
			$mail->CharSet = "windows-1251";
			$mail->IsHTML(true);  
			
			if(!$mail->Send())
			{
				//echo "Ошибка отправки письма: " . $mail->ErrorInfo;
			}
			else 
			{
				// echo "Письмо отправленно!";
			} 
			
			$addition='';
			 
		  
				//внесем автомат. комментарии об отправке  
			$_kni=new BDRNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$editing_user['id'];
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=0; //$result['id'];
		  
		 
			if($print_mode==0) $notes_params['note']='Автоматическое примечание: '.$addition.'pdf-форма БДР была отправлена на электронную почту '.$email.' '.date('d.m.Y H:i:s').' '.$paste.' пользователем '.SecStr($result['name_s'].' ');
			elseif($print_mode==1)  $notes_params['note']='Автоматическое примечание: '.$addition.'pdf-форма БДДС была отправлена на электронную почту '.$email.' '.date('d.m.Y H:i:s').' '.$paste.' пользователем '.SecStr($result['name_s'].' ');
			$_kni->Add($notes_params);
			
			
		}	
		 
	 
	 
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>БДР было отправлено на следующие адреса:</strong></div>';
			$txt.='<ul>';
			
			foreach($_addresses as $k=>$email){
				$txt.='<li>'.$email.'</li>';
			}
			$txt.='</ul>';
			
			if(count($_filenames)>0){
				$txt.='<div>&nbsp;</div>';
				$txt.='<div><strong>Были приложены следующие файлы:</strong></div>';
				$txt.='<ul>';
				foreach($_filenames as $k=>$file){
					$txt.='<li>'.$file.'</li>';
				}
				$txt.='</ul>';
			}
			
		 
			//$txt.='<p></p>';			
			
			$sm->assign('message', $txt);
			
			$sm->display('page_email.html');
			
		 
		 
 
}

	foreach($filenames_to_send as $k=>$v){
		unlink($v['fullname']);
		$nname=eregi_replace("\.pdf$", "", basename($v['fullname']));
		//echo $nname;
		unlink(ABSPATH.'/tmp/'.$nname.'.html');
		unlink(ABSPATH.'/tmp/f'.$nname.'.html');
			
	}
 
	 
	exit;	
?>
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

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

require_once('classes/sched.class.php');



require_once('classes/tender_fileitem.php');
require_once('classes/tender_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/tender_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/tender_history_item.php');
require_once('classes/tender_history_group.php');

require_once('classes/phpmailer/class.phpmailer.php');

require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/usercontactdataitem.php');

require_once('classes/tender_history_fileitem.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



 

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new Tender_AbstractItem;

$_plan1=new Sched_Group;
$available_users=$_plan1->GetAvailableUserIds($result['id']);

$_plan=new Tender_Group;


$_supplier=new SupplierItem;
 $log=new ActionLog;
 $_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

 
	$object_id[]=931;
	 
 
if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);


 
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

$print=1;

$print_add='_print';

 
 

	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	 
 
 	$log=new ActionLog;
	 
	
	 
 
 
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 
		
		
		
		
		$_res=new Tender_Resolver();
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		if($editing_user['pdate_placing']!='')  $editing_user['pdate_placing']=datefromYmd($editing_user['pdate_placing']);
		
		if($editing_user['pdate_claiming']!='')  $editing_user['pdate_claiming']=datefromYmd($editing_user['pdate_claiming']);
		
		if($editing_user['pdate_finish']!='')  $editing_user['pdate_finish']=datefromYmd($editing_user['pdate_finish']);
		
		
		  //валюты
		$_curr=new PlCurrGroup;
		$sm1->assign('currs', $_curr->GetItemsArr($editing_user['currency_id']));
		
		  
		  //виды тендеров
		$_tks=new TenderKindGroup;
		$tks=$_tks->GetItemsArr();
		//var_dump($tks);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($tks as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('tender_ids', $_ids); $sm1->assign('tender_vals', $_vals);
		$_tki=new TenderKindItem;
		$tki=$_tki->GetItemById($editing_user['kind_id']);
		$editing_user['kind_name']=$tki['name'];
		
		
		
		//типы оборудования 
		$_eqs=new Tender_EqTypeGroup;
		$eqs=$_eqs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($eqs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('eq_ids', $_ids); $sm1->assign('eq_vals', $_vals);
		 $_tki=new Tender_EqTypeItem;
		$tki=$_tki->GetItemById($editing_user['eq_type_id']);
		$editing_user['eq_name']=$tki['name'];   
		
		
		
		//виды ФЗ
		$_fzs=new Tender_FZGroup;
		$fzs=$_fzs->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fzs as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fz_ids', $_ids); $sm1->assign('fz_vals', $_vals);
		 
		//print_r($eqs); 
		 
		$sm1->assign('fzs_total', $fzs); 
		
		
		 $_tki=new Tender_FZItem;
		$tki=$_tki->GetItemById($editing_user['fz_id']);
		$editing_user['fz_name']=$tki['name'];   
		
		
		
		
		
		
		//причины отказа
		$_fails=new Tender_FailGroup;
		$fails=$_fails->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fails as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fail_ids', $_ids); $sm1->assign('fail_vals', $_vals);
		
		 $_tki=new Tender_FailItem;
		$tki=$_tki->GetItemById($editing_user['fail_reason_id']);
		$editing_user['fail_name']=$tki['name'];   
		
		
		
		
		
		
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',931);
		if(!$au->user_rights->CheckAccess('w',931)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',931);
			if(!$au->user_rights->CheckAccess('w',931)) $reason='недостаточно прав для данной операции';
		
		
		
		
		 
		
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
		if($editing_user['is_confirmed_done']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',931)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',931)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//блок утв. выполнения
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['user_confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		   
		  if($editing_user['is_confirmed_done']==1){
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',937))||($editing_user['manager_id']==$result['id']));
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',931)&&(($au->user_rights->CheckAccess('w',935))||($editing_user['manager_id']==$result['id']));
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
		
		//блок утв. принятия
		if(($editing_user['is_fulfiled']==1)&&($editing_user['user_fulfiled_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_fulfiled_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfiled_pdate']);
			
			$sm1->assign('is_fulfiled_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed_done']==1){
		
		  if($editing_user['is_fulfiled']==1){
			   $can_confirm_shipping=$au->user_rights->CheckAccess('w',938);
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',936);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed_done']==1);
		
		
		$sm1->assign('can_confirm_fulfil',$can_confirm_shipping);
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		
		
		
		
		//лента задачи
		$_hg=new Tender_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'tender/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',932),
			 $au->user_rights->CheckAccess('w',933),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		
		
		 
		//контрагенты
		$_suppliers=new Tender_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		 $sm1->assign('can_modify_ribbon', $can_modify_ribbon);  
		
		
		
		
		 
		
		
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		
		
		//реестр прикрепленных файлов
		$folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 
			$decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  
			  
			  $ffg=new TenderFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TenderFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('tender/tender_files_list.html', $decorator,0,10000,'ed_tender.php', 'tender_file.html', 'swfupl-js/tender_files.php',  
			  false,  
			 false, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
		 
		 
		 
		 $_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		
		$sm1->assign('bill', $editing_user);
		
		
		$sm1->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm1->assign('print_username',$username);
		
		$html=$sm1->fetch('tender/edit_tender'.$print_add.'.html');
		 
	  
	/*	
	 echo $html; 
	die(); */
  
	
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
	
	
	//$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 73mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html --header-html ".SITEURL."/tpl-sm/plan_pdf/pdf_header.html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

exec($comand);
	
	//$_ki=new SchedKindItem;
	//$ki=$_ki->GetItemById($editing_user['kind_id']);
		 
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_addresses=explode(',',$emails);
		
		 
		$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		 
		
		$filenames_to_send=array();
		$filenames_to_send[]=array(
			'fullname'=>$filename,
			'name'=>'Тендер_'.$editing_user['code'].'.pdf'
		
		);
		
		
		if($_GET['with_files']==1){
			//получим файлы, прикрепленные к задаче
			$_ree_fi=new TenderFileItem;
			$_hi_fi=new Tender_HistoryFileItem;
			
			$sql='select * from tender_file where bill_id="'.$id.'" ';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$filenames_to_send[]=array(
					'fullname'=>$_ree_fi->GetStoragePath().$f['filename'],
					'name'=>$f['orig_name']
				
				);
			}
			
			$sql='select * from tender_history_file where history_id in(select id from tender_history where sched_id="'.$id.'" )';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$filenames_to_send[]=array(
					'fullname'=>$_hi_fi->GetStoragePath().$f['filename'],
					'name'=>$f['orig_name']
				
				);
			}
		}
		 
		
		 $_filenames=array();
		foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
		
		//var_dump($_filenames); 
		 
		$org=$_orgitem->Getitembyid($result['org_id']);
		$opf=$_opf->getitembyid($org['opf_id']);
		//использовать класс отправки сообщения
		foreach($_addresses as $k=>$email){
			
			//найти ФИО по адресу эл.почты...
			//1) в карте к-та
			$has_cont=false; $user_name='контрагент';
			/*$_sdi=new SupplierContactDataItem;
			$sdi=$_sdi->GetItemByFields(array('value'=>$email));
			if($sdi!==false){
				$_sci=new SupplierContactItem;
				$sci=$_sci->GetItemById($sdi['contact_id']);
				if($sci!==false){
					$user_name=$sci['name'];
					$has_cont=true;
				}
			}*/
			
			//2) в карте сотр-ка
			if(!$has_cont){
				$_uci=new UserContactDataItem;
				$_ui=new UserItem;
				$uci=$_uci->GetItemByFields(array('value'=>$email));
				$ui=$_ui->GetItemById($uci['user_id']);
				if($ui!==false) $user_name=$ui['name_s'];
				
			}
			
			
			
			
			
			$mail = new PHPMailer();
			$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div> <div>&nbsp;</div> <div>Благодарим Вас за то, что Вы обратились к нам!</div> <div>С уважением, компания %opf_name% %company_name% .</div>
 "; 
 			
			$body=str_replace('%contact_name%',  $user_name,$body);
			$body=str_replace('%docs%', implode(', ',$_filenames),  $body);
			$body=str_replace('%company_name%', $org['full_name'],  $body);
			$body=str_replace('%opf_name%', $opf['name'],  $body);
			
			
		
			$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$org['full_name']);
		
			  
		
			$mail->AddAddress(trim($email),  $email);
		
			$mail->Subject = "документы для Вас!"; 
			$mail->Body=$body;
			
			//echo $body;
			
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
			
		 
		 	$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию тендера',NULL,931, NULL, 'тендер № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
			
		}	
		 
		
		 
		//перейти в карту  
		/*if(!isset($_GET['doClose'])){
			header("Location: ed_sched.php?action=1&id=".$editing_user['id'].'&from_begin=1');
		}else{*/
			/*echo '<script type="text/javascript"> alert("Запись планировщика была отправлена на адреса электронной почты: '.$_GET['email'].'"); window.close();</script>';	*/
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Тендер был отправлен на следующие адреса:</strong></div>';
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
			
		//}
		//die();	
		
		
	}else{
		
	
		$log->PutEntry($result['id'],'получил pdf-версию тендера',NULL,931, NULL, 'тендер № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Тендер_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;


?>
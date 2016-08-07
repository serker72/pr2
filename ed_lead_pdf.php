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
require_once('classes/pl_proditem.php');
 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/tender.class.php');

 
require_once('classes/lead.class.php');


require_once('classes/lead_fileitem.php');
require_once('classes/lead_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');

require_once('classes/lead_history_fileitem.php');

require_once('classes/phpmailer/class.phpmailer.php');

require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/usercontactdataitem.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Лид');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new Lead_AbstractItem;

$_plan1=new Lead_Group;
$available_users=$_plan1->GetAvailableLeadIds($result['id']);

$_plan=new Lead_Group;


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
 
	$object_id[]=950;
	
	
	
	
  if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);




if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

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
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$_tg=new lead_Group;
	
	if(!$au->user_rights->CheckAccess('w',953)){	
		$available_tenders=$_tg->GetAvailableLeadIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
 
   	 
 
 	$log=new ActionLog;
	 
	
	 
 
 
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		$can_modify_ribbon=!in_array($editing_user['status_id'], array(30, 31, 32, 3, 10));
		
		
		 if($editing_user['tender_id']!=0){
			 
			 //подставить ТЕНДЕР
			 
			 $_tender=new Tender_Item;
			 $tender=$_tender->GetItemById($editing_user['tender_id']);
			 $sm1->assign('tender', $tender);
			 
		 
			 	
		}
		 
		
		
		$_res=new Lead_Resolver();
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		
		
		if($editing_user['pdate_finish']!='')  $editing_user['pdate_finish']=datefromYmd($editing_user['pdate_finish']);
		
		
		$from_hrs=array();
		//$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_h',$from_hrs);
	 
				
		$from_ms=array();
		//$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_finish_m',$from_ms);
		 
		if($editing_user['ptime_finish']!=""){
			$sm1->assign('ptime_finish_h',substr($editing_user['ptime_finish'],  0,2 ));
			$sm1->assign('ptime_finish_m',substr($editing_user['ptime_finish'],  3,2 )); 
		}
		 
		  
		  //виды тендеров
		$_tks=new LeadKindGroup;
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
		$_eqs=new Lead_EqTypeGroup;
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
		
		$sm1->assign('opfs_total', $eqs);
		
		
		//причины отказа
		$_fails=new Lead_FailGroup;
		$fails=$_fails->GetItemsArr();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($fails as $k=>$v){
			$_ids[]=$v['id'];
			$_vals[]=$v['name'];
		}
		$sm1->assign('fail_ids', $_ids); $sm1->assign('fail_vals', $_vals);
		
		 $_tki=new Lead_FailItem;
		$tki=$_tki->GetItemById($editing_user['fail_reason_id']);
		$editing_user['fail_name']=$tki['name'];   
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',950);
		if(!$au->user_rights->CheckAccess('w',950)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',961);
			if(!$au->user_rights->CheckAccess('w',961)) $reason='недостаточно прав для данной операции';
		
		
		
		
		 
		
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
			  if($au->user_rights->CheckAccess('w',950)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',950)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
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
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',958))/*||($editing_user['manager_id']==$result['id'])*/);
		  }else{
		  //ставим утв	
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',950)&&(($au->user_rights->CheckAccess('w',956))/*||($editing_user['manager_id']==$result['id'])*/);
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
			   $can_confirm_shipping=$au->user_rights->CheckAccess('w',959);
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',957);
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
		
		$_prods=new PlProdGroup;
		$prods=$_prods->GetItemsArr();
		$sm1->assign('prods',  $prods); 
		
		$_prod=new PlProdItem;
		$prod=$_prod->GetItemById($editing_user['producer_id']);
		$editing_user['prod_name']=$prod['name'];
		
		//лента задачи
		$_hg=new Lead_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'lead/lenta'.$print_add.'.html', 
			 new DBDecorator(), 
			 $can_modify_ribbon,
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',951),
			 $au->user_rights->CheckAccess('w',952),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		
		
		 
		//контрагенты
		$_suppliers=new Lead_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		
		
		
	 
		
		
		$sm1->assign('can_modify', $can_modify);  
		 $sm1->assign('can_modify_ribbon', $can_modify_ribbon);  
		 
		 
		 $sm1->assign('can_modify_supplier', ( $can_modify&&($editing_user['tender_id']==0)));
		 
		$sm1->assign('can_add_supplier', $au->user_rights->CheckAccess('w',87)); 
		
		$sm1->assign('can_expand_types', $au->user_rights->CheckAccess('w',939));
		$sm1->assign('can_modify_iam',  ($editing_user['is_confirmed_done']==0)&&($editing_user['manager_id']==0));
		
		 
		
		$sm1->assign('can_fail', ($editing_user['is_confirmed']==1)&&($editing_user['is_fulfiled']==0)&&($editing_user['status_id']!=31));
		
		
		
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
			  
			  
			  
			  
			  $ffg=new LeadFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new LeadFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('lead/lead_files_list'.$print_add.'.html', $decorator,0,10000,'ed_lead.php', 'lead_file.html', 'swfupl-js/lead_files.php',  
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
		 
		 
		$html=$sm1->fetch('lead/edit_lead'.$print_add.'.html');
		 
		
	   
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
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

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
			'name'=>'Лид_'.$editing_user['code'].'.pdf'
		
		);
		
		
		if($_GET['with_files']==1){
			//получим файлы, прикрепленные к задаче
			$_ree_fi=new LeadFileItem;
			$_hi_fi=new Lead_HistoryFileItem;
			
			$sql='select * from lead_file where bill_id="'.$id.'" ';
	
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
			
			$sql='select * from lead_history_file where history_id in(select id from lead_history where sched_id="'.$id.'" )';
	
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
			
		 
		 	$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию лида',NULL,950, NULL, 'лид № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
			
		}	
		 
		
		 
		//перейти в карту  
		/*if(!isset($_GET['doClose'])){
			header("Location: ed_sched.php?action=1&id=".$editing_user['id'].'&from_begin=1');
		}else{*/
			/*echo '<script type="text/javascript"> alert("Запись планировщика была отправлена на адреса электронной почты: '.$_GET['email'].'"); window.close();</script>';	*/
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Лид был отправлен на следующие адреса:</strong></div>';
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
		
	
		$log->PutEntry($result['id'],'получил pdf-версию лида',NULL,950, NULL, 'лид № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Лид_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;

?>
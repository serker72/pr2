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
require_once('classes/kp_in.class.php');
require_once('classes/tz.class.php');
require_once('classes/bdr.class.php');
require_once('classes/doc_in.class.php');
require_once('classes/sched.class.php');



require_once('classes/doc_in_fileitem.php');
require_once('classes/doc_in_filegroup.php');
 

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
 
require_once('classes/lead_history_group.php');
require_once('classes/docstatusitem.php');

require_once('classes/lead_history_item.php'); 

require_once('classes/lead_view_item.php');

require_once('classes/usercontactdataitem.php'); 
require_once('classes/phpmailer/class.phpmailer.php');

require_once('classes/suppliercontactdataitem.php'); 

require_once('classes/user_pos_item.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Входящее КП');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DocIn_AbstractItem;

$_plan1=new DocIn_Group;
$available_users=$_plan1->GetAvailableDocIds($result['id']);

$_plan=new KpIn_Group;


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

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;

$object_id=array();
switch($action){
	case 0:
	$object_id[]=1079;
	break;
	case 1:
	$object_id[]=1080;
	break;
	case 2:
	$object_id[]=1080;
	break;
	default:
	$object_id[]=1080;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
 



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

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 
	
 

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
	
	
	$_tg=new DocIn_Group;
	
	if(!$au->user_rights->CheckAccess('w',1083)){	
		$available_tenders=$_tg->GetAvailableDocIds($result['id']);
		$is_shown=in_array($id, $available_tenders);
	
		if(!$is_shown){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}
	}
	
 
  
  
		if($print==0) $print_add='';
		else $print_add='_print';


	 
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	 
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
	 
		//var_dump($field_rights);
		
		
		//построим доступы
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		
			 
			 $_lead=new Lead_Item;
			 $lead=$_lead->GetItemById($editing_user['lead_id']);
			 $sm1->assign('lead', $lead);
			 
		 
			 	
		 
		
		
		$_res=new DocIn_Resolver($editing_user['kind_id']);
		
		
		if($editing_user['reg_pdate']!=0) $editing_user['reg_pdate']=date('d.m.Y', $editing_user['reg_pdate']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		if($editing_user['received_pdate']!=0) $editing_user['received_pdate']=date('d.m.Y', $editing_user['received_pdate']);
		
		
		
		if($editing_user['given_pdate']!='')  $editing_user['given_pdate']=date('d.m.Y', $editing_user['given_pdate']);
		
		
		   
		
	  
		 
		
		 
		
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
			  if($au->user_rights->CheckAccess('w',1023)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',1022)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		  
		$reason='';
		
		
		 
		
		
		//отвеств сотр-к
		$_user_s=new UserSItem;
		$user_s=$_user_s->GetItemById($editing_user['manager_id']);
		$editing_user['manager_string']=$user_s['name_s'];
		$_up=new UserPosItem;
		$up=$_up->GetItemById($user_s['position_id']);
		
		//получить должность и контакт(рабочий телефон) менеджера
		$sm1->assign('position_s', $up['name']);
		
	 	//раб телефон
		$_cv=new UserContactDataItem;
		$cv=$_cv->GetItemByFields(array('user_id'=>$user_s['id'], 'kind_id'=>1));
		$sm1->assign('phone', $cv['value']);
		 
		//контрагенты
		$_suppliers=new DocIn_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['lead_id']);
		$sm1->assign('suppliers', $sup);
		
		
	    
		
	 
		
		
	  
		  
		
		
		$sm1->assign('can_modify', $can_modify);  
		   
		 
		
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
			  
			  
			  
			  
			$ffg=new DocInFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new DocInFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html',  $decorator,0,1000,'ed_doc_in.php', 'doc_in_file.html', 'swfupl-js/doc_in_files.php',  
			  $can_modify,    
			 $can_modify, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_', $files 
			   );
		
		
		$sm1->assign('files', $filetext);
		
		$sm1->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm1->assign('print_username',$username);
		
		
	
	
	$html=$sm1->fetch('doc_in/edit_doc_in_print.html');
	
		  $tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
	exec($cd);
	
	
	//скомпилируем подвал
	/*$sm=new SmartyAdm;
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm->assign('print_username',$username);
	$foot=$sm->fetch('plan_pdf/pdf_footer_unlogo.html');
	$ftmp='f'.time();
	
	$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
	fputs($f, $foot);
	fclose($f);
	*/
	
	 
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 35mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tpl-sm/pl_pdf/pdf_footer.html --header-html ".SITEURL."/tpl-sm/kp_pdf/common/pdf_header.html ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

exec($comand);
	
	 	 
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_addresses=explode(',',$emails);
		
		$file_ids=explode(',', $_GET['file_ids']);
		
		 
		$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		 
		
		$filenames_to_send=array();
		if(in_array('0', $file_ids)) $filenames_to_send[]=array(
			'fullname'=>$filename,
			'name'=>'ВХ_'.$editing_user['code'].'.pdf'
		
		);
		
		
	 
	 	
		//получим файлы, прикрепленные к задаче
		$_ree_fi=new DocInFileItem;
		 
		$sql='select * from doc_in_file where bill_id="'.$id.'" ';

		$set=new mysqlset($sql);
		$rs=$set->GetResult();	
		$rc=$set->GetResultnumrows();
	
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if(in_array($f['id'], $file_ids)) $filenames_to_send[]=array(
													'fullname'=>$_ree_fi->GetStoragePath().$f['filename'],
													'name'=>$f['orig_name'],
													'pdate'=>$f['pdate']
												
												);
		}
		
		/*var_dump(in_array('0', $file_ids));	
		print_r($filenames_to_send); die(); 
		 */
		
		$_filenames=array();
		foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
		
		//var_dump($_filenames); 
		 
		$org=$_orgitem->Getitembyid($result['org_id']);
		$opf=$_opf->getitembyid($org['opf_id']);
		
		
		$was_sent_to_supplier=false; $file_was_sent=false;
		//ищем все адреса почты контрагентов-клиентов, среди них найдем каждый отправляемый
		//если было хотя бы одно совпадение - то: фиксировать отправку КП исх. клиенту, писать примечания в КПИ, ЛИД + ЖС, менять статус лида на "на рассмотрении" 35
		$_supplierstz=new DocIn_SupplierGroup;
		$sup=$_supplierstz->GetItemsByIdArr($editing_user['id']);
		
		$_scg=new SupplierContactGroup;
		$sprav_emails=array();
		foreach($sup as $ks=>$vs){
			$scg=$_scg->GetItemsByIdArr($vs['supplier_id']);
			
			foreach($scg as $kc=>$vc){
				foreach($vc['data'] as $kd=>$vd) if($vd['kind_id']==5) $sprav_emails[]=$vd['value']; 	
			}
		}
		//массив адресов клиентов, куда отправили
		$_was_sent_to_addr=array();
		
		//var_dump($sprav_emails);
		
	 
		
		
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
			
			$mail->Subject = $editing_user['topic']; 
			$mail->Body=$body;
			
			//echo $body;
			
			foreach($filenames_to_send as $k=>$v) {
				$mail->AddAttachment($v['fullname'],  $v['name']);  
				
				$file_was_sent=$file_was_sent||true;
			}
			 
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
			
			
			 
				//фиксируем отправку КП клиенту
				 
				if(in_array($email, $sprav_emails)) {
					$was_sent_to_supplier=$was_sent_to_supplier||true;
					//$addition='ТЗ было отправлено поставщику: ';
					$_was_sent_to_addr[]=$email;
				}
				
			 
			 
		 
		 	$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию вх. документа',NULL,1080, NULL, ' № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
			
			
			
			//коммент
			  //примечания
			   $_lhi=new DocIn_HistoryItem;
			  
			   $notes_params=array();
				$notes_params['is_auto']=1;
				$notes_params['user_id']=$editing_user['id'];
				$notes_params['pdate']=time();
				$notes_params['posted_user_id']=0; //$result['id'];
			  
			 
				$notes_params['note']='Автоматическое примечание: входящий документ № '.$editing_user['code'].' был отправлен на электронную почту  '.$email.' '.date('d.m.Y H:i:s').' пользователем '.SecStr($result['name_s'].' ').'.';
				 
				
				$_lhi->Add(array(
					'sched_id'=>$editing_user['id'],
					'user_id'=>0,
					'txt'=>$notes_params['note'],
					 
					'pdate'=>time()
				));	 
		}	
		 
		//была зафиксирована отправка 
		
		
		//var_dump($was_sent_to_supplier); var_dump($file_was_sent);
		
		 
		 
	
		
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Вх. документ был отправлен на следующие адреса:</strong></div>';
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
			
			 
		
		
		 
		
		
	}else{
		
	
		$log->PutEntry($result['id'],'получил pdf-версию вх. документа',NULL,1080, NULL, ' № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="ВХ_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;	

?>
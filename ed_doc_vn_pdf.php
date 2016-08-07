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
require_once('classes/doc_vn.class.php');



require_once('classes/doc_in_fileitem.php');
require_once('classes/doc_in_filegroup.php');

require_once('classes/doc_vn_fileitem.php');
require_once('classes/doc_vn_filegroup.php');
 

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

require_once( "classes/phpqr/qrlib.php");  


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Печать внутреннего документа');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DocVn_AbstractItem;

$_plan1=new DocVn_Group;
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
	$object_id[]=1091;
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


if(!isset($_GET['printmode'])){
	if(!isset($_POST['printmode'])){
		$printmode=0;
	}else $printmode=abs((int)$_POST['printmode']); 
}else $printmode=abs((int)$_GET['printmode']);

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
	
	
	$_tg=new DocVn_Group;
	
	if(!$au->user_rights->CheckAccess('w',1095)){	
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
	
	
	$sm=new SmartyAdm;
	
	include('inc/ed_doc_vn_print.php');
	
	
	//echo $html; die();
	
	
		  $tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
	exec($cd);
	
	
	//скомпилируем подвал
	//подвал + qr - код
	if($print!=0){
		 $PNG_WEB_DIR = ABSPATH.'tmp/';
		 $PNG_TEMP_DIR =ABSPATH.'classes/phpqr/temp/';
		 $errorCorrectionLevel='Q';
		 $matrixPointSize = 1;
		 $data= 'СЗ на комаднировку '.$editing_user['code'].', статус: '.$editing_user['status'];
		// if($editing_user['is_ruk']==1) $data.=' утверждено руководителем отдела: '.$confirmer;
  		//if($editing_user['is_dir']==1) $data.=' утверждено генеральным директором: '.$confirmerd;
  		
		
		$data=iconv('windows-1251', 'utf-8', $data);
		
		$filename = $PNG_TEMP_DIR.'doc_vn_'.$id.'.png';	  
		
		 QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   
		
	 	
		
		 
		rename($filename, $PNG_WEB_DIR.basename($filename));
		 
		 
		$sm->assign('qr', 'tmp/'.basename($filename));
		
		//подготовим подвал
		$sm_foot=new SmartyAdm;
		$user1=$_user->GetItemById($result['id']);
		$sm_foot->assign('qr', 'tmp/'.basename($filename));
		$sm_foot->assign('user_signed',$user1);
		$sm_foot->assign('pdate_signed', date("d.m.Y H:i:s"));
		$sm_foot->assign('SITEURL', SITEURL);
		$footerh=$sm_foot->fetch('petition/petition_footer.html');
		
		
	}
	
		$footer=fopen(ABSPATH.'/tmp/f'.$tmp.'.html','w');
		fputs($footer,$footerh);
		fclose($footer);
		
	 
 
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 5mm --margin-bottom 15mm --margin-left 10mm --margin-right 10mm --footer-html ".ABSPATH."/tmp/f".$tmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
 

exec($comand);
	
	 	 
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		/*$emails=$_GET['email'];
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
			
			 
		
		
		 
		
		*/
	}else{
		
	
		$log->PutEntry($result['id'],'получил pdf-версию вн. документа',NULL,1091, NULL, ' № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="ВН_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;	

?>
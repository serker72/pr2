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

require_once('classes/prikazgroup.php');
require_once('classes/prikazitem.php');


require_once('classes/prikazfilegroup.php');
require_once('classes/prikazfileitem.php');


if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Приказ');

$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
//require_once('inc/restr.php');


$mi=new PrikazItem;
//$hi=new ClaimHistoryItem();

$log=new ActionLog();







if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(($action==1)||($action==2)||($action==10)||($action==3)||($action==4)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();
		}else $id=abs((int)$_POST['id']);
	}else $id=abs((int)$_GET['id']);
	
	$prikaz=$mi->GetItemById($id);
	
	if($prikaz===false){
		header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();
	}
		
}

if($action==0){
	if(!$au->user_rights->CheckAccess('w',806)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}	
}

if($action==1){
	
	
	if(!$au->user_rights->CheckAccess('w',805)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
		
}


//журнал событий 
if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту приказа',NULL,805, NULL, $prikaz['id'].', вход. № '.$prikaz['vhod_no'],$id);			
}



if($action==2){
	if(!$au->user_rights->CheckAccess('w',808)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
		
	
	if(!isset($_GET['file_id'])){
		if(!isset($_POST['file_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();
		}else $file_idabs((int)$_POST['file_id']);
	}else $file_id=abs((int)$_GET['file_id']);
	
	$_pfi=new PrikazFileItem;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл приказа',NULL,812,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: edit_prikaz.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}



//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',812)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	if(isset($_GET['file_id'])) $file_id=abs((int)$_GET['file_id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_ff=new FileDocFolderItem(4, $id, new PrikazFileItem(4));
	$_ff->SetTablename('prikaz_file_folder');
	$_ff->SetDocIdName('id');
		
	
	
	$file=$_ff->GetItemById($file_id);
	
	
	
	if($file!==false){
		$_ff->Del($file_id,$file,$result,812);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,812,NULL,'имя папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:edit_prikaz.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',812)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//обработать файлы
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  $_file=new PrikazFileItem(4);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщения...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(4, $id, new PrikazFileItem(4));
	$_ff->SetTablename('prikaz_file_folder');
	$_ff->SetDocIdName('id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 812);
			
			//записи в журнал, сообщения...
		}
	}
	
	
	header("Location:edit_prikaz.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}









//обработка данных
if(($action==0)&&( isset($_POST['makePrikaz'])||isset($_POST['makePrikazStay'])   )){
	
	if(!$au->user_rights->CheckAccess('w',806)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	$params['pdate']=datefromdmy($_POST['pdate'])  + time()-datefromdmy(date('d.m.Y'))   ;
	
	$params['vhod_no']=SecStr($_POST['vhod_no']);
	$params['name']=SecStr($_POST['name']);
	$params['user_id']=$result['id'];
	
	
	$code=$mi->Add($params);
	
	$log->PutEntry($result['id'],'создал приказ',NULL,806,NULL,'Номер приказа: '.$code.' Вход. номер приказа:'.$params['vhod_no'].' Название приказа: '.($params['name']));
	
	
	if(isset($_POST['makePrikazStay'])){
		header('Location: edit_prikaz.php?action=1&id='.$code);
		die();	
			
	}else{
		header('Location: prikaz.php');
		die();	
	}
	
}elseif(($action==1)&&( isset($_POST['makePrikaz'])||isset($_POST['makePrikazStay'])   )){
	if(!$au->user_rights->CheckAccess('w',807)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$params=array();
	
	$params['pdate']=datefromdmy($_POST['pdate'])  + time()-datefromdmy(date('d.m.Y'))   ;
	
	$params['vhod_no']=SecStr($_POST['vhod_no']);
	$params['name']=SecStr($_POST['name']);
	$params['user_id']=$result['id'];
	
	
	$mi->Edit($id,$params);
	
	$log->PutEntry($result['id'],'редактировал приказ',NULL,807,NULL,'Номер приказа: '.$id.' Вход. номер приказа:'.$params['vhod_no'].' Название приказа: '.($params['name']));
	
	
	if(isset($_POST['makePrikazStay'])){
		header('Location: edit_prikaz.php?action=1&id='.$id);
		die();	
			
	}else{
		header('Location: prikaz.php');
		die();	
	}
		

}elseif(($action==10)){
	if(!$au->user_rights->CheckAccess('w',808)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$mi->Del($id);
	$log->PutEntry($result['id'],'удалил приказ',NULL,808,NULL,'Номер приказа: '.$id.' Вход. номер приказа:'.$prikaz['vhod_no'].' Название приказа: '.($prikaz['name']));
	
	
		header('Location: prikaz.php');
		die();	
		
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=8;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	 
	
	//создание prikaza
	if($action==0){
	  $sm1=new SmartyAdm;
	  
	  
	  $sm1->assign('session_id',session_id());
	  $sm1->assign('pdate',date('d.m.Y'));
	  
	  //$ckg=new ClaimKindGroup;
	  //$sm1->assign('items',$ckg->GetItemsArr());
	  
	  $sm1->assign('can_add',$au->user_rights->CheckAccess('w',806));
	  $llg=$sm1->fetch('prikaz/prikaz_create.html');
	  
	}elseif($action==1){
		$sm1=new SmartyAdm;
	  
	    $prikaz['pdate']=date('d.m.Y',$prikaz['pdate']);
	    
		
		
	    $sm1->assign('prikaz',$prikaz);
	  	$sm1->assign('session_id',session_id());
		 $sm1->assign('can_edit',$au->user_rights->CheckAccess('w',807));
	 	 $llg=$sm1->fetch('prikaz/prikaz_edit.html');
		 
		 
		 
		 //файлы приказа
		 $pfg=new PrikazFileGroup;
		  $decorator=new DBDecorator;
		  
		  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
		 // $decorator->AddEntry(new SqlEntry('id',$id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$id));
		  //$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
		  
		  
		  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
		  $navi_dec=new DBDecorator;
		  $navi_dec->AddEntry(new UriEntry('action',1));
		  
		  
		  if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
		  else $from=0;
		  
		  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
		  else $to_page=ITEMS_PER_PAGE;
		  
		  $ffg=new PrikazFileGroup(4,  $id,  new FileDocFolderItem(4,  $id, new PrikazFileItem(4)));;
		  
		  $filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'edit_prikaz.php', 'prikaz_file.html', 'swfupl-js/prikaz_files.php',  
		  $au->user_rights->CheckAccess('w',809),  
		  $au->user_rights->CheckAccess('w',812) , 
		  $au->user_rights->CheckAccess('w',810) , 
		  $folder_id, 
		  $au->user_rights->CheckAccess('w',809) , 
		  $au->user_rights->CheckAccess('w',809) , 
		  $au->user_rights->CheckAccess('w',812) , 
		  $au->user_rights->CheckAccess('w',809) ,    
		  '',  // );
		  
		   $au->user_rights->CheckAccess('w',811),  
		   $result, 
		   $navi_dec, 'file_' 
		   );
		  
		  
		  //$filetext=$ffg->ShowFiles($id,'prikaz/files_list.html', $decorator,$from,$to_page,'edit_prikaz.php', 'prikaz_file.html', 'swfupl-js/prikaz_files.php', $au->user_rights->CheckAccess('w',92), $au->user_rights->CheckAccess('w',94), $au->user_rights->CheckAccess('w',93));
		  
		 
		  
		 /* foreach($user as $k=>$v){
			  $user[$k]=stripslashes($v);
		  }
		  
		  $_opf=new OpfItem;
		  $opf=$_opf->GetItemById($user['opf_id']);
		  $user['opf_name']=stripslashes($opf['name']);
		  
		  $sm->assign('user',$user);*/
		  
		  
		  $llg.=$filetext;
		 
		 
	}
	
	
	$sm->assign('log',$llg);
	
	$content=$sm->fetch('prikaz/prikaz_page.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>
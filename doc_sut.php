<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей
 


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');




require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');
 

require_once('classes/doc_vn.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","ѕравила расчета суточных");

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


$_menu_id=82;	
	  include('inc/menu.php');
 
 
 
 
 	if(!$au->user_rights->CheckAccess('w',1115)){
		$content='<h1>GYDEX.¬ работе!</h1>';
		
	}else{
  		$sm1=new SmartyAdm;
	 
		 
		
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlOrdEntry('begin_pdate',SqlOrdEntry::ASC));
	

	
	$_cp=new DocVn_SutGroup;
	
	$_cp->SetAuthResult($result);
	$ships=$_cp->ShowAllPos('doc_vn/doc_sut_list.html',  $decorator, $some);


	

	$sm->assign('log2',$ships); 
	
	
	
	$content=$sm->fetch('doc_vn/doc_sut_page.html');
	
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'открыл раздел ѕравила расчета суточных',NULL,1115, NULL);
	 

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
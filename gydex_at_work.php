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
require_once('classes/filemessagegroup.php');
/*require_once('classes/orderfilegroup.php');
require_once('classes/reclamfilegroup.php');
require_once('classes/claimfilegroup.php');
require_once('classes/opfitem.php');
require_once('classes/fagroup.php');
*/

require_once('classes/userpaspgroup.php');
require_once('classes/wffilegroup.php');
require_once('classes/trustfilegroup.php');
//require_once('classes/storagefilegroup.php');
require_once('classes/sh_i_filegroup.php');
require_once('classes/payfilegroup.php');
//require_once('classes/kvfilegroup.php');
//require_once('classes/isfilegroup.php');
//require_once('classes/wffilegroup.php');
require_once('classes/filegroup.php');
require_once('classes/billfilegroup.php');
require_once('classes/accfilegroup.php');



require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","GYDEX.¬ работе!");

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


	
	  include('inc/menu.php');
	  
 
$content='
<h1>GYDEX.¬ работе! </h1>
 
';

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
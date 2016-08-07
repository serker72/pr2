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
require_once('classes/pl_posgroup_pdf.php');

require_once('classes/posgroupgroup.php');
require_once('classes/posgroup.php');
require_once('classes/price_kind_item.php');

require_once('classes/rl/rl_man.php');

//require_once('classes/mpdf/mpdf.php');
//require_once('classes/dompdf/dompdf_config.inc.php');

//require_once('classes/tcpdf/tcpdf.php');


$prefix='_1';
	

 
 

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;
 

//проверить, может ли пол-ль работать с выбранным видом цен
if(isset($_GET['price_kind_id'.$prefix])) $price_kind_id=abs((int)$_GET['price_kind_id'.$prefix]);
elseif(isset($_POST['price_kind_id'.$prefix])){
	$price_kind_id=abs((int)$_POST['price_kind_id'.$prefix]);
}else $price_kind_id=0;

if($price_kind_id>0){
	$_pki=new PriceKindItem;
	$pki=$_pki->GetItemById($price_kind_id);
	if($pki['print_object_id']>0){
		if(!$au->user_rights->CheckAccess('w',$pki['print_object_id'])){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}

	}
		
}

 

 $_rl=new RLMan;

$prefix='_1';

if(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
	if(!$_rl->CheckFullAccess($result['id'], abs((int)$_GET['group_id'.$prefix]), 1, 'w', 'catalog_group', 0)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
		 
}

if(isset($_GET['producer_id'.$prefix])&&(strlen($_GET['producer_id'.$prefix])>0)){
	if(!$_rl->CheckFullAccess($result['id'], abs((int)$_GET['producer_id'.$prefix]), 34, 'w', 'pl_producer', 0)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
}

if(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
	if(!$_rl->CheckFullAccess($result['id'], abs((int)$_GET['two_group_id'.$prefix]), 1, 'w', 'catalog_group', 0)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
}
	
	
	
//журнал событий 
$log=new ActionLog;
$pr_descr=array();
if(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
	$_pl_gr=new PosGroupItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['group_id'.$prefix]));
	$pr_descr[]= ' раздел: '.$pl_gr['name'].'';
		
}
if(isset($_GET['price_kind_id'.$prefix])&&(strlen($_GET['price_kind_id'.$prefix])>0)){
	$_pl_gr=new PriceKindItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['price_kind_id'.$prefix]));
	$pr_descr[]= ' вид цен: '.$pl_gr['name'].'';
		
}


if(isset($_GET['producer_id'.$prefix])&&(strlen($_GET['producer_id'.$prefix])>0)){
	$_pl_gr=new PlProdItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['producer_id'.$prefix]));
	$pr_descr[]= ' производитель: '.$pl_gr['name'].'';
		
}
if(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
	$_pl_gr=new PosGroupItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['two_group_id'.$prefix]));
	$pr_descr[]= ' категория: '.$pl_gr['name'].'';
		
}

if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
	$_pl_gr=new PosGroupItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['three_group_id'.$prefix]));
	$pr_descr[]= ' подкатегория: '.$pl_gr['name'].'';
		
}
if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
	$_pl_gr=new PlPosItem;
	$pl_gr=$_pl_gr->Getitembyid(abs((int)$_GET['id'.$prefix]));
	$pr_descr[]= ' оборудование: '.$pl_gr['name'].'';
		
}

$log->PutEntry($result['id'],'получил pdf-версию прайс-листа',NULL,600, NULL,SecStr(implode(', ', $pr_descr)));	
		
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//покажем лог
	$log=new PlPosGroupPDF;
	$prefix='_1';
	
	 
	//url='pricelist_pdf.php?price_kind_id%{$prefix}%=%{$price_kind_id}%&group_id%{$prefix}%=%{$group_id}%&two_group_id%{$prefix}%=%{$two_group_id}%&producer_id%{$prefix}%=%{$producer_id}%&id%{$prefix}%=%{$id}%&with_options='+with_options;
			
	
	
	 
	 
	if(isset($_GET['group_id'.$prefix])) $group_id=abs((int)$_GET['group_id'.$prefix]);
	elseif(isset($_POST['group_id'.$prefix])){
		$group_id=abs((int)$_POST['group_id'.$prefix]);
	}else $group_id=0;
	
	//для определенния шаблона запросим группу
	$_pgi=new PosGroupItem;
	$pgi=$_pgi->GetItemById($group_id);
	
	
	if(isset($_GET['two_group_id'.$prefix])) $two_group_id=abs((int)$_GET['two_group_id'.$prefix]);
	elseif(isset($_POST['two_group_id'.$prefix])){
		$two_group_id=abs((int)$_POST['two_group_id'.$prefix]);
	}else $two_group_id=0;
	
	if(isset($_GET['three_group_id'.$prefix])) $three_group_id=abs((int)$_GET['three_group_id'.$prefix]);
	elseif(isset($_POST['three_group_id'.$prefix])){
		$three_group_id=abs((int)$_POST['three_group_id'.$prefix]);
	}else $three_group_id=0;
	
	if(isset($_GET['producer_id'.$prefix])) $producer_id=abs((int)$_GET['producer_id'.$prefix]);
	elseif(isset($_POST['producer_id'.$prefix])){
		$producer_id=abs((int)$_POST['producer_id'.$prefix]);
	}else $producer_id=0;
	
	if(isset($_GET['id'.$prefix])) $id=abs((int)$_GET['id'.$prefix]);
	elseif(isset($_POST['id'.$prefix])){
		$id=abs((int)$_POST['id'.$prefix]);
	}else $id=0;
	
	if(isset($_GET['with_options'.$prefix])) $with_options=abs((int)$_GET['with_options'.$prefix]);
	elseif(isset($_POST['with_options'.$prefix])){
		$with_options=abs((int)$_POST['with_options'.$prefix]);
	}else $with_options=0;
	
	if(isset($_GET['show_price_f'.$prefix])) $show_price_f=abs((int)$_GET['show_price_f'.$prefix]);
	elseif(isset($_POST['show_price_f'.$prefix])){
		$show_price_f=abs((int)$_POST['show_price_f'.$prefix]);
	}else $show_price_f=0;
	
	if(isset($_GET['manager_id'.$prefix])) $manager_id=abs((int)$_GET['manager_id'.$prefix]);
	elseif(isset($_POST['manager_id'.$prefix])){
		$manager_id=abs((int)$_POST['manager_id'.$prefix]);
	}else $manager_id=0;
	
	if(isset($_GET['lang_rus'.$prefix])) $lang_rus=abs((int)$_GET['lang_rus'.$prefix]);
	elseif(isset($_POST['lang_rus'.$prefix])){
		$lang_rus=abs((int)$_POST['lang_rus'.$prefix]);
	}else $lang_rus=0;
	
	if(isset($_GET['lang_en'.$prefix])) $lang_en=abs((int)$_GET['lang_en'.$prefix]);
	elseif(isset($_POST['lang_en'.$prefix])){
		$lang_en=abs((int)$_POST['lang_en'.$prefix]);
	}else $lang_en=0;
	
	if(($lang_rus==0)&&($lang_en==0)) $lang_rus=1;
	
	$llg=$log->ShowPrint( $group_id, $price_kind_id, $producer_id, $two_group_id, $id,  $with_options, /*'pl_pdf/body_pdf.html'*/ 'pl_pdf/'.$pgi['print_pdf'], NULL, $show_price_f, $manager_id, $result['org_id']	, $lang_rus, $lang_en, $three_group_id   	   
	  );
	
	// echo $llg;
	
	
	/*$sm=new SmartyAdm;
	$header=$sm->fetch('pl_pdf/header.html');
	
	$sm=new SmartyAdm;
	$footer=$sm->fetch('pl_pdf/footer.html');*/
	
	//echo $llg;
	
	
	$tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $llg);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
exec($cd);
$comand = "wkhtmltopdf-i386 --load-error-handling ignore --encoding windows-1251 --margin-top 35mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tpl-sm/pl_pdf/pdf_footer.html --header-html ".SITEURL."/tpl-sm/pl_pdf/pdf_header.html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
//$comand = "wkhtmltopdf --footer-html ".ABSPATH."/tpl-sm/pl_pdf/footer.html --header-center \"blblblbl\"  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."catalog.pdf";
exec($comand);
if (file_exists(ABSPATH.'/tmp/'.$tmp.'.pdf')) {
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="pricelist.pdf"');
readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
}
unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	
?>
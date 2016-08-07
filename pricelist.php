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
require_once('classes/pl_posgroup.php');

require_once('classes/posgroupgroup.php');
require_once('classes/posgroupitem.php');
require_once('classes/posgroup.php');
require_once('classes/price_kind_item.php');
require_once('classes/rl/rl_man.php');

require_once('classes/pl_proditem.php');
require_once('classes/price_kind_item.php');
require_once('classes/pl_positem.php');

$prefix='_1';
	

if(isset($_GET['memory'.$prefix])) $memory=abs((int)$_GET['memory'.$prefix]);
elseif(isset($_POST['memory'.$prefix])){
	$memory=abs((int)$_POST['memory'.$prefix]);
}else $memory=0;

if($memory==0){
	$_pl_g=new PlPosGroup;
	$_pl_g->MakeMemory($memory);	
}


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Прайс-лист');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;

 
if(!$au->user_rights->CheckAccess('w',600)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//проверить, может ли пол-ль работать с выбранным видом цен
if(isset($_GET['price_kind_id'.$prefix])) $price_kind_id=abs((int)$_GET['price_kind_id'.$prefix]);
elseif(isset($_POST['price_kind_id'.$prefix])){
	$price_kind_id=abs((int)$_POST['price_kind_id'.$prefix]);
}else $price_kind_id=0;

if($price_kind_id>0){
	$_pki=new PriceKindItem;
	$pki=$_pki->GetItemById($price_kind_id);
	if($pki['object_id']>0){
		if(!$au->user_rights->CheckAccess('w',$pki['object_id'])){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}

	}
		
}



//**************** проверка доступа на уровне записей: разделы, производитель, категории...
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

$log->PutEntry($result['id'],'открыл раздел Прайс-лист',NULL,600, NULL,SecStr(implode(', ', $pr_descr)));	
	


//пропишем удаление
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',603)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	$id=abs((int)$_GET['id']);
	
	
	$_pi=new plpositem;
	$pi=$_pi->GetItemById($id);
	if($_pi->CanDelete($id)){
		$_pi->Del($id);
	
		$log->PutEntry($result['id'],'удалил позицию прайс-листа',NULL,603, NULL,  'позиция '.SecStr($pi['name']),$id);
		if($pi['parent_id']!=0){
			$log->PutEntry($result['id'],'удалил опцию прайс-листа',NULL,603, NULL,  'опция '.SecStr($pi['code']).' '.SecStr($pi['name']),$pi['parent_id']);
		}
			
	
	}
	header("Location: pricelist.php");
	die();
}




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3)));



$smarty->display('top.html');
unset($smarty);


$_menu_id=48;
	include('inc/menu.php');
	
	/*echo '<pre>';
	print_r($_COOKIE);
	echo '</pre>';
	*/
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//покажем лог
	$log=new PlPosGroup;
	$prefix='_1';
	
	//Разбор переменных запроса
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	
	if(isset($_GET['memory'.$prefix])) $memory=abs((int)$_GET['memory'.$prefix]);
	elseif(isset($_POST['memory'.$prefix])){
		$memory=abs((int)$_POST['memory'.$prefix]);
	}else $memory=0;
	

	
	
	
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	$decorator_for_equipment=new DBDecorator;
	
	//Запомним КП, откуда пришли
	if(isset($_GET['from_kp'.$prefix])&&(strlen($_GET['from_kp'.$prefix])>0)){
		 
		$decorator->AddEntry(new UriEntry('from_kp',$_GET['from_kp'.$prefix]));
	
	} 
	
	//запомним лид, откуда пришли
	if(isset($_GET['lead_id'.$prefix])&&(strlen($_GET['lead_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('lead_id',$_GET['lead_id'.$prefix]));
	
	} 
	
	if(isset($_GET['tz_id'.$prefix])&&(strlen($_GET['tz_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('tz_id',$_GET['tz_id'.$prefix]));
	
	} 
	
	
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.id',abs((int)$_GET['id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		
		
		$decorator_for_equipment->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
		
	}
	
	if(isset($_GET['pl_id'.$prefix])&&(strlen($_GET['pl_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.id',abs((int)$_GET['pl_id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('pl_id',$_GET['pl_id'.$prefix]));
	}
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	
	}
	
	
	if(isset($_GET['producer_id'.$prefix])&&(strlen($_GET['producer_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.producer_id',abs((int)$_GET['producer_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('producer_id',$_GET['producer_id'.$prefix]));
		
		$decorator_for_equipment->AddEntry(new SqlEntry('p.producer_id',abs((int)$_GET['producer_id'.$prefix]), SqlEntry::E));
		
	}
	
	
 
	if(isset($_GET['price_kind_id'.$prefix])&&(strlen($_GET['price_kind_id'.$prefix])>0)){
		//$decorator->AddEntry(new SqlEntry('plp.price_kind_id',abs((int)$_GET['price_kind_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		
		//$decorator_for_equipment->AddEntry(new SqlEntry('p.price_kind_id',abs((int)$_GET['price_kind_id'.$prefix]), SqlEntry::E));
		 
	}
	
	
	
	
	
	$current_group_id=0;
	if(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
		$current_group_id=$_GET['group_id'.$prefix];
		
		 
	}
	
	
	if(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('three_group_id',$_GET['three_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id'.$prefix]), SqlEntry::E));
		$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id'.$prefix]), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		
		
		
		//добавить обработку подгрупп...
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id'.$prefix]), SqlEntry::E));
		
		
		$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id'.$prefix]), SqlEntry::E));
		
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
			
			$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
	}elseif(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id'.$prefix]), SqlEntry::E));
		
		$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id'.$prefix]), SqlEntry::E));
		
		//добавить обработку подгрупп...
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
			
			$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		$decorator_for_equipment->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name'.$prefix])&&(strlen($_GET['name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name'.$prefix]));
	}
	
	if(isset($_GET['producer_name'.$prefix])&&(strlen($_GET['producer_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pp.name',SecStr($_GET['producer_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('producer_name',$_GET['producer_name'.$prefix]));
	}
	
	
	
	
	if(isset($_GET['dimension_id'.$prefix])&&(strlen($_GET['dimension_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_GET['dimension_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('dimension_id',$_GET['dimension_id'.$prefix]));
	}
	
	
	
	if(isset($_GET['price'.$prefix])&&(strlen($_GET['price'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('price',$_GET['price'.$prefix]));
	}
	
	
	if(isset($_GET['doShow'.$prefix])&&(strlen($_GET['doShow'.$prefix])>0)){
	//	$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('doShow',$_GET['doShow'.$prefix]));
	}
	
	
	//языки
	if(isset($_GET['lang_rus'.$prefix])&&(($_GET['lang_rus'.$prefix])==1)){
		//$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('lang_rus',1));
	}else{
		if(!(isset($_GET['lang_en'.$prefix])&&(($_GET['lang_en'.$prefix])==1))){
			$decorator->AddEntry(new UriEntry('lang_rus',1));
		}else $decorator->AddEntry(new UriEntry('lang_rus',0));
	}
	
	if(isset($_GET['lang_en'.$prefix])&&(($_GET['lang_en'.$prefix])==1)){
		//$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('lang_en',1));
	}else{
		$decorator->AddEntry(new UriEntry('lang_en',0));
	}
	
	 
	
	
	//фильтроварть оборудование по закрытым подразделам, разделам, пр-лям
	$restricted_groups=array();
//	$restricted_two_groups=array();
	$restricted_producers=array();
	
	
	$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
	$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
	
	if(count($restricted_producers)>0){
		$decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
		$decorator_for_equipment->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
	}
	
	if(count($restricted_groups)>0){
		$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
		$decorator_for_equipment->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));	
	}
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=3;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	$decorator->AddEntry(new SqlOrdEntry('pp.name',SqlOrdEntry::ASC));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::ASC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::DESC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::ASC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	$decorator_for_equipment->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	 
	 
	 
	$log->SetAuthResult($result);
	

	
	 $llg=$log->ShowPos('pl/list_new.html',$decorator,  $decorator_for_equipment, $from,$to_page, 
	$au->user_rights->CheckAccess('w',602), 
	 $au->user_rights->CheckAccess('w',603),
	 false,
	 $au->user_rights->CheckAccess('w',70)||$au->user_rights->CheckAccess('w',150)||$au->user_rights->CheckAccess('w',151), 
	 $au->user_rights->CheckAccess('w',70), 
	 $au->user_rights->CheckAccess('w',150),
	  $au->user_rights->CheckAccess('w',151), 
	  $au->user_rights->CheckAccess('w',601),
	  $au->user_rights->CheckAccess('w',605),
	  $au->user_rights->CheckAccess('w',92),
	  $prefix,
	   $au->user_rights->CheckAccess('w',606), $memory,
	   $au->user_rights->CheckAccess('w',696),
	   $result,
	   $au->user_rights->CheckAccess('w',741),
	   $au->user_rights->CheckAccess('w',742),	   
	   $au->user_rights->CheckAccess('w',743),
	   $au->user_rights->CheckAccess('w',744),	   
	   $au->user_rights->CheckAccess('w',745),
	   $au->user_rights->CheckAccess('w',746),	   
	   $au->user_rights->CheckAccess('w',747),
	   $au->user_rights->CheckAccess('w',748),	   
	   $au->user_rights->CheckAccess('w',749),
	   $au->user_rights->CheckAccess('w',750),
	   $au->user_rights->CheckAccess('w', $pki['print_object_id']),
	   $au->user_rights->CheckAccess('w',751),
	   $au->user_rights->CheckAccess('w',752),
	   $au->user_rights->CheckAccess('w',753),
	   $au->user_rights->CheckAccess('w',754),
	   $au->user_rights->CheckAccess('w',755),
	   
	   $au->user_rights->CheckAccess('w',756),
	   $au->user_rights->CheckAccess('w',757),
	   $au->user_rights->CheckAccess('w',758),
	   $au->user_rights->CheckAccess('w',759), 
	   $au->user_rights->CheckAccess('w',768),
	   $au->user_rights->CheckAccess('w',116),
	   $au->user_rights->CheckAccess('w',815),
	   $au->user_rights->CheckAccess('w',842)
	  );
	
	
	$sm->assign('log',$llg);


	
	
	
	$content=$sm->fetch('pl/pl.html');	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
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
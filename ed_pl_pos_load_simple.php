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

require_once('classes/positem.php');
require_once('classes/pl_positem.php');

require_once('classes/posdimgroup.php');
require_once('classes/posdimitem.php');
require_once('classes/posgroupgroup.php');
require_once('classes/posgroupitem.php');

require_once('classes/pl_proditem.php');
require_once('classes/pl_prodgroup.php');

require_once('classes/pl_disgroup.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/pl_dismaxvalitem.php');
require_once('classes/pl_disitem.php');

require_once('classes/pl_groupgroup.php');
require_once('classes/pl_groupitem.php');
require_once('classes/pl_posgroup.php');
require_once('classes/pl_currgroup.php');
require_once('classes/pl_curritem.php');
require_once('classes/pl_pospriceitem.php');


require_once('classes/kp_form_item.php');
require_once('classes/kp_form_group.php');
require_once('classes/PHPExcel.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Позиция прайс-листа');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new PosItem;
//$lc=new LoginCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=1;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=67;
	break;
	case 1:
	$object_id=68;
	break;
	case 2:
	$object_id=69;
	break;
	default:
	$object_id=67;
	break;
}
//echo $object_id;
//die();
if(!$au->user_rights->CheckAccess('w',$object_id)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if($action==0){

	
}


if(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
}
 




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	?>
     <a href="ed_pl_position.php?action=1&id=<?=$_POST['id']?>">в карту оборудования.</a><br />
<br />

    <?
		
		$parent_id=abs((int)$_POST['id']);
	/*	$currency_id=abs((int)$_POST['currency_id']);
		$price_kind_id=abs((int)$_POST['price_kind_id']);
		*/
		$_plgg=new PlGroupItem; $_pi=new PlPosItem;
		
		
		$objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
		$objPHPExcel->setActiveSheetIndex(0);
		$aSheet = $objPHPExcel->getActiveSheet();
		
			$index=0;
		
		 $last_group='';  
		 
		 $pl_group_id=28; $currency_id=3; $price_kind_id=3;
		 
		 foreach($aSheet->getRowIterator() as $row){
			  //получим итератор ячеек текущей строки
			  $cellIterator = $row->getCellIterator();
			  $cellIterator->setIterateOnlyExistingCells(false);
			  
			  echo '<h1>Ряд '.$index.'</h1>';
			  
			  $iter_index=0; $params=array(); 
			  
			  $values=array();
			  foreach($cellIterator as $cell){
				  	$value=iconv('utf-8', 'cp1251//TRANSLIT', $cell->getCalculatedValue());
					//echo $iter_index.') '.$value.'<br>';
					
					
					$values[]=$value;
					$iter_index++;
			  }
			  
			  if(($values[0]!='')&&($values[1]!='')&&($values[2]!='')){
				  $params=array();
				  $params['code']=''; //SecStr($values[1]);
				  //$params['group_id']=abs((int)$_POST['group_id']);
				  
				  $params['group_id']=$editing_user['group_id'];
				  $params['parent_id']=$editing_user['id'];
				  
				 
					  $params['name']=SecStr($values[0]);
					  $params['name_en']=SecStr($values[1]);
				  
				   
				  
				  $params['dimension_id']=$editing_user['dimension_id'];
				  $params['producer_id']=$editing_user['producer_id'];
				  
				  $params['pl_group_id']=$pl_group_id;
										  
				  $pos_id=$ui->Add($params);
			  	
				 $_pi=new PlPosItem;
			       
						//$pl_id=$_pi->Add(array('name'=>SecStr($last_group)));
						$params=array();
						$params['position_id']=$pos_id;
						$pl_id=$_pi->Add($params);
							
					
					$_price=new PlPositionPriceItem;
						
					$price_params=array();
					$price_params['price']=round((float)str_replace(",",".",$values[2]),0);
					$price_params['currency_id']=$currency_id;
					$price_params['price_kind_id']=$price_kind_id;
					//$test_price=$_price->GetItemByFields(array('pl_position_id'=>$pl_id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
					
				 	$price_params['pl_position_id']=$pl_id;
					$_price->Add($price_params);
					 
			  
			  }
			  
			  
			  $index++;
		 }
		
	?>
    <br />
<br />

    <a href="ed_pl_position.php?action=1&id=<?=$_POST['id']?>">в карту оборудования.</a>
    <?


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>
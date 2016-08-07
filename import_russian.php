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


$items_per=50;

if(isset($_FILES['file']['tmp_name'])){
	copy($_FILES['file']['tmp_name'], ABSPATH.'tmp/'.SecureCyr($_FILES['file']['name']));
	
	
	header("Location: import_russian.php?file=".SecureCyr($_FILES['file']['name'])."&from=1");
	die();
}


$file=$_GET['file'];
$from=abs((int)$_GET['from']);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	?>
     

    <?
		
		$objPHPExcel = PHPExcel_IOFactory::load(ABSPATH.'tmp/'.$file);
		$objPHPExcel->setActiveSheetIndex(0);
		$aSheet = $objPHPExcel->getActiveSheet();
		
		$index=0;
		
		foreach($aSheet->getRowIterator() as $row){
			  $index++;
			  
			  
			  if($index<$from) continue;
			  if($index>=($from+$items_per)) continue;
			  
			  echo '<h1>Ряд '.$index.'</h1>';
			  
			  //получим итератор ячеек текущей строки
			  $cellIterator = $row->getCellIterator();
			  $cellIterator->setIterateOnlyExistingCells(false);
			  
			   $values=array();
			  foreach($cellIterator as $cell){
				  	$value=iconv('utf-8', 'cp1251//TRANSLIT', $cell->getCalculatedValue());
					$values[]=$value;
			  }
			  
			  //print_r($values);
			  
			  if(($values[2]!="")&&($values[3]!="")){
					echo 'found data: '.$values[2].' and '.$values[3].'  <br>';
					
					$sql='select * from catalog_position where id in('.implode(', ',explode('; ',$values[0])).') ';
					$set=new mysqlset($sql);
					$rs=$set->getResult();
					$rc=$set->getResultNumRows();
					for($i=0; $i<$rc; $i++){
						$f=mysqli_fetch_array($rs);
						
						$params=array();
						$params['name']=SecStr($values[3]);
						$params['name_en']=SecStr($values[2]);
						$ui->Edit($f['id'], $params);
					}
			  }
		}
		
		
		if(($from+$items_per)>$index){
			//конец	
			?>
            <a href="pricelist.php">закончить работу</a>
            <script type="text/javascript">
			function Away(){
				location.href='pricelist.php';
				
			}
			window.setTimeout(function(){ Away(); }, 2000);
			</script>
            <?
		}else{
			// не конец
			?>
			<a href="import_russian.php?file=<?=$file?>&from=<?=($from+$items_per)?>">продолжить работу</a>
            <script type="text/javascript">
			function Away(){
				location.href='import_russian.php?file=<?=$file?>&from=<?=($from+$items_per)?>';
				
			
			}
			window.setTimeout(function(){ Away(); }, 2000);
			</script>
            <?
		}
		
		
		
	?>
    <br />
<br />

   
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
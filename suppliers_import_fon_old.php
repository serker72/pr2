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
require_once('classes/actionlog.php');
require_once('classes/suppliersgroup.php');

require_once('classes/supplieritem.php');

require_once('classes/suppliercontactgroup.php');
require_once('classes/suppliercontactkindgroup.php');

require_once('classes/suppliercontactgroup.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactdatagroup.php');
require_once('classes/suppliercontactkindgroup.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/opfgroup.php');
require_once('classes/opfitem.php');
require_once('classes/supcreator.php');


require_once('classes/supplier_notesgroup.php');
require_once('classes/supplier_notesitem.php');


require_once('classes/supplier_cities_item.php');
require_once('classes/supplier_city_item.php');

require_once('classes/supplier_branches_item.php');
require_once('classes/faitem.php');

require_once('classes/supplier_responsible_user_item.php');


require_once('classes/PHPExcel.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Импорт контрагентов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['from'])){
	if(!isset($_POST['from'])){
		$from=0;
	}else $from=abs((int)$_POST['from']); 
}else $from=abs((int)$_GET['from']);



if(!$au->user_rights->CheckAccess('w',87)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$copy_path=ABSPATH.'upload/files/supplier_shema/';
$total_counter=0; $max_records=200;


if(isset($_FILES['import_file'])){
	
	if(file_exists($copy_path.'suppliers.xls')) unlink($copy_path.'suppliers.xls');
		copy($_FILES['import_file']['tmp_name'], $copy_path.'suppliers.xls');
		
		header('Location: suppliers_import_fon.php?from=2');
		die();
	
}









//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	$sm1=new SmartyAdm;
	
	
	$load_arr=array();
	if(isset($from)){
		
		$inputFileName = $copy_path.'suppliers.xls';
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		
		/** Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true,true,true);
		
		/*echo '<pre>';
		var_dump($sheetData);
		echo '</pre>';*/
		
		$total_counter=count($sheetData);
	
				
		
		for($i=$from; $i<$from+$max_records; $i++){
			//читаем строку
			if(($sheetData[$i]['S']===NULL)||($sheetData[$i]['S']=="")) continue;
			$load_arr[]=$sheetData[$i];
		}
		
		
		
		
		//перекодировка
		$na=array();
		foreach($load_arr as $k=>$v){
			$la=array();
			foreach($v as $kk=>$vv){
				$la[$kk]=iconv('utf-8','windows-1251', $vv);	
			}
			$na[$k]=$la;
		}
		$load_arr=$na;
		
		/*echo '<pre>';
			var_dump($load_arr);
			echo '</pre>';*/
		
		$_opg=new OpfGroup;
		$opg=$_opg->GetItemsArr();
		$_si=new SupplierItem;
		$lc=new SupCreator;
		
		$log=new ActionLog;
	
		
		
		foreach($load_arr as $k=>$v){
			//echo ($k+$from).'. ';
			//анализируем название S
			$v['S']=eregi_replace('"','',$v['S']);
			
			//найти ОПФ... перебрать все опф до совпадения... если есть совпадение по опф - то вводим также опф
			$opf_detected=false; $detected_opf=NULL;
			foreach($opg as $opf){
				if(eregi("^".$opf['name']."([ ]+)",$v['S'])||eregi("([ ]+)".$opf['name']."$",$v['S'])){
					$opf_detected=true;
					$detected_opf=$opf;	
				}
			}
			
			$params=array();
			if($opf_detected){
				//echo ' <strong>нашли ОПФ: '.$detected_opf['name'].'</strong> ';
				$v['S']=eregi_replace("^".$detected_opf['name']."([ ]+)", "", $v['S']);
				$v['S']=eregi_replace("([ ]+)".$detected_opf['name']."$", "", $v['S']);
				
				$params['opf_id']=$detected_opf['id'];	
				
			}else $params['opf_id']=0;
			
			//echo $v['J'];
			$params['full_name']=SecStr($v['S']);
			$params['org_id']=abs((int)$result['org_id']);
			$params['ur_or_fiz']=0;
			
			$lc->ses->ClearOldSessions();
			$params['code']=$lc->GenLogin($result['id']);
			
			$params['time_from_h']=9;
			$params['time_from_m']=0;
			$params['time_to_h']=18;
			$params['time_to_m']=0;
		
			
			//отрасль
			
			if(($v['BA']!==NULL)&&($v['BA']!="")){
				$_bi=new SupplierBranchesItem;
				$bi=$_bi->GetItemByFields(array('name'=>SecStr($v['BA'])));		
				if($bi!==false){
					$params['branch_id']=$bi['id'];	
				}
			}
				
			
			$supplier_id=$_si->Add($params);
			
			
			//echo '<p>';
			
			if($supplier_id>0){
				$log->PutEntry($result['id'],'создал контрагента',NULL,86,NULL,$params['full_name'],$supplier_id);
			
			
				foreach($params as $pk=>$pv){
					$log->PutEntry($result['id'],'создал контрагента',NULL,86, NULL, 'в поле '.$pk.' установлено значение '.$pv,$supplier_id);
				}
				
				
				//добавим ответственного - Епишкин 65
				$_user=new usersitem;
				$_sri=new SupplierResponsibleUserItem;
				$_sri->Add(array(
					'supplier_id'=>$supplier_id,
					'user_id'=>65
				));
				
				$user=$_user->GetItemById(65);
			  	$description=SecStr($user['name_s'].' '.$user['login']).',  ';
				$log->PutEntry($result['id'],'добавил ответственного сотрудника в карту контрагента',NULL,910,NULL,$description,$supplier_id);	
				
				
				//внесем контакты
				
				$contacts_mask=array(
					array('index_table'=>'P',
						  'kind_id'=>5
						  ),
					array('index_table'=>'Q',
						  'kind_id'=>5
						  ),	  	
					array('index_table'=>'M',
						  'kind_id'=>1
						  ),
					array('index_table'=>'N',
						  'kind_id'=>3
						  ),
					array('index_table'=>'DB',
						  'kind_id'=>1
						  ),						  	  
					array('index_table'=>'O',
						  'kind_id'=>2
						  )
					
				);
				
				
				$ri_id=0; $was_data=array();
				foreach($contacts_mask as $ck=>$cv){
					if(($v[$cv['index_table']]!==NULL)&&($v[$cv['index_table']]!="")){
						if($ri_id==0){
						  $ri=new SupplierContactItem;
						  
						  $name=SecStr($v['E'].' '.$v['F']);
						  $position=SecStr($v['G']);
						  
						  
						  $ri_id=$ri->Add(array(
									  'name'=>$name,
									  'position'=>$position,
									  
									  'supplier_id'=>$supplier_id
								  ));
						  
						  $log->PutEntry($result['id'],'добавил контакт контрагенту', NULL,165,NULL,'имя:  должность: ',$supplier_id);	
						}
						
						if(!in_array(array($v[$cv['index_table']], $cv['kind_id']), $was_data)){ 
						
							$ri=new SupplierContactDataItem;
							$ri->Add(array(
								'value'=>SecStr($v[$cv['index_table']]),
								'contact_id'=>$ri_id,
								'kind_id'=>$cv['kind_id']
							));
							
							
							$log->PutEntry($result['id'],'добавил данные контакта контрагенту', NULL,165,NULL,SecStr($v[$cv['index_table']]),$supplier_id);
							
							$was_data[]=array($v[$cv['index_table']], $cv['kind_id']);
						}
					}
					
				}
				
				
				//занесем ГОРОД, если такой есть в нашем словаре
				if(($v['B']!==NULL)&&($v['B']!="")){
					$_city=new SupplierCityItem;
					$city=$_city->GetItemByFields(array('name'=>SecStr($v['B'])));
					if($city!==false){
						$_sc=new SupplierCitiesItem;
						$_sc->Add(array(
							'supplier_id'=>$supplier_id,
							'city_id'=>$city['id']
						));
						$log->PutEntry($result['id'],'добавил город контрагенту', NULL,87,NULL,SecStr($city['name']),$supplier_id);
					}
				}
				
				//занесем фактический адрес
				if(
				 
				 (($v['C']!==NULL)&&($v['C']!=""))
				
				){
					$note=$v['B'];
					if(strlen($note)>0) $note.=',';
					
					$note.=' '.$v['C'];
					
					$_fa=new FaItem;
					$_fa->Add(array(
						'user_id'=>$supplier_id,
						'address'=>SecStr($note)
					));
					$log->PutEntry($result['id'],'добавил фактический адрес контрагенту', NULL,87,NULL,SecStr($note),$supplier_id);
				}
				
				
				//обработаем   прримечания
				 
				if(
				(($v['BJ']!==NULL)&&($v['BJ']!="")) 
				 
				
				){
					$note=$v['BJ'];//.' '.$v['G'].' '.$v['H'] ;
					
					$ri=new SupplierNotesItem;
					$ri->Add(array(
								'note'=>SecStr($note,9),
								'pdate'=>time(),
								'user_id'=>$supplier_id,
								'posted_user_id'=>$result['id']
							));
					
					$log->PutEntry($result['id'],'добавил примечания по контрагенту', NULL,87, NULL,SecStr($note,9),$supplier_id);
					
				}
				
				
				//проверим возможность утверждения и утвердим...
				$can_confirm=$_si->CanConfirmActive($supplier_id, $rss);
				if($can_confirm){
					$params=array();
					$params['is_active']=1;
					$params['user_id']=$result['id'];
					$params['active_pdate']=time();
					
					$_si->Edit($supplier_id, $params);
					$log->PutEntry($result['id'],'утвердил контрагента',NULL,89, NULL, NULL,$supplier_id);	
				}
			 
			}
		}
		
		$sm1->assign('from', $from);
		$sm1->assign('total_counter',$total_counter);
		$sm1->assign('max_records',$max_records);
		
		$sm1->assign('to_proceed', count($load_arr));
		
	}
	
	
	
	$c=$sm1->fetch('suppliers/import_fon.html');
	
	$sm->assign('users',$c);
	
	
	$content=$sm->fetch('suppliers/suppliers_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>
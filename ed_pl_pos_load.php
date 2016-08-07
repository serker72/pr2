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

if(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',68)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$params=array();
	
	
	$params['code']=SecStr($_POST['code']);
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$group_id=abs((int)$_POST['group_id']);
	$group_id2=abs((int)$_POST['group_id2']);
	$group_id3=abs((int)$_POST['group_id3']);
	
	if($group_id3>0) $params['group_id']=$group_id3;
	elseif($group_id2>0) $params['group_id']=$group_id2;
	else $params['group_id']=$group_id;
	
	
	$params['name']=SecStr($_POST['name']);
	 
	
	$params['dimension_id']=abs((int)$_POST['dimension_id']);
	$params['producer_id']=abs((int)$_POST['producer_id']);
	
	$params['pl_group_id']=abs((int)$_POST['pl_group_id']);
	
	
	$params['notes']=SecStr($_POST['notes']);
	 
	
	$params['txt_for_kp']=SecStr($_POST['txt_for_kp']);
	$params['photo_for_kp']=SecStr($_POST['photo_for_kp']);
	
	
	if(isset($_POST['is_install'])) $params['is_install']=1;
	else $params['is_install']=0;
	
	if(isset($_POST['is_delivery'])) $params['is_delivery']=1;
	else $params['is_delivery']=0;
	
	$ui->Edit($id,$params);
	
	
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			if($k=='text_for_kp') $value=SecStr(substr(strip_tags($v), 0, 255).'...');
						else $value=SecStr($v);
			
			$log->PutEntry($result['id'],'редактировал позицию каталога',NULL,68, NULL, 'в поле '.$k.' установлено значение '.$value,$id);		
		}
	}
	
	
	//работа с позицией п.л.
	$_pi=new PlPosItem;
	$test_pl=$_pi->GetItemByFields(array('position_id'=>$id));
	
	if(isset($_POST['to_pl'])){ //галочка стоит
		if($test_pl!==false){ //есть в базе в пл
				//правим	
				
				$params1=array();
				//$params1['price']=abs((float)str_replace(",",".",$_POST['price']));
				$params1['kp_form_id']=abs((int)$_POST['kp_form_id']);
				
				//определим, какая скидка активна - заносим ее
				//если неактивно ни одной - обнуляем скидку
				//print_r($_POST);
				
				$active_discount_id=0;
				foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					if((trim($v)!="")&&(abs((int)$v)>0)){
					//echo($_id);	
						$active_discount_id=$_id;
						break;
					}
				}
				
				if($active_discount_id!=0){
					$params1['discount_id']=$active_discount_id;
					$params1['discount_value']=abs((float)str_replace(",",".",$_POST['discount_'.$active_discount_id]));
					$params1['discount_rub_or_percent']=abs((int)$_POST['discount_rub_or_percent_'.$active_discount_id]);
					
				}else{
					$params1['discount_id']=1;
					$params1['discount_value']=0;
					$params1['discount_rub_or_percent']=0;
				}
					
				
				
				$_pi->Edit($test_pl['id'], $params1);
				
				
				foreach($params1 as $kk=>$vv){
					if($pi[$kk]!=$vv){
						if($kk=='text_for_kp') $value=SecStr(substr(strip_tags($vv), 0, 255).'...');
						else $value=SecStr($vv);
						
						$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$test_pl['id']);
						
						$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$test_pl['position_id']);
					
					}
				}
				
				
				
				//внесем цену
				$_curr=new PlCurrItem;
				$_price=new PlPositionPriceItem;
					
				$price_params=array();
				$price_params['price']=abs((float)str_replace(",",".",$_POST['price']));
				$price_params['currency_id']=abs((float)str_replace(",",".",$_POST['currency_id']));
				$price_params['price_kind_id']=PRICE_KIND_DEFAULT_ID;
				$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
				
				$curr=$_curr->GetItemById($price_params['currency_id']);
				
				$do_log_price=false;
				if($test_price===false){
					//цены нет, внести ее	
					$price_params['pl_position_id']=$id;
					$_price->Add($price_params);
					$do_log_price=true;
					
				}else{
					//цена есть, редактировать ее
					$_price->Edit($test_price['id'], $price_params);
					$do_log_price=($price_params['price']!=$test_price['price']);
				}
				 
				if($do_log_price) $log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				
				
				//работаем с ограничениями скидок
				//разобрать блок ограничений
				//если текущее ограничение равно пустой строке - удалить если есть такое
				//если равно строке - внести строку
				if($au->user_rights->CheckAccess('w',605)) foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					
					$_mvi=new PlDisMaxValItem;
					$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$test_pl['id'], 'discount_id'=>$_id));
					$_dis=new PlDisItem;
					$_test_dis=$_dis->GetItemById($vv);
					
					if(trim($_POST['dl_value_'.$_id])==""){
						if($_test_mvi!==false){
							$_mvi->Del($_test_mvi['id']);
							//запись в журнал о снятии ограничения	
							$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$test_pl['id']);
							
							$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$test_pl['position_id']);
						}
					}else{
						if($_test_mvi!==false){
							$m_params=array();
							 
							$m_params['value']=abs((float)$_POST['dl_value_'.$_id]);
							$m_params['rub_or_percent']=abs((int)$_POST['dl_rub_or_percent_'.$_id]);
							
							$_mvi->Edit($_test_mvi['id'], $m_params);
						}else{
							$m_params=array();
							$m_params['pl_position_id']=$test_pl['id'];
							$m_params['discount_id']=$_id;
							$m_params['value']=abs((float)$_POST['dl_value_'.$_id]);
							$m_params['rub_or_percent']=abs((int)$_POST['dl_rub_or_percent_'.$_id]);
							$_mvi->Add($m_params);
						}
						if($m_params['rub_or_percent']==0) $descr='руб.';
						elseif($m_params['rub_or_percent']==1) $descr='%';
						
						//print_r($m_params);
						
						//запись в журнал об установке ограничения
						$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$test_pl['id']);
						
						$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$test_pl['position_id']);
					}
				}
				
				
				
				
		}else{ //нет в базе в пл.
			//добавим
				$params1=array();
				$params1['position_id']=$id;
				
				//$params1['price']=abs((float)str_replace(",",".",$_POST['price']));
				$params1['kp_form_id']=abs((int)$_POST['kp_form_id']);
				
				$active_discount_id=0;
				foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					if((trim($v)!="")&&(abs((int)$v)>0)){
					//echo($_id);	
						$active_discount_id=$_id;
						break;
					}
				}
				
				if($active_discount_id!=0){
					$params1['discount_id']=$active_discount_id;
					$params1['discount_value']=abs((float)str_replace(",",".",$_POST['discount_'.$active_discount_id]));
					$params1['discount_rub_or_percent']=abs((int)$_POST['discount_rub_or_percent_'.$active_discount_id]);
					
				}else{
					$params1['discount_id']=1;
					$params1['discount_value']=0;
					$params1['discount_rub_or_percent']=0;
				}
					
					
				$code1=$_pi->Add($params1);
				foreach($params1 as $kk=>$vv){
					
					if($kk=='text_for_kp') $value=SecStr(substr(strip_tags($vv), 0, 255).'...');
					else $value=SecStr($vv);
						
					
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$code1);
					
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': в поле '.$kk.' установлено значение '.$value,$id);
					
				}
				
				
				//внесем цену
				$_curr=new PlCurrItem;
				$_price=new PlPositionPriceItem;
					
				$price_params=array();
				$price_params['price']=abs((float)str_replace(",",".",$_POST['price']));
				$price_params['currency_id']=abs((float)str_replace(",",".",$_POST['currency_id']));
				$price_params['price_kind_id']=PRICE_KIND_DEFAULT_ID;
				$test_price=$_price->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
				
				$curr=$_curr->GetItemById($price_params['currency_id']);
				
				$do_log_price=false;
				if($test_price===false){
					//цены нет, внести ее	
					$price_params['pl_position_id']=$id;
					$_price->Add($price_params);
					$do_log_price=true;
					
				}else{
					//цена есть, редактировать ее
					$_price->Edit($test_price['id'], $price_params);
					$do_log_price=($price_params['price']!=$test_price['price']);
				}
				 
				if($do_log_price) $log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': Установлена цена '.$price_params['price'].' '.SecStr($curr['signature']),$id);
				
				
				//работаем с ограничениями скидок
				//разобрать блок ограничений
				//если текущее ограничение равно пустой строке - удалить если есть такое
				//если равно строке - внести строку
				if($au->user_rights->CheckAccess('w',605)) foreach($_POST as $k=>$v) if(eregi('^discount_[0-9]+$', $k)){
					$_id=abs((int)eregi_replace('^discount_', '', $k));	
					
					$_mvi=new PlDisMaxValItem;
					$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$code1, 'discount_id'=>$_id));
					$_dis=new PlDisItem;
					$_test_dis=$_dis->GetItemById($vv);
					
					if(trim($_POST['dl_value_'.$_id])==""){
						if($_test_mvi!==false){
							$_mvi->Del($_test_mvi['id']);
							//запись в журнал о снятии ограничения	
							$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$test_pl['id']);
							$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($params['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$test_pl['position_id']);
						}
					}else{
						if($_test_mvi!==false){
							$m_params=array();
							 
							$m_params['value']=abs((float)$_POST['dl_value_'.$_id]);
							$m_params['rub_or_percent']=abs((int)$_POST['dl_rub_or_percent_'.$_id]);
							
							$_mvi->Edit($_test_mvi['id'], $m_params);
						}else{
							$m_params=array();
							$m_params['pl_position_id']=$code1;
							$m_params['discount_id']=$_id;
							$m_params['value']=abs((float)$_POST['dl_value_'.$_id]);
							$m_params['rub_or_percent']=abs((int)$_POST['dl_rub_or_percent_'.$_id]);
							$_mvi->Add($m_params);
						}
						if($m_params['rub_or_percent']==0) $descr='руб.';
						elseif($m_params['rub_or_percent']==1) $descr='%';
						
						//print_r($m_params);
						
						//запись в журнал об установке ограничения
						$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$test_pl['id']);
						
						$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($params['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$test_pl['position_id']);
					}
				}
				
				
				
				
				
		}
	}else{ //галочки нет
		
		//die();
		
		if($test_pl!==false){ //есть в базе в пл
			//удалить, если снимаем из формы
			if(($_POST['was_pl']==1)&&$au->user_rights->CheckAccess('w',603)&&$_pi->CanDelete($test_pl['id'])){
				$_pi->Del($test_pl['id']);
				$log->PutEntry($result['id'],'удалил позицию прайс-листа',NULL,603, NULL,'позиция '.SecStr($params['name']),$test_pl['id']);
				
				$log->PutEntry($result['id'],'удалил позицию прайс-листа',NULL,603, NULL,'позиция '.SecStr($params['name']),$test_pl['position_id']);
			}
		}else{ //нет в базе в пл.
			//не делать ничего
		}
	
	}
	
	 
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		if($editing_user['parent_id']==0) header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."#user_".$id);
		else header("Location: ed_pl_position.php?action=1&id=".$editing_user['parent_id']."#options");
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',68)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_pl_position.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: pricelist.php?group_id_1=".abs((int)$_POST['group_id'])."&two_group_id_1=".abs((int)$_POST['group_id2'])."&producer_id_1=".abs((int)$_POST['producer_id']).'&price_kind_id_1='.PRICE_KIND_DEFAULT_ID."#user_".$id);
		die();
	}
	
	
	die();
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
		$currency_id=abs((int)$_POST['currency_id']);
		$price_kind_id=abs((int)$_POST['price_kind_id']);
		
		$_plgg=new PlGroupItem; $_pi=new PlPosItem;
		
		
		$objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
		$objPHPExcel->setActiveSheetIndex(0);
		$aSheet = $objPHPExcel->getActiveSheet();
		
			$index=0;
		
		 $last_group='';
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
			  
			  if(($values[0]!='')&&($values[1]=='')&&($values[2]=='')&&($values[3]=='')){
				  echo '<strong>Это группа опций: '.$values[0].'</strong><br>';
				   $last_group=$values[0];
				   
				   
			  }elseif(($values[1]!='')&&($values[2]!='')&&($values[3]!='')){
				  echo '<strong>Это позиция, ее код='.$values[1].'</strong><br>';
				  echo '<strong>в группу опций '. $last_group.'</strong><br>';
				  
				  $test_plgg=$_plgg->GetItemByFields(array('name'=>SecStr($last_group)));
				  if($test_plgg===false) $test_plgg=$_plgg->GetItemByFields(array('name_en'=>SecStr($last_group)));
				  
				  $pl_group_id=0;
				  if($test_plgg===false){
						echo '<span style="color:red;">Группа опций '. $last_group.' НЕ найдена, создаем... </span><br>';  						
						$pl_group_id=$_plgg->Add(array('name'=>SecStr($last_group)));
				  }else{
						echo 'Группа опций '. $last_group.'    найдена, ок... <br>';    
						$pl_group_id=$test_plgg['id'];
				  }
				  
				  
				  $test_pos=$ui->GetItemByFields(array('code'=>SecStr($values[1]), 'parent_id'=>$parent_id));
				 
				  $translate_pos=$ui->GetItemByFields(array('code'=>SecStr($values[1])));
				  
				  
				 // var_dump($translate_pos);
				  $pos_id=0;
				  if($test_pos===false){
						echo '<span style="color:red;">Позиция '. $values[1].' НЕ найдена, создаем... </span><br>'; 
						
						$params=array();
						$params['code']=SecStr($values[1]);
						//$params['group_id']=abs((int)$_POST['group_id']);
						
						$params['group_id']=$editing_user['group_id'];
						$params['parent_id']=$editing_user['id'];
						
						if(($translate_pos==false)) $params['name']=SecStr($values[3]);
						else{
							$params['name']=$translate_pos['name'];
							$params['name_en']=$translate_pos['name_en'];
						}
						 
						
						$params['dimension_id']=$editing_user['dimension_id'];
						$params['producer_id']=$editing_user['producer_id'];
						
						$params['pl_group_id']=$pl_group_id;
						 						
						$pos_id=$ui->Add($params);
				  }else{
						echo 'Позиция '. $values[1].'   найдена, ок... <br>';    
						//$pl_group_id=$test_plgg['id'];
						$pos_id=$test_pos['id'];
						
						$params=array();
						 
				  }
				  
				  $_pi=new PlPosItem;
			      $test_pl=$_pi->GetItemByFields(array('position_id'=>$pos_id));
				  $pl_id=0;
				  if($test_pl===false){
						echo '<span style="color:red;">Позиция ПЛ '. $values[1].' НЕ найдена, создаем... </span><br>';  						
						//$pl_id=$_pi->Add(array('name'=>SecStr($last_group)));
						$params=array();
						$params['position_id']=$pos_id;
						$pl_id=$_pi->Add($params);
						
				  }else{
						echo 'Позиция ПЛ '. $values[1].'    найдена, меняем ей цену ок... <br>';    
						//$pl_group_id=$test_plgg['id'];
						$pl_id=$test_pl['id'];
						 
				  }
				  
				  
				  
					//внесем цену
					$_curr=new PlCurrItem;
					$_price=new PlPositionPriceItem;
						
					$price_params=array();
					$price_params['price']=abs((float)str_replace(",",".",$values[2]));
					$price_params['currency_id']=$currency_id;
					$price_params['price_kind_id']=$price_kind_id;
					$test_price=$_price->GetItemByFields(array('pl_position_id'=>$pl_id, 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
					
					 
					if($test_price===false){
						//цены нет, внести ее	
						echo '<span style="color:red;">ЦЕНА '. $values[2].' НЕ найдена, создаем... </span><br>'; 
						$price_params['pl_position_id']=$pl_id;
						$_price->Add($price_params);
					 
					}else{
						//цена есть, редактировать ее
					 
						echo 'ЦЕНА '. $values[2].'   найдена и равна '.$test_price['price'].', меняем ее... <br>';
						
						$_price->Edit($test_price['id'], $price_params);    
					}
				  
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
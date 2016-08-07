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
$smarty->assign("SITETITLE",'Скрытие неактивных опций, изменение цен и кодов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
  
$ui=new PosItem;
 


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	?>
    <div style=" text-align:left;">
     <a href="ed_pl_position.php?action=1&id=<?=$_POST['id']?>">в карту оборудования.</a><br />
<br />
	<h1>Скрытие неактивных опций, изменение цен и кодов</h1>
	
    <form method="post" enctype="multipart/form-data">
    <label for="id">ID оборудования</label>
   <!-- <input type="text" value="<?=$_POST['id']?>" id="id" name="id" />
    -->
    
    <select id="id" name="id" style="width:250px;"> 
    <?
	  $sql='select p.id, p.code, p.name from 
			  catalog_position as p
			 
			  where p.parent_id=0 and p.producer_id=1';
			  
			  $set=new mysqlset($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNUmRows();
			  
			  for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				?>
                <option value="<?=$f['id']?>" <? if((int)$f['id']==(int)$_POST['id']){?> selected="selected"<? }?>><?=$f['name']?></option>
                
                <?
				//echo $f['id'].' vs '.$_POST['id'].'<br>';
			  }
			  ?>
     </select> 
    
    <br />
<br />

    <h2>Загрузите опции:</h2>
    <input type="file" name="file" />
<br />
<br />
    
    <input type="submit" value="Go!" />
    </form>

    <?
		if(isset($_FILES['file'])){
			$parent_id=abs((int)$_POST['id']);
			$currency_id=2;//abs((int)$_POST['currency_id']);
			$price_kind_id=3;//abs((int)$_POST['price_kind_id']);
			
			$from_file_array=array();
			$from_program_array=array();
			
			$new_from_file_array=array();
			$new_from_program_data=array();
			
			$changed_array=array();
			
			
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
				  
				 // echo '<h1>Ряд '.$index.'</h1>';
				  
				  $iter_index=0; $params=array();   
				  
				  $values=array();
				  foreach($cellIterator as $cell){
						$value=iconv('utf-8', 'cp1251//TRANSLIT', $cell->getCalculatedValue());
						//echo $iter_index.') '.$value.'<br>';
						
						
						$values[]=$value;
						$iter_index++;
				  }
				  
				  
				   if(($values[0]!='')&&($values[1]=='')&&($values[2]=='')&&($values[3]=='')){
					 // echo '<strong>Это группа опций: '.$values[0].'</strong><br>';
					   $last_group=$values[0];
					   
					   
				  }elseif(($values[1]!='')&&($values[2]!='')&&($values[3]!='')){
					   $test_plgg=$_plgg->GetItemByFields(array('name'=>SecStr($last_group)));
					  if($test_plgg===false) $test_plgg=$_plgg->GetItemByFields(array('name_en'=>SecStr($last_group)));
					  
					  $pl_group_id=0;
					  if($test_plgg===false){
							//echo '<span style="color:red;">Группа опций '. $last_group.' НЕ найдена, создаем... </span><br>';  						
							//$pl_group_id=$_plgg->Add(array('name'=>SecStr($last_group)));
					  }else{
							//echo 'Группа опций '. $last_group.'    найдена, ок... <br>';    
							$pl_group_id=$test_plgg['id'];
					  }
					  
					  $from_file_array[]=array(
					  	'id'=>'',
					  	'code'=>($values[1]),
					  	'name_en'=>($values[3]),
						'price'=>abs((float)str_replace(",",".",$values[2])),
						'group_id'=>$pl_group_id
						
					  );
				  }
				  
				  
				  
				  
				  /*
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
							//$pl_group_id=$_plgg->Add(array('name'=>SecStr($last_group)));
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
													
							//$pos_id=$ui->Add($params);
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
							//$pl_id=$_pi->Add($params);
							
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
							//$_price->Add($price_params);
						 
						}else{
							//цена есть, редактировать ее
						 
							echo 'ЦЕНА '. $values[2].'   найдена и равна '.$test_price['price'].', меняем ее... <br>';
							
							//$_price->Edit($test_price['id'], $price_params);    
						}
					  
				  }*/
				  
				  $index++;
			 }
			 
			 
			 //
			  //получим данные из программы:
			  /* $from_file_array[]=array(
					  	'id'=>'',
					  	'code'=>SecStr($values[1]),
					  	'name_en'=>SecStr($values[3]),
						'price'=>abs((float)str_replace(",",".",$values[2])),
						'group_id'=>$pl_group_id
					  );*/
			  $sql='select p.id, p.code, p.name_en, p.pl_group_id as group_id, pr.price from 
			  catalog_position as p
			  inner join pl_position_price as pr on pl_position_id=p.id and pr.price_kind_id='.$price_kind_id.' and pr.currency_id='.$currency_id.' 
			  where p.parent_id='.$parent_id.' and p.is_install=0';
			  
			  $set=new mysqlset($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNUmRows();
			  
			  for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$from_program_array[]=array(
				'id'=>$f['id'],
					  	'code'=> ($f['code']),
					  	'name_en'=> ($f['name_en']),
						'price'=>abs((float)str_replace(",",".",$f['price'])),
						'group_id'=>$f['group_id']
				);  
			  }
			  
			  //print_r($from_file_array);
			 // print_r($from_program_array);
			 
			 //найти опции, которые есть в МТО и нет в ПЛ:
			 foreach($from_file_array as $k=>$file_data){
				
				$was=false;
				$program_data=array();
				foreach($from_program_array as $kk=>$program_data1){
					//сравнить сначала код, затем имя
					if($file_data['code']==$program_data1['code']){
						$was=true;
						$program_data=$program_data1;
						break;	
					}elseif($file_data['name_en']==$program_data1['name_en']){
						$was=true;
						$program_data=$program_data1;
						break;
					}
				}
				if(!$was){
					$new_from_file_array[]=$file_data;
				}
				 
				 
			 }
			 
			 //найти опции, которые есть в ПЛ, но нет в МТО
			 foreach($from_program_array as $kk=>$program_data1){
				 $was=false;
				 $program_data=array();
				 foreach($from_file_array as $k=>$file_data){
					 //сравнить сначала код, затем имя
					if($file_data['code']==$program_data1['code']){
						$was=true;
						$program_data=$program_data1;
						break;	
					}elseif($file_data['name_en']==$program_data1['name_en']){
						$was=true;
						$program_data=$program_data1;
						break;
					}
				 }
				 if(!$was){
					 $new_from_program_data[]=$program_data1;
				}
			 }
			 
			 
			 //найти измененные опции из ПЛ
			 foreach($from_program_array as $kk=>$program_data1){
				 $was=false;
				 $program_data=array();
				 foreach($from_file_array as $k=>$file_data){
					 //сравнить сначала код, затем имя
					if($file_data['code']==$program_data1['code']){
						$was=true;
						$program_data=$file_data;
						break;	
					}elseif(trim($file_data['name_en'])==(trim($program_data1['name_en']))){
						$was=true;
						$program_data=$file_data;
						break;
					}
				 }
				 if( $was){
					 //найти измененные поля
					 
					 
					// if(($program_data1['name_en']!=$program_data['name_en'])){
					 if(($program_data1['code']!=$program_data['code'])||
					  /*((trim($program_data1['name_en']))!=trim($program_data['name_en']))||*/
					  ($program_data1['price']!=$program_data['price'])){
					 	
						$changed_fields=array();
						if($program_data1['code']!=$program_data['code']) $changed_fields[]='code';
						if(trim($program_data1['name_en'])!=trim($program_data['name_en'])) $changed_fields[]='name_en';
						if($program_data1['price']!=$program_data['price']) $changed_fields[]='price';
						
						$program_data['changed_fields']=$changed_fields;
						
						$program_data['code_old']=$program_data1['code'];
						$program_data['name_en_old']=$program_data1['name_en'];
						$program_data['price_old']=$program_data1['price'];
						
						$program_data['id']=$program_data1['id'];
						
						
						$changed_array[]=$program_data;
					  }
				}
			 }
			 
			
			 
			 function SliceByGroups($arr){
				 $_plgg=new PlGroupItem;
				$arr1=array();
				$group_ids=array();
				foreach($arr as $k=>$v){
					if(!in_array(  $v['group_id'], $group_ids)) $group_ids[]=$v['group_id'];
					
									
				}
				
				foreach($group_ids as $k=>$v){
					
					$test_plgg=$_plgg->GetItemById($v);
					
					//var_dump($test_plgg);
					$items=array();
					
					foreach($arr as $kk=>$vv){
						if($vv['group_id']==$v){
							$items[]=$vv;	
						}
					}
					
					if(strlen($test_plgg['name_en'])==0) $group_name=$test_plgg['name'];
					else $group_name=$test_plgg['name_en'];
					$arr1[]=array(
						'group_id'=>$v,
						'group_name'=>$group_name,
						'items'=>$items				
					);
					
					 
				}
				
				
				return $arr1; 
			 }
			 
			 //разбить по опциям...
			 /*new_from_file_array 
			 print_r($changed_array); 
			 new_from_program_data*/
			/* $new_from_file_array_in=SliceByGroups($new_from_file_array );
			 $new_from_program_data_in=SliceByGroups($new_from_program_data );
			 $changed_array_in=SliceByGroups($changed_array );*/
			/* echo '<pre>';
			 print_r($new_from_file_array_in);
			 echo '</pre>'; */ 
			 
			 //блок вывода данных:
			 $eq=$ui->GetItemById($parent_id);
			 
			 
			 echo '<pre>';
			 print_r($new_from_program_data);
			 print_r($changed_array);
			 echo '</pre>';
			 
			 
			 $_pos=new PosItem;
			 //$_price=new plp
			 
			 //разберем удаляемый массив!
			 foreach($new_from_program_data as $k=>$data){
				 
				 $_pos->Edit($data['id'], array('is_active'=>0));
			 }
			 
			 //разберем изменяемый массив!
			 foreach($changed_array as $k=>$data){
				 foreach($data['changed_fields'] as $kk=>$field){
					if($field=='code')  $_pos->Edit($data['id'], array('code'=>$data['code'])); 
					elseif($field=='price'){
							//внесем цену
						$_curr=new PlCurrItem;
						$_price=new PlPositionPriceItem;
							
						$price_params=array();
						$price_params['price']=abs((float)str_replace(",",".",$data['price']));
						$price_params['currency_id']=$currency_id;
						$price_params['price_kind_id']=$price_kind_id;
						$test_price=$_price->GetItemByFields(array('pl_position_id'=>$data['id'], 'currency_id'=>$price_params['currency_id'], 'price_kind_id'=>$price_params['price_kind_id']));
						
						 
						if($test_price===false){
							//цены нет, внести ее	
							echo '<span style="color:red;">ЦЕНА '. $data['price'].' НЕ найдена, создаем... </span><br>'; 
							$price_params['pl_position_id']=$data['id'];
							$_price->Add($price_params);
						 
						}else{
							//цена есть, редактировать ее
						 
							echo 'ЦЕНА '. $data['price'].'   найдена и равна '.$test_price['price'].', меняем ее... <br>';
							
							$_price->Edit($test_price['id'], $price_params);    
						}	
					}
				 }
			 }
			 
			 
		}
		
	?>
    <br />
<br />

    <a href="ed_pl_position.php?action=1&id=<?=$_POST['id']?>">в карту оборудования.</a>
    </div>
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
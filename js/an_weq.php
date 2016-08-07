<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');


require_once('../classes/user_s_item.php');


require_once('../classes/billitem.php');

require_once('../classes/bill_in_item.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="toggle_scan_bill_eq")){
	$id=abs((int)$_POST['id']);
	$_ki=new BillItem;
	$_ki_in=new BillInItem;
	$ki=$_ki->GetItemById($id);
	
	
	if(($ki['status_id']==2)||($ki['status_id']==9)){
	  
	  if($ki['is_incoming']==1){
		  if($au->user_rights->CheckAccess('w',624)){
		 
			  
			  //получить позиции счета
			  //по каждой вызвать сканирование, собрать все результаты, вывести их
			  $pos=$_ki_in->GetPositionsArr($id,false);
			  
			  $output='';
			  foreach($pos as $k=>$v){
				  $args=array();
				 
				 /*and sp.position_id="'.$_t_arr[0].'" 
				and sp.pl_position_id="'.$_t_arr[1].'"
				and sp.pl_discount_id="'.$_t_arr[2].'"
				and sp.pl_discount_value="'.$_t_arr[3].'"
				and sp.pl_discount_rub_or_percent="'.$_t_arr[4].'"
				and sp.out_bill_id="'.$_t_arr[6].'"*/
				
				  $args[]= $v['position_id'].';'. $v['pl_position_id'].';'. $v['pl_discount_id'].';'. $v['pl_discount_value'].';'. $v['pl_discount_rub_or_percent'].';'.$v['quantity'].';'.$v['out_bill_id'];
				  
				  $_ki_in->ScanEq($id,$args,$output2, $ki,  true,   '. Позиция будет выровнена.');
				  $output.=$output2;
			  }
			  
			  
			  $ret=$output;
			  
		  }else{
			  $ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
		  }

	  }else{
		  if($au->user_rights->CheckAccess('w',292)){
		 
			  
			  //получить позиции счета
			  //по каждой вызвать сканирование, собрать все результаты, вывести их
			  $pos=$_ki->GetPositionsArr($id,false);
			  
			  $output='';
			  foreach($pos as $k=>$v){
				  $args=array();
				 
				 /*and sp.position_id="'.$_t_arr[0].'" 
				and sp.pl_position_id="'.$_t_arr[1].'"
				and sp.pl_discount_id="'.$_t_arr[2].'"
				and sp.pl_discount_value="'.$_t_arr[3].'"
				and sp.pl_discount_rub_or_percent="'.$_t_arr[4].'"
				and sp.kp_id="'.$_t_arr[6].'"*/
				 
				   $args[]= $v['position_id'].';'. $v['pl_position_id'].';'. $v['pl_discount_id'].';'. $v['pl_discount_value'].';'. $v['pl_discount_rub_or_percent'].';'.$v['quantity'].';'.$v['kp_id'];
				  
				  $_ki->ScanEq($id,$args,$output2, $ki, true,  '. Позиция будет выровнена.');
				  $output.=$output2;
			  }
			  
			  
			  $ret=$output;
			  
		  }else{
			  $ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
		  }
	  }
	}else{
		 $ret='<script>alert("Недопустимый статус счета.");</script>';
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_bill_eq")){
	$id=abs((int)$_POST['id']);
	$_ki=new BillItem;
	$_ki_in=new BillInItem;
	
	
	$ki=$_ki->GetItemById($id);
	if(($ki['status_id']==2)||($ki['status_id']==9)){
		
		if($ki['is_incoming']==1){
			if($au->user_rights->CheckAccess('w',624)){
		   
				
				//получить позиции счета
				//по каждой вызвать сканирование, собрать все результаты, вывести их
				$pos=$_ki_in->GetPositionsArr($id,false);
				$args=array();
				//$output='';
				foreach($pos as $k=>$v){
					
					 $args[]= $v['position_id'].';'. $v['pl_position_id'].';'. $v['pl_discount_id'].';'. $v['pl_discount_value'].';'. $v['pl_discount_rub_or_percent'].';'.$v['quantity'].';'.$v['out_bill_id'];
				
				}
				
				$_ki_in->DoEq($id,$args,$output,false,$ki,$result,false); 
				$ret='';
				
			}else{
				$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
			}	
		}else{
			if($au->user_rights->CheckAccess('w',292)){
		   
				
				//получить позиции счета
				//по каждой вызвать сканирование, собрать все результаты, вывести их
				$pos=$_ki->GetPositionsArr($id,false);
				$args=array();
				//$output='';
				foreach($pos as $k=>$v){
					 
					 $args[]= $v['position_id'].';'. $v['pl_position_id'].';'. $v['pl_discount_id'].';'. $v['pl_discount_value'].';'. $v['pl_discount_rub_or_percent'].';'.$v['quantity'].';'.$v['kp_id'];
				  
				
				}
				
				$_ki->DoEq($id,$args,$output,false,$ki,$result,false); 
				$ret='';
				
			}else{
				$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
			}	

		}
		
	  
	}else{
		 $ret='<script>alert("Недопустимый статус счета.");</script>';
	}
	
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>
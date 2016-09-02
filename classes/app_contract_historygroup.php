<?

require_once('abstractgroup.php');
require_once('app_contract_history_filegroup.php');

// абстрактная группа
class AppContractHistoryGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='app_contract_history';
		$this->pagename='app_contracthistory.php';		
		$this->subkeyname='app_contract_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function ShowHistory($order_id, $template, DBDecorator $dec, $can_edit=false, $has_header=true, $is_ajax=false, $result=NULL, $can_edit_record=false, $can_edit_all_record=false, &$data, $do_template=true, $do_read=false, $item=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$sm->assign('can_edit_record',$can_edit_record);
		$sm->assign('can_edit_all_record',$can_edit_all_record);
		
		if($result===NULL){
			$_au=new AuthUser;
			$result=$_au->Auth(false,false);	
		}
		
		$_app_c = new AppContractItem;
		if($item===NULL) $item = $_app_c->GetItemById($order_id);
		
		$ofg=new AppContractHistoryFileGroup;
		
		$sql='select o.*, u.login as login, u.group_id, u.name_s, s.name as last_status
                      from '.$this->tablename.' as o
			left join user as u on o.user_id=u.id	
			left join app_contract_status as s on o.status_id=s.id
		where o.'.$this->subkeyname.'="'.$order_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as o 
			left join user as u on o.user_id=u.id	
			left join app_contract_status as s on o.status_id=s.id
		where o.'.$this->subkeyname.'="'.$order_id.'" ';
		
                if(!$can_edit_all_record) {
                    $sql .=
                        'and 
                            (o.user_id="'.$result['id'].'" or
                                    (o.user_id<>"'.$result['id'].'" and o.is_shown=1)
                            )
                        ';
                    
                    $sql_count .=
                        'and 
                            (o.user_id="'.$result['id'].'" or
                                    (o.user_id<>"'.$result['id'].'" and o.is_shown=1)
                            )
                        ';
                }
		
					 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
                //echo '<p>'.$sql.'</p>';
		//echo $sql_count;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		$alls=array();
                $viewed_ids=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['txt']=nl2br($f['txt']);
			$f['files']=$ofg->GetItemsByIdArr($f['id']);
			//print_r($f);	
			$alls[]=$f;
                        $viewed_ids[]=$f['id'];
		}
		
		$data=$alls;
		
		//пометим прочитаннымм
		if($do_read){
			$this->ToggleRead($order_id, $viewed_ids, $result['id']);	
		}
		
		//заполним шаблон полями
		$current_status='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='status_id'){
				 $current_status=$v->GetValue();
			}
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$sm->assign('id',$order_id);
		$sm->assign('items',$alls);
		$sm->assign('can_create_order',$can_create_order);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('has_header', true);
                
		$sm->assign('session_id', session_id());
		
		$sm->assign('item',$item);
		
		
		if( $do_template) return $sm->fetch($template);
		else return $alls;
	}
	
	//подсчет новых историй данного заказа
	public function CountHistory($order_id){
		$ts=new mysqlSet('select count(*) from app_contract_history where app_contract_id="'.$order_id.'"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
	}
	
	//подсчет новых историй данного заказа
	public function CountNew($order_id){
		$ts=new mysqlSet('select count(*) from app_contract_history where app_contract_id="'.$order_id.'" and is_new="1"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
	}
	
	//подсчет заказов с новыми данными
	public function CountNewOrders($user_id){
		
	}
	
	//пометить прочитанными все изменения
	public function ToggleRead($order_id, $user_id){
		
	}

	//список позиций
	public function GetItemsArr($order_id, $current_id=0,  $is_shown=0){
		$arr = Array();
		//$set = new MysqlSet('select * from '.$this->tablename);
		if($is_shown == 0) $set = new MysqlSet('select * from '.$this->tablename.' where app_contract_id="'.$order_id.'" order by id asc');
		else $set = new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" and app_contract_id="'.$order_id.'" order by ord desc, id asc');
		$rs = $set->GetResult();
		$rc = $set->GetResultNumRows();
		for($i = 0; $i < $rc; $i++){
			$f = mysqli_fetch_array($rs);
			$f['is_current'] = (bool)($f['id'] == $current_id);
			foreach($f as $k => $v) $f[$k] = stripslashes($v);
			$arr[] = $f;
		}
		
		return $arr;
	}
        
}
?>
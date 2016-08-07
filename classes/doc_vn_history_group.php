<?

require_once('abstractgroup.php');
require_once('doc_vn_history_filegroup.php');
require_once('doc_vn_history_view_item.php');

// лента вн док-та
class  DocVn_HistoryGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='doc_vn_history';
		$this->pagename='myclaimhistory.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	public function CountHistory($id){
		$sql='select count(*)
	 
			 from '.$this->tablename.' as o
				 
				 
		 where o.'.$this->subkeyname.'="'.$id.'" ';
		 
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		 
		return (int)$f[0]; 
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
		
		
		$_lead=new Lead_Item;
		if($item===NULL) $item=$_lead->GetItemById($order_id);
		
		
		$ofg=new DocVn_HistoryFileGroup;
		
		$sql='select o.*, u.login as login,
		u.name_s as name_s
			 from '.$this->tablename.' as o
				left join user as u on o.user_id=u.id	
				 
		 where o.'.$this->subkeyname.'="'.$order_id.'" ';
		 
		 if(!$can_edit_all_record) 
		 $sql.=
		 'and 
		 	(o.user_id="'.$result['id'].'" or
		 		(o.user_id<>"'.$result['id'].'" and o.is_shown=1)
			)
		 ';
	
		
		
					 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		//echo $sql_count;
		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		$_vi=new DocVn_HistoryViewItem;
		
		$alls=array(); $viewed_ids=array(); $was_new_earlier=false;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//$f['txt']=nl2br($f['txt']);
			$f['files']=$ofg->GetItemsByIdArr($f['id']);
			
			//возможность редактировать...
			$local_edit=$can_edit;
			
			if($f['user_id']==$result['id']){
				$local_edit=$local_edit&&$can_edit_record;	
			}else{
				$local_edit=$local_edit&&$can_edit_all_record;
			}
			$f['can_edit']=$local_edit;
			
			//возможность прятать/открывать
			$local_can_hide=$can_edit;
			if($f['user_id']==$result['id']){
				//$local_can_hide=$local_can_hide&&($f['user_id']==$result['id']);
			}else{
				$local_can_hide=$local_can_hide&&$can_edit_all_record;	
			}
			$f['can_hide']=$local_can_hide;
			
			//новый/не новый?
			//выводим 1 раз
			if(!$was_new_earlier) {
				
				$vi=$_vi->GetItemByFields(array('sched_id'=>$order_id, 'history_id'=>$f['id'], 'user_id'=>$result['id']));
				
				
				
				$f['is_new']=($vi===false);
			 	$was_new_earlier=$was_new_earlier||$f['is_new'];
				
				 
				
			}else $f['is_new']=false;
				
			
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
		$sm->assign('can_edit',$can_edit);
		$sm->assign('has_header', $has_header);
		
		$sm->assign('session_id', session_id());
		
		$sm->assign('item',$item);
		
		//ссылка для кнопок сортировки
		/*$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);*/
		
		if( $do_template) return $sm->fetch($template);
		else return $alls;
		
	}
	
	
	//пометить прочитанными указанные комменты
	public function ToggleRead($id, array $history_ids, $user_id){
		$_vi=new DocVn_HistoryViewItem;
		foreach($history_ids as $k=>$history_id){	
			$params=array('sched_id'=>$id, 'history_id'=>$history_id, 'user_id'=>$user_id);
			$vi=$_vi->GetItemByFields($params);
			
			if($vi===false) $_vi->Add($params);
		
		}
	}
	
	
	
	
	//сколько новых комментов для данного пол-ля (кроме своих) с учетом правил доступа по всем лидам
	public function CalcNew($user_id, $available_ids, &$first_id){
		$check=time()-7*24*60*60;
		$first_id=0;
		
		$sql='select count(*) from doc_in_history where user_id<>"'.$user_id.'" and user_id<>0
		and sched_id in(
			'.implode(', ',$available_ids).'
		)
		/*and sched_id not in(select id from lead where is_fulfiled=1 or status_id in (36))*/
		 
		and id not in(select history_id from doc_in_history_view where user_id="'.$user_id.'")
		and pdate>"'.$check.'"
		';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			//получить айди первого документа
			$sql='select distinct sched_id from doc_in_history where user_id<>"'.$user_id.'" and user_id<>0
			and sched_id in(
				'.implode(', ',$available_ids).'
			)
		/*	and sched_id not in(select id from lead where is_fulfiled=1 or status_id in (36))*/
			 
			and id not in(select history_id from doc_in_history_view where user_id="'.$user_id.'")
			and pdate>"'.$check.'"
			order by sched_id asc limit 1
			';	
			
			$set=new mysqlSet($sql); 
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);
			$first_id=(int)$g['sched_id'];
		}
		
		return (int)$f[0];
		
	}
	
	
	
}
?>
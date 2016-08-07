<?

require_once('abstractgroup.php');
require_once('memoblink.php');

require_once('memousergroup.php');
require_once('memoitem.php');
//require_once('tasksuppliergroup.php');
require_once('memokindgroup.php');
require_once('memohistorygroup.php');
require_once('authuser.php');

require_once('memo_field_rules.php');

require_once('memo_view.class.php');

require_once('memonotesitem.php');


// абстрактная группа служ зап.
class MemoGroup extends AbstractGroup {
	public $_thg;
	public $_tblink;
	
	public $prefix='';
	
	protected $_view;
	
	protected $new_list; //список новых документов для текущего пользователя с разбивкой их на группы
	 
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo';
		$this->pagename='memo.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new MemoHistoryGroup;
		$this->_tblink=new MemoBlink;
		
		$this->_item=new MemoItem;
		
		$this->_view=new Memo_ViewGroup;
			$this->new_list=NULL; 
	}
	
	
	
	
		
	public function ShowPos($user_id, //0
	 $template, //1
	 DBDecorator $dec, //2
	 $is_ajax=false, //3
	 
	 $can_create_task=false, //4
	 &$alls, //5
	 $from=0, //6
	 $to_page=ITEMS_PER_PAGE, //7
	 $result=NULL,  //8
	 $can_delete=false,  //9
	 $can_edit=false,  //10
	 $can_print=false, //11
	 $has_header=true, //12
	 $can_confirm=false, //13
	 $can_unconfirm=false,  //14
	  $can_confirm_ruk=false,  //15
	 $can_unconfirm_ruk=false,  //16
	 $can_restore=false, //17
	 $can_confirm_dir=false,  //18
	 $can_unconfirm_dir=false  //19
	 ){
		
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		//echo $from;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$alls=array();
		
		
		$this->GainSql($user_id, $sql, $sql_count);
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->prefix));
		$navig->SetFirstParamName('from'.$this->prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		$_tu=new MemoUserGroup;
		$_rules=new Memo_FieldRules;
		
			
		$_uis=new UserSItem;
	
		$_ug=new UsersSGroup;
	

		
		 
		$this->new_list=NULL;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			
			if($f['confirm_pdate']==0) $f['confirm_pdate']='';
			else $f['confirm_pdate']=date('d.m.Y H:i:s', $f['confirm_pdate']);
			
			if($f['ruk_pdate']==0) $f['ruk_pdate']='';
			else $f['ruk_pdate']=date('d.m.Y H:i:s', $f['ruk_pdate']);
			
				if($f['dir_pdate']==0) $f['dir_pdate']='';
			else $f['dir_pdate']=date('d.m.Y H:i:s', $f['dir_pdate']);
			 
			
			 
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			 //получить блоки "новый документ"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			
			
			$f['field_rules']=$_rules->GetFields($f,$this->_auth_result['id'],$f['status_id']);  
			
			
			//item.can_confirm_ruk
			//доступно ли действие согласно правилам?
			//утв или не утв
			//права - супер или является рук-лем отдела
			$uis=$_uis->GetItemById($f['manager_id']);
	
			$user_ruk=$_ug->GetRuk($uis); 
		 	$user_dir=$_ug->GetDir($uis);
			
			$f['can_confirm_ruk']=$f['field_rules']['to_ruk_sz'];
			if($f['is_ruk']==0){
				if(!$can_confirm_ruk){
					
					$f['can_confirm_ruk']=($f['can_confirm_ruk']&&($user_ruk['id']==$this->_auth_result['id']));
				}
			}else{
				if(!$can_unconfirm_ruk){
					$f['can_confirm_ruk']=($f['can_confirm_ruk']&&($user_ruk['id']==$this->_auth_result['id']));
				}
			}
			
			
			$f['can_confirm_dir']=$f['field_rules']['to_dir_sz'];
			if($f['is_dir']==0){
				if(!$can_confirm_ruk){
					
					$f['can_confirm_dir']=($f['can_confirm_dir']&&($user_dir['id']==$this->_auth_result['id']));
				}
			}else{
				if(!$can_unconfirm_ruk){
					$f['can_confirm_dir']=($f['can_confirm_dir']&&($user_dir['id']==$this->_auth_result['id']));
				}
			}
			
			
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}	
			
		
		
		
		
		//заполним шаблон полями
		
		$current_kind='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='kind_id') $current_kind=$v->GetValue();
			
	
				$sm->assign($v->GetName(),$v->GetValue());	
		}
		
			//vidy
		$_sts=new MemoKindGroup;
		$sts=$_sts->GetItemsArr($current_kind);
		
		$sts[]=array('id'=>0, 'name'=>'все сл.записки');
		foreach($sts as $kk=>$v){
			if($v['id']==$current_kind) $sts[$kk]['is_current']=true;
			else $sts[$kk]['is_current']=false;
			
		}
		$sm->assign('sc', $sts);
		
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from'.$this->prefix,$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create_task', $can_create_task);
		
			$sm->assign('can_delete', $can_delete);
		$sm->assign('can_restore', $can_restore);
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_print', $can_print);
		$sm->assign('has_header', $has_header);
		
		$sm->assign('can_confirm', $can_confirm);
		$sm->assign('can_unconfirm', $can_unconfirm);
			$sm->assign('can_confirm_ruk', $can_confirm_ruk);
		$sm->assign('can_unconfirm_ruk', $can_unconfirm_ruk);
		
		$sm->assign('can_confirm_dir', $can_confirm_dir);
		$sm->assign('can_unconfirm_dir', $can_unconfirm_dir);
		
		
		
		
		$sm->assign('prefix',$this->prefix);
		
		
			//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		return $sm->fetch($template);
	
	}
	
	
	public function GainSql($user_id, &$sql, &$sql_count){
		
		
	}
	
	
	
	
	
	//попадает ли текущий документ при текущем пользователе в индикацию, если попадает - вернуть данные для построения блока
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		//print_r($this->new_list);
		
		//пересмотреть список данных
		foreach($this->new_list as $k=>$type){
			if(in_array($document_id, $type['doc_ids'])){
				
				$url=str_replace('{id}',$document_id, $type['url'], $subst_count);
				
				$sub_id=$type['sub_ids'][array_search($document_id,$type['doc_ids'])];
				
								
				$url=str_replace('{sub_id}',$sub_id, $url, $subst_count1);
				$subst_count+=$subst_count1;
				
				if($subst_count==0) $url=$type['url'].$document_id;
				
				$data[]=array(
					'class'=>$type['class'],
					
					'url'=>$url,
					'comment'=>$type['comment'],
					'doc_counters'=>(int)$type['doc_counters'][array_search($document_id, $type['doc_ids'])]
				

				
				);	
			}
		}
		
		 
		
		return $data;	
	}	
	
	
	
	
	
	//список ID документов, которых может видеть текущий сотрудник
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',733)){
			$sql='select id from '.$this->tablename;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{ 
			//свои
			$sql='select p.id from '.$this->tablename.' as p where p.user_id="'.$user_id.'"  or p.manager_id="'.$user_id.'" ';	
			
			//руководитель отдела - давать доступ к документам подчиненных
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or( p.manager_id in(select id from user where department_id="'.$user['department_id'].'") and p.is_confirmed=1)';
					
			} 
			
			
			//подчиненные пользователи
			$_ug=new UsersSGroup;
			$podd=array();
			$_ug->GetSubordinates($user_id, $podd);
			if(count($podd)>0){
				$sql.=' or p.manager_id in('.implode(', ',$podd).')';

			}
			
			
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
			
			 
			
		}
		
		 
		//вставка -1 для корректности
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//проверка, будет ли доступен документ при указанном менеджере указанному сотруднику
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',733)){
			 
			
			return true;
		}else{
			//свои
			
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			//руководитель отдела - давать доступ к документам подчиненных
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	//$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
				$user_ids=array();
				$sql=' select id from user where department_id="'.$user['department_id'].'"';
				$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
				}
				if(in_array($manager_id,$user_ids)) return true;	 
			} 
			 
			
			
			
		}
		
		 
		return false;
		
	}  
	
	
	  
	
	
	//конструирование списка новых лидов для заданного пользователя
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'Примите лиды в работу!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		 
		
			$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		$_upos=new UserPosItem;
		$upos=$_upos->GetItemById($user['position_id']);
		
		 
		//индикация: есть право утв в роли рук-ля 1120 - есть док-ты в статусе    41
		 if(
		 	eregi('Руководитель отдела',   $upos['name'])||
		 	$man->CheckAccess($user_id,'w',1120)	
		){
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1120)) $add=' and m.department_id="'.$user['department_id'].'" ';
			
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					inner join user as m on m.id=t.user_id
				 
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
				 
					'.$add;
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					'.$this->tablename.' as t
					inner join user as m on m.id=t.user_id
				 
				
				where t.id in ('.implode(', ',$tender_ids).')
				 
				and t.status_id=41
			 
					'.$add.'
				order by t.id 
				';
				
			//	echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_m',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'memo_my_history.php?action=1&id={id}',
					'comment'=>'Утвердите служебную записку в роли руководителя отдела!'
				
				);
			}
		} 
		
		
		
		 
		//индикация: есть право утв в роли ген дир 1122 - есть док-ты в статусе    43
		 if(
		 	eregi('Генеральный директор',   $upos['name'])||
		 	$man->CheckAccess($user_id,'w',1122)	
		){
			$add=''; 
			 
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					inner join user as m on m.id=t.user_id
				 
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
				 
					'.$add;
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					'.$this->tablename.' as t
					inner join user as m on m.id=t.user_id
				 
				
				where t.id in ('.implode(', ',$tender_ids).')
				 
				and t.status_id=43
			 
					'.$add.'
				order by t.id 
				';
				
			//	echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_m',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'memo_my_history.php?action=1&id={id}',
					'comment'=>'Утвердите служебную записку в роли генерального директора!'
				
				);
			}
		} 
		 
		
	}
	
	
	
	//число новых док-тов - расширенная индикация
	public function CountNewDocsExtended($user_id, $do_iconv=true){
		$data=array();
			
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		 
		
		
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		$_upos=new UserPosItem;
		$upos=$_upos->GetItemById($user['position_id']);
		
		//var_dump(eregi('Руководитель отдела',   $upos['name']));
		 
		//индикация: есть право утв в роли рук-ля 1120 - есть док-ты в статусе 41
	 
		if( eregi('Руководитель отдела',   $upos['name'])||
			 $man->CheckAccess($user_id,'w',1120)	
		){	
		
			//echo $upos['name'];
		
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1120)) $add=' and m.department_id="'.$user['department_id'].'" ';
			
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					inner join user as m on m.id=t.user_id
				 
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
				 
					'.$add;
			
			//echo $sql.'<br>';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					'.$this->tablename.' as t
					 
					inner join user as m on m.id=t.user_id
				 
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
					 
					 
				'.$add;
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='memo_my_history.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Утвердите служебную записку в роли руководителя отдела!'
				
				);
			}	
			
		}
		
		
		//индикация: есть право утв в роли ген дир 1122 - есть док-ты в статусе 43
		 
		if( eregi('Генеральный директор',   $upos['name'])||
			 $man->CheckAccess($user_id,'w',1122)	
		){	
		
			//echo $upos['name'];
		
			$add=''; 
//			if(!$man->CheckAccess($user_id,'w',1122)) $add=' and m.department_id="'.$user['department_id'].'" ';
			
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					inner join user as m on m.id=t.user_id
				 
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
				 
					'.$add;
			
			//echo $sql.'<br>';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					'.$this->tablename.' as t
					 
					inner join user as m on m.id=t.user_id
				 
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
					 
					 
				'.$add;
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='memo_my_history.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Утвердите служебную записку в роли генерального директора!'
				
				);
			}	
			
		}
		
		
		/*$data[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		
		
		if($do_iconv) foreach($data as $k=>$v) $data[$k]['comment']=iconv('windows-1251', 'utf-8', $v['comment']);
		
		return $data;
	}
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 $log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
	 
		$_ni=new MemoNotesItem;
		
		$sql='select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc';
		//echo $sql;
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			 
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления служебной записки,  документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания служебной записки,  документ не утвержден';
					}
				}
			}
			 
			
			
			
			
			
			
			
			if($can_annul){
				
				 
				 
			
			 $this->_item->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование служебной записки',NULL,1125,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
			 	'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: служебная записка была автоматически аннулирована, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
}
?>
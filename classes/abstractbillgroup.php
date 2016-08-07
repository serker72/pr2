<?
require_once('abstractgroup.php');
require_once('billitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('billnotesgroup.php');
require_once('billnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');

// абстрактная группа счетов
class AbstractBillGroup extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->pagename='bills.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		
		$this->_auth_result=NULL;
	}
	
	
	
	
	public function GainSql(&$sql, &$sql_count){
		
		$sql='select p.*,
					
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login,  p.confirm_shipping_pdate as confirm_shipping_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					
				where is_incoming="'.$this->is_incoming.'" 
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					
				where is_incoming="'.$this->is_incoming.'" 
					';
		
	}
	
	
	
	
	
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE, $can_add=false, $can_edit=false, $can_delete=false, $add_to_bill='', $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL,$nested_bill_positions=NULL, $can_confirm_ship=false, $can_unconfirm=false, $can_unconfirm_ship=false){
				
		
	
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$this->GainSql($sql, $sql_count);
				 
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
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			if($f['supplier_bill_pdate']>0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['total_cost']=$this->_item->CalcCost($f['id']);
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			
			$f['binded_payments']=$this->_item->GetBindedPayments($f['id'],$binded_summ);
			$f['binded_payments_summ']=$binded_summ;
			
			
			$f['avans_payments']=$this->_payforbillgroup->GetAvans($f['supplier_id'],$f['org_id'],$f['id'],$avans, $raw_ids, $raw_invs, $f['contract_id']);
			$f['avans_payments_summ']=$avans;
			$f['sum_by_bill']=$this->_payforbillgroup->SumByBill($f['id']);
			
			//снятие утверждения отгрузки
			$reason='';
			$f['can_unconfirm_by_document']=$this->_item->DocCanUnconfirmShip($f['id'],$reason,$f);
			$f['can_unconfirm_by_document_reason']=$reason;
			
			
			//echo $f['binded_payments'];
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		//	if($v->GetName()=='sector_id'.$add_to_bill) $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id'.$add_to_bill) $current_supplier=$v->GetValue();
		//	if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm_price',$can_confirm);
			$sm->assign('can_unconfirm_price',$can_unconfirm);
		$sm->assign('can_super_confirm_price',$can_unconfirm);
		
		$sm->assign('can_confirm_shipping', $can_confirm_ship);
		$sm->assign('can_unconfirm_shipping', $can_unconfirm_ship);
		$sm->assign('can_super_confirm_shipping',$can_unconfirm_ship);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('prefix',$this->prefix);
		
		
		
		$sm->assign('has_header',$has_header);
		
	
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and is_incoming="'.$this->is_incoming.'" order by  id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	

	/*
	
	//автоматическое аннулирование
	public function AutoAnnul($days=90, $days_after_restore=90, $annul_status_id=3){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
		 $_itm=new BillItem;
		
		$_ni=new BillNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and cannot_an=0 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//проверить наличие связ. документов утв
			
			$sql1='select count(id) from sh_i_id where bill_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			$sql1='select count(id) from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed_price']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления исходящего счета, нет утвержденных связанных документов, документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+30*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более 30 дней с даты создания исходящего счета, нет утвержденных связанных документов, документ не утвержден';
					}
				}
			}elseif(($f['is_confirmed_price']==1)&&($f['is_confirmed_shipping']==0)){
				//работаем с датой простановки 1 галочки	
					if(($f['confirm_price_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения цен исходящего счета, нет утвержденных связанных документов, не утверждена отгрузка';
					}
				
			}elseif(($f['is_confirmed_price']==1)&&($f['is_confirmed_shipping']==1)){
				//работаем с датой простановки 2 галочки	
					if(($f['confirm_shipping_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения отгрузки исходящего счета, нет утвержденных связанных документов';
					}
				
			}
			
			
			
			
			
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed_price'=>0, 'is_confirmed_shipping'=>0, 'status_id'=>$annul_status_id));
				
				$_itm->FreeBindedPayments($f['id']);
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование исходящего счета',NULL,94,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: исходящий счет был автоматически аннулирован, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	//автоматическое выравнивание
	public function AutoEq($days=30, $days_no_acc=45){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new BillNotesItem;
		$_itm=new BillItem;
		
		//перебрать все счета в статусе "утв" или "не вып"
		
		 
		
		$now=time();
		
		$sql='select * from bill where status_id in(2,9) and is_confirmed_price=1 and is_confirmed_shipping=1 and cannot_eq=0 order by id desc';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$can_annul=false;
		
			//проверка по завозу
			$checked_time=$now-$days*24*60*60;
			$checked_time_noacc=$now-$days_no_acc*24*60*60;
			
			$sql1='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1  order by given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$can_annul=true;	
				$g=mysqli_fetch_array($rs1);
				//print_r($g);
			}
			
			
			
			//работать со счетом, только если есть завоз (неважно какой даты)
			if($can_annul){
				//найти позиции счета
				
				$posset=new mysqlset('select * from bill_position where bill_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				
				
				$was_eqed=false;
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					$args=array();
					$can_annul_position=false;
					//по каждой из позиций - получим связанные реализации
					//если дата последнего поступления (и оно есть) раньше 30 дней - выравн. позицию
					//иначе - проверим дату последнего поступления...
					
					$sql3='select * 
					from acceptance as a inner join acceptance_position as ap on a.id=ap.acceptance_id 
					where 
						a.is_confirmed=1 
						and a.bill_id='.$f['id'].' 
						 
						and ap.position_id='.$h['position_id'].' 
						and ap.pl_position_id='.$h['pl_position_id'].' 
						and ap.pl_discount_id='.$h['pl_discount_id'].' 
						and ap.pl_discount_value='.$h['pl_discount_value'].' 
						and ap.pl_discount_rub_or_percent='.$h['pl_discount_rub_or_percent'].' 
						
					order by a.given_pdate desc limit 1';
					
					$acset=new mysqlset($sql3);
					$rs3=$acset->GetResult();
					$rc3=$acset->GetResultNumRows();
					if($rc3>0){
						//завоз есть, проверить дату завоза
						$hh=mysqli_fetch_array($rs3);
						if($hh['given_pdate']<$checked_time){
							$can_annul_position=true;
							//echo 'Позиция '.$h['name'].' подлежит выравниванию, дата последнего поступления '.date('d.m.Y',$hh['given_pdate']).' '.$hh['id'].' по позиции более 30 дней назад<br>';
						}
					}else{
						//нет завоза - проверка по дате последнего завоза
						
						if($g['given_pdate']<$checked_time_noacc){
							$can_annul_position=true;
							//echo 'Позиция '.$h['name'].' подлежит обнулению, дата последнего поступления '.date('d.m.Y',$g['given_pdate']).' '.$g['id'].' по счету более 45 дней назад<br>';
						}
						
					}
					
					//если выравниваем...
					if($can_annul_position){
						
						$args[]=$h['position_id'].';'.$h['pl_position_id'].';'.$h['pl_discount_id'].';'.$h['pl_discount_value'].';'.$h['pl_discount_rub_or_percent'].';'.$h['quantity'];	
					//	echo 'Выравниваю позицию '.$h['name'].' <br>';
						
						$_itm->DoEq($f['id'],$args,$some_output, 1, $f, $_result,true);
						$was_eqed=$was_eqed||true;
					}else{
					//	echo 'НЕ выравниваю позицию '.$h['name'].' <br>';
					}
				}// of bill positions
				if($was_eqed){
					//echo 'Счет '.$f['code'].' '.$f['id'].' подлежит выравниванию <br><br>';
					$_itm->ScanDocStatus($f['id'],array(),array(),$f,$_result);		
				}
				
			}//of bill
		}
		 
		
	}*/
}
?>
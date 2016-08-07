<?
require_once('abstractgroup.php');
require_once('payitem.php');
require_once('abstract_payforbillgroup.php');


require_once('paynotesgroup.php');
require_once('paynotesitem.php');
require_once('billitem.php');
require_once('user_s_item.php');

// абстрактная группа оплат
class AbstractPayGroup extends AbstractGroup {
	protected $sub_tablename;
	protected $_auth_result;
	
	
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	protected $_posgroup;
	protected $_bill_item;
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_confirmed';		
		$this->sub_tablename='bill';
		
		$this->_item=new PayItem;
		$this->_notes_group=new PaymentNotesGroup;
		$this->_payforbillgroup=new AbstractPayForBillGroup;
		$this->_posgroup=new AbstractPayForBillGroup;
		
		$this->_bill_item=new BillItem;
		
		
		
		$this->_auth_result=NULL;
	}
	
	
	public function ShowPos($bill_id, $supplier_id, $template, DBDecorator $dec,$can_edit=false, $can_delete=false,  $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$can_unconfirm=false,$only_bill=NULL, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL){
		
		
		
		$bill_item=$this->_bill_item->getitembyid($bill_id);
		
		if($summ_by_bill==NULL) $summ_by_bill=$this->_bill_item->CalcCost($bill_id);
		if($summ_by_payed==NULL) $summ_by_payed=$this->_bill_item->CalcPayed($bill_id);
		
		
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select pb.value as value, 
					p.`id`, p.`code`, p.`bill_id`, p.`supplier_id`, p.`user_confirm_id`, p.`confirm_pdate`, p.`pdate`,  p.`is_confirmed`, p.`notes`, p.`org_id`, p.`manager_id`, p.`supplier_bdetails_id`, p.`org_bdetails_id`, p.`pay_for_dogovor`, p.`pay_for_bill`, p.value as summa, p.status_id, p.given_pdate, p.given_no,
					o.id as o_id, o.pdate as o_pdate,
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_name, u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					inner join payment_for_bill as pb on p.id=pb.payment_id
					left join '.$this->sub_tablename.' as o on p.'.$this->subkeyname.'=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
				where pb.'.$this->subkeyname.'="'.$bill_id.'"
				  and p.is_incoming="'.$this->is_incoming.'"
				  and p.supplier_id="'.$supplier_id.'" ';
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' where '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);
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
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			
			
			$this->_payforbillgroup->SetIdName('payment_id');
			if($only_bill===NULL){
				//echo 'zzzzzzzzzzzz';	
				 $f['osnovanie']=$this->_payforbillgroup->GetItemsByIdForPage($f['id']);
			}else{
				 $f['osnovanie']=$this->_payforbillgroup->GetItemsByIdForPage($f['id'],$bill_id);
				 
			}
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$user_confirm_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		$sm->assign('supplier_id',$supplier_id);
		
		$sm->assign('action',1);
		$sm->assign('id',$bill_id);
		$sm->assign('bill_id',$bill_id);
		
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix',$this->prefix);
		
		
		//$can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL
		//блок простановки 
		$sm->assign('can_confirm_in_buh',$can_confirm_in_buh);
		$sm->assign('can_unconfirm_in_buh',$can_unconfirm_in_buh);
		
		//can_is_in_buh
		
		$can_is_in_buh=$this->_bill_item->CanIsInBuh($bill_id, $rss22, $bill_item, $can_confirm_in_buh, $can_unconfirm_in_buh, $summ_by_bill, $summ_by_payed);
		
		$sm->assign('can_is_in_buh',$can_is_in_buh);
		$sm->assign('cannot_is_in_buh_reason',$rss22);
		
		$sm->assign('is_in_buh', $bill_item['is_in_buh']);
		if($bill_item['is_in_buh']==1){
			$_ui=new UserSItem;
			
			if($bill_item['user_in_buh_id']==-1){
				 $sm->assign('user_in_buh', 'Автоматическая система утверждения на основании 100% оплаты счета'.' '.date('d.m.Y H:i:s',$bill_item['in_buh_pdate']));			
			
			}else{
			  $ui=$_ui->GetItemById($bill_item['user_in_buh_id']);
			  
			  $sm->assign('user_in_buh', $ui['position_s'].' '.$ui['name_s'].' '.$ui['login'].' '.date('d.m.Y H:i:s',$bill_item['in_buh_pdate']));
			}
		}
		//echo $rss22;
		
		//ссылка для кнопок сортировки
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	
	
	
	public function ShowAllPos($template, DBDecorator $dec,$can_edit=false, $can_delete=false, $from=0, $to_page=ITEMS_PER_PAGE,  $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$can_unconfirm=false){
		
		
		
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code as o_code,
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_price_name,
				
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					p.value as summa
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'"
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'"
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
		
//		echo $total;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			//print_r($f);	
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			$this->_payforbillgroup->SetIdName('payment_id');
			$f['osnovanie']=$this->_payforbillgroup->GetItemsByIdForPage($f['id']);
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_supplier='';
		$user_confirm_id='';
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
						
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//kontragent
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
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix',$this->prefix);
		
		//ссылка для кнопок сортировки
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		return $sm->fetch($template);
	}
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
	}
	
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>
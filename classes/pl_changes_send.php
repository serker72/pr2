<?
require_once('abstractitem.php');
require_once('pl_positem.php');
require_once('authuser.php');
require_once('pl_posgroup.php');
require_once('pl_curritem.php');
require_once('posgroupitem.php');

require_once('posgroup_direction.php');
require_once('upos_direction.php');
require_once('user_s_group.php');
require_once('messageitem.php');
require_once('price_kind_group.php');

require_once('pl_proditem.php');

//класс отправки сообщений по изменению цен
class PlChangesSend{
	//public $pl_position_id;
	public $old_price;
	public $new_price;
	public $currency_id;
	public $_pl_position; public $pl_position; public $pl_group;
	public $_curr_item; public $curr_item;
	public $_group; public $group;
	public $_prod; public $prod;
	
	public $_upd; public $_ppd; public $list_of_users;
	//public  $old_discount_base, $old_discount_add, $old_profit_exw, $old_profit_ddpm;
	
	public $changed_price_kinds; //список измененных видов цен
	
	public $_auth_result;
	protected $_log;
		
	public function __construct($pl_position_id, $old_price, $new_price, $currency_id, $changed_price_kinds, $_auth_result=NULL/*, $old_discount_base, $old_discount_add, $old_profit_exw, $old_profit_ddpm*/){
		
//		$this->old_discount_base=$old_discount_base; $this->old_discount_add=$old_discount_add; $this->old_profit_exw=$old_profit_exw; $this->old_profit_ddpm=$old_profit_ddpm;
		if($_auth_result===NULL){
			$au=new AuthUser;
			$_auth_result=$au->Auth();
		}
		
		
		$this->_auth_result=$_auth_result;
		$this->_log=new ActionLog;
		
		$this->changed_price_kinds=$changed_price_kinds;
		
		$this->_pl_position=new PlPosItem;
		$this->pl_position=$this->_pl_position->GetItemById($pl_position_id);
		
		$this->pl_group=new PlPosGroup;
		
		
		$this->_curr_item=new PlCurrItem;
		$this->curr_item=$this->_curr_item->GetItemById($currency_id);
		
		
		
		$this->currency_id=$currency_id;
		$this->old_price=$old_price;
		$this->new_price=$new_price;
		
		$this->_group=new PosGroupItem;
		$this->group=$this->_group->GetItemById($this->pl_position['group_id']);
		
		$this->_prod=new PlProdItem;
		$this->prod=$this->_prod->GetItemById($this->pl_position['producer_id']);
		
		
		$this->_ppd=new PosGroupDirection;
		$this->_upd=new UposDirection;
		
		
		$this->list_of_users=array();
		$dirs=$this->_ppd->GetDirsByGroupArr($this->group['id']);
		
		$_usg=new UsersSGroup;
		foreach($dirs as $k=>$v){
			//не позиции, а отделы
			
			
			$positions=$this->_upd->GetPositionsByDirArr($v['id']);
			
			$users=array();
			foreach($positions as $kk=>$vv){
				$users=	$_usg->getitemsbyfieldsarr(array('department_id'=>$vv['id'], 'is_active'=>1));
			}
			foreach($users as $kk=>$vv){
				if(!in_array($vv, $this->list_of_users)) $this->list_of_users[]=$vv['id'];	
			}
		}
		sort($this->list_of_users);
		
	}
	
	
	
	//отправка сообщений
	public function Send(){
		//print_r($this->list_of_users);
		
		//подняться на уровень вверх по разделу...
		$_group=new PosGroupItem;
		$group=$_group->GetItemById($this->group['parent_group_id']);
		
		$_pkg=new PriceKindGroup;
		$pkg=$_pkg->GetItemsByFieldsArr(array('is_calc_price'=>1, 'group_id'=>$group['id']));
		
		$mi=new MessageItem;
		
		//цикл по видам цен.... 
		foreach($pkg as $k=>$pr_group){
			if(!in_array($pr_group['id'], $this->changed_price_kinds)) continue;
			
			
			$txt='<div><em>Данное сообщение сгенерировано автоматически.</em></div>
									  <div><br /></div>';
									  
			$txt.='<div>Уважаемые коллеги!</div>';
			$txt.='<div>Изменились цены '.$pr_group['fullname'].' на следующее оборудование:</div>';						  			$txt.='<div><br /></div>';
			
			$txt.='<div>Производитель: '.$this->prod['name'].', категория '.$this->group['name'].':</div>';
			$txt.='<div><br /></div>';
			
			if($this->pl_position['parent_id']==0){
				//не опция	
				$txt.='<div>Оборудование '.$this->pl_position['id'].' '.$this->pl_position['code'].' '.$this->pl_position['name'].':  ';
				$affected_id=$this->pl_position['id'];
				$action_description='изменена цена';
				$action_value='позиция '.SecStr($this->pl_position['code']).' '.SecStr($this->pl_position['name']).': '; 
				
			}else{
				//опция, найдем родит. оборудование
				$_parent_pos=new PlPosItem;
				$parent_pos=$_parent_pos->GetItemById($this->pl_position['parent_id']);
				$txt.='<div>Опция оборудования '.$parent_pos['id'].' '.$parent_pos['code'].' '.$parent_pos['name'].':</div>';
				$short_name=$this->pl_position['name']; if(strlen($short_name)>80) $short_name=substr($short_name, 0, 80).'...';
				$txt.='<div>'.$this->pl_position['id'].' '.$this->pl_position['code'].' '.$short_name.':  ';
				
				$affected_id=$this->pl_position['parent_id'];
				$action_description='изменена цена';
				$action_value='опция '.SecStr($this->pl_position['code']).' '.SecStr($this->pl_position['name']).': ';
				 
			}
			//найти старую и новые цены
			//старую цену пока не ищем!
			$new_price=$this->_pl_position->DispatchCalcPrice($this->pl_position['id'],$this->currency_id,NULL,$pr_group['id']);
			$txt.=' новая цена '.SecStr($pr_group['fullname']).' '.$new_price.' '.$this->curr_item['signature'].'</div>';
			
			$action_value.=' новая цена '.SecStr($pr_group['fullname']).' '.$new_price.' '.$this->curr_item['signature'].' ';
			$this->_log->PutEntry($this->_auth_result['id'], $action_description,NULL,602, NULL, $action_value,  $affected_id);
		
			
			if($this->pl_position['parent_id']==0){
				//охватить измененные опции
				//в случае, если прошли изменения оборудования - в опциях они точно будут!
				$txt.=$this->BuildAffectedOptions($pr_group['id'], $pr_group);
			}
			
			
			//echo $txt;	
			foreach($this->list_of_users as $kk=>$user_id){	
				$params1=array();
				$params1['topic']=SecStr('Изменение цен '.$pr_group['fullname']);
				$params1['txt']=SecStr($txt);
				$params1['to_id']= $user_id;
				$params1['from_id']=-1; //Автоматическая система рассылки сообщений
				$params1['pdate']=time();
				
				$mi->Send(0,0,$params1,false);	
			}
			
			
		}
	}
	
	
	//показ измененных опций
	protected function BuildAffectedOptions($price_kind_id, $pr_group){
		$txt='';
		$opts=	$this->pl_group->ShowOptionsArr($this->pl_position['id'],  $this->currency_id, $price_kind_id, NULL, NULL, true)	;
		
		if(count($opts)>0) $txt.='<div>Измененные опции оборудования:</div>';
		foreach($opts as $k=>$v){
			if($v['kind']=='group') continue;
			$short_name=$v['name']; if(strlen($short_name)>80) $short_name=substr($short_name, 0, 80).'...';
			$txt.='<div>'.$v['id'].' '.$v['code'].' '.$short_name.':  ';	
			$txt.=' новая цена '.$v['price'].' '.$this->curr_item['signature'].'</div>';
			
			
			$affected_id=$v['parent_id'];
			$action_description='изменена цена';
			$action_value='опция '.SecStr($v['code']).' '.SecStr($v['name']).': '.' новая цена '.SecStr($pr_group['fullname']).' '.$v['price'].' '.$this->curr_item['signature'];
			
			
			$this->_log->PutEntry($this->_auth_result['id'], $action_description,NULL,602, NULL, $action_value,  $affected_id);
		}
		
		return $txt;
	}

}
?>
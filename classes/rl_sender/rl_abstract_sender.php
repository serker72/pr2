<?

class RlAbstractSender{

	public $receivers; public $full_receivers; 
	
	//полный список возможного оборудования
	public $eqs;
	public $full_eqs;
	
	
	function __construct(){
		$this->receivers=array(); $this->full_receivers=array();
		$this->eqs=array(); $this->full_eqs=array();
		
	}
	
	
	
	
	
	
	//отправка сообщения получателям.
	public function SendMessages(){
		$this->init();
		
		$_mm=new MessageItem;
		$_gi=new PosGroupItem;
		$_rl=new RLMan;
		
		//print_r($this->full_receivers );
	 
		foreach($this->full_receivers as $k=>$user){
			
			//print_r( $user);
			
			//проверять, есть ли у получателя права на это оборудование.
			//-получить полную иерархию по оборудованию
			//получить айди поставщика
			//проверить, не содержится ли это оборудование в запретных списках данного пол-ля
			//если не содержится - то рассылать сообщения!
			
			//получить запретнные списки по оборудованию, пр-лю
			$restricted_groups=$_rl->GetBlockedItemsArr($user['id'],1,'w', 'catalog_group', 0);
			$restricted_producers=$_rl->GetBlockedItemsArr($user['id'],34,'w', 'pl_producer', 0);
			
			$our_eqs=array();
			
			foreach($this->full_eqs as $kK=>$f){
				$put=true;
				
				$gi=$_gi->GetItemById($f['group_id']);
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					
					if(in_array($gi['parent_group_id'], $restricted_groups)) $put=$put&&false;
					
					if($gi2['parent_group_id']>0){
						//$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						
						//$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
						if(in_array($gi2['parent_group_id'], $restricted_groups)) $put=$put&&false;
					}else{
						
						//$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
					}
				}else{
						
					if(in_array($f['group_id'], $restricted_groups)) $put=$put&&false;
				}
				
				
				
				if(in_array($f['producer_id'], $restricted_producers)) $put=$put&&false;
				
				if($put){
					$our_eqs[]=$f;
				}
			}
			
			if(count($our_eqs)>0){
				$_eqs=array();
				foreach($our_eqs as $k=>$eq) $_eqs[]=$eq['id'].' '.$eq['code'].' '.$eq['name'];
		
				$eqs_string=implode(';<br />',$_eqs);
				
				
				$message_to_managers="
							  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
							  <div>Уважаемый/ая ".$user['name_s']."!</div>
							  <div>C ".date("d.m.Y")." Вам доступны формы коммерческого предложения по оборудованию:</div>
							  <div>".$eqs_string.".</div>
							 
							  ";
				
				
				//echo $message_to_managers;
				
				$params1=array();
								
				$params1['topic']='Доступны новые формы КП!';
				$params1['txt']=SecStr($message_to_managers);
				$params1['to_id']= $user['id'];
				$params1['from_id']=-1; //Автоматическая система рассылки сообщений
				$params1['pdate']=time();
				
				$_mm->Send(0,0,$params1,false);
			}
			
		}
		
	}
	
	
	//заполнение данных
	protected function init(){
		
		//заполним получателей
		$sql='select * from user where is_active=1 and id in('.implode(', ',$this->receivers).') ';
 //		echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$this->full_receivers=array();
		$this->full_eqs=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$this->full_receivers[]=$f;	
			
		}
		
		
		//заполним оборудование!
		
		$sql=' select pl.id as pl_id, pl.discount_id, pl.discount_value, pl.discount_rub_or_percent, pl.position_id, 
					pl.discount_base, pl.discount_add, pl.profit_exw, pl.profit_ddpm, pl.kp_form_id, p.txt_for_kp,
					p.is_install, p.is_mandatory,  pl.delivery_ddpm, pl.duty_ddpm, pl.svh_broker,
					
				 
				 
					p.*,
					d.name as dim_name,
					g.name as group_name,
					pp.name as producer_name
					
					
				from pl_position as pl
					inner join catalog_position as p on p.id=pl.position_id
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					 
				where parent_id=0 and p.is_active=1 and p.id in('.implode(', ',$this->eqs).') 
					';
		
		//echo $sql;				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$this->full_eqs[]=$f;	
			
		}
		
	}
	
	
}
?>
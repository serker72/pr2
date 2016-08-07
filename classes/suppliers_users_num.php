<?
require_once('abstractitem.php');
require_once('useritem.php');
 
require_once('user_s_item.php');
require_once('discr_man.php');
 
//статистика контрагентов по менеджерам
class SuppliersUsersNum extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='suppliers_users_num'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
	//массовое формирование статистики
	public function PutData($pdate=NULL){
		
		if($pdate===NULL) $pdate=time();
		
		
		
		$pdate=DateFromdmy(date('d.m.Y', $pdate))+23*60*60+59*60+59;
		
		$sql='select * from user';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			
			$num=0;
			$sql1=' 
			select count(distinct p.id) from supplier as p
			inner join supplier_responsible_user as sr on sr.supplier_id=p.id
			where p.is_active=1 and p.active_first_pdate<="'.$pdate.'" and p.is_customer=1
			and sr.user_id="'.$f['id'].'"';
			
			//echo $sql1.'<br><br>';
			
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$num=(int)$g[0];
			
			$params=array('pdate'=>$pdate, 'user_id'=>$f['id'], 'num_suppliers'=>$num);
			$this->Add($params);
			
		}
		
		$sql1=' 
			select count(distinct p.id) from supplier as p
			inner join supplier_responsible_user as sr on sr.supplier_id=p.id
			where p.is_active=1 and p.active_first_pdate<="'.$pdate.'" and p.is_customer=1
		 ';
			
			//echo $sql1.'<br><br>';
			
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$num=(int)$g[0];
			
			$params=array('pdate'=>$pdate, 'user_id'=>0, 'num_suppliers'=>$num);
			$this->Add($params);
		
	}
	
	//получение статистики по дате, юзеру
	public function GetNum($pdate, $user_id){
		//дату приводим на сутки назад, 23:59:59
		
		$pdate=DateFromdmy(date('d.m.Y', $pdate))-24*60*60  + 23*60*60+59*60+59;
		
		//echo date('d.m.Y H:i:s', $pdate).'<br>';
		
		$data=$this->GetItemByFields(array(	'pdate'=>$pdate, 'user_id'=>$user_id));
		if($data===false) return '-';
		else return (int)$data['num_suppliers'];
	}
	
	//подсчет числа контрагентов по СТАТИСТИКЕ на дату по менеджерАМ
	public function GetNumTotal($pdate){
		
		$pdate=DateFromdmy(date('d.m.Y', $pdate))-24*60*60  + 23*60*60+59*60+59;
		
		$data=$this->GetItemByFields(array(	'pdate'=>$pdate, 'user_id'=>0));
		
		//var_dump(array(	'pdate'=>$pdate, 'user_id'=>0));
		
		if($data===false) return '-';
		else return (int)$data['num_suppliers'];
 
			
	}
	 
	 
	
}
?>
<?
require_once('abstractitem.php');

//������� ��������
class PosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='catalog_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//�������
	public function Del($id){
		
		
		if($this->CanDelete($id)){
		  $query = 'delete from catalog_position_in_group where position_id="'.$id.'";';
		  $it=new nonSet($query);
		  /*
		  unset($it);				
		  
		  $this->item=NULL;*/
		  
		  $query = 'delete from catalog_position where parent_id="'.$id.'";';
		  $it=new nonSet($query);
		  
		  
		  AbstractItem::Del($id);
		}
	}	
	
	
	//�������
	public function Edit($id,$params){
		
		AbstractItem::Edit($id,$params);
		
		if(isset($params['name'])){
			$_tables=array();
			
			/*$_tables[]='acceptance_position';
			$_tables[]='bill_position';
			$_tables[]='interstore_position';
			$_tables[]='interstore_wf_position';
			
			$_tables[]='inventory_position';
			
			//$_tables[]='komplekt_ved_pos';
			$_tables[]='sh_i_position';
			$_tables[]='trust_position';
			
			*/
			
			//$_tables[]='kp_position';
			
			
			foreach($_tables as $k=>$v){
				new NonSet('update '.$v.' set name="'.$params['name'].'" where position_id="'.$id.'"');
			
			}
			
				
		}
	}
	
	
	//�������� ����������� ��������
	public function CanDelete($id){
		$can_delete=true;
		
		
		$set=new mysqlSet('select count(*) from kp_position as p inner join kp as b on b.id=p.kp_id where p.position_id="'.$id.'" and (b.is_confirmed_price=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		$set=new mysqlSet('select count(*) from bill_position as p inner join bill as b on b.id=p.bill_id where p.position_id="'.$id.'" and (b.is_confirmed_price=1 or b.is_confirmed_shipping=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		$set=new mysqlSet('select count(*) from sh_i_position as p inner join sh_i as b on b.id=p.sh_i_id where p.position_id="'.$id.'" and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		$set=new mysqlSet('select count(*) from acceptance_position as p inner join acceptance as b on b.id=p.acceptance_id where p.position_id="'.$id.'"  and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		return $can_delete;
	}
	//
	
}
?>
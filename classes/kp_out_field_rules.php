<?

class KpInOut_FieldRules{
	protected $table;
	
	public static $_viewed_ids;
	
	
	
	//�������� �����, �������� �����!
	public function GetFields(array $lead, $user_id, $current_status_id=NULL){
		
		 
		//$viewed_ids=$_plans->GetAvailableUserIds($result['id']);
		 
		
		//echo $role_id;
		
		if($current_status_id===NULL) $status_id=$lead['status_id'];
		else $status_id=$current_status_id;
		
		
		
		//echo $status_id;
		 
		
		if(isset($this->table[$status_id])){
			$result=	$this->table[$status_id];
		}else $result=false;
		
		return $result;
	}
	
	
	public function HasAccess(array $lead, $user_id, $fieldname){
		/*
		$status_id --?
		*/
	 
		 
		
		if(isset($this->table[$lead['status_id']][$fieldname])){
			$result=	$this->table[$lead['status_id']][$fieldname];
		}else $result=false;
		
		return $result;
	}
	
	
	public function GetTable(){ return $this->table; }
	
	
	
	function __construct($result=NULL){
		
		if($result===NULL){
			$_result=new AuthUser();
			$result=$_result->Auth();	
		}
		
		
		$this->table=array();
		
		$data=array();
		
/********************************status*************************************************/
 	
			
		
		$data[1]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_bdr']=false;





 		
		$status_data=$u;
		
		
			
		$data[1]=$status_data;	
		
	 
		
		
		
/********************************status*************************************************/
 	
			
		 
		$data[2]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_bdr']=true;






 		
		$status_data=$u;
		
		
			
		$data[2]=$status_data;	
		
/********************************status*************************************************/
 	
			
		 
		$data[3]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_bdr']=false;







 		
		$status_data=$u;
		
		
			
		$data[3]=$status_data;	
		
 
	
				
		$this->table=$data;
		 
		
		
		
	}
	
}

?>
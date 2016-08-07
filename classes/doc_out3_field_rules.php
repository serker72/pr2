<?

//правила доступности полей по статусам в исх письме
class DocOut_3_FieldRules{
	protected $table;
	
	public static $_viewed_ids;
	
	
	
	//основной метод, работает везде!
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
		
		/*if($result===NULL){
			$_result=new AuthUser();
			$result=$_result->Auth();	
		}
		*/
	
	
		
		$this->table=array();
		
		$data=array();
		
/********************************status*************************************************/
 	
			
		
		$data[18]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=false;
$u['to_email']=false;
$u['to_receive']=false;
$u['topic_txt']=true;
$u['to_confirm']=true;
$u['received_data']=false;
$u['to_copy']=true;





 		
		$status_data=$u;
		
		
			
		$data[18]=$status_data;	
		
		
/********************************status*************************************************/
 	
			
		
		$data[3]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=false;
$u['to_email']=false;
$u['to_receive']=false;
$u['topic_txt']=false;
$u['to_confirm']=false;
$u['received_data']=false;
$u['to_copy']=false;





 		
		$status_data=$u;
		
		
			
		$data[3]=$status_data;	
		
	
		
/********************************status*************************************************/
 	
			
		
		$data[33]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=true;
$u['to_email']=false;
$u['to_receive']=false;
$u['topic_txt']=true;
$u['to_confirm']=true;
$u['received_data']=false;
$u['to_copy']=true;


 		
		$status_data=$u;
		
		
			
		$data[33]=$status_data;	
		
	
		
/********************************status*************************************************/
 	
			
		
		$data[44]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=false;
$u['to_email']=true;
$u['to_receive']=false;
$u['topic_txt']=false;
$u['to_confirm']=true;
$u['received_data']=false;
$u['to_copy']=true;




 		
		$status_data=$u;
		
		
			
		$data[44]=$status_data;	
		
	
		
/********************************status*************************************************/
 	
			
		
		$data[45]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=false;
$u['to_email']=true;
$u['to_receive']=true;
$u['topic_txt']=false;
$u['to_confirm']=true;
$u['received_data']=true;
$u['to_copy']=true;




 		
		$status_data=$u;
		
		
			
		$data[45]=$status_data;	
		
	
		
/********************************status*************************************************/
 	
			
		
		$data[46]=array();
		
		$status_data=array();
		
		$u=array();
		
	$u['to_reg']=false;
$u['to_email']=true;
$u['to_receive']=true;
$u['topic_txt']=false;
$u['to_confirm']=false;
$u['received_data']=false;
$u['to_copy']=true;




 		
		$status_data=$u;
		
		
			
		$data[46]=$status_data;	
		
	
	

											
 		
	
				
		$this->table=$data;
		 
		
		
		
	}
	
}

?>
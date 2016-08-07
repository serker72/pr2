<?

//правила доступности полей по статусам в отчете о расх ав ср-в
class DocVn_2_FieldRules{
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
		
		$u['to_view']=false;
$u['to_email']=false;
$u['is_viewed']=false;
$u['view_comment']=false;
$u['topic_txt']=true;
$u['to_confirm']=true;
$u['to_reply']=false;
$u['to_copy']=true;





 		
		$status_data=$u;
		
		
			
		$data[18]=$status_data;	
		
	 
/********************************status*************************************************/
 	
			
		
		$data[3]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_view']=false;
$u['to_email']=false;
$u['is_viewed']=false;
$u['view_comment']=false;
$u['topic_txt']=false;
$u['to_confirm']=false;
$u['to_reply']=false;
$u['to_copy']=false;





 		
		$status_data=$u;
		
		
			
		$data[3]=$status_data;	
		
	 		
/********************************status*************************************************/
 	
			
		
		$data[33]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_view']=true;
$u['to_email']=true;
$u['is_viewed']=false;
$u['view_comment']=false;
$u['topic_txt']=false;
$u['to_confirm']=true;
$u['to_reply']=false;
$u['to_copy']=true;



 		
		$status_data=$u;
		
		
			
		$data[33]=$status_data;	
		
	 		
/********************************status*************************************************/
 	
			
		
		$data[35]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_view']=false;
$u['to_email']=true;
$u['is_viewed']=true;
$u['view_comment']=true;
$u['topic_txt']=false;
$u['to_confirm']=true;
$u['to_reply']=false;
$u['to_copy']=true;



 		
		$status_data=$u;
		
		
			
		$data[35]=$status_data;	
		

/********************************status*************************************************/
 	
			
		
		$data[47]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_view']=false;
$u['to_email']=true;
$u['is_viewed']=true;
$u['view_comment']=false;
$u['topic_txt']=false;
$u['to_confirm']=false;
$u['to_reply']=true;
$u['to_copy']=true;


 		
		$status_data=$u;
		
		
			
		$data[47]=$status_data;	
		
	
								 
 		
	
				
		$this->table=$data;
		 
		
		
		
	}
	
}

?>
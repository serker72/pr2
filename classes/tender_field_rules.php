<?

class Tender_FieldRules{
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
		
		//�������� ������ ������. ���-���
		/*if(self::$_viewed_ids===NULL){
			$_pls=new Sched_Group;
			self::$_viewed_ids=$_pls->GetAvailableUserIds($result['id'], true);	
		}
		*/
		
		$this->table=array();
		
		$data=array();
		
/********************************status*************************************************/
 	
			
		
		$data[18]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=true;
$u['to_resp_by_dep']=true;



 		
		$status_data=$u;
		
		
			
		$data[18]=$status_data;	
		
	 
/********************************status*************************************************/
 	
			
		
		$data[33]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=true;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=true;
$u['to_resp_by_dep']=true;

 		
		$status_data=$u;
		
		
			
		$data[33]=$status_data;	
		
			
		
		
/********************************status*************************************************/
 	
			
		 
		$data[2]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=true;
$u['to_refuse']=false;
$u['to_cancel']=true;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;

$u['to_resp_by_sup']=true;
$u['to_resp_by_dep']=true;


 		
		$status_data=$u;
		
		
			
		$data[2]=$status_data;	
		
/********************************status*************************************************/
 	
			
		 
		$data[28]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=true;
$u['to_cancel']=true;
$u['to_set_konk']=true;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;




 		
		$status_data=$u;
		
		
			
		$data[28]=$status_data;	
		
				
/********************************status*************************************************/
 	
			
		 $data[34]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=true;

$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;


 		
		$status_data=$u;
		
		
			
		$data[34]=$status_data;	



			
/********************************status*************************************************/
 	
			
		 $data[3]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;




 		
		$status_data=$u;
		
		
			
		$data[3]=$status_data;	
		

			
/********************************status*************************************************/
 	
			
		 $data[32]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;

$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;



 		
		$status_data=$u;
		
		
			
		$data[32]=$status_data;	
		


			
/********************************status*************************************************/
 	
			
		 $data[36]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;

$u['to_rework']=false;
$u['to_restore_work']=true;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;



 		
		$status_data=$u;
		
		
			
		$data[36]=$status_data;			



			
/********************************status*************************************************/
 	
			
		 $data[26]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=true;
$u['to_set_konk']=false;
$u['to_go_konk']=true;
$u['to_no_go_konk']=true;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=true;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;



 		
		$status_data=$u;
		
		
			
		$data[26]=$status_data;			
			


			
/********************************status*************************************************/
 	
			
		 $data[23]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=true;
$u['to_cancel']=true;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=true;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;


 		
		$status_data=$u;
		
		
			
		$data[23]=$status_data;			
			
		

		
/********************************status*************************************************/
 	
			
		 $data[29]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=true;
$u['to_cancel']=true;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=true;
$u['to_fail']=true;
$u['to_rework']=false;
$u['to_restore_work']=false;

$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;

 		
		$status_data=$u;
		
		
			
		$data[29]=$status_data;			
			
		
/********************************status*************************************************/
 	
			
		 $data[30]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;

$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;


 		
		$status_data=$u;
		
		
			
		$data[30]=$status_data;			
			
		
/********************************status*************************************************/
 	
			
		 $data[31]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=false;
$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;


 		
		$status_data=$u;
		
		
			
		$data[31]=$status_data;			
			
		
			
	/********************************status*************************************************/
 	
			
		 $data[37]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['to_work']=false;
$u['to_refuse']=false;
$u['to_cancel']=false;
$u['to_set_konk']=false;
$u['to_go_konk']=false;
$u['to_no_go_konk']=false;
$u['to_claim_konk']=false;
$u['to_win']=false;
$u['to_fail']=false;
$u['to_rework']=false;
$u['to_restore_work']=true;


$u['to_resp_by_sup']=false;
$u['to_resp_by_dep']=false;

 		
		$status_data=$u;
		
		
			
		$data[37]=$status_data;			
			
		
					
	  
	
				
		$this->table=$data;
		 
		
		
		
	}
	
}

?>
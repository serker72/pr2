<?

//������� ����������� ����� �� �������� � ����-�� �� ���-��
class DocVn_1_FieldRules{
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
		
		$u['common']=true;
$u['plan']=true;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=true;
$u['to_done']=false;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;

$u['to_petition']=false;




 		
		$status_data=$u;
		
		
			
		$data[18]=$status_data;	
		
	 
/********************************status*************************************************/
 	
			
		
		$data[33]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=true;
$u['to_done']=false;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=true;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;







 		
		$status_data=$u;
		
		
			
		$data[33]=$status_data;	
		
	 		
/********************************status*************************************************/
 	
			
		
		$data[41]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=true;
$u['to_done']=false;
$u['to_ruk_sz']=true;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=true;
$u['to_rework_ot']=false;
$u['to_petition']=true;





 		
		$status_data=$u;
		
		
			
		$data[41]=$status_data;	
		
	 		
/********************************status*************************************************/
 	
			
		
		$data[42]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=false;
$u['to_ruk_sz']=true;
$u['to_dir_sz']=true;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=true;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;





 		
		$status_data=$u;
		
		
			
		$data[42]=$status_data;	
		

/********************************status*************************************************/
 	
			
		
		$data[43]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=false;
$u['to_ruk_sz']=true;
$u['to_dir_sz']=true;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=true;
$u['to_rework_ot']=false;

$u['to_petition']=true;



 		
		$status_data=$u;
		
		
			
		$data[43]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[2]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=true;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=true;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;


 		
		$status_data=$u;
		
		
			
		$data[2]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[8]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=true;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;




 		
		$status_data=$u;
		
		
			
		$data[8]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[48]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=true;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=true;
$u['to_petition']=true;




 		
		$status_data=$u;
		
		
			
		$data[48]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[49]=array();
		
		$status_data=array();
		
		$u=array();
		
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=true;
$u['to_dir_ot']=true;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=true;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;



 		
		$status_data=$u;
		
		
			
		$data[49]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[50]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=true;
$u['to_dir_ot']=true;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=true;
$u['to_petition']=true;




 		
		$status_data=$u;
		
		
			
		$data[50]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[51]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=true;
$u['to_print_sz']=true;
$u['to_print_email']=true;
$u['to_confirm']=false;
$u['to_done']=true;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=true;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=true;



 		
		$status_data=$u;
		
		
			
		$data[51]=$status_data;	
		
	
/********************************status*************************************************/
 	
			
		
		$data[3]=array();
		
		$status_data=array();
		
		$u=array();
		
		$u['common']=false;
$u['plan']=false;
$u['fact']=false;
$u['to_email']=false;
$u['to_print_sz']=false;
$u['to_print_email']=false;
$u['to_confirm']=false;
$u['to_done']=false;
$u['to_ruk_sz']=false;
$u['to_dir_sz']=false;
$u['to_ruk_ot']=false;
$u['to_dir_ot']=false;
$u['send_ruk_sz']=false;
$u['send_dir_sz']=false;
$u['send_ruk_ot']=false;
$u['send_dir_ot']=false;
$u['to_rework_sz']=false;
$u['to_rework_ot']=false;
$u['to_petition']=false;



 		
		$status_data=$u;
		
		
			
		$data[3]=$status_data;	
		
	
															 
 		
	
				
		$this->table=$data;
		 
		
		
		
	}
	
}

?>
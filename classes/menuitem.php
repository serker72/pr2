<?


require_once('taskincominghistorygroup.php');
require_once('missionhistorygroup.php');
require_once('messenger_group.php');

require_once('memoincominghistorygroup.php');

 
require_once('petitionblink.php');
require_once('petitionallgroup.php');
require_once('memoallgroup.php');
require_once('program_group.php');
require_once('tender.class.php');
require_once('lead.class.php');
require_once('tz.class.php');
require_once('doc_vn.class.php');


class MenuItem{
	public $id;
	public $parent_id;
	public $object_id;
	public $name;
	public $description;
	public $url;
	public $ord;
	public $is_messages;
	public $is_komplekts;
	public $is_tasks;
	public $is_missions;
	/*public $is_orders;
	public $is_my_orders;
	public $is_pret;
	public $is_my_pret;
	public $is_claim;
	public $is_my_claim;*/
	
		public $is_messenger;
		
	public $is_switch;
		
	public $is_petitons;
	public $is_memos;
	
	public $user_id;
	
	public $is_pic;
	public $pic;
	public $has_open_tag;
	public $has_close_tag;
	public $has_style_indent;
	protected $_auth_result;
	
	public $has_searchform_after;
	public $is_tenders;
	public $is_leads;
	
	public $is_tzs;
	
	public $is_doc_vn;
	
	
	function __construct($id, $parent_id, $object_id, $name, $description, $url, $ord,$is_messages, $is_uploads,$user_id, $is_komplekts, $is_pic, $pic, $has_open_tag, $has_close_tag, $has_style_indent, $is_tasks, $is_missions, $is_messenger,  $is_memos, $is_petitons, $is_switch, $has_searchform_after, $is_tenders, $is_leads, $is_tzs, $is_doc_vn/*,$is_orders,$is_my_orders, $is_pret,$is_my_pret, $is_claim,$is_my_claim*/){
		$this->id=$id;
		$this->parent_id=$parent_id;
		$this->object_id=$object_id;
		$this->name=$name;
		$this->description=$description;
		$this->url=$url;
		$this->ord=$ord;
		$this->is_messages=$is_messages;
		$this->is_uploads=$is_uploads;
		
		$this->user_id=$user_id;
		$this->is_komplekts=$is_komplekts;
		
		$this->is_tasks=$is_tasks;
		$this->is_missions=$is_missions;
		
		$this->is_messenger=$is_messenger;
		
		$this->is_petitons=$is_petitons;
		$this->is_memos=$is_memos;
		$this->is_switch=$is_switch;
		$this->has_searchform_after=$has_searchform_after;
		$this->is_tenders=$is_tenders;
		$this->is_leads=$is_leads;
		$this->is_tzs=$is_tzs;
		$this->is_doc_vn=$is_doc_vn;
	
		/*if($is_petitons==1){
			echo' yyyyyyyyyyyyyy';
		}	*/
		
		/*$this->is_orders=$is_orders;
		$this->is_my_orders=$is_my_orders;
		
		
		$this->is_pret=$is_pret;
		$this->is_my_pret=$is_my_pret;
		
		$this->is_claim=$is_claim;
		$this->is_my_claim=$is_my_claim;*/
		
		$this->is_pic=$is_pic;
		$this->pic=$pic;
		$this->has_open_tag=$has_open_tag;
		$this->has_close_tag=$has_close_tag;
		$this->has_style_indent=$has_style_indent;
		$this->_auth_result=NULL;
		
		
	}
	
	public function DeployItem(){}
	
	protected function CodeItem($current_id=0){
		
		$au=new AuthUser;
		  
	   // $res=$au->Auth();
		
		
		if($this->is_messages==1){
		  $mg=new MessageGroup();
		 
		  
		  //$count_of_messages=$mg->CalcNew($res['id']);
		  $count_of_messages=$mg->CalcNew($this->user_id);
		}else $count_of_messages=0;
		
		
		if($this->is_messenger==1){
		  $mg=new MessengerGroup;
		 
		  
		  //$count_of_messages=$mg->CalcNew($res['id']);
		  $messenger_count_of_messages=$mg->CalcNew($this->user_id);
		}else $messenger_count_of_messages=0;
		
		
		
		if($this->is_tasks==1){
		  $mg=new TaskIncomingHistoryGroup;
		 
		//  echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		  //$count_of_messages=$mg->CalcNew($res['id']);
		  $count_of_tasks=$mg->CountNewOrders($this->user_id);
		}else $count_of_tasks=0;
		
		if($this->is_missions==1){
		  $mg=new MissionHistoryGroup;
		 
		//  echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		  //$count_of_messages=$mg->CalcNew($res['id']);
		  $count_of_missions=$mg->CountNewOrders($this->user_id);
		}else $count_of_missions=0;
		
		
		if($this->is_memos==1){
		  //$mg=new MemoIncomingHistoryGroup;
		 
		  //$count_of_memos=$mg->CountNewOrders($this->user_id);
		  $tg=new MemoAllGroup;
			$data_of_memos=$tg->CountNewDocsExtended($this->user_id, false);
		}else $data_of_memos=array();
		
		
		if($this->is_petitons==1){
		 // $mg=new PetitionBlink;
		 
		  //echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		  //$count_of_messages=$mg->CalcNew($res['id']);
		 	// $count_of_petitions=$mg->CountNewOrders($this->user_id);
			$tg=new PetitionAllGroup;
			$data_of_petitions=$tg->CountNewDocsExtended($this->user_id, false);
		}else $data_of_petitions=array();
		 
		if($this->is_tenders==1){
			$tg=new Tender_Group;
			$data_of_tenders=$tg->CountNewTendersExtended($this->user_id, false);
		}else $data_of_tenders=array();
		
		
		
		if($this->is_leads==1){
			$tg=new Lead_Group;
			 
			$data_of_leads=$tg->CountNewLeadsExtended($this->user_id,  false);
			
			 
		}else $data_of_leads=array();
		
		if($this->is_tzs==1){
			$tg=new TZ_Group;
			$count_of_tzs=$tg->CountNewTzs($this->user_id);
			
			 
		}else $count_of_tzs=0;
		
		if($this->is_doc_vn==1){
			$tg=new DocVn_Group;
			$count_of_dvs=$tg->CountNewDocsExtended($this->user_id,  false);
			
			 
		}else $count_of_dvs=0;
		
		 
		$count_of_komplekts=0;
		
	 
		
		$has_change_base=false;
		if($this->is_switch==1){	
			$_pg=new ProgramGroup;
			
			$_pg->FindAccess($this->_auth_result['email_s'], ($this->_auth_result['password']), 'login_program.html', $matched, $this->_auth_result['org_id'], true);
			 
			
			$has_change_base= (bool)(count($matched)>0);
			
		
		}
		

		
		$res=array('id'=>$this->id, 
					 'parent_id'=>$this->parent_id, 
					 'object_id'=>$this->object_id, 
					 'name'=>$this->name,
					 'description'=>$this->description,
					 'url'=>$this->url,
					 'ord'=>$this->ord,
					 'is_messages'=>$this->is_messages,
					 'is_uploads'=>$this->is_uploads,
					 'is_komplekts'=>$this->is_komplekts,
					 'is_tasks'=>$this->is_tasks,
					 'is_missions'=>$this->is_missions,
					  'is_messenger'=>$this->is_messenger,
					 
					 'is_memos'=>$this->is_memos,
					 'is_petitons'=>$this->is_petitons,
					 'is_tenders'=>$this->is_tenders,
					 'is_leads'=>$this->is_leads,
					 'is_tzs'=>$this->is_tzs,
					 'is_doc_vn'=>$this->is_doc_vn,
					
					 
					 
					/* 'is_my_orders'=>$this->is_my_orders,
					 'is_orders'=>$this->is_orders,
					 'count_of_orders'=>$count_of_orders,
					 'count_of_my_orders'=>$count_of_myorders,*/
					 'count_of_messages'=>$count_of_messages,
					 'count_of_komplekts'=>$count_of_komplekts,
					 'count_of_tasks'=>$count_of_tasks,
					 'count_of_missions'=>$count_of_missions,
					 'messenger_count_of_messages'=>$messenger_count_of_messages,
					 
					 'count_of_memos'=>$count_of_memos,
					 'count_of_petitions'=>$count_of_petitions,
					 'data_of_tenders'=>$data_of_tenders,
					 'data_of_leads'=>$data_of_leads,
					 'data_of_petitions'=>$data_of_petitions,
					 'data_of_memos'=>$data_of_memos,
					 'count_of_tzs'=>$count_of_tzs,
					 'count_of_dvs'=>$count_of_dvs,
					 
				/*	 'is_my_pret'=>$this->is_my_pret,
					 'is_pret'=>$this->is_pret,
					 'count_of_pret'=>$count_of_pret,
					 'count_of_my_pret'=>$count_of_mypret,
					 
					 'is_my_claim'=>$this->is_my_claim,
					 'is_claim'=>$this->is_claim,
					 'count_of_claim'=>$count_of_claim,
					 'count_of_my_claim'=>$count_of_myclaim*/
					 
					 'is_pic'=>$this->is_pic,
					 'pic'=>$this->pic,
					 'has_open_tag'=>$this->has_open_tag,
					 'has_close_tag'=>$this->has_close_tag,
					  'has_style_indent'=>$this->has_style_indent,
					  
					  'has_change_base'=>$has_change_base,
					  'is_switch'=>$this->is_switch,
					  'has_searchform_after'=>$this->has_searchform_after,
					  'is_active'=>(int)(($current_id==$this->id)||($count_of_tenders>0)), 
					  
					  //для тендеров: передать раскрытие родительского блока, если новых тендеров больше 0
					 // 'parent_active'=>(int)($count_of_tenders>0)
					  
					 );	
					 //echo $current_id.' vs '.$this->id.'<br>';
		return $res;
	}
	
	public function SetAuthResult($result){
		$this->_auth_result=$result;	
	}

}
?>
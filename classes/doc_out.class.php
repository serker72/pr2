<?
 
require_once('abstractitem.php');
 
require_once('supplieritem.php');
require_once('suppliercontactitem.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactdatagroup.php');
require_once('user_s_group.php');
require_once('supplier_cities_group.php');

require_once('supplier_responsible_user_group.php');
require_once('supplier_responsible_user_item.php');
require_once('actionlog.php');

require_once('doc_out_history_group.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('lead_view_item.php');
require_once('tender.class.php');
require_once('lead_field_rules.php');
require_once('pl_prodgroup.php');
require_once('doc_out_view.class.php');
 

require_once('doc_out1_field_rules.php');
require_once('doc_out2_field_rules.php');
require_once('doc_out3_field_rules.php');

require_once('abstract_working_kind_group.php');

require_once('doc_out_history_item.php');
require_once('lead.class.php');
 

require_once('kp_supply_pdate_group.php');
require_once('kp_supply_pdate_item.php');
require_once('docstatusitem.php');
require_once('messageitem.php');

//���������� ������� ��� ����������


//����������� ������ ��� ���������
class DocOut_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='doc_out';
		$this->item=NULL;
		$this->pagename='ed_doc_out.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=8;
		
		switch($params['kind_id']){
			/*case 0:
				$begin='���';
			break;
			case 1:
				$begin='���';
			break;*/
			 
			default:
				$begin='��';
			break;
				
			
		}
		
		
		 
		
		$sql='select max(code) from '.$this->tablename.' where  code REGEXP "^'.$begin.'[0-9]+"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			
			//$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi($begin."([[:digit:]]{".$digits."})",$f[0],$regs);
			//print_r($regs);
			
			
			$number=(int)$regs[1];
			//print_r($regs); die();
			$number++;
			
			$test_login=$begin.sprintf("%0".$digits."d",$number);
			
			 
			$login=$test_login;
		}else{
			
			//$f=mysqli_fetch_array($rs);	
			$login=$begin.sprintf("%0".$digits."d",1);
			
		 
		}
		
		// echo $login; die();
		
		$params['code']=$login;
		
		
		return AbstractItem::Add($params);
		
	}
	
	
	//��������� ���������������� ������
	public function GenRegNo($id){
		
		$digits=8;
	 
		$begin='��';
		
		$sql='select max(reg_no) from '.$this->tablename.' where  reg_no REGEXP "^'.$begin.'[0-9]+"';// and id<>"'.$id.'"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			
			//$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi($begin."([[:digit:]]{".$digits."})",$f[0],$regs);
			//print_r($regs);
			
			
			$number=(int)$regs[1];
			//print_r($regs); die();
			$number++;
			
			$test_login=$begin.sprintf("%0".$digits."d",$number);
			
			 
			$login=$test_login;
		}else{
			
			//$f=mysqli_fetch_array($rs);	
			$login=$begin.sprintf("%0".$digits."d",1);
			
		 
		}
		
		// echo $login; die();
		
		return $login;
		
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);	
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������, ���� ����� ������������
			if(!$au->user_rights->CheckAccess('w',1070)){
				$_hg=new DocOut_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='�� ���. ��������� �������� '.$cou.' ������������';
					$reason.=implode(', ',$reasons);
				}
			}	 
			
		}
		
		 
		return $can;
	}
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ���. ������ �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_received']==1){
			
			$can=$can&&false;
			$reasons[]='� ���. ������ ���������� ���������';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ���. ������ ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			
			/*if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� �� �� ������ ������������� ���������';
			
			}*/
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmReceive($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_received']!=1){
			
			$can=$can&&false;
			$reasons[]='� ���. ������ �� ���������� ���������';
			$reason.=implode(', ',$reasons);
		}/*elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='� ��������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		 
		}*/
		
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmReceive($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_received']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ���. ������ ���������� ���������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			$reasons[]='� ���. ������ �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		 
		}else{
			
			/*if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� �� �� ������ ������������� ���������';
			
			}*/
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		parent::Edit($id, $params);
		
		 
		//if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
 
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//����� ������� �� 33
				$setted_status_id=33;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���. ���������',NULL,1065,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				//����� ������� �� 18
				$setted_status_id=18;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���. ���������',NULL,1072,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				$_signs=new DocOut_SignGroup;
				$_signs->ClearSigns($id);
				
			}
		} 
		
		
		elseif(isset($new_params['is_received'])&&isset($old_params['is_received'])){
			if(($new_params['is_received']==1)&&($old_params['is_received']==0)){
				//����� ������� �� 46
				$setted_status_id=46;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���. ������',NULL,1077,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_received']==0)&&($old_params['is_received']==1)){
				//����� ������� �� 45
				$setted_status_id=45;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���. ������',NULL,1078,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				 
			}
		} 
			
			
		 //���������� ������ 44 - ��������������� - ������������� �������. ������ � ����������� ������� ����
		 if(isset($new_params['status_id'])&&isset($old_params['status_id'])){
			if(($new_params['status_id']==44)&&($old_params['status_id']!=44)){
				
				$this->Edit($id, array('reg_pdate'=>DateFromDMY(date('d.m.Y')), 'reg_no'=>$this->GenRegNo($id)),false, $_result);
			}
		 }
		//die();
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '��������� ��������, ������ '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '��������� �������� '.$item['code'].', ������ '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		return date('d.m.Y', $item['pdate_beg']);
	}
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	//�������� �������� ��� �� ������
	public function ConstructContacts($id, $item=NULL){
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		 
				 
		
			$_addr=new TenderContactItem;
			$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
			
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'];
		
		 
		
		
		return $res;
	}
	
	 
	 
	
}


/*************************************************************************************************/
//��������� ������
class DocOut_Item extends DocOut_AbstractItem{
	public $kind_id=1;
	
  
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		DocOut_AbstractItem::Edit($id, $params);
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	 
	 
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='��������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//������ �������
		
		
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='��������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
	 
		return $res; //', ������ '.$stat['name'];
	}
	
	 
	
	
	
	 
}

/*************************************************************************************************/
//�������������� ������
class DocOut_InfItem extends DocOut_AbstractItem{
	public $kind_id=2;
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		DocOut_AbstractItem::Edit($id, $params);
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	 
	
	
	 
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='��������� �������������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//������ �������
		
		
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='��������� �������������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
	 
		return $res; //', ������ '.$stat['name'];
	}
	
}

/*************************************************************************************************/
//���������������� ������
class DocOut_SoprItem extends DocOut_AbstractItem{
	public $kind_id=3;
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		DocOut_AbstractItem::Edit($id, $params);
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	 
	
	
	 
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='���������������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//������ �������
		
		
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='���������������� ������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new DocOut_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
	 
		return $res; //', ������ '.$stat['name'];
	}
	
}

 


/***********************************************************************************************/
//����������� ������ ������
class DocOut_Resolver{
	public $instance, $group_instance, $rules_instance;
	function __construct($kind_id=1){
		switch($kind_id){
			case 1:
				$this->instance= new DocOut_Item;
				$this->group_instance=new DocOut_Group;
				$this->rules_instance=new DocOut_1_FieldRules;
			break;
			case 2:
				$this->instance= new DocOut_InfItem;
				$this->group_instance= new DocOut_InfGroup;
				$this->rules_instance=new DocOut_2_FieldRules;
			break;
			case 3:
				$this->instance= new DocOut_SoprItem;
				$this->group_instance= new DocOut_SoprGroup;
				$this->rules_instance=new DocOut_3_FieldRules;
			break;
			 
			default:
				$this->instance=new DocOut_Item;
				$this->group_instance=new DocOut_Group;
				$this->rules_instance=new DocOut_1_FieldRules;
			break;
		}; 
		 
	}
	
	 
}


/****************************************************************************************************/
//����������� ������ ���. ����������
class DocOut_AbstractGroup extends AbstractGroup{
	public $kind_id=1;
	
	protected $_auth_result;
	protected $_view;
	
	protected $new_list; //������ ����� ���������� ��� �������� ������������ � ��������� �� �� ������
	 
	protected function init(){
		$this->tablename='doc_out';
		$this->pagename='doc_outs.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocOut_ViewsGroup1;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	}
	
	//������ ����������
    public function ShowPos( 
		$template, //0
		DBDecorator $dec, //1
		$can_create=false, //2
		$can_edit=false,  //3
		$from, //4
		$to_page, //5
		$has_header=true,  //6
		$is_ajax=false, //7
		$can_delete=true, //8
		$can_restore=true, //9
		$can_confirm_price=true, //10
		$can_unconfirm_price=true, //11
		$can_email=true, //12
		 
	 	$prefix='' //13
	 
		
		){
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		  $sm->assign('can_create', $can_create);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 
		 $sm->assign('can_email', $can_email);
		 $sm->assign('kind_id', $this->kind_id);
		
		
		
		
		 //������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1072);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new DocOut_SupplierGroup;
		$_lds=new DocOut_LeadGroup;
		
	 
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active ,
		send.name_s as send_user
		 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
			 
			where p.kind_id="'.$this->kind_id.'"	 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
				left join user as send on send.id=p.send_user_id
			 
			 
			where p.kind_id="'.$this->kind_id.'"	 
		 
				 ';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
	//	$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10, '&'.$link);
		$navig->SetFirstParamName('from'.$prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		$man=new DiscrMan;
		
		$this->new_list=NULL;
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			if($f['reg_pdate']!=0) $f['reg_pdate']=date('d.m.Y', $f['reg_pdate']); else $f['reg_pdate']='-';
			
			if($f['send_pdate']!=0) $f['send_pdate']=date('d.m.Y', $f['send_pdate']); else $f['send_pdate']='-';
			
			if($f['received_pdate']!=0) $f['received_pdate']=date('d.m.Y', $f['received_pdate']); else $f['received_pdate']='-';
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			 
			 
			$_res=new  DocOut_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			//$f['supplierstz']=$_tsg->GetItemsByIdArr($f['id']);	
				
			$f['field_rules']=$_res->rules_instance->GetFields($f,$this->_auth_result['id'],$f['status_id']);  
			 
			
			 //�������� ����� "����� ��������"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			$f['leads']=$_lds->GetItemsByIdArr($f['id'], false, $f);
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//�������� ������ ������
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		 
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('prefix',$prefix);
		 
		
		$sm->assign('can_edit',$can_edit);
	 
 
		 //����� ������������
		 
			$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		 
	 	
		
		//������ ��� ������ ����������
			$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		$sm->assign('link',$link);
		
//		echo $link;
		
		
		return $sm->fetch($template);
	}
	
	
	//�������� �� ������� �������� ��� ������� ������������ � ���������, ���� �������� - ������� ������ ��� ���������� �����
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'������� ���� � ������!'
				
				);
		*/
		
		//������������ ������ ������
		foreach($this->new_list as $k=>$type){
			if(in_array($document_id, $type['doc_ids'])){
				
				$url=str_replace('{id}',$document_id, $type['url'], $subst_count);
				
				$sub_id=$type['sub_ids'][array_search($document_id,$type['doc_ids'])];
				
								
				$url=str_replace('{sub_id}',$sub_id, $url, $subst_count1);
				$subst_count+=$subst_count1;
				
				if($subst_count==0) $url=$type['url'].$document_id;
				
				$data[]=array(
					'class'=>$type['class'],
					
					'url'=>$url,
					'comment'=>$type['comment'],
					'doc_counters'=>(int)$type['doc_counters'][array_search($document_id, $type['doc_ids'])]
				

				
				);	
			}
		}
		
		 
		
		return $data;	
	}	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//������ ��� ���������� �� ���������� (� �.�. ��������� �� ���������)
    public function ShowPosArrByDec( 
		 
		DBDecorator $dec 
		 
		
		){
		 
		 
 
		
		 
		$_sg=new DocOut_SupplierGroup;
		$_lds=new DocOut_LeadGroup;
		
	 
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active ,
		send.name_s as send_user
		 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
			 
			 	 
				 	 
				 ';
				
		 
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			 
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	  
		$alls=array();
		$man=new DiscrMan;
		
		 
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			if($f['reg_pdate']!=0) $f['reg_pdate']=date('d.m.Y', $f['reg_pdate']); else $f['reg_pdate']='-';
			
			if($f['send_pdate']!=0) $f['send_pdate']=date('d.m.Y', $f['send_pdate']); else $f['send_pdate']='-';
			
			if($f['received_pdate']!=0) $f['received_pdate']=date('d.m.Y', $f['received_pdate']); else $f['received_pdate']='-';
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			 
			 
			$_res=new  DocOut_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			//$f['supplierstz']=$_tsg->GetItemsByIdArr($f['id']);	
				
			$f['field_rules']=$_res->rules_instance->GetFields($f,$this->_auth_result['id'],$f['status_id']);  
			 
			
			  
			$f['leads']=$_lds->GetItemsByIdArr($f['id'], false, $f);
			
			
			$f['name']=$_res->instance->ConstructName($f['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		return $alls;
	}
	
}


/****************************************************************************************************/
// ������ ��� �����
class  DocOut_Group extends DocOut_AbstractGroup {
	 public $kind_id=1;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out';
		$this->pagename='doc_outs.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocOut_ViewsGroup1;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	
	
	
	
	
	//������ ID ����������, ������� ����� ������ ������� ���������
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			$sql='select id from doc_out';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from doc_out where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
					
			} 
			
			//��������� ���� ����� ��������������
			$sql.=' or id in(select distinct doc_out_id from  doc_out_sign  where user_id="'.$user['id'].'" )';
			
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
			
		}
		
		 
		//������� -1 ��� ������������
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//��������, ����� �� �������� �������� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			 
			
			return true;
		}else{
			//����
			
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	//$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
				$user_ids=array();
				$sql=' select id from user where department_id="'.$user['department_id'].'"';
				$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
				}
				if(in_array($manager_id,$user_ids)) return true;	 
			} 
			 
			
		}
		
		 
		return false;
		
	}  
	
	
	  
	
	
	//��������������� ������ ����� ����� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//�� �������� �������, ����������� ����� � ����� ������� ������ ��������� ������������ �� ��
		/*if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				order by t.id 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
					'comment'=>'����� �������: ��������� ������������ �� ��!'
				
				);
			}
		}*/
		
		 
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 
	}
	
}




/****************************************************************************************************/
// ������ �������������� �����
class  DocOut_InfGroup extends DocOut_AbstractGroup {
	 public $kind_id=2;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out';
		$this->pagename='doc_outs.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocOut_ViewsGroup2;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	
	
	
	
	
	//������ ID ����������, ������� ����� ������ ������� ���������
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			$sql='select id from doc_out';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from doc_out where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
					
			} 
			
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
			
		}
		
		 
		//������� -1 ��� ������������
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//��������, ����� �� �������� �������� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			 
			
			return true;
		}else{
			//����
			
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	//$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
				$user_ids=array();
				$sql=' select id from user where department_id="'.$user['department_id'].'"';
				$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
				}
				if(in_array($manager_id,$user_ids)) return true;	 
			} 
			 
			
		}
		
		 
		return false;
		
	}  
	
	
	  
	
	
	//��������������� ������ ����� ����� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//�� �������� �������, ����������� ����� � ����� ������� ������ ��������� ������������ �� ��
		/*if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				order by t.id 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
					'comment'=>'����� �������: ��������� ������������ �� ��!'
				
				);
			}
		}*/
		
		 
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 
	}
	
}



/****************************************************************************************************/
// ������ ���������������� �����
class  DocOut_SoprGroup extends DocOut_AbstractGroup {
	 public $kind_id=3;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out';
		$this->pagename='doc_outs.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocOut_ViewsGroup3;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	
	
	
	
	
	//������ ID ����������, ������� ����� ������ ������� ���������
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			$sql='select id from doc_out';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from doc_out where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
					
			} 
			
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
			
		}
		
		 
		//������� -1 ��� ������������
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//��������, ����� �� �������� �������� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1068)){
			 
			
			return true;
		}else{
			//����
			
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	//$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
				$user_ids=array();
				$sql=' select id from user where department_id="'.$user['department_id'].'"';
				$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
				}
				if(in_array($manager_id,$user_ids)) return true;	 
			} 
			 
			
		}
		
		 
		return false;
		
	}  
	
	
	 
	
	
	
	
	//��������������� ������ ����� ����� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//�� �������� �������, ����������� ����� � ����� ������� ������ ��������� ������������ �� ��
		/*if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				order by t.id 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
					'comment'=>'����� �������: ��������� ������������ �� ��!'
				
				);
			}
		}*/
		
		 
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 
	}
	
}








   
    




/*******************************************************************************************************/


// ���������� ��������� �-��
class DocOut_SupplierContactGroup extends SupplierContactGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, $current_k_id=0){
		$arr=array();
		
                // KSK - 01.04.2016
                // ��������� ������� ������ ������� ��� ����������� is_shown=1
		//$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'=1 order by id asc');
		
		
		
		$_sdg=new DocOut_SupplierContactDataGroup;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['data']=$_sdg->GetItemsByIdArr($f['id'], $current_k_id, array(1,2,3,4,5,6,7,8) );
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
}


//  ������ ��������� �������� �����������
class DocOut_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->pagename='view.php';		
		$this->subkeyname='contact_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, array $kinds){
		$arr=Array();
		$set=new MysqlSet('select p.*,
			pc.name as pc_name, pc.icon as pc_icon
		
		 from '.$this->tablename.' as p left join supplier_contact_kind as pc on pc.id=p.kind_id
		  where p.'.$this->subkeyname.'="'.$id.'" and pc.id in('.implode(', ', $kinds).') order by p.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}


//����������� ���. ���������
class DocOut_SupplierItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class DocOut_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_suppliers';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//����� ����������� ��� ������� ������������
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$_csg=new SupplierCitiesGroup;
		
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth(false,false);
		
		$sql='select p.*, po.name as opf_name from supplier as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='(( p.is_org=0 and p.is_active=1 and p.org_id='.$resu['org_id'].') or (p.is_org=1 and p.is_active=1 and p.id<>'.$resu['org_id'].')) ';
		
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']= $csg;	 
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//������ ��� ������ ����������
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	 
	//������ �������, ����� ����
	public function GetItemsByIdArr($id){
		$arr=array(); $_csg=new SupplierCitiesGroup;
		$_sc=new DocOut_ScGroup;
		
		$sql='select ct.sched_id, ct.id as c_id, ct.note,  s.full_name, opf.name as opf_name,
		s.id as supplier_id, s.id as id
		
		 from '.$this->tablename.' as ct 
		 left join supplier as s on ct.supplier_id=s.id
		 left join opf as opf on s.opf_id=opf.id
		  
		
		where ct.sched_id="'.$id.'" order by s.full_name asc';
		 
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$csg=$_csg->GetItemsByIdArr($f['supplier_id']);
			$f['cities']= $csg;	
			
			$f['contacts']=$_sc->GetItemsByIdArr( $f['c_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//������� �������
	public function AddSuppliers($current_id, array $positions,  $result=NULL){
		$_kpi=new DocOut_SupplierItem; $_cg=new DocOut_ScGroup;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				
				$_cg->ClearById($code); $_cg->PutIds($code, $v['contacts']);
				
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				$_cg->ClearById($kpi['id']); 
				$_cg->PutIds($kpi['id'], $v['contacts']);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� prava
				
				$to_log=false;
				if($kpi['city_id']!=$add_array['city_id']) $to_log=$to_log||true;
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['c_id']);
			
			$_cg->ClearById($v['c_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	//������� ������������ ��� ���������
	public function AddSuppliersWoCont($current_id, array $positions,  $result=NULL){
		$_kpi=new DocOut_SupplierItem;  
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['c_id']);
			
		 
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	 
	
}

//�������� ����������� �� ��� ���-��	
class DocOut_ScItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class DocOut_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//������, ����� ����
	public function GetItemsByIdArr($id){
		$arr=array();
		
		$sql='select p.sc_id, p.contact_id, p.contact_id as id, p.id as c_id,
		
		c.name, c.position
		
		
		
		 from '.$this->tablename.' as p
		 
		 inner join supplier_contact as c on p.contact_id=c.id
		 
		  where p.sc_id="'.$id.'" order by  c.name asc';
		//echo $sql;
		 $set=new MysqlSet($sql);
	 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
 	
	public function ClearById($id){
		$sql='delete
		
		 from '.$this->tablename.' 
		 
		 
		  where  sc_id="'.$id.'" ';
		  
		  //echo $sql; die();
		
		 $set=new nonSet($sql);
	}
	
	public function PutIds($id, $ids){
		
		$sql='insert into  '.$this->tablename.' (sc_id, contact_id) values ';
		
		$_pairs=array();
		foreach($ids as $k=>$v) $_pairs[]=' ("'.$id.'", "'.$v.'") ';
		$sql.= implode(', ',$_pairs);
		
		if(count($ids)>0) $set=new nonSet($sql);
	}
		
}


/******************************************************************************************************/
//���� ��� ���-���

//������ ��� ��� ���-���
class DocOut_KindItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ ����� ��� ���-���
class DocOut_KindGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_kind';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
}


/****************************************************************************************************/
//����������-�������������

//������ ���������
class DocOut_SignItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_sign';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ �����������, ���������� �� �� ���� ����������
class DocOut_SignGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_sign';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	//������ ������� - ��������� ����� ��� ���������� ������
	public function GetItemsByIdArr($id, $sign_kind_id, $show_statistics=true, $document=NULL, $_result=NULL){
		$alls=array();
		
		if($document===NULL){
			$_doc=new DocOut_AbstractItem;
			$document=$_doc->GetItemById($id);
			
		}
		$au=new AuthUser();
		
		if($_result===NULL){
			
			$_result=$au->Auth(false,false,false);	
		}
		
		$_res=new DocOut_Resolver($document['kind_id']);
		$fields=$_res->rules_instance->GetFields($document, $_result['id']);
		
		
		 
		$sql='select des.*,  
			doc.name_s as user_name_s, up.name as position_name, doc.is_active,
			si.name_s as si_name_s
			
		 
		from  doc_out_sign as des
		inner join user as doc on doc.id=des.user_id
		left join user_position as up on up.id=doc.position_id
		left join user as si on si.id=des.user_sign_id
		
		where 
		 	des.'.$this->subkeyname.'="'.$id.'" 
			and des.sign_kind_id="'.$sign_kind_id.'" 
			
		
			order by doc.name_s asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['is_enabled']=true;
			
			
			
			//���������� ������� �������
			if($sign_kind_id==1){
				if(!$fields['to_sign_1_checks']) $f['is_enabled']=$f['is_enabled']&&false;
				
				//var_dump($fields);
				if($f['is_signed']==0){
					if(!(
						$au->user_rights->CheckAccess('w',1073)
						||
						($f['user_id']==$_result['id'])
					
					)) $f['is_enabled']=$f['is_enabled']&&false;
				}else{
					if(!(
						$au->user_rights->CheckAccess('w',1074)
						||
						($f['user_id']==$_result['id'])
					
					)) $f['is_enabled']=$f['is_enabled']&&false;
				}
				
					
			}elseif($sign_kind_id==2){
				if(!$fields['to_sign_2_checks']) $f['is_enabled']=$f['is_enabled']&&false;
				
				if($f['is_signed']==0){
					if(!(
						$au->user_rights->CheckAccess('w',1075)
						||
						($f['user_id']==$_result['id'])
					
					)) $f['is_enabled']=$f['is_enabled']&&false;
				}else{
					if(!(
						$au->user_rights->CheckAccess('w',1076)
						||
						($f['user_id']==$_result['id'])
					
					)) $f['is_enabled']=$f['is_enabled']&&false;
				}
			}
			
			//��� � ����, ��� ��������!
			$f['sign_pdate']=date('d.m.Y H:i:s', $f['sign_pdate']);
			if($f['is_signed']==1){
				$f['signer_1']=$f['si_name_s'].' '.$f['sign_pdate'];	
			}elseif($f['is_signed']==2){
				$f['signer_2']=$f['si_name_s'].' '.$f['sign_pdate'];	
			}
			
			$f['hash']=md5($f['user_id']);
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	
	
	//����� ������ ��������
	public function ClearSigns($current_id){
		$_kpi=new DocOut_SignItem;  $_hi=new DocOut_HistoryItem; $_ui=new UserSItem; $_dsi=new DocStatusItem;
		
		 
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id, 1);
		foreach($old_positions as $k=>$v){
			
			$_kpi->Edit($v['id'], array('is_signed'=>0, 'sign_pdate'=>time(), 'user_sign_id'=>0));
			
			 
		}
		
		$old_positions=$this->GetItemsByIdArr($current_id, 2);
		
		foreach($old_positions as $k=>$v){
			
			$_kpi->Edit($v['id'], array('is_signed'=>0, 'sign_pdate'=>time(), 'user_sign_id'=>0));
			
			 
		}
		
	 	
		 
		
	}
	
	
	
	//����� ���������� ��������
	public function SaveSigns($current_id, $sign_kind_id, array $positions, $document=NULL, $result=NULL){
		$_kpi=new DocOut_SignItem;  $_hi=new DocOut_HistoryItem; $_ui=new UserSItem; $_dsi=new DocStatusItem;
		
		$log_entries=array(); $log=new ActionLog;
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id, $sign_kind_id);
		
		$au=new AuthUser(); $_doc=new DocOut_AbstractItem;
		if($result===NULL){
			$result=$au->Auth(false,false,false);	
		}
		if($document===NULL){
			
			$document=$_doc->GetItemById($current_id);	
		}
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('doc_out_id'=>$v['doc_out_id'],'user_id'=>$v['user_id'], 'sign_kind_id'=>$v['sign_kind_id']));
			
			if($kpi===false){
				 
			}else{
				//++ pozicii
				
				$add_array=array();
				
				
				$add_array['is_signed']=$v['is_signed'];
				$add_array['sign_pdate']=time();
				$add_array['user_sign_id']=$result['id'];
				
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				if(($v['is_signed']==2)&&($kpi['is_signed']!=2)){
					//������ �������������� ����������� �� �������� �� ��������� ��������� �� ������� $v['notes']
					$user=$_ui->GetItemById($kpi['user_id']);
					if($sign_kind_id==1){
						$comment='��������� �������� '.$document['code'].' ��������� �� ��������� ����������� '.$result['name_s'].' ��� ������������ �� ���������� '.$user['name_s'].', �������: '.$v['notes'];
					}elseif($sign_kind_id==2){
						$comment='��������� �������� '.$document['code'].' ��������� �� ��������� ����������� '.$result['name_s'].' ��� ������� �� ���������� '.$user['name_s'].', �������: '.$v['notes'];
					}
					
					$params=array();
					$params['sched_id']=$current_id;
					$params['txt']=SecStr('�������������� �����������: '.$comment);
					$params['user_id']=0;
					$params['pdate']=time();
					
					$code=$_hi->Add($params);
			 		
					
					$log->PutEntry($result['id'],'������������ ��������� ��������',NULL,1065, NULL,SecStr($comment),$current_id);
				}
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['is_signed']!=$add_array['is_signed']) $to_log=$to_log||true;
			 
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'doc_out_id'=>$v['doc_out_id'],
					'user_id'=>$v['user_id'] ,
					'is_signed'=>$v['is_signed'],
					'notes'=>$v['notes'],
					'user_sign_id'=>$result['id']
					
				  );
				}
				
			}
		}
		
	 	
		//�����������, ��� ����������
		$old_positions=$this->GetItemsByIdArr($current_id, $sign_kind_id);
		$total_signs=count($old_positions);
		
		if($total_signs>0){
			//�������, ������� ����������� ��������� 2
			//�������, ������� ��������� 1
			$count_2=0; $count_1=0;
			foreach($old_positions as $k=>$v){
				if($v['is_signed']==1) $count_1++;
				elseif($v['is_signed']==2) $count_2++;
					
			}
			
			$setted_status_id=$document['status_id'];
			
			//���� ���-��� � 1 - ������ ���������� / ���������
			if($count_1==$total_signs){
				if($sign_kind_id==1) $setted_status_id=42;
				elseif($sign_kind_id==2) $setted_status_id=2;	
				
				 if($setted_status_id!=$document['status_id']) $this->SendMessagesToManagersComplete($current_id, $sign_kind_id);
			}else{
			
				//���� ���� �� ���� ��������� �������� 2 - ������ ����� / ����������
				if($count_2>0){
					if($sign_kind_id==1) $setted_status_id=33;
					elseif($sign_kind_id==2) {
						
						if($document['kind_id']==1) $setted_status_id=42;	
						elseif($document['kind_id']==2) $setted_status_id=33;							
						
					}
					
					if($setted_status_id!=$document['status_id']) $this->SendMessagesToManagersRework($current_id, $sign_kind_id);	
				}
			}
			
			if($setted_status_id!=$document['status_id']){
				$_doc->Edit($current_id, array('status_id'=>$setted_status_id), false, $result);
				$dsi=$_dsi->GetItemById($setted_status_id);
				$log->PutEntry($result['id'],'�������������� ����� ������� ���������� ���������',NULL,1065, NULL,SecStr('��������� �������� '.$document['code'].': ���������� ������ '.$dsi['name']),$current_id);	
			}
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
		
	}
	
	
	//������� ����/����
	public function AddItems($current_id, $sign_kind_id, array $positions,  $result=NULL){
		$_kpi=new DocOut_SignItem; 
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id, $sign_kind_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('doc_out_id'=>$v['doc_out_id'],'user_id'=>$v['user_id'], 'sign_kind_id'=>$v['sign_kind_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['sign_kind_id']=$sign_kind_id;
				$add_array['is_signed']=0;
				$add_array['sign_pdate']=0;
				$add_array['user_sign_id']=0;
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'doc_out_id'=>$v['doc_out_id'],
					'user_id'=>$v['user_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['sign_kind_id']=$sign_kind_id;
				
				
			 
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['doc_out_id']!=$add_array['doc_out_id']) $to_log=$to_log||true;
				if($kpi['user_id']!=$add_array['user_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'doc_out_id'=>$v['doc_out_id'],
					'user_id'=>$v['user_id'] 
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['doc_out_id']==$v['doc_out_id'])&&($vv['user_id']==$v['user_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'doc_out_id'=>$v['doc_out_id'],
					'user_id'=>$v['user_id'] 
			);
			
			//������� �������
			$_kpi->Del($v['id']);
			
		 
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	//�������� ����� ��������������, ����������� "��� �������� ��������"
	public function SendMessagesToSigners($id, $sign_kind_id){
		$_doc=new DocOut_AbstractItem;  
		$doc=$_doc->GetItemById($id); 
		$_mi=new MessageItem;
		$_res=new DocOut_Resolver($doc['kind_id']);
		
		
		
		$signers=$this->GetItemsByIdArr($id,$sign_kind_id,false,$doc);
		
		switch($sign_kind_id){
			case 1:
				$action=" �� ������������";
			break;
			case 2:
				$action=" �� �����������";
			break;	
		}
		 
		
		
		$topic="��������� �������� �������� ��� ".$action;
		
		foreach($signers as $signer){
			if($signer['is_signed']==1) continue;
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['user_name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� GYDEX.��������������� ��� �������� '.$action.' ��������� ��������� ��������:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_doc_out.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';
	
			$txt.='</ul><div><strong>������ ������������ ������������� ��� ����������� ��������!</strong></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['user_id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}
				
			
	}
	
	//�������� ����� ��������� - ��� �������� �����������/���������
	public function SendMessagesToManagersComplete($id, $sign_kind_id){
		
		$_doc=new DocOut_AbstractItem;  
		$doc=$_doc->GetItemById($id); 
		$_mi=new MessageItem;
		$_res=new DocOut_Resolver($doc['kind_id']);
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		 
		
		switch($sign_kind_id){
			case 1:
				$action=" ����������";
			break;
			case 2:
				$action=" ���������";
			break;	
		}
		 
		
		
		$topic="��� ��������� �������� ".$action;
		
	 
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['user_name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>� GYDEX.��������������� ��� '.$action.' ��������� ��� ��������� ��������:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="ed_doc_out.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';

		$txt.='</ul><div><strong>������ ������������ ������������ ��� ����������� ��������!</strong></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		 
		 	
	}
	
	//�������� ����� ��������� - ��� �������� ��������� �� ��������� ��� ������������/�����������
	public function SendMessagesToManagersRework($id, $sign_kind_id){
		$_doc=new DocOut_AbstractItem;  
		$doc=$_doc->GetItemById($id); 
		$_mi=new MessageItem;
		$_res=new DocOut_Resolver($doc['kind_id']);
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		 
		
		switch($sign_kind_id){
			case 1:
				$action=" ��� ������������";
			break;
			case 2:
				$action=" ��� �����������";
			break;	
		}
		 
		
		
		$topic="��� ��������� �������� ��� ��������� �� ��������� ".$action;
		
	 
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['user_name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>� GYDEX.��������������� '.$action.' ��� ��������� �� ��������� ��������� ��� ��������� ��������:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="ed_doc_out.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';

		$txt.='</ul><div><strong>������ ������������ ������������ ��� ����������� ��������!</strong></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
}



/****************************************************************************************************/
//������������� ����

//������ ������������� ���
class DocOut_LeadItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_leads';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ �����, ������������� � ��� ���
class  DocOut_LeadGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_leads';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	//������ ������� - ��������� ����� ��� ���������� ������
	public function GetItemsByIdArr($id, $show_statistics=true, $document=NULL){
		$alls=array();
		
		 
		$sql='select des.*,
			doc.code as code, doc.id as d_id, doc.pdate, 
			st.name as status_name
			
		 
		from  doc_out_leads as des
		inner join lead as doc on doc.id=des.lead_id
		left join document_status as st on doc.status_id=st.id 
		
		where 
		 	des.'.$this->subkeyname.'="'.$id.'" 
			 
		
			order by doc.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	
	//������� ����
	public function AddItems($current_id, array $positions,  $result=NULL){
		$_kpi=new DocOut_LeadItem; 
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('doc_out_id'=>$v['doc_out_id'],'lead_id'=>$v['lead_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['lead_id']=$v['lead_id'];
				
				$add_array['pdate']=time();
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['lead_id']=$v['lead_id'];
				
			 
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['doc_out_id']!=$add_array['doc_out_id']) $to_log=$to_log||true;
				if($kpi['lead_id']!=$add_array['lead_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'] 
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['doc_out_id']==$v['doc_out_id'])&&($vv['lead_id']==$v['lead_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['id']);
			
		 
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
}
  

/**************************************************************************************************/

// users S
class DocOut_UsersSGroup extends UsersSGroup {
	protected $group_id;
	public $instance;
	public $pagename;

	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=1;
		$this->instance=new UserSItem;

	}
	
	 
	
	
	
	
	//����� ����������� ��� ������ � ������ ����
	public function GetItemsForBill(  DBDecorator $dec){
		$txt='';
		
		 
		
		$sql='select p.*, up.name as position_s, p.id as user_id from '.$this->tablename.' as p 
		
		left join user_position as up on up.id=p.position_id
		 where p.group_id="'.$this->group_id.'"

			 ';
		
	
		
		 
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		/*if(strlen($db_flt)>0) $sql.=' and ';
		else */$sql.=' and ';
		
		$sql.=' p.is_active=1 ';
		
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
			//print_r($f);
			$alls[]=$f;
		}
		
		 
	 
		
		return $alls;
	}
	
	
	
	 
}




?>
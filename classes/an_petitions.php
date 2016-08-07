<?

require_once('abstractgroup.php');
require_once('petitionblink.php');

require_once('petitionusergroup.php');
//require_once('tasksuppliergroup.php');
require_once('petitionkindgroup.php');
require_once('petitionstatusgroup.php');

require_once('petition_field_rules.php'); 
 
 
require_once('petitionitem.php');
require_once('authuser.php');
require_once('sched.class.php');

//отчет заявления
class AnPetitions {
	public $_thg;
	public $_tblink;
	
	public $prefix='';
	public $_item;
	
	//установка всех имен
	 function __construct(){
		$this->tablename='petition';
		$this->pagename='an_petitions.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
		 
		$this->_tblink=new PetitionBlink;
		$this->_item=new PetitionItem;
	}
	
	
	
	public function ShowPos( 
	 $selected_kind_ids,
	 $pdate1,
	 $pdate2,	
	
	 $kind_ids, //0
	 $template, //1
	 DBDecorator $dec, //2
	 $is_ajax=false, //3
	 $can_create_task=false, //4
	 &$alls, //5
	 $is_shown=false, //6
	 $result=NULL,  //7
	 $can_delete=false,  //8
	 $can_edit=false,  //9
	 $can_print=false, //10
	 $has_header=true, //11
	 $can_confirm=false, //12
	 $can_unconfirm=false,  //13
	 $can_confirm_chief=false,  //14
	 $can_unconfirm_chief=false,  //15
	 $can_restore=false //16
	 ){
		
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		//echo $from;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$alls=array();
		
		
			//vidy
		$_sts=new petitionKindGroup;
		$sts=$_sts->GetItemsArr($current_kind);
		
		$_cg=new Sched_CityGroup;
		$_sg=new Sched_SupplierGroup;
		
		$sts[]=array('id'=>0, 'name'=>'все заявл.'); $sts2=array();
		foreach($sts as $kk=>$v){
			
		//	if($v['id']==$current_kind) $sts[$kk]['is_current']=true;
			//else $sts[$kk]['is_current']=false;
			if( in_array( $v['id'], $kind_ids)||($v['id']==0)){$sts2[]=$v;}
		}
		
		
		$working_kinds=array();
		if($selected_kind_ids===NULL){
			//работаем по всем видам
			foreach($sts2 as $k=>$v) if($v['id']!=0) $working_kinds[]=$v;
		}else{
			
			 
			//работаем по определенным видам
			foreach($sts2 as $k=>$v) if(($v['id']!=0)&&in_array($v['id'], $selected_kind_ids)){
				 $working_kinds[]=$v;	
				
			}
		}
		
		//отметить активность
		foreach($sts2 as $k=>$v) {
			if(in_array($v, $working_kinds)) {
				$v['is_active']=true;
				$sts2[$k]=$v;
			}
		}
		
		$sm->assign('sc', $sts2);
		
		//print_r($working_kinds);
		foreach($working_kinds as $kind_no=>$working_kind){
		
			$data=array(
				'kind_id'=>$working_kind['id'],
				'kind_name'=>$working_kind['name'],
				'items'=>array()
			
			);
			
			$sql='select distinct t.*,
				k.name as kind_name,
				st.name as status_name,
				u.login as login, u.name_s as name_s, u.position_s as position_s,
				u1.login as confirmed_login, u1.name_s as confirmed_name,
				u2.login as confirmed_chief_login, u2.name_s as confirmed_chief_name,
				crea.name_s as crea_name_s,
				reas.name as reas_name,
				sc.code as sc_code,
				rease.name as rease_name,
				scm.name as scm_name, sc.meet_value
				
				
			from petition as t
				left join petition_kind as k on t.kind_id=k.id
				left join document_status as st on st.id=t.status_id
				left join petition_vyh_reason as reas on reas.id=t.vyh_reason_id
				left join petition_early_reasons as rease on rease.id=t.vyh_reason_id
				left join sched as sc on sc.id=t.sched_id
				left join sched_meet as scm on scm.id=sc.meet_id
				left join user as u on u.id=t.manager_id
				left join user as crea on crea.id=t.user_id
				left join user as u1 on u1.id=t.user_confirm_id
				left join user as u2 on u2.id=t.user_comfirm_chief_id
				left join  petition_vyh_date as vd on vd.petition_id=t.id
			
			where t.kind_id in('.implode(', ',$kind_ids).')
			and t.kind_id="'.$working_kind['id'].'"
			
			';
			
			if(in_array($working_kind['id'], array(1,2))){
				$sql.=' and ( t.begin_pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'")';
				
			}
			
			if(in_array($working_kind['id'], array(4,5,6))){
				$sql.=' and ( t.given_pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'")';
		 
			}
			
			
			if(in_array($working_kind['id'], array(3,8))){
				$sql.=' and  t.id in(select distinct petition_id from petition_vyh_date_otp where pdate between "'.DateFromdmY($pdate1).'" and "'.(DateFromdmY($pdate2)+60*60*24-1).'")';
				
			}
		 
			
			$db_flt=$dec->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$sql.=' and '.$db_flt;
				
			}
			
			
			
			$ord_flt=$dec->GenFltOrd();
			if(strlen($ord_flt)>0){
				$sql.=' order by '.$ord_flt;
			}
			
			
			
			 if($is_shown){
				
				//echo $sql.'<br><br>';
			
				
				 
				$set=new mysqlSet($sql);  
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$total=$set->GetResultNumRowsUnf();
				
				
				 
				
				 
				
				$_tu=new petitionUserGroup;
				
				$_rules=new Petition_FieldRules;
			
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					$f['pdate']=date("d.m.Y",$f['pdate']);
					
					 
					
			 
					 
					 if($f['kind_id']==3){
					  $f['dates']=$this->_item->GetVyhdatesOtp($f['id']);
					  
					  $f['v_dates']=$this->_item->GetVyhdates($f['id']);
					  
					  
					}
					
					 
					 if($f['kind_id']==8){
						  $f['dates']=$this->_item->GetVyhdatesOtp($f['id']);  
					 }
				 
				 
					
					if($f['given_pdate']!=0){
						 $f['given_pdate_unf']=$f['given_pdate'];
						 $f['given_pdate']=date('d.m.Y', $f['given_pdate']);
					}else{
						 $f['given_pdate_unf']=$f['given_pdate'];
						 $f['given_pdate']='';
					}
					
					if($f['begin_pdate']!=0){
						 $f['begin_pdate_unf']=$f['begin_pdate'];
						 $f['begin_pdate']=date('d.m.Y', $f['begin_pdate']);
					}else{
						 $f['begin_pdate_unf']=$f['begin_pdate'];
						 $f['begin_pdate']='-';
					}
					
					if($f['end_pdate']!=0){
						 $f['end_pdate_unf']=$f['end_pdate'];
						 $f['end_pdate']=date('d.m.Y', $f['end_pdate']);
					}else{
						 $f['end_pdate_unf']=$f['end_pdate'];
						 $f['end_pdate']='-';
					}
					if($f['confirm_pdate']==0) $f['confirm_pdate']='';
					else $f['confirm_pdate']=date('d.m.Y H:i:s', $f['confirm_pdate']);
					
					if($f['confirm_chief_pdate']==0) $f['confirm_chief_pdate']='';
					else $f['confirm_chief_pdate']=date('d.m.Y H:i:s', $f['confirm_chief_pdate']);
					
						$f['field_rules']=$_rules->GetFields($f,$result['id'],$f['status_id']);  
				 
					
					if($f['kind_id']==3){
						
							$f['cities']=$_cg->GetItemsByIdArr($f['sched_id']);
							$f['suppliers']=$_sg->GetItemsByIdArr($f['sched_id']);	
					
					}
					
					
					if($f['kind_id']==6){
						$f['suppliers']=$_sg->GetItemsByIdArr($f['sched_id']);	
					
					}	
					
					
					//print_r($f);	
					$data['items'][]=$f;
				}	
			}
			
			
			
			 
			$alls[]=$data;
		}
		
		
		//заполним шаблон полями
		
		$current_kind=''; $tab_page='1';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			//if($v->GetName()=='kind_id') $current_kind=$v->GetValue();
			if($v->GetName()=='status_id') $current_status=$v->GetValue();
				
			 if($v->GetName()=='tab_page') $tab_page=$v->GetValue();
			
				$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
	
	
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from'.$this->prefix,$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create_task', $can_create_task);
		
		$sm->assign('can_delete', $can_delete);
		$sm->assign('can_restore', $can_restore);
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_print', $can_print);
		$sm->assign('has_header', $has_header);
		
		$sm->assign('can_confirm', $can_confirm);
		$sm->assign('can_unconfirm', $can_unconfirm);
		$sm->assign('can_confirm_chief', $can_confirm_chief);
		$sm->assign('can_unconfirm_chief', $can_unconfirm_chief);
		
		$sm->assign('is_shown', $is_shown);
		
		 
		 
		
		
		$sm->assign('prefix',$this->prefix);
		
		
			//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1&tab_page='.$tab_page;
		$sm->assign('link',$link);
		
		
		return $sm->fetch($template);
	
	}
	
	
	 
	
	
}
?>
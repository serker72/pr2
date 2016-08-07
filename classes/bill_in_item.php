<?
require_once('abstractitem.php');
require_once('billitem.php');

require_once('billpositem.php');
require_once('billpospmformer.php');
require_once('billposgroup.php');
require_once('bill_in_posgroup.php');
require_once('docstatusitem.php');

require_once('trust_group.php');
require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('payforbillitem.php');
require_once('payitem.php');
require_once('invcalcitem.php');
require_once('payforbillgroup.php');

//require_once('komplitem.php');
require_once('period_checker.php');
require_once('billnotesitem.php');


require_once('invcalcitem.php');

require_once('invcalcnotesitem.php');
require_once('paynotesitem.php');
require_once('bill_in_positem.php');

//�������� ����
class BillInItem  extends BillItem{

	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bill';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
	}
	
	
	
	//������� �������
	public function GetPositionsArr($id,$show_statistics=true, $bill=NULL){
		$kpg=new BillInPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$show_statistics,$bill);
		
		return $arr;		
		
	}
	
	
	//������� �������
	public function AddPositions($current_id, array $positions,$can_change_cascade=false, $check_delta_summ=false, $result=NULL,$bill=NULL){
		$_kpi=new BillInPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		if($bill===NULL) $bill=$this->getitembyid($current_id);
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id,true,$bill);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'bill_id'=>$v['bill_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
			'out_bill_id'=>$v['out_bill_id']
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['bill_id']=$v['bill_id'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['out_bill_id']=$v['out_bill_id'];
				
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'price_f'=>$v['price_f'],
					'price_pm'=>$v['price_pm'],
					'pl_position_id'=>$v['pl_position_id'],
					'pl_discount_id'=>$v['pl_discount_id'],
					'pl_discount_value'=>$v['pl_discount_value'],
					'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'out_bill_id'=>$v['out_bill_id'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['out_bill_id']=$v['out_bill_id'];
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['pl_discount_id']!=$add_array['pl_discount_id']) $to_log=$to_log||true;
				if($kpi['pl_discount_value']!=$add_array['pl_discount_value']) $to_log=$to_log||true;
				if($kpi['pl_discount_rub_or_percent']!=$add_array['pl_discount_rub_or_percent']) $to_log=$to_log||true;
				
				if($kpi['price_f']!=$add_array['price_f']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				if($kpi['price_pm']!=$add_array['price_pm']) $to_log=$to_log||true;
				if($kpi['total']!=$add_array['total']) $to_log=$to_log||true;
				
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'out_bill_id'=>$v['out_bill_id'],
					  'pms'=>$v['pms']
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
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			foreach($positions as $kk=>$vv){
				if(($vv['pl_position_id']==$v['pl_position_id'])
				&&($vv['position_id']==$v['position_id'])
				&&($vv['pl_discount_id']==$v['pl_discount_id'])
				&&($vv['pl_discount_value']==$v['pl_discount_value'])
				&&($vv['pl_discount_rub_or_percent']==$v['pl_discount_rub_or_percent'])
				&&($vv['out_bill_id']==$v['out_bill_id'])
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
			$pms=NULL;
			if($v['plus_or_minus']==1){
				$pms=array(
						'plus_or_minus'=>$v['plus_or_minus'],
						'rub_or_percent'=>$v['rub_or_percent'],
						'value'=>$v['value']
					);	
			}
			
			$log_entries[]=array(
					'action'=>2,
					 'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'out_bill_id'=>$v['out_bill_id'],
					'pms'=>$pms
			);
			
			//������� �������
			$_kpi->Del($v['p_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	
	//������ ��������� �� �������
	public function CalcAcc($id, $item=NULL, $positions=NULL, $before_pdate=NULL){
		//  $sql3='select * from acceptance where bill_id="'.$id.'" and is_confirmed=1 order by given_pdate asc';
		  
		  $before_flt='';	
		  if($before_pdate!==NULL)	$before_flt=' and given_pdate<="'.$before_pdate.'" ';
		  	
		  $sql3='select sum(total) from acceptance_position where acceptance_id in(select id from acceptance where bill_id="'.$id.'" and is_confirmed=1 '.$before_flt.')';
			
			  
		  $set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
		  $rs3=$set3->GetResult();
		  $rc3=$set3->GetResultNumRows();	
		  
		  
		  $g=mysqli_fetch_array($rs3);
		  
		  return round((float)$g[0],2);
	}
	
	//�������� ����������� ��������
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='���� ���������';
			$can_delete=$can_delete&&false;
		}
		
		
		$set=new mysqlSet('select * from sh_i where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='�� ����� ������� ������������ �� �������: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='�'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		$set=new mysqlSet('select * from payment where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='�� ����� ������� ������: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='�'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		
		
		return $can_delete;
	}
	
	
	public function HasR($id){
		$coun=0;
		
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		//$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		$coun+=(int)$f[0];
		
		return $coun;
	}
	
	public function HasRList($id){
		$txt='';
		
		$nums=array();
		
		$set=new mysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='����������� �'.$f['id'];
				
			}
			
		$txt=implode(', ',$nums);
		
		return $txt;
	}
	
	
	
	public function CalcPayed($id, $except_id=NULL, $except_inv=NULL){
		
		$res=0;
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.bill_id="'.$id.'" ';
		
		if($except_id!==NULL) $sql.=' and p.id<>"'.$except_id.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//$rc=$set->GetResultNumRows();
		//echo $rc;
		
		$res+=(float)$f[0];
		
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join invcalc as p on bp.invcalc_id=p.id where p.is_confirmed_inv=1 and bp.bill_id="'.$id.'" ';
		
		if($except_inv!==NULL) $sql.=' and p.id<>"'.$except_inv.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		$res+=(float)$f[0];
		
		return round($res,2);
		
	}
	
	
	
	
	
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_price'])&&isset($old_params['is_confirmed_price'])){
			
			
			
			if(($new_params['is_confirmed_price']==1)&&($old_params['is_confirmed_price']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==9)||($old_params['status_id']==10))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
			}
		}else{
			//��������� �������� �� 2-9, 9-2, 9-10, 10-9
		  
		  //������� 2-9
		  if($item['status_id']==2){
			  //��������� ���������� �	
			  //����� ����� ��������� ������� 2-10
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$id);
				  
				  
				  //��������� ��������� � �����������, ���� ���� ����������� �� ������
				  
			  }else{
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� 9-10 - ��� ������� ��������, ��� ��������� (���� ��������� ������������)
		  if($item['status_id']==9){
			  //��������� ���������� �	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$id);
				  
				  //��������� ��������� � ������������, ���� ���� ����������� �� ������
				  
			  }
		  }
		  
		  //������� 10-9 - �� ��� ������� ���������
		  if($item['status_id']==10){
			  //��������� ���������� �	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,613,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }	
			
			
			
			
		}
		
		
		//die();
	}
	
	
	
	
	//������ � ���������� ������� �� ����������� ������������
	public function CheckDeltaPositions($id){
		$res=false;
		
		
		$positions=$this->GetPositionsArr($id,false);
		
		$delta=0;
		foreach($positions as $k=>$v){
			
			
			
			$sql='select sum(quantity) as s_q from acceptance_position 
			
			where acceptance_id in(
				select id from acceptance 
				where is_confirmed=1 
				
				and sh_i_id in(
					select id from sh_i 
					where is_confirmed=1 
					and bill_id="'.$id.'" 
					)
				) 
				and position_id="'.$v['position_id'].'" 
				and pl_position_id="'.$v['pl_position_id'].'" 
				and pl_discount_id="'.$v['pl_discount_id'].'" 
				and pl_discount_value="'.$v['pl_discount_value'].'" 
				and pl_discount_rub_or_percent="'.$v['pl_discount_rub_or_percent'].'" 
				and out_bill_id="'.$v['out_bill_id'].'" 
				';
			
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			//$delta+=($v['quantity']-$f['s_q']);
			$zc=($v['quantity']-$f['s_q']);
			if($zc>=0) $delta+=$zc; 
			
			/*echo $sql;
			echo '<pre>';
			print_r($v);
			print_r($f);
			echo '</pre>';*/
		}
		
		//print_r($delta);
		//die();
		
		$res=($delta==0);
		
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		  //��������� ��������� �����������
		 
		  $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  	  
			  //if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ���������� <a target=_blank href=ed_acc.php?id='.$v['id'].'&action=1&from_begin=1>� '.$v['id'].'</a> ������ ���������: '.$v['name'];	
			  //}
			  
		  }
		  if(count($reasons)>0) $reason.="<br />�� ����� ������� ������������ �����������: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  //��������� ��������� ������������ �� ��������
		
		    $set=new mysqlSet('select p.*, s.name from sh_i as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  
			  
			 // if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ������������ �� ������� <a target=_blank href=ed_ship.php?id='.$v['id'].'&action=1&from_begin=1>� '.$v['id'].'</a> ������ ���������: '.$v['name'];	
			 // }
			  
		  }
		  if(count($reasons)>0) $reason.="<br />�� ����� ������� ������������ ������������ �� �������: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		 
		  
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
	
	//������������� ���������
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//������ ��������� �� �������������� �� ��� ���������� ��� �����������������
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		//��������� ��������� �����������
		
			  
		 $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  
			  if($v['status_id']==4) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ����� ������� �������������� �����������: ";
		  $reason.=implode(', ',$reasons);
		  
		  
		  //��������� ��������� ������������ �� ��������
		
 		$set=new mysqlSet('select p.*, s.name from sh_i as p inner join document_status as s on p.status_id=s.id where bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  			  
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' ������������ �� ������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ����� ������� �������������� ������������ �� �������: ";
		  $reason.=implode(', ',$reasons);
		  
		  //��������� ��������� ������������
		  /*$_accg=new TrustGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' ������������ � '.$v['id'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ����� ������� �������������� ������������: ";
		  $reason.=implode(', ',$reasons);
		  
		  //��������� ���� ������
		  $set=new mysqlSet('select * from payment where status_id=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  $dsi=$_dsi->GetItemById($v['status_id']);
			
				  $can=$can&&false;
				  $reasons[]=' ������ � '.$v['code'].' ������ ���������: '.$dsi['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ����� ������� �������������� ������: ";
		  $reason.=implode(', ',$reasons);*/
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$set=new MysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� �����',NULL,94,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� �����',NULL,242,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where bill_id="'.$id.'"');
		
		
		/*$set=new MysqlSet('select * from trust where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ������������ � ����� � �������������� �����',NULL,94,NULL,'������������ � '.$f['id'].': ���������� ������ '.$stat['name'],$f['bill_id']);
		}	
		
		$ns=new NonSet('update trust set status_id=3 where bill_id="'.$id.'"');*/
		
		
		
		
		$set=new MysqlSet('select * from sh_i where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ������������ �� ������� � ����� � �������������� �����',NULL,94,NULL,'������������ � '.$f['id'].': ���������� ������ '.$stat['name'],$f['bill_id']);
			
			
			$log->PutEntry($_result['id'],'������������� ������������ �� ������� � ����� � �������������� �����',NULL,226,NULL,'������������ � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update sh_i set status_id=3 where bill_id="'.$id.'"');
		
		
		/*$set=new MysqlSet('select * from payment where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ������ � ����� � �������������� �����',NULL,613,NULL,'������ � '.$f['code'].': ���������� ������ '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'������������� ������ � ����� � �������������� �����',NULL,279,NULL,'������ � '.$f['code'].': ���������� ������ '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update payment set status_id=3 where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');	*/
	}
	
	
	
	//�������� ������ ��������� �����
	public function GetBindedPayments($bill_id,&$summ){
		$summ=0;
		$names=array();	
		$_inv=new InvCalcItem;
		
		//$set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$bill_id.'")');
		$set=new mysqlSet('select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.given_pdate
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1  order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		//������� ������ �� ���. �����
		
		$set=new mysqlSet('select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.invcalc_pdate
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		
		
		return implode(', ',$names);
	}
	
	public function GetBindedPaymentsFull($bill_id){
		$summ=0;
		$alls=array();	
		
		$_inv=new InvCalcItem;
		
		//$set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$bill_id.'")');
		$set=new mysqlSet('select distinct pb.payment_id, "0" as kind, pb.id, b.code as bill_code, p.code, p.value, p.given_no, p.given_pdate as given_pdate,  p.given_pdate as given_payment_pdate, p.given_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$alls[]=$f;
		}
		
		//������� ���. ����
		$sql='select distinct pb.invcalc_id, "1" as kind, pb.id, b.code as bill_code, p.code, pb.value, p.given_no,  p.invcalc_pdate as invcalc_pdate, p.invcalc_pdate as given_payment_pdate, p.invcalc_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc';
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$real_debt_stru=$_inv->FindRealDebt($f['invcalc_id']);
			//$f['real_debt']=$real_debt_stru['real_debt'];
			//$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			$f['debt']=$real_debt_stru['real_debt'];
			
			
			//$f['value']=$f['real_debt'];
			
			$alls[]=$f;
		}
		
		
		
		return $alls;
	}
	
	//������� ������� �� ��������� �����
	public function FreeBindedPayments($bill_id, $is_auto=0, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayItem;
		
		$_pb=new PayForBillItem;
		$_inv=new InvCalcItem;
		
		$auto_flt='';
		if($is_auto==1) $auto_flt=' and pb.is_auto=1 ';
		
		$sql='select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 '.$auto_flt;
		
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'�������� ������� �� ����� �� �������� ������',NULL,613,NULL,'�������� ������ � '.$f['code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. ',$bill_id);
			
			$log->PutEntry($_result['id'],'�������� ������� �� ����� �� �������� ������',NULL,272,NULL,'�������� ������ � '.$f['code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. ',$f['payment_id']);
			
			//�������� ������ �� ����� � ������������ ������ ������� �����������...
			$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}	
		
		//������ ������� �� ���. �����
		$sql='select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 '.$auto_flt;
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ������������������� ����',NULL,613,NULL,'��� � '.$f['code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. ',$bill_id);
			
			$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ������������������� ����',NULL,452,NULL,'��� � '.$f['code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. ',$f['invcalc_id']);
			
			//�������� ������ �� ����� � ������������ ������ ������� �����������...
			//$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}
		
		
	}
	
	//��������� ���� � ������� � �������
	public function BindPayments($bill_id,$org_id, $_result=NULL){
		$bill=$this->GetItemById($bill_id);
		if($bill===false) return;
		$_bpi=new PayForBillItem;
		$_pfg=new PayForBillgroup;
		$_bpf=new BillPosPMFormer;
		
		
		$_inv=new InvCalcItem;
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		
		$opl_codes=$_pfg->GetAvans($bill['supplier_id'],$org_id,$bill_id,$avans,$opl_ids,$inv_ids,  $bill['contract_id']);
		if((count($opl_ids)==0)&&(count($inv_ids)==0)) return;
		if($avans<=0) return;
		
		
		
		$total_cost=$_bpf->CalcCost($this->GetPositionsArr($bill_id));
		
		$sum_by_bill=$_pfg->SumByBill($bill_id);
		if($total_cost<=$sum_by_bill) return;
		
		$delta=$total_cost-$sum_by_bill;
		
		
		//����� �� ��� �����...
		if(count($inv_ids)>0){
			$set=new mysqlset('select * from invcalc where is_confirmed_inv=1 and supplier_id="'.$bill['supplier_id'].'" and org_id="'.$org_id.'" and id in('.implode(', ',$inv_ids).') and invcalc_pdate<"'.$bill['supplier_bill_pdate'].'"');
			
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  $real_debt_stru=$_inv->FindRealDebt($f['id'],$f);
			  $f['real_debt']=$real_debt_stru['real_debt'];
			  $f['real_debt_id']=$real_debt_stru['real_debt_id'];
			  
			 // echo 'zz';
			  if(!(($real_debt_stru['real_debt_id']==2)&&($real_debt_stru['real_debt']!=0))) continue;
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			   //���� ��� ������ ��� � ����� - �� �� �������� ��������� � value (��� ������� �� ������)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  
			  if((float)$f['real_debt']>(float)$g[0]){
				  
				  $delta_local=((float)$f['real_debt']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'���������� ������� �� ����� � ������������������ ���',NULL,613,NULL,'��� � '.$f['code'].': �������� ������ �� ����� '.$bill['code'].' �� ����� '.($delta_local+$by_bill).' ���. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'���������� ������� �� ����� � ������������������ ���',NULL,452,NULL,'��� � '.$f['code'].': �������� ������ �� ����� '.$bill['code'].' �� ����� '.($delta_local+$by_bill).' ���. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
			//die();
		}
		
		
		if($delta==0) return;
		//����� �� �������
		if(count($opl_ids)>0){
		  $set=new mysqlset('select * from payment
		   where 
		   	is_confirmed=1 
			and is_incoming=0 
			and contract_id="'.$bill['contract_id'].'"
			and supplier_id="'.$bill['supplier_id'].'" 
			and org_id="'.$org_id.'" 
			and id in('.implode(', ',$opl_ids).')');
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //echo 'zz';
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			  
			  //���� ��� ������ ��� � ����� - �� �� �������� ��������� � value (��� ������� �� ������)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  if((float)$f['value']>(float)$g[0]){
				  
				  $delta_local=((float)$f['value']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('payment_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'���������� ������� �� ����� � ��������� ������',NULL,613,NULL,'��������� ������ � '.$f['code'].': �������� ������ �� ����� '.$bill['code'].' �� ����� '.($delta_local+$by_bill).' ���. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'���������� ������� �� ����� � ��������� ������',NULL,272,NULL,'��������� ������ � '.$f['code'].': �������� ������ �� ����� '.$bill['code'].' �� ����� '.($delta_local+$by_bill).' ���. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
		}
		
	}
	
	
	
	
	
	
	
	
	//�������� ���� �� ��������� � �������� ������
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		//echo $item['supplier_bill_pdate'];
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $rss=' ���� ����� ����������� '.$rss23;
			  //echo'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';	
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
		
		if($item['is_confirmed_price']!=1){
			
			$can=$can&&false;
			$reasons[]='� ����� �� ���������� ����';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //�������� ��������� ������� 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' ���� ����� ����������� '.$rss23;	
		  }
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
		
		if($item['is_confirmed_price']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ����� ���������� ����';
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������� ������� 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='���� ����� ����������� '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			 
			 //�������� �� ����� ������� 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} 
		}
		
		return $can;
	}
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_shipping']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ����� ���������� ��������';
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������� ������� 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='���� ����� ����������� '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			
			/*//�������� �� ����� ������� 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} */
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_shipping']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ����� �� ���������� ��������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //��������� ��������� �����������
		  $_accg=new AccInGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.=" �� ����� ������� ������������ �����������: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  //��������� ��������� ������������ �� ��������
		  $_accg=new ShIInGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ������������ �� ������� � '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.='; ';
			  $reason.=" �� ����� ������� ������������ ������������ �� �������: ";
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		   //�������� ��������� ������� 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' ���� ����� ����������� '.$rss23;	
		  }
		  if(count($reasons)>0) {
		   if(strlen($reason)!=0) $reason.='; ';
		   $reason.=implode(' ',$reasons);
		  }
		  
		  
		  
		  //��������� ��������� ������������
		 /* $_accg=new TrustGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			//  $dsi=$_dsi->GetItemById($v['status_id']);
			 if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ������������ � '.$v['id'].'';	
			  }
			  
		  }
		  
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.=',';
			  $reason.=" �� ����� ������� ������������ ������������: ";
		  }
		  $reason.=implode(', ',$reasons);
		  
		  //��������� ���� ������
		  $set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ������ � '.$v['code'].'';	
			 
			  
		  }
		  
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.=',';
			  $reason.=" �� ����� ������� ������������ ������: ";
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		}
		
		return $can;
	}
	
	
	//��������, �������� �� ������� ����� ��� ��������������/�����������
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//���� �� ������ ������� � ����. �� ��������. ���� - ������� ���. ���� �� ��, ��� - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(
				($v['position_id']==$acc_position['id'])
				&&($v['pl_position_id']==$acc_position['pl_position_id'])
				&&($v['pl_discount_id']==$acc_position['pl_discount_id'])
				&&($v['pl_discount_value']==$acc_position['pl_discount_value'])
				&&($v['pl_discount_rub_or_percent']==$acc_position['pl_discount_rub_or_percent'])
				&&($v['out_bill_id']==$acc_position['out_bill_id'])
				){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	
	
	
	public function HasShsorAccs($id){
		 
		  $can=false;
		 
		//��������� ��������� �����������
		  $_accg=new AccGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				  //$reasons[]=' ����������� � '.$v['id'].'';	
			  }
			  
		  }
		  /*if(count($reasons)>0) {
			// if(strlen($reason)!=0) $reason.='; ';
			//  $reason.=" �� ����� ������� ������������ �����������: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  */
		  
		  //��������� ��������� ������������ �� ��������
		  $_accg=new ShIGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				//  $reasons[]=' ������������ �� �������� � '.$v['id'].'';	
			  }
			  
		  }
		 /* if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.='; ';
			 // $reason.=" �� ����� ������� ������������ ������������ �� ��������: ";
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		  return $can;	
	}
	
	
	
	
	
	//����� ��� ������������ �� ������������
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
		$_sh1=new ShIInItem;
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_confirmed_shipping']==0){
			$output='������������ ������� ����������: �� ���������� �������� �����.';
			return;
		}
		
		//��������� ����� �������. �����������
		$items=$this->ScanEq($id,  $args, $output1, $sh, $express_scan);
		
		$_ni=new BillNotesItem;
		
		//������� ��� ����. �� ��������, ������� ��
		if($is_auto==0){
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  
			  $sql='select sp.quantity, s.id 
			  from sh_i as s inner join sh_i_position as sp on s.id=sp.sh_i_id 
			  where 
			  	s.is_confirmed=1 
				and s.bill_id="'.$id.'" 
				and sp.position_id="'.$_t_arr[0].'" 
				and sp.pl_position_id="'.$_t_arr[1].'"
				and sp.pl_discount_id="'.$_t_arr[2].'"
				and sp.pl_discount_value="'.$_t_arr[3].'"
				and sp.pl_discount_rub_or_percent="'.$_t_arr[4].'"
				and sp.out_bill_id="'.$_t_arr[6].'"
				
				';
			  //echo $sql;
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $args_sh=array();
				  $args_sh[]=$_t_arr[0].';'.$_t_arr[1].';'.$_t_arr[2].';'.$_t_arr[3].';'.$_t_arr[4].';'.$f['quantity'].';'.$_t_arr[6];
				  //echo $_t_arr[0].';'.$f['quantity'].';'.$_t_arr[4];
				  $_sh1->DoEq($f['id'], $args_sh,$output);
				  
			  }
			  
		  }
		}
		//����������� ������� �����
		
		$_sh_p=new BillInPosItem;
		$_sh_pm=new BillPosPMItem;
		foreach($items as $k=>$v){
			if($v['delta']==0) continue;
			$sh_p=$_sh_p->GetItemByFields(array(
				'bill_id'=>$id, 
				'position_id'=>$v['position_id'],
				'pl_position_id'=>$v['pl_position_id'],
				'pl_discount_id'=>$v['pl_discount_id'],
				'pl_discount_value'=>$v['pl_discount_value'],
				'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
				'out_bill_id'=>$v['out_bill_id']
				));
			
			if($sh_p!==false){
				$params=array();
				
				if($v['delta']>=0){ //����������� ������ �������! ������� �� �����������!
				
				  $params['quantity']=round(($v['quantity']-$v['delta']),3);
				  
				  //�������� +/- ��� ����������
				  $sh_pm=$_sh_pm->GetItemByFields(array('bill_position_id'=>$sh_p['id']));
				  if($sh_pm!==false){
					  $pms=array(
						  'plus_or_minus'=>$sh_pm['plus_or_minus'],
						  'rub_or_percent'=>$sh_pm['rub_or_percent'],
						  'value'=>$sh_pm['value'],
						  //'discount_plus_or_minus'=>$sh_pm['discount_plus_or_minus'],
						  'discount_rub_or_percent'=>$sh_pm['discount_rub_or_percent'],
						  'discount_value'=>$sh_pm['discount_value']
					  );	
				  }else $pms=NULL;
				  
				  
				  $_sh_p->Edit($sh_p['id'], $params, $pms);
				  
				  $description='���� �'.$sh['code'].': '.$sh_p['name'].' <br /> ���-��: '.$v['quantity'].' ���� �������� ��:  '.round($params['quantity'],3).'<br /> ';
				 
				  //������� ���������� 
				  if($is_auto==1){
					 $log->PutEntry(0,'�������������� �������������� ������� ����� � ����� � ������������� �������',NULL,613,NULL,$description.$extra_reason,$id);	 
					 $posted_user_id=0;
					 $note='�������������� ����������: ������� ����� '.$sh_p['name'].' ���� ��������� ��� �������������� ������������, ���-�� '.$v['quantity'].' ���� �������� �� '.round($params['quantity'],3).''.$extra_reason;
				  }else{
					 $log->PutEntry($_result['id'],'������������ ������� ����� � ����� � ������������� �������',NULL,613,NULL,$description.$extra_reason,$id);	
					 
					 $posted_user_id=$_result['id'];
					 $note='�������������� ����������: ������� ����� '.$sh_p['name'].' ���� ���������, ���-�� '.$v['quantity'].' ���� �������� �� '.round($params['quantity'],3).''.$extra_reason; 
				  }
				  
				   $_ni->Add(array(
						'user_id'=>$id,
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$posted_user_id,
						'note'=>$note
						));
				  
				}
			}
			
		}
		$output='������������ ������� ���������.';
		if(!$express_scan) $this->ScanDocStatus($id,array(),array());	
	}
	
	
	
	
	//������������ ������������ ����������� �����-��� � ��������
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\n���������� ������������ ������ �������?"){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array();
		$total_summ=0; $summ_in_doc=0;
		$output='';
		$docs=array();
		$_pos=new PlPosItem;
		$_pdi=new PosDimItem;
		
		$count_acc=0;
		//������� �� ��������
		foreach($args as $k=>$v){
			$_t_arr=explode(';',$v);
			$summ=0;
			
			/*
		  stri=$("#new_position_id_"+thash).val()+";";  //0
				  stri=stri+$("#new_pl_position_id_"+thash).val()+";";	//1
				  stri=stri+$("#new_pl_discount_id_"+thash).val()+";";	//2
				  stri=stri+$("#new_pl_discount_value_"+thash).val()+";";	//3	
				  stri=stri+$("#new_pl_discount_rub_or_percent_"+thash).val()+";";	//4
				  stri=stri+$("#new_quantity_"+thash).val()+";";	//5
				  stri=stri+$("#new_out_bill_id_"+thash).val();	//6
				  */
			
			
			$summ_in_doc+=$_t_arr[5];
			
			//�� ������ ������� ��������� ��� ������� ����������� �����������
			$sql='select * from acceptance_position 
			where acceptance_id in(
				select id from acceptance where 
					is_confirmed=1 
					
					and bill_id="'.$id.'" 
				) 
			and position_id="'.$_t_arr[0].'" 
			and pl_position_id="'.$_t_arr[1].'"
			and pl_discount_id="'.$_t_arr[2].'"
			and pl_discount_value="'.$_t_arr[3].'"
			and pl_discount_rub_or_percent="'.$_t_arr[4].'"
			and out_bill_id="'.$_t_arr[6].'"
			
			
			';
			
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ+=$f['quantity'];
				$count_acc+=$f['quantity'];
				if(!in_array('�'.$f['acceptance_id'],$docs)) $docs[]='�'.$f['acceptance_id'];
			}
			
			$pos=$_pos->GetItemById($_t_arr[0]);
			$pdi=$_pdi->GetItemById($pos['dimension_id']);
			
			
			$items[]=array(
				'position_id'=>$_t_arr[0], 
				'quantity'=>$_t_arr[5], 
				'pl_position_id'=>$_t_arr[1], 
				'pl_discount_id'=>$_t_arr[2], 
				'pl_discount_value'=>$_t_arr[3], 
				'pl_discount_rub_or_percent'=>$_t_arr[4], 
				'out_bill_id'=>$_t_arr[6], 
				'delta'=>round(($_t_arr[5]-$summ),3));
			
			$total_summ+=$summ;	
			
		}
			
			
		
		
		$docs1=array();
		$count_sh=0;
		
		if(!$express_scan){
		
		  //������� �� ��������
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  $summ=0;
			  
			  
			  //�� ������ ������� ��������� ��� ������� ����������� ���� �� ������
			  $sql='select * from sh_i_position where  
			  sh_i_id in(
			  		select id from sh_i where 
						is_confirmed=1 
						and bill_id="'.$id.'" 
						
					) 
			 and position_id="'.$_t_arr[0].'"
			  and pl_position_id="'.$_t_arr[1].'"
			  and pl_discount_id="'.$_t_arr[2].'"
			  and pl_discount_value="'.$_t_arr[3].'"
			  and pl_discount_rub_or_percent="'.$_t_arr[4].'"
			  and out_bill_id="'.$_t_arr[6].'"
			 
			 ';
			  
			  
			  
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $summ+=$f['quantity'];
				  $count_sh+=$f['quantity'];
				  if(!in_array('�'.$f['sh_i_id'],$docs1)) $docs1[]='�'.$f['sh_i_id'];
			  }
			  
		  
			  $total_summ+=$summ;	
			  
		  }
		}
		
		
		if($total_summ==0){
			$output.="\n������� ".htmlspecialchars($pos["name"])." �� ������� �� � ����� ������������ ����������� ���������. ���������� ����� ��������. ���������� ������������ ������ �������?";
		}else{
			
			if(!$express_scan){
			  //� �������������
			  if(count($docs1)){
				   $output.="\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ ������������� �� �������: ".implode(", ",$docs1)." � ���������� ".$count_sh." ".htmlspecialchars($pdi["name"]);
				   
					if($count_sh>$summ_in_doc){
					   $output.=', ��� ��������� ���������� � c���� '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				   }
			  }else $output.="\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ ������������� �� �������.";
			}
			
			
			
			//� ������������ 
			if(count($docs)){
				 $output.="\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ ������������: ".implode(", ",$docs)." � ���������� ".$count_acc." ".htmlspecialchars($pdi["name"]);
				 if($count_acc>$summ_in_doc){
					 $output.=', ��� ��������� ���������� � c���� '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif($count_acc<$summ_in_doc){
					 $output.=', ��� ������ ���������� � c���� '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }
			}else $output.="\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ ������������.";
			
			if($count_acc>=$summ_in_doc){
				$output.=".\n������� ������������ �� ��������.";
			}else $output.=$continue_message; //".\n���������� ������������ ������ �������?";
			//.
		}
		
		
		
		
		
		
		return $items;
	}
	
	
	
	
	
	
	
	
	
	
	//�������� ����������� �������������� kol-va �������
	public function CanEditQuantities($id, &$reason, $itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='���� ���������';
			$can_delete=$can_delete&&false;
		}
		
		
		$set=new mysqlSet('select * from sh_i where bill_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='�� ����� ������� ������������ ������������ �� �������: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='�'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		$set=new mysqlSet('select * from acceptance where is_confirmed=1 and sh_i_id in(select id from sh_i where bill_id="'.$id.'" and is_confirmed=1)');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='�� ����� ������� ������������ �����������: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='�'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
	
	
	//�������� ����������� �������������� "���� � �����������"
	public function CanIsInBuh($bill_id, &$rss, $bill_item=NULL, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL){
		$can_is_in_buh=true;
		
		//echo '<h1>zzzz</h1>';
		
		$_rss=array();
		if($bill_item===NULL) $bill_item=$this->getitembyid($bill_id);
		
		if($summ_by_bill===NULL) $summ_by_bill=$this->CalcCost($bill_id);
		if($summ_by_payed===NULL) $summ_by_payed=$this->CalcPayed($bill_id);
		
		
		if($bill_item['is_in_buh']==1){
			$can_is_in_buh=$can_is_in_buh&&$can_unconfirm_in_buh;
			
			if(!$can_unconfirm_in_buh) $_rss[]=' � ��� ������������ ���� ��� ������ �����, ����������, ���������� � �������������� ��� ��������� ���� �������';
		}else{
			$can_is_in_buh=$can_is_in_buh&&$can_confirm_in_buh;
			if(!$can_confirm_in_buh) $_rss[]=' � ��� ������������ ���� ��� ��������� �����, ����������, ���������� � �������������� ��� ��������� ���� �������';
		}
		
		if(!(($bill_item['is_confirmed_price']==1)&&($bill_item['is_confirmed_shipping']==1))){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' � ����� ������ ���� ���������� ���� � �������� ';
		}
		
		
		if($summ_by_bill<=$summ_by_payed){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' ���� ��������� ������� ';	
		}
		
		
		if($bill_item['is_in_buh']==1){
			if($bill_item['user_in_buh_id']==-1) $_rss[]=' ������� ����� � ����������� ���������� ������������� �� ��������� 100% ������ �����';
		}
		
		$rss=implode(', ',$_rss);
		
		return $can_is_in_buh;
	}
	
		
		
	
	
	//���������� ����� ����������� � ����� ����� ��� ���������� ������ �����
	public function LowPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL, $calc_payed=NULL, $actor_id=NULL){
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($new_bill_summ===NULL) $new_bill_summ=$this->CalcCost($id);
		if($calc_payed===NULL) $calc_payed=$this->CalcPayed($id);
		
		
		$_pbi=new PayForBillItem;
		$_pi=new PayItem;
		
		//��������� ����� �����, � �� ����� ���� ������
		//���� ����� ����� ������, ��� ����� ����� - ��������� ����� �����.
		if(($new_bill_summ<$old_bill_summ)&&($calc_payed>0)&&($calc_payed>$new_bill_summ)){
			$delta=$calc_payed-$new_bill_summ;
			
			//���������� ������������� � ����� ��� ����, ������
			$sql='select pb.*, p.code as invcalc_code, b.code as bill_code, pp.code as payment_code
			 from payment_for_bill as pb
				left join invcalc as p on p.id=pb.invcalc_id
				left join bill as b on b.id=pb.bill_id
				left join payment as pp on pb.payment_id=pp.id
			where pb.bill_id="'.$id.'"';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				if($delta<=0) break;
				
				//$delta_local=$delta-(float)$f['value'];
				if($delta>=(float)$f['value']){
					//����� ������������ ������, ��� �������
					//��������� ��� ������������ ���������
					$_pbi->Del($f['id']);
					
					//�������������� ���������� (� ����, ������, ��������)
					if($f['payment_id']!=0){
						$note='��������� ������ � '.$f['payment_code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. �� ��������� ���������� ����� ����� � '.$old_bill_summ.' ���. �� '.$new_bill_summ.' ���. ��� ����������� ����������� �'.$actor_id.' ��� ������.';	
					}elseif($f['invcalc_id']!=0){
						$note='��� � '.$f['invcalc_code'].': ������ ������ �� ����� '.$f['bill_code'].' �� ����� '.$f['value'].' ���. �� ��������� ���������� ����� ����� � '.$old_bill_summ.' ���. �� '.$new_bill_summ.' ���. ��� ����������� ����������� �'.$actor_id.' ��� ������.';
					}
					
					
					$delta-=(float)$f['value'];
						
				}else{
					//����� ������������ ������, ��� �������
					//��������� ����� ������������	
					$_pbi->Edit($f['id'], array('value'=>((float)$f['value']-$delta)));
					
					//�������������� ���������� (� ����, ������, ��������)
					if($f['payment_id']!=0){
						$note='��������� ������ � '.$f['payment_code'].': �������� ������ �� ����� '.$f['bill_code'].' � ����� '.$f['value'].' ���. �� ����� '.((float)$f['value']-$delta).' ���. �� ��������� ���������� ����� ����� � '.$old_bill_summ.' ���. �� '.$new_bill_summ.' ���. ��� ����������� ����������� �'.$actor_id.' ��� ������.';	
					}elseif($f['invcalc_id']!=0){
						$note='��� � '.$f['invcalc_code'].': �������� ������ �� ����� '.$f['bill_code'].' � ����� '.$f['value'].' ���. �� ����� '.((float)$f['value']-$delta).' ���. �� ��������� ���������� ����� ����� � '.$old_bill_summ.' ���. �� '.$new_bill_summ.' ���. ��� ����������� ����������� �'.$actor_id.' ��� ������.';	
					}
					
					$delta=0;
				}
				
				
				
				
				//�������������� ���������� (� ����, ������, ��������)
				$_bni=new BillNotesItem;	
				if($f['payment_id']!=0){
					$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ��������� ������',NULL,613,NULL,$note,$f['bill_id']);
		
					$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ��������� ������',NULL,272,NULL,$note,$f['payment_id']);
					
					$_pni=new PaymentNotesItem;	
					
					 $_pni->Add(array(
							  'user_id'=>$f['payment_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}elseif($f['invcalc_id']!=0){
					$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ������������������� ����',NULL,613,NULL,$note,$bill_id);
		
					$log->PutEntry($_result['id'],'�������� ������� �� ����� �� ������������������� ����',NULL,452,NULL,$note,$f['invcalc_id']);
					
					$_pni=new InvCalcNotesItem();
					 $_pni->Add(array(
							  'user_id'=>$f['invcalc_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}
				
				 $_bni->Add(array(
							  'user_id'=>$f['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
				));
				
				//�������������� ����� � ������...
				if($f['payment_id']!=0){
					//��������� ����� ������ � ������
					//$_pi->BindPayments(	$f['payment_id'], $_result['org_id']);	
					//������� �����
				}
			}
		}
	}
	
	
}
?>
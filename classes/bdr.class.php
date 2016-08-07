<?
 
require_once('abstractversionitem.php');
require_once('abstractversiongroup.php');
 
require_once('supplieritem.php');
require_once('suppliercontactitem.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactdatagroup.php');
require_once('user_s_group.php');
require_once('supplier_cities_group.php');

require_once('supplier_responsible_user_group.php');
require_once('supplier_responsible_user_item.php');
require_once('actionlog.php');

require_once('lead_history_group.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('lead_view_item.php');
require_once('tender.class.php');
require_once('tz_field_rules.php');
require_once('pl_prodgroup.php');
require_once('bdr_view.class.php');
require_once('user_s_item.php');

require_once('lead_history_item.php');
require_once('lead.class.php');
require_once('tz.class.php');
require_once('kp_in.class.php');


require_once('abstract_working_kind_group.php');
require_once('notesgroup.php');
require_once('notesitem.php');

require_once('currency/currency_solver.class.php');

//���������� ������� ���


//����������� ������ ���

class BDR_AbstractItem extends AbstractVersionItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='bdr';
		$this->version_tablename='bdr_version';
		$this->item=NULL;
		$this->pagename='ed_bdr.php';	
		$this->mid_name='bdr_id';
		$this->version_id_name='vid';
		
		$this->vis_name='is_shown';	
	}	
	
	
	public function Add($params, $version_params){
		/**/
		$digits=5;
		
		
		$begin='���';
		
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
		
		
		return AbstractVersionItem::Add($params, $version_params);
		
	}
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//������ � �����������  ��� ���� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	//������ � �����������  ��� ���� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip1($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip1($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	public function Edit($id, $version_id=AbstractVersionItem::ACTIVE_VERSION, $params, $version_params,  $scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id, $version_id );
		
		
		 
		
		parent::Edit($id, $version_id, $params, $version_params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$version_params,NULL,$_result);
	}
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		 
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '���, ������ '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '��� '.$item['code'].', ������ '.$stat['name'];
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
		
	}
	
	
	//������� �������
	public function AddPositions($id, $version_id, $p_or_m,  array $positions, $result=NULL,$document=NULL){
	}
	 
	 
	//�������� ������ �� ������ ����������
	public function AddVersionByParams($id, $version_params){
		/*$it1=new AbstractItem();
		$it1->SetTableName($this->version_tablename);
		
		$version_params[$this->mid_name]=$id;
		//���� �� ����� ����� ������, �� ������������� ��� �� ������ ���������...
		$version_params['version_no']=$this->GenerateVersionNo($id);
		
		$version_id=$it1->Add($version_params);
		
		//print_r($version_params);
		
		return $version_id;*/
		
		$sql='select * from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by '.$this->version_id_name.' desc limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs, MYSQL_ASSOC);
			
			$old_version_id=$f[$this->version_id_name];
		
			$version_id=parent::AddVersionByParams($id, $version_params);
			
			$this->CopyPositions($id, $old_version_id, $version_id);
			
			$this->CopyBDDSPositions($id, $old_version_id, $version_id);
			
			//� ���������� ������ ����� ��� �����������, ���������� ������ 1
			$this->Edit($id, $version_id, array('status_id'=>1), array('is_confirmed'=>0, 'confirm_pdate'=>time(), 'user_confirm_id'=>0, 'is_confirmed_version'=>0, 'confirm_version_pdate'=>time(), 'user_confirm_version_id'=>0, 'gain_val'=>NULL,
			'gain_percent'=>NULL,
			'gain_ebitda'=>NULL,
			'gain_chp'=>NULL), false);
			
		}else $version_id=parent::AddVersionByParams($id, $version_params);
		
		
		return $version_id;
	}
	
	//�������� ������, ������� ����������
	public function AddVersion($id, &$version_no){
		 
		
		
		//�������� ��������� ������ ������� ���������
		$sql='select * from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by '.$this->version_id_name.' desc limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs, MYSQL_ASSOC);
			
			$old_version_id=$f[$this->version_id_name];
			
			$version_id=parent::AddVersion($id, $version_no);	
			
			$this->CopyPositions($id, $old_version_id, $version_id);
			
			$this->CopyBDDSPositions($id, $old_version_id, $version_id);
			
				//� ���������� ������ ����� ��� �����������, ���������� ������ 1
			$this->Edit($id, $version_id, array('status_id'=>1), array('is_confirmed'=>0, 'confirm_pdate'=>time(), 'user_confirm_id'=>0, 'is_confirmed_version'=>0, 'confirm_version_pdate'=>time(), 'user_confirm_version_id'=>0, 'gain_val'=>NULL,
			'gain_percent'=>NULL,
			'gain_ebitda'=>NULL,
			'gain_chp'=>NULL), false);
		}
		
		return $version_id;
	}
	
	//���������� ������� ������� ������ � ������� �� ����� ������ � ������
	protected function CopyPositions($id, $old_version_id, $new_version_id){
		//�������� ������� ���� ������, ������ � ����� ������
		
		$sql='select * from bdr_account_version where bdr_id="'.$id.'" and version_id="'.$old_version_id.'" ';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_pos=new BDR_AccountVersionItem;
		
		for($i=0; $i<$rc; $i++){	
			$f=mysqli_fetch_array($rs, MYSQL_ASSOC);
			
			unset($f['id']);
			$f['version_id']=$new_version_id;
			
			$_pos->Add($f);
			
		}
		
	}
	
	
	//����������� �������, ���� ����  �� ����� ������ � ������
	protected function CopyBDDSPositions($id, $old_version_id, $new_version_id){
		//�������� ��� ������, �������� ������
		$sql='select * from bdds_account where bdr_id="'.$id.'" and version_id="'.$old_version_id.'" ';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_pos=new BDDS_AccountItem;
		$_vpos=new BDDS_AccountValueItem;
		
		for($i=0; $i<$rc; $i++){	
			$f=mysqli_fetch_array($rs, MYSQL_ASSOC);
			
			$old_account_id=$f['id'];
			
			unset($f['id']);
			$f['version_id']=$new_version_id;
			
			$ds_account_id=$_pos->Add($f);
			
			//�������� ����� �� ������ ������
			$sql1='select * from bdds_account_value where bdr_id="'.$id.'" and version_id="'.$old_version_id.'" and ds_account_id="'.$old_account_id.'"';
			
			//echo $sql1; die();
		
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			for($j=0; $j<$rc1; $j++){	
				$g=mysqli_fetch_array($rs1, MYSQL_ASSOC);
				
				unset($g['id']);
				$g['version_id']=$new_version_id;
				$g['ds_account_id']=$ds_account_id;
				
				$_vpos->Add($g);
			}
			
			
		}
	}
	
	
	
	
	
	//���������� ����� ������� �� ��������� ��������� � ������
	public function CalcGain($id, $version_id=NULL){
		if($version_id==self::ACTIVE_VERSION){
			//�������� ��������� ������
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		
		$item=$this->GetItemById($id, $version_id);
		
		 
		
		$_pdata=new BDR_P_Block;
		$pos=$_pdata->ConstructById($id, $version_id,$vyruchka); //>ConstructByAccounts($paccounts, $vyruchka);
		
		$_mdata=new BDR_M_Block;
		$mpos=$_mdata->ConstructById($item['kp_in_id'], $id, $version_id, $zatraty); //ConstructByAccounts($maccounts, $item['kp_in_id'], $zatraty);
		
		//����� ����� �����, �������
		$kiaur=0; $nalogi=0;
		foreach($mpos as $k=>$v) {
			if(trim($v['name'])=='������') $nalogi=$v['cost'];
			if(trim($v['name'])=='�����') $kiaur=$v['cost'];
		}
		
		$gain_val=$vyruchka-($zatraty-$nalogi-$kiaur);
		$gain_percent=round(100*$gain_val/$vyruchka,2);
		$gain_ebitda=$gain_val-$kiaur;
		$gain_chp=$gain_ebitda-$nalogi;
		
		return array(
			'gain_val'=>$gain_val,
			'gain_percent'=>$gain_percent,
			'gain_ebitda'=>$gain_ebitda,
			'gain_chp'=>$gain_chp,
			'gain_val_formatted'=>number_format($gain_val, 2, '.',''),
			'gain_percent_formatted'=>number_format($gain_percent, 2, '.',''),
			'gain_ebitda_formatted'=>number_format($gain_ebitda, 2, '.',''),
			'gain_chp_formatted'=>number_format($gain_chp, 2, '.','')
		
		);
		
	}
	
	//���������� ����� ������� �� ���������, ������, ������� �������
	public function CalcGainByAccounst($id, $version_id, $maccounts, $paccounts){
		if($version_id==self::ACTIVE_VERSION){
			//�������� ��������� ������
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		$item=$this->GetItemById($id, $version_id);
		
		 
		
		$_pdata=new BDR_P_Block;
		$pos=$_pdata->ConstructByAccounts($paccounts, $vyruchka);
		
		$_mdata=new BDR_M_Block;
		$mpos=$_mdata->ConstructByAccounts($maccounts, $item['kp_in_id'], $zatraty);
		
		
		//echo "    $zatraty ";
		
		//����� ����� �����, �������
		$kiaur=0; $nalogi=0;
		foreach($mpos as $k=>$v) {
			if(trim($v['name'])=='������') $nalogi=$v['cost'];
			if(trim($v['name'])=='�����') $kiaur=$v['cost'];
		}
		
		//echo " $vyruchka $zatraty $nalogi $kiaur ";
		
		$gain_val=$vyruchka-($zatraty-$nalogi-$kiaur);
		$gain_percent=round(100*$gain_val/$vyruchka,2);
		$gain_ebitda=$gain_val-$kiaur;
		$gain_chp=$gain_ebitda-$nalogi;
		
		return array(
			'gain_val'=>$gain_val,
			'gain_percent'=>$gain_percent,
			'gain_ebitda'=>$gain_ebitda,
			'gain_chp'=>$gain_chp,
			'gain_val_formatted'=>number_format($gain_val, 2, '.',''),
			'gain_percent_formatted'=>number_format($gain_percent, 2, '.',''),
			'gain_ebitda_formatted'=>number_format($gain_ebitda, 2, '.',''),
			'gain_chp_formatted'=>number_format($gain_chp, 2, '.','')
		
		);
	}
}


/*************************************************************************************************/
//���
class BDR_Item extends BDR_AbstractItem{
	public $kind_id=1;
	
	//�������� 
	/*public function Add($params){
		 
		$code=parent::Add($params);
		
		
		

		return $code;
	} */
	
	
	public function NewTenderMessage($code){
		
	}
	
	
	public function Edit($id,  $version_id=AbstractVersionItem::ACTIVE_VERSION, $params, $version_params, $scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id, $version_id);
		
		
		
		 
		
		AbstractVersionItem::Edit($id, $version_id, $params, $version_params);
		
		 
		
		//�������� ���� ����� �������
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			 
			
		 
		}
		
		
		
		//����������� ������ �������
		if(isset($version_params['is_confirmed'])&&isset($item['is_confirmed'])&&($version_params['is_confirmed']!=$item['is_confirmed'])){
			
			if(($version_params['is_confirmed']==1)&&($item['is_confirmed']!=1)){
				
				//��������� �������� 4 (����) � ���, ���, ��, ���
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_out_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				
				//������ ��������� ��� ��, ������ � ���, ���
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>5, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>6, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>5, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>6, 'in_or_out'=>0, 'pdate'=>time()));
				
				 
				
			}
			elseif(($version_params['is_confirmed']==0)&&($item['is_confirmed']!=0)){
				 
				//������ �������� 4 (����) � ���, ���, ��, ���
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_out_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				
				//��������� ��������� ��� ��, ������ � ���, ���
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>5, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>6, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>5, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>6, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			
		}
		
		//������� ��� ������
		if(isset($version_params['is_confirmed_version'])&&isset($item['is_confirmed_version'])&&($version_params['is_confirmed_version']!=$item['is_confirmed_version'])){
			
			if(($version_params['is_confirmed_version']==1)&&($item['is_confirmed_version']!=1)){
				//���, ��� - ���� ������� ��� ������ 5
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>5, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>5, 'in_or_out'=>1, 'pdate'=>time()));
				
			}elseif(($version_params['is_confirmed_version']==0)&&($item['is_confirmed_version']!=0)){
				//���, ��� - ��� ������� ��� ������ 5
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>5, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>5, 'in_or_out'=>0, 'pdate'=>time()));
			}
			
		}
			
		//������� ��� ���
		if(isset($version_params['is_confirmed_version1'])&&isset($item['is_confirmed_version1'])&&($version_params['is_confirmed_version1']!=$item['is_confirmed_version1'])){
			
			if(($version_params['is_confirmed_version1']==1)&&($item['is_confirmed_version1']!=1)){
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>6, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>6, 'in_or_out'=>1, 'pdate'=>time()));
				
			}elseif(($version_params['is_confirmed_version1']==0)&&($item['is_confirmed_version1']!=0)){
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>6, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new BDR_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>6, 'in_or_out'=>0, 'pdate'=>time()));
				
			}
			
		}		
				
		
		/*
		
		//����������� ������ �������
		if(isset($params['is_not_eq'])&&isset($item['is_not_eq'])&&($params['is_not_eq']!=$item['is_not_eq'])){
			
			if(($params['is_not_eq']==1)&&($item['is_not_eq']!=1)){
				
				//��������� ������� ���������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				//�������� ������� �������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
					
			}
			elseif(($params['is_not_eq']==0)&&($item['is_not_eq']!=0)){
				//��������� ������� ���������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				//������������� ������� �������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
			}
		}
		
		//����������� ������� ������� - �������� ������� ���������
		if(isset($params['force_eq_in'])&&isset($item['force_eq_in'])&&($params['force_eq_in']!=$item['force_eq_in'])){
			
			if(($params['force_eq_in']==1)&&($item['force_eq_in']!=1)){
				//��������� ������� �������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				
				//������� ������� ���������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
			}
			elseif(($params['force_eq_in']==0)&&($item['force_eq_in']!=0)){
				//������ �������� �������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				
				//��������� ��-�� ���������
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
		}*/
		
		 
		if($scan_status) $this->ScanDocStatus($id, $item, $version_params,NULL,$_result);
	}
	
	//�������������� �������� ��� ���� � ����� �����������, ���� ��� ��� ���
	protected function ScanResp($tender_id, $manager_id, $result_id){
	  
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);	
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������, ���� ����� ������������
			/*if(!$au->user_rights->CheckAccess('w',960)){
				$_hg=new Lead_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='�� ���� �������� '.$cou.' ������������';
					$reason.=implode(', ',$reasons);
				}
			}	*/
			
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
			$reasons[]='� ��� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_version']==1){
			
			$can=$can&&false;
			$reasons[]='� ��� ���������� �������';
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
			$reasons[]='� ��� ���������� ����������';
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
	
	  
	//������ � �����������  ��� ��� ������� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_version']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� ��������� ���������� �������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� �� ��������� ';
			 $reason.=implode(', ',$reasons);
		}else{
			 
			
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ��� ������� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_version']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� �� ��������� ���������� �������';
			$reason.=implode(', ',$reasons);
		} 
		 
		return $can;
	}  
	
	
	
	
	//������ � �����������  ��� ����������� ���������� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip1($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_version1']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� ��������� ����������� ����������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� �� ��������� ����������� ����������';
			 $reason.=implode(', ',$reasons);
		}else{
			 
			
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ����������� ���������� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip1($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_version1']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' ��� �� ��������� ����������� ����������';
			$reason.=implode(', ',$reasons);
		} 
		 
		return $can;
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
					//����� ������� �� 2
					$setted_status_id=2;
					$this->Edit($id,   NULL,  array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���',NULL,1042,NULL,'���������� ������ '.$stat['name'],$item['id']);
					
					
					//��������������, ���� ����������� ���� ����, ��� ���� �� ���� �� ����
					//���� ��� ��� - �� ������ �������������� ����������
					//$item['course_dol'] $item['course_euro'] $item['pdate_course']
					$_solver=new CurrencySolver;
					$rates=$_solver->GetToDate(date('d.m.Y',$item['pdate_course']));
					$dol=round(CurrencySolver::Convert(1, $rates, 3, 1),5);
					$eur=round(CurrencySolver::Convert(1, $rates, 2, 1),5);
					
					$lower=false; $acts=array();
					if($item['course_dol']<$dol){
						
						$acts[]='���� ������� �� '.date('d.m.Y',$item['pdate_course']).' ���������� '.$dol.' ���., ����������� �������� '.$item['course_dol'].'  ���.';
						$lower=$lower||true;
					}
					
					if($item['course_euro']<$eur){
						$acts[]='���� ���� �� '.date('d.m.Y',$item['pdate_course']).' ���������� '.$eur.' ���., ����������� �������� '.$item['course_euro'].'  ���.';
						$lower=$lower||true;
					}
					if($lower){
						$comment=SecStr('����������� � ��� '.$item['code'].' ����������� '.$_result['name_s'].' ����� ����� ����, ��� ���� ����� �� �������� ����: '.implode(', ',$acts));
						
						$_nbdr=new BDRNotesItem;
						$_nbdr->Add(array(
								'note'=>'�������������� ����������: '.$comment,
								'pdate'=>time(),
								'user_id'=>$id,
								'posted_user_id'=>0
							));
						
						$log->PutEntry($_result['id'],'������ ���',NULL,1041,NULL,$comment,$item['id']);
					}
					
					
					//������������� �������!
						$gain=$this->CalcGain($id);
						$params=array();
						
						$gain_params=array(
							'gain_val'=>$gain['gain_val'],
							'gain_percent'=>$gain['gain_percent'],
							'gain_ebitda'=>$gain['gain_ebitda'],
							'gain_chp'=>$gain['gain_chp']
							);
						$this->Edit($id, NULL, NULL, $gain_params);
					
					 
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//����� ������� �� 1
					$setted_status_id=1;
					$this->Edit($id,  NULL,  array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���',NULL,1043,NULL,'���������� ������ '.$stat['name'],$item['id']);
					
					
					//�������� �������!
						
						$gain_params=array(
							'gain_val'=>NULL,
							'gain_percent'=>NULL,
							'gain_ebitda'=>NULL,
							'gain_chp'=>NULL,
							);
						$this->Edit($id, NULL, NULL, $gain_params);
				}
				
				
			} 
			
			/*elseif(isset($new_params['is_confirmed_version'])&&isset($old_params['is_confirmed_version'])){
				if(($new_params['is_confirmed_version']==1)&&($old_params['is_confirmed_version']==0)){
					
					 	 
						//����� ������� �� 40
						$setted_status_id=40;
						
						$params['status_id']=$setted_status_id	;
						
						$this->Edit($id, NULL,  $params);
						
						$stat=$_stat->GetItemById($setted_status_id);
						$log->PutEntry($_result['id'],'����� ������� ���',NULL,1050,NULL,'���������� ������ '.$stat['name'],$item['id']);
					 
					
				}elseif(($new_params['is_confirmed_version']==0)&&($old_params['is_confirmed_version']==1)){
					 
						$setted_status_id=2;
						
						$this->Edit($id, NULL, array('status_id'=>$setted_status_id));
						
						$stat=$_stat->GetItemById($setted_status_id);
						$log->PutEntry($_result['id'],'����� ������� ���',NULL,1051,NULL,'���������� ������ '.$stat['name'],$item['id']);
					 
				}
				
			}*/
			
			 if( 
		(isset($new_params['is_confirmed_version'])||isset($new_params['is_confirmed_version1']))
		&&(($new_params['is_confirmed_version']!=$old_params['is_confirmed_version'])||
		($new_params['is_confirmed_version1']!=$old_params['is_confirmed_version1']))
		
		
		){
			$new_item=$this->GetItemById($id);
			
			if(($new_item['is_confirmed_version']!=0)&&($new_item['is_confirmed_version1']!=0)){
				//����������� ������ �� 40 - ��������
				$setted_status_id=40;
						
				$params['status_id']=$setted_status_id	;
				
				$this->Edit($id, NULL,  $params);
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���',NULL,1050,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				//�������� - � ��� ��, ��� - ��� ��-� ���� 4
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_out_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				
				 
			}
			else{
				//����� ������ 40 - ��������
				//����� ������� �� 2
				$setted_status_id=2;
						
				$this->Edit($id, NULL, array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���',NULL,1051,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				//�������� - � ��� ��, ��� - ���� ��-� ���� 4
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_out_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				 
			}
		}
		
			
			  
		
		
		//die();
	}
	 
	
	
	 
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
	    $res.='���, ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new Lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
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
		 
		$res.='��� '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		 
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	
	
	//������� �������
	public function AddPositions($id, $version_id, $p_or_m,  array $positions, $result=NULL,$document=NULL){
		
		if($version_id==self::ACTIVE_VERSION){
			//�������� ��������� ������
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		
		$_kpi=new BDR_AccountVersionItem; $_kpr=new BDR_AccountVersionGroup;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		if($document===NULL) $document=$this->getitembyid($id);
		$old_positions=array();
		
		$old_positions=$_kpr->GetItemsByIdArr($id, $version_id, $p_or_m, false, $document); 
		/*
			$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes
		 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
		where 
		 	  des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
			order by des.ord desc, des.code asc';*/
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'bdr_id'=>$v['bdr_id'],
			'version_id'=>$version_id, 
			'account_id'=>$v['account_id']
			 
			
			));
			
 
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['bdr_id']=$v['bdr_id'];
				
				$add_array['version_id']=$version_id;
				$add_array['account_id']=$v['account_id'];
				
				$add_array['cost']=$v['cost'];
				 
				
				$add_array['notes']=$v['notes'];
				
				
				
			 
				$_kpi->Add($add_array );
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'bdr_id'=>$v['bdr_id'],
					'version_id'=>$version_id,
					'account_id'=>$v['account_id'],
					'cost'=>$v['cost'],
					'notes'=>$v['notes'] 
					 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['bdr_id']=$v['bdr_id'];
				$add_array['version_id']=$version_id;
				$add_array['account_id']=$v['account_id'];
				$add_array['cost']=$v['cost'];
				
				
				$add_array['notes']=$v['notes'];
				 
				 
				$_kpi->Edit($kpi['id'], $add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				if($kpi['bdr_id']!=$add_array['bdr_id']) $to_log=$to_log||true;
				if($kpi['version_id']!=$add_array['version_id']) $to_log=$to_log||true;
				if($kpi['account_id']!=$add_array['account_id']) $to_log=$to_log||true;
				 
				
				if($kpi['cost']!=$add_array['cost']) $to_log=$to_log||true;
				if($kpi['notes']!=$add_array['notes']) $to_log=$to_log||true;
			 	
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'bdr_id'=>$v['bdr_id'],
						'version_id'=>$version_id,
						'account_id'=>$v['account_id'],
						'cost'=>$v['cost'],
						'notes'=>$v['notes'] 
						 
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
			
			/*echo $vv['bdr_id']==$v['bdr_id'];
			echo  $vv['version_id']==$version_id*/
			 
			foreach($positions as $kk=>$vv){
				if(($vv['bdr_id']==$v['bdr_id'])
				&&($version_id==$v['version_id'])
				&&($vv['account_id']==$v['account_id'])
				 
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
					 'bdr_id'=>$v['bdr_id'],
						'version_id'=>$version_id,
						'account_id'=>$v['account_id'],
						'cost'=>$v['cost'],
						'notes'=>$v['notes'] 
			);
			
			//������� �������
			$_kpi->Del($v['d_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	 
	
	
	
	//������� ������� ����
	public function AddBDDSPositions($id, $version_id, $p_or_m,  array $positions, $result=NULL,$document=NULL){
		
		if($version_id==self::ACTIVE_VERSION){
			//�������� ��������� ������
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		
		$_kpi=new BDDS_AccountValueItem; $_kpr=new BDDS_AccountValuesGroup;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		if($document===NULL) $document=$this->getitembyid($id);
		$old_positions=array();
		
		$old_positions=$_kpr->GetItemsByIdArr($id, $version_id, $p_or_m,  false, $document); 
		 /*$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.ds_account_id, doc.cost, doc.year, doc.month
		 
		from  bdds_account as des
		inner join bdds_account_value as doc on doc.ds_account_id=des.id
		
		where 
		 	  des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
			order by des.name asc';*/
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'bdr_id'=>$v['bdr_id'],
			'version_id'=>$version_id, 
			'ds_account_id'=>$v['ds_account_id'],
			'year'=>$v['year'],
			'month'=>$v['month']
			
			
			));
			
			//�������� �������				
			if($v['cost']==NULL){
				
				 
				if($kpi!==false){
					$log_entries[]=array(
							'action'=>2,
							 'bdr_id'=>$v['bdr_id'],
								'version_id'=>$version_id,
								'ds_account_id'=>$v['ds_account_id'],
								'cost'=>$kpi['cost'],
								'year'=>$v['year'],
								'month'=>$v['month'] 
					);
					
					//������� �������
					$_kpi->Del($kpi['id']);
				}
				
			}elseif($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['bdr_id']=$v['bdr_id'];
				
				$add_array['version_id']=$version_id;
				$add_array['ds_account_id']=$v['ds_account_id'];
				
				$add_array['year']=$v['year'];
				$add_array['month']=$v['month'];
				
				$add_array['cost']=$v['cost'];
				 
				 
				
			 
				$_kpi->Add($add_array );
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'bdr_id'=>$v['bdr_id'],
					'version_id'=>$version_id,
					'ds_account_id'=>$v['ds_account_id'],
					'cost'=>$v['cost'],
					'year'=>$v['year'],
					'month'=>$v['month']
			
					 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['bdr_id']=$v['bdr_id'];
				$add_array['version_id']=$version_id;
				$add_array['ds_account_id']=$v['ds_account_id'];
				
				$add_array['year']=$v['year'];
				$add_array['month']=$v['month'];
				
				$add_array['cost']=$v['cost'];
				 
			 
				 
				 
				$_kpi->Edit($kpi['id'], $add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				if($kpi['bdr_id']!=$add_array['bdr_id']) $to_log=$to_log||true;
				if($kpi['version_id']!=$add_array['version_id']) $to_log=$to_log||true;
				if($kpi['ds_account_id']!=$add_array['ds_account_id']) $to_log=$to_log||true;
				
				if($kpi['month']!=$add_array['month']) $to_log=$to_log||true;
				if($kpi['year']!=$add_array['year']) $to_log=$to_log||true;
				 
				
				if($kpi['cost']!=$add_array['cost']) $to_log=$to_log||true;
				 
			 	
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'bdr_id'=>$v['bdr_id'],
						'version_id'=>$version_id,
						'ds_account_id'=>$v['ds_account_id'],
						'cost'=>$v['cost'],
						'year'=>$v['year'],
						'month'=>$v['month']
						 
				  );
				}
				
			}
		}
		
		 
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	 
}


   

 


/***********************************************************************************************/
//����������� ������ ������
class BDR_Resolver{
	public $instance;
	function __construct($kind_id=1){
		/*switch($kind_id){
			case 1:
				$this->instance= new Sched_TaskItem;
			break;
			case 2:
				$this->instance= new Sched_MissionItem;
			break;
			case 3:
				$this->instance= new Sched_MeetItem;
			break;
			case 4:
				$this->instance= new Sched_CallItem;
			break;
			case 5:
				$this->instance= new Sched_NoteItem;
			break;
			default:
				$this->instance=new Sched_AbstractItem;
			break;
		};*/
		$this->instance=new BDR_Item;
	}
	
	 
}





// ������ ���
class  BDR_Group extends AbstractVersionGroup {
	protected $_auth_result;
	protected $_view;
	
	protected $new_list; //������ ����� ����� ��� �������� ������������ � ��������� �� �� ������
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr';
		$this->version_tablename='bdr_version';
		$this->mid_name='bdr_id';
				 
		$this->version_id_name='vid';
				 
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
				
		$this->_view=new BDR_ViewsGroup;
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	//������ ���
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
		$can_confirm_version=true, //12
		$can_unconfirm_version=true, //13
		
		$can_email=true, //14
		 
	 	$prefix='' //15
		
		){
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		  $sm->assign('can_create', $can_create);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 
		 $sm->assign('can_confirm_version', $can_confirm_version);
		 $sm->assign('can_unconfirm_version', $can_unconfirm_version);
		 
		 $sm->assign('can_email', $can_email);
		
		
		
		
		 //������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1043);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new Lead_SupplierGroup;
	 
		
		$sql='select distinct t.*, l.*
		
		 ,
		s.name as status_name, s.weight as status_weight ,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, l.confirm_pdate as confirm_price_pdate,
		uv.name_s as confirmed_version_name, uv.login as confirmed_version_login, l.confirm_version_pdate as confirm_version_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		lead.code as lead_code,
		kp_in.code as kp_in_code,
		kp_out.code as kp_out_code,
		tz.code as tz_code  
		 
		 
					 
				from '.$this->tablename.' as t
				
				 
		
				left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
			
				left join document_status as s on s.id=t.status_id
				left join user as u on u.id=t.manager_id
				left join user as up on up.id=l.user_confirm_id
				left join user as uv on uv.id=l.user_confirm_version_id 
			 	 
			 left join lead as lead on lead.id=t.lead_id
				left join kp_in as kp_out on kp_out.id=t.kp_out_id
				left join kp_in as kp_in on kp_in.id=t.kp_in_id
				left join tz as tz on tz.id=t.tz_id
				 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				
				 
				
				left join user as cr on cr.id=t.created_id
			 
			 
				 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
				from '.$this->tablename.' as t
				
				 
				left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
				
				left join document_status as s on s.id=t.status_id
				left join user as u on u.id=t.manager_id
				left join user as up on up.id=l.user_confirm_id
				left join user as uv on uv.id=l.user_confirm_version_id	
			 	 
				left join lead as lead on lead.id=t.lead_id
				left join kp_in as kp_out on kp_out.id=t.kp_out_id
				left join kp_in as kp_in on kp_in.id=t.kp_in_id
				left join tz as tz on tz.id=t.tz_id 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				
				 
				
				left join user as cr on cr.id=t.created_id
			 
				 
		 
				 ';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br><br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$link);
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
			
		 
			 $f['given_pdate']=date('d.m.Y H:i:s', $f['given_pdate']);
			
			$f['pdate_d']=date('d.m.Y', $f['pdate']);
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			if($f['confirm_version_pdate']!=0) $f['confirm_version_pdate']=date('d.m.Y H:i:s', $f['confirm_version_pdate']);
			else $f['confirm_version_pdate']='-'; 
			 
			 
				$_res=new BDR_Resolver();
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			
			 
			
			
			 
			$f['suppliers']=$_sg->GetItemsByIdArr($f['lead_id']);	
				
			 
			 	
			 
			
			
				
			
			
			//�������� ����� "����� ��������"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			
			
			
			
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
	
	
	
	
	//������ ID ��, ������� ����� ������ ������� ���������
	public function GetAvailableBDRIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1040)){
			$sql='select id from bdr';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from bdr where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
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
	
	//��������, ����� �� �������� ��� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1040)){
			 
			
			return true;
		}else{
			//����
			 
			
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
	
	
	
	//����� ����� ����� ��� �������� ����������
	public function CountNewTzs($user_id){
		
		$tender_ids=$this->GetAvailableBDRIds($user_id);
		
		
		
		$man=new DiscrMan;
		
		//
	 
		if($man->CheckAccess($user_id,'w',1016)){	
			$sql='select count(*) from 
			tz as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.status_id=2
			and t.was_sent=0
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
			  
					$f=mysqli_fetch_array($rs);	
		}
		//echo $f[0];			
		return (int)$f[0];	
			 
	}
	
	
	
	//����� ����� BDR - ����������� ���������
	public function CountNewTzsExtended($user_id, $do_iconv=true){
		$data=array();
			
		$tender_ids=$this->GetAvailableBDRIds($user_id);
		
		  
		$man=new DiscrMan;
		/*
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1028)	
		){
			
			$sql='select count(*) from 
			tz as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.is_not_eq=1
			and t.force_eq_in=0
			and t.status_id not in(3, 27)
			and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					tz as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 
				and t.is_not_eq=1
				and t.force_eq_in=0
				and t.status_id not in(3, 27)
				and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tz.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_tz',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'�������� �������� �� �� ��!'
				
				);
			}	
		}
		
		//�� �� ���� ������� ��
		//��������� ������������� ����!
		$sql='select count(*) from 
			tz as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.force_eq_in=1
			and t.status_id<>39
			and t.manager_id="'.$user_id.'"
			';
			
			
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				tz as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.force_eq_in=1
			and t.status_id<>39
			and t.manager_id="'.$user_id.'"
			order by t.id asc limit 1
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);		
			$url='ed_tz.php?action=1&id='.$g['id'].'&from_begin=1';
			
			$data[]=array(
				'class'=>'menu_new_kp',
				'num'=>(int)$f[0],
				'first_url'=>$url,
				'comment'=>'�������� �� �� ��!'
			
			);
		}	
		
		
		//�� �������� �������, ����������� ����� � ����� ������� ������ ��������� ������������ �� ��
		if($user['department_id']==4){
			$sql='select count(*) from 
				kp_in as t
				
				where t.tz_id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id<>3
				and  t.fulful_kp=0
				';
				
				
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					kp_in as t
				
				where t.tz_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id<>3
				and  t.fulful_kp=0
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpv',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'����� �������: ��������� ������������ �� ��!'
				
				);
			}
		}
		if($user['department_id']==7){	
			$sql='select count(*) from 
				kp_in as t
				
				where t.tz_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id<>3
				and  t.fulful_kp1=0
				';
				
				
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					kp_in as t
				
				where t.tz_id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id<>3
				and  t.fulful_kp1=0
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpv',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'����������� �����: ��������� ������������ �� ��!'
				
				);
			}
		}
		*/
	
		if($do_iconv) foreach($data as $k=>$v) $data[$k]['comment']=iconv('windows-1251', 'utf-8', $v['comment']);
		
		return $data;
	}
	
	//�������� �� ������� �� ��� ������� ������������ � ���������, ���� �������� - ������� ������ ��� ���������� �����
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
		
		$tender_ids=$this->GetAvailableBDRIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		
		//��� ������ ��� ����� 1057:
		//���� ���. ���, �� �� ����������� ������� ��������
		if(/*($user['main_department_id']==5)||*/
			$man->CheckAccess($user_id,'w',1057)	
		){
			$sql='select
			
			count(distinct t.id) 
		  	from '.$this->tablename.' as t
			 left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
			 
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select distinct t.*, l.*  
					from '.$this->tablename.' as t
			 left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
			 
				
				where t.id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version=0
				and t.status_id not in(3, 27)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
					
				}	
				 
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'url'=>'ed_bdr.php?action=1&id=',
					'comment'=>'���������� ������: ��������� �������� ���!'
				
				);
			}	
		}
		
		
		//������ ��� ����� 1057:
		//���� ���. ���, �� �� ����������� ������� ��������
		if(/*($user['main_department_id']==5)||*/
			$man->CheckAccess($user_id,'w',1055)	
		){
			$sql='select
			
			count(distinct t.id) 
		  	from '.$this->tablename.' as t
			 left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
			 
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version1=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select distinct t.*, l.*  
					from '.$this->tablename.' as t
			 left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
			 
				
				where t.id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version1=0
				and t.status_id not in(3, 27)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
					
				}	
				 
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'url'=>'ed_bdr.php?action=1&id=',
					'comment'=>'����������� ��������: ��������� �������� ���!'
				
				);
			}	
		}
		
		
		
	}
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		/*$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		$_res=new Lead_Item;
	 
		$_ni=new Lead_HistoryItem;
		 
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id=18 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//������ 1 - ��� ������ �������:
			if($f['is_confirmed']==0){
				
				
					
				//�������� ���� ��������������
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� ����,  �������� �� ���������';
					}
				}else{
					//�������� � ����� ��������	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� �������� ����,  �������� �� ���������';
					}
				}
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				 
					//$_res->instance->Edit($id, $params);
				
				$_res->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'�������������� ������������� ����',NULL,950,NULL,'� ���������: '.$f['code'].' ���������� ������ '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'sched_id'=>$f['id'],
			 
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>'�������������� ����������: ��� ��� ������������� �����������, �������: '.$reason.'.'
				)); 
					
			}
		}*/
		
	}
	
}





   









 /*************************************************************************************************/  
  

 
//������ � ������ � �����/������ ��� �� ���������
class BDR_WorkingItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//������ ������� � ������ � �����/������ ��� �� ���������
class BDR_WorkingGroup extends Abstract_WorkingKindGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
 
}
 


/*******************************************************************************************************/

 

// ������ ���������� ���
class BDRNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}

//���������� � BDR
class BDRNotesItem extends NotesItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}


/*****************************************************************************************************/
//����������� ���� ������
class BDR_Abstract_Block{
	
	//���������� ������ �� ���� ���������
	public function ConstructById($id){
		
	}
	
	
	//���������� ������ �� ���� ������
	public function ConstructByAccounts($accounts){
		
	}
		
}


//���� ������ �������
class BDR_P_Block extends BDR_Abstract_Block{
	
	//���������� ������ �� ���� ���������
	public function ConstructById($id, $version_id, &$total_wo_nds){
		
		$arr=array(); $total_nds=0; $total_wo_nds=0; $total=0;
		$no=$this->LoadBranchArr($id,$version_id,0,0, $arr,  $total_nds, $total_wo_nds, $total);
		
		//�������� ������� �����
		$arr[]=array(
			'id'=>0,
			'code'=>sprintf("%02d.", ($no+1)),
			'name'=>'����� �������',
			'cost'=>$total,
			'nds_cost'=>$total_nds,
			'cost_wo_nds'=>$total_wo_nds,
			'cost_formatted'=>number_format($total, 2, ',',' '),
			'nds_cost_formatted'=>number_format($total_nds, 2, ',',' '),
			'cost_wo_nds_formatted'=>number_format($total_wo_nds, 2, ',',' ')
			);
			
			
		
		return $arr;
	}
	
	
	//���������� ������ �� ���� ������
	public function ConstructByAccounts($accounts, &$total_wo_nds){
		$arr=array(); $total_nds=0; $total_wo_nds=0; $total=0;
		$no=$this->LoadBranchArrAccounts($accounts,0,0, $arr,  $total_nds, $total_wo_nds, $total);
		
		
		//�������� ������� �����
		$arr[]=array(
			'id'=>0,
			'code'=>sprintf("%02d.", ($no+1)),
			'name'=>'����� �������',
			'cost'=>$total,
			'nds_cost'=>$total_nds,
			'cost_wo_nds'=>$total_wo_nds,
			'cost_formatted'=>number_format($total, 2, ',',' '),
			'nds_cost_formatted'=>number_format($total_nds, 2, ',',' '),
			'cost_wo_nds_formatted'=>number_format($total_wo_nds, 2, ',',' ')
			);
			
			
		
		return $arr;
	}
	
	
	//������ ������� ���������� ���  Accounts
	public function LoadBranchArrAccounts($accounts, $parent_id=0, $p_or_m=0, &$alls, &$total_nds, &$total_wo_nds, &$total){
		
		
		$sql='select des.*,
			count(sub.id) as s_q 
		from  bdr_account as des
		 
		
		 left join bdr_account as sub on sub.parent_id=des.id and sub.id in('.implode(', ',$accounts['ids']).')
		 
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and des.id in('.implode(', ',$accounts['ids']).')
		
		group by des.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//������ ���������
			//���� ��� �������� - �� ����� ��������� �� ����
			//���� ���� ������� - �� ����� �� �����
			$s_q=(int)$f['s_q'];
			$f['editable']=($s_q==0);
			
			$f['notes']= $accounts['data'][$f['id']]['notes'];
			if($s_q==0){
				
				$f['cost']= $accounts['data'][$f['id']]['cost']; //$f['cost'];
				
				//����� ����� ����� ��� � ��������� ��� ���
				$f['nds_cost']=round($f['cost']-$f['cost']/(1+$f['nds']),2);	
				$f['cost_wo_nds']=round($f['cost']-$f['nds_cost'],2);	
				
			}else{
				//������������ ���� �������� - ��� ���� ��������?
				$cost=0; $nds_cost=0; $cost_wo_nds=0;
				$this->SumOfChildAccounts($accounts, $f['id'], $p_or_m, $cost, $nds_cost, $cost_wo_nds);
				
				//echo $nds_cost;
				$f['cost']=$cost;
				
				$f['nds_cost']=$nds_cost;	
				$f['cost_wo_nds']=$cost_wo_nds;
			}
			
			
			
			//��� �������� ������ ����������� ��������� �������� ������
			if($parent_id==0){
				$total+=$f['cost'];
				$total_nds+=$f['nds_cost'];
				$total_wo_nds+=	$f['cost_wo_nds'];
			}
			
			$f['cost_formatted']=number_format($f['cost'], 2, ',',' ');
			$f['nds_cost_formatted']=number_format($f['nds_cost'], 2, ',',' ');
			$f['cost_wo_nds_formatted']=number_format($f['cost_wo_nds'], 2, ',',' ');
			
			
			
			
			$alls[]=$f;
			
			$this->LoadBranchArrAccounts($accounts, $f['id'], $p_or_m, $alls,  $total_nds, $total_wo_nds, $total);
		}	
			
		return $no;	
		
	}
	
	//��������� �� ��������
	protected function SumOfChildAccounts($accounts, $parent_id=0, $p_or_m=0, &$cost, &$nds_cost, &$cost_wo_nds){
		$sql='select des.*,
			count(sub.id) as s_q 	 
		from  bdr_account as des
		 
	 	left join bdr_account as sub on sub.parent_id=des.id  and sub.id in('.implode(', ',$accounts['ids']).')
	 
		
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and des.id in('.implode(', ',$accounts['ids']).')
		
		group by des.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if((int)$f['s_q']==0){
				 $cost1=$accounts['data'][$f['id']]['cost']; 
				 $cost+=$cost1;
								 
				 $nds_cost1=round((float)$cost1-(float)$cost1/(1+$f['nds']),2);	
				 
				 $nds_cost+=$nds_cost1;
				 
				 $cost_wo_nds+=round((float)$cost1-$nds_cost1,2);	
				 
			}else  $this->SumOfChildAccounts($accounts, $f['id'], $p_or_m, $cost, $nds_cost, $cost_wo_nds);
		}
	}
	
	
	//������ ������� ���������� ���  ConstructById
	public function LoadBranchArr($id, $version_id, $parent_id=0, $p_or_m=0, &$alls, &$total_nds, &$total_wo_nds, &$total){
		
		
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes ,
			count(sub.id) as s_q 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
		
		 left join bdr_account as sub on sub.parent_id=des.id
		left join bdr_account_version as suba on suba.account_id=sub.id
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
		group by doc.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//������ ���������
			//���� ��� �������� - �� ����� ��������� �� ����
			//���� ���� ������� - �� ����� �� �����
			$s_q=(int)$f['s_q'];
			$f['editable']=($s_q==0);
			
			if($s_q==0){
				$f['cost']=$f['cost'];
				
				//����� ����� ����� ��� � ��������� ��� ���
				$f['nds_cost']=round($f['cost']-$f['cost']/(1+$f['nds']),2);	
				$f['cost_wo_nds']=round($f['cost']-$f['nds_cost'],2);	
				
			}else{
				//������������ ���� �������� - ��� ���� ��������?
				$cost=0; $nds_cost=0; $cost_wo_nds=0;
				$this->SumOfChild($id, $version_id, $f['id'], $p_or_m, $cost, $nds_cost, $cost_wo_nds);
				
				//echo $nds_cost;
				$f['cost']=$cost;
				
				$f['nds_cost']=$nds_cost;	
				$f['cost_wo_nds']=$cost_wo_nds;
			}
			
			
			
			//��� �������� ������ ����������� ��������� �������� ������
			if($parent_id==0){
				$total+=$f['cost'];
				$total_nds+=$f['nds_cost'];
				$total_wo_nds+=	$f['cost_wo_nds'];
			}
			
			$f['cost_formatted']=number_format($f['cost'], 2, ',',' ');
			$f['nds_cost_formatted']=number_format($f['nds_cost'], 2, ',',' ');
			$f['cost_wo_nds_formatted']=number_format($f['cost_wo_nds'], 2, ',',' ');
			
			
			
			
			$alls[]=$f;
			
			$this->LoadBranchArr($id, $version_id, $f['id'], $p_or_m, $alls,  $total_nds, $total_wo_nds, $total);
		}	
			
		return $no;	
		
	}
	
	//��������� �� ��������
	protected function SumOfChild($id, $version_id, $parent_id=0, $p_or_m=0, &$cost, &$nds_cost, &$cost_wo_nds){
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes ,
			count(sub.id) as s_q 	 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
	 	left join bdr_account as sub on sub.parent_id=des.id
		left join bdr_account_version as suba on suba.account_id=sub.id
		
		
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
		group by doc.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if((int)$f['s_q']==0){
				 $cost+=(float)$f['cost'];
				 
				 
				 $nds_cost1=round((float)$f['cost']-(float)$f['cost']/(1+$f['nds']),2);	
				 
				 $nds_cost+=$nds_cost1;
				 
				 $cost_wo_nds+=round((float)$f['cost']-$nds_cost1,2);	
				 
			}else  $this->SumOfChild($id, $version_id, $f['id'], $p_or_m, $cost, $nds_cost, $cost_wo_nds);
		}
	}
	
		
}

//���� ������ �������
class BDR_M_Block extends BDR_Abstract_Block{
	
	//���������� ������ �� ���� ���������
	public function ConstructById($kp_in_id, $id, $version_id, &$total){
		
		$_kp_in=new KpIn_Item;
		$kp_in=$_kp_in->getitembyid($kp_in_id);
		
		$_curs=new CurrencySolver;
		$rates=$_curs->GetActual();
		
		//echo $kp_in['cost'];
		
		$kp_in['cost']=round(CurrencySolver::Convert($kp_in['cost'], $rates,  $kp_in['currency_id'],1),2);
		
		
		
		$arr=array();   $total=0;
		$no=$this->LoadBranchArr($kp_in, $id,$version_id,0,1, $arr,    $total);
		
		//�������� ������� �����
		$arr[]=array(
			'id'=>0,
			'code'=>sprintf("%02d.", ($no+1)),
			'name'=>'����� ������� ������� ��� ���',
			'cost'=>$total,
			 
			'cost_formatted'=>number_format($total, 2, ',',' ') 
			);
			
			
		
		return $arr;
	}
	
	
	//���������� ������ �� ���� ������
	public function ConstructByAccounts($accounts, $kp_in_id, &$total){
		$arr=array();   $total=0;
		
		$_kp_in=new KpIn_Item;
		$kp_in=$_kp_in->getitembyid($kp_in_id);
		
		$_curs=new CurrencySolver;
		$rates=$_curs->GetActual();
		
		//echo $kp_in['cost'];
		
		$kp_in['cost']=round(CurrencySolver::Convert($kp_in['cost'], $rates,  $kp_in['currency_id'],1),2);
		
		//�������� ������������ - ������� ������ ������� ������
		//��������� ������� �������� �� ����. ���� ������� �� ���� ����, �� �� �������� ������� ��� - ������� ������
			
		$this->PureAccounts($accounts);
		
		
		$no=$this->LoadBranchArrAccounts($accounts,0,1, $kp_in, $arr,   $total);
		
		
		//�������� ������� �����
		$arr[]=array(
			'id'=>0,
			'code'=>sprintf("%02d.", ($no+1)),
			'name'=>'����� ������� ������� ��� ���',
			'cost'=>$total,
			 
			'cost_formatted'=>number_format($total, 2, ',',' '),
			 
			);
			
			
		
		return $arr;
	}
	
	//����������� ��������� ������� Accounts
	//������� ��������, ��� ������� ���� ������� �� �����������, �� ��� �������� �� Accounts
	protected function PureAccounts(&$accounts){
		//������� ���������� ������� - ���� ������� �� �����������, �� ��� �������� �� Accounts
		$do_next_turn=false;
		
		//���������� ��������
		foreach($accounts['ids'] as $k=>$node){
			//������� ����� ������ �������� �� �����������
			$sql='select id from bdr_account where parent_id="'.$node.'"';
			$set=new mysqlset($sql);
			$rs=$set->GetResult(); $rc=$set->GetResultNumRows();
			
			if($rc>0){
				$children=array();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					$children[]=$f['id'];
				}
				//���� ����� �������� �� ����������� !=0 - �� ������� ����� �������� �� Accounts, ��� ����� ����� ��� ������ �������� Accounts ��������� �� ���������� � �������� �� �����������
				
				$count_in_accounts=0;
				foreach($accounts['ids'] as $kk=>$node1){
					if($k==$kk) continue;
					if(in_array($node1, $children)) $count_in_accounts++;	
				}
				
				//���� ����� �������� �� Accounts !=0 - ��. ����� - ������� ��� ������, �������� ������� ���������� �������
				if($count_in_accounts==0){
					unset($accounts['ids'][$k]);
					unset($accounts['data'][$node]);
					
					$do_next_turn=$do_next_turn||true;	
				}
				
				
				
			}
		}
		
		if($do_next_turn) $this->PureAccounts($accounts);
		
	}
	
	
	//������ ������� ���������� ���  Accounts
	public function LoadBranchArrAccounts($accounts, $parent_id=0, $p_or_m=0, $kp_in, &$alls,  &$total){
		
		
		$sql='select des.*,
			count(sub.id) as s_q 
		from  bdr_account as des
		 
		
		 left join bdr_account as sub on sub.parent_id=des.id and sub.id in('.implode(', ',$accounts['ids']).')
		 
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and des.id in('.implode(', ',$accounts['ids']).')
		
		group by des.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			
			//������ ���������
			//���� ��� �������� - �� ����� ��������� �� ����
			//���� ���� ������� - �� ����� �� �����
			$s_q=(int)$f['s_q'];
			
			
			 
			$f['editable']=($s_q==0)&&($f['from_kp_in']!=1);
			
			$f['notes']= $accounts['data'][$f['id']]['notes'];
			if($s_q==0){
				
				if($f['from_kp_in']==1) $f['cost']=$kp_in['cost'];
				else  $f['cost']= $accounts['data'][$f['id']]['cost']; //$f['cost'];
				
				 
				
			}else{
				//������������ ���� �������� - ��� ���� ��������?
				$cost=0; $nds_cost=0; $cost_wo_nds=0;
				$this->SumOfChildAccounts($accounts, $f['id'], $p_or_m, $kp_in, $cost);
				
				//echo $nds_cost;
				$f['cost']=$cost;
				 
			}
			
			
			
			//��� �������� ������ ����������� ��������� �������� ������
			if($parent_id==0){
				$total+=$f['cost'];
				 
			}
			
			$f['cost_formatted']=number_format($f['cost'], 2, ',',' ');
			 
			
			$alls[]=$f;
			
			$this->LoadBranchArrAccounts($accounts, $f['id'], $p_or_m, $kp_in, $alls, $total);
		}	
			
		return $no;	
		
	}
	
	//��������� �� ��������
	protected function SumOfChildAccounts($accounts, $parent_id=0, $p_or_m=0, $kp_in, &$cost){
		$sql='select des.*,
			count(sub.id) as s_q 	 
		from  bdr_account as des
		 
	 	left join bdr_account as sub on sub.parent_id=des.id  and sub.id in('.implode(', ',$accounts['ids']).')
	 
		
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and des.id in('.implode(', ',$accounts['ids']).')
		
		group by des.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if((int)$f['s_q']==0){
				
				if($f['from_kp_in']==1) $cost1=$kp_in['cost'];
				else $cost1=$accounts['data'][$f['id']]['cost']; 
				
				$cost+=$cost1;
								 
				 
				 
			}else  $this->SumOfChildAccounts($accounts, $f['id'], $p_or_m, $kp_in, $cost);
		}
	}
	
	
	//����������� ��������� ������� ������
	public function GetParents($id, &$ids){
		
		$_record=new BDR_AccountItem;
		$record=$_record->GetItemById($id);
		if($record!==false){
		
			$sql='select * from bdr_account where id="'.$record['parent_id'].'"';
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			if($rc>0){
				$f=mysqli_fetch_array($rs);
				
				$ids[]=$f['id'];
				$this->GetParents($f['id'], $ids);	
			}
		}
	}
	
	
	
	//������ ������� ���������� ���  ConstructById
	public function LoadBranchArr($kp_in, $id, $version_id, $parent_id=0, $p_or_m=1, &$alls,  &$total){
		
		
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes ,
			count(sub.id) as s_q 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
		
		 left join bdr_account as sub on sub.parent_id=des.id
		left join bdr_account_version as suba on suba.account_id=sub.id
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
		group by doc.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//������ ���������
			//���� ��� �������� - �� ����� ��������� �� ����
			//���� ���� ������� - �� ����� �� �����
			$s_q=(int)$f['s_q'];
			$f['editable']=($s_q==0)&&($f['from_kp_in']!=1);
			
			if($s_q==0){
				if($f['from_kp_in']==1) $f['cost']=$kp_in['cost'];
				else $f['cost']=$f['cost'];
				
				 
				
			}else{
				//������������ ���� �������� - ��� ���� ��������?
				$cost=0; 
				$this->SumOfChild($kp_in, $id, $version_id, $f['id'], $p_or_m, $cost);
				
				//echo $nds_cost;
				$f['cost']=$cost;
				
				 
			}
			
			
			
			//��� �������� ������ ����������� ��������� �������� ������
			if($parent_id==0){
				$total+=$f['cost'];
				 
			}
			
			$f['cost_formatted']=number_format($f['cost'], 2, ',',' ');
			 
			
			$alls[]=$f;
			
			$this->LoadBranchArr($kp_in, $id, $version_id, $f['id'], $p_or_m, $alls, $total);
		}	
			
		return $no;	
		
	}
	
	//��������� �� ��������
	protected function SumOfChild($kp_in, $id, $version_id, $parent_id=0, $p_or_m=0, &$cost){
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes ,
			count(sub.id) as s_q 	 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
	 	left join bdr_account as sub on sub.parent_id=des.id
		left join bdr_account_version as suba on suba.account_id=sub.id
		
		
		
		
		where 
		 	des.parent_id="'.$parent_id.'" and des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
		group by doc.id
			order by des.ord desc, des.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if((int)$f['s_q']==0){
				 
				 if($f['from_kp_in']==1) $cost+=$kp_in['cost'];
				 else $cost+=(float)$f['cost'];
				 
				 
				 
				 
			}else  $this->SumOfChild($kp_in, $id, $version_id, $f['id'], $p_or_m, $cost);
		}
	}
	
		
}

/*****************************************************************************************************/
//������ ��������� �� ������� �������-�������
class BDR_AccountVersionItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_account_version';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}
//������ ������� ��������� �� ������� �������-�������
class BDR_AccountVersionGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_account_version';
		$this->pagename='claim.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	//������ ������� - ��������� ����� ��� ���������� ������
	public function GetItemsByIdArr($id, $version_id, $p_or_m=0, $show_statistics=true, $document=NULL){
		$alls=array();
		
		//��� ��������, �������� ������
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.account_id, doc.cost, doc.normativ, doc.notes
		 
		from  bdr_account as des
		inner join bdr_account_version as doc on doc.account_id=des.id
		
		where 
		 	  des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
			order by des.ord desc, des.code asc';
		
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
	
}



/****************************************************************************************************/
//������� ������ �������-�������

//������ �������-�������
class BDR_AccountItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_account';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	 
	
	//�������
	public function Del($id){
		
		$query = 'delete from '.$this->tablename.' where parent_id="'.$id.'"';
		$it=new nonSet($query);
		
		 	
		
		parent::Del($id); 
	}	
	
	//����� ������������
	public function CountSubs($id, $p_or_m=0){
		$item=new mysqlSet('select count(*) from '.$this->tablename.' where parent_id="'.$id.'" and p_or_m="'.$p_or_m.'"');
		

		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		 
		 
			$res=mysqli_fetch_array($result);	
			
		return (int)$res[0];	
	}
	
}

//������ ������ �����������
class BDR_AccountGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdr_account';
		$this->pagename='claim.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	public function LoadBranchArr($parent_id=0, $p_or_m=0){
		
		$alls=array();
		$sql='select * from '.$this->tablename.' where parent_id="'.$parent_id.'" and p_or_m="'.$p_or_m.'" order by code asc, name asc';
		
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_it=new BDR_AccountItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['has_subbranches']=($_it->CountSubs($f['id'], $p_or_m)>0);
			
			$alls[]=$f;
		}	
			
		return $alls;	
		
	}
	
	
}






/******************************************************************************************************/
//������ ����


//������ ���� � ��������� � ���, ������
class BDDS_AccountItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdds_account';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	public function Del($id){
		
		$sql='delete from bdds_account_value where ds_account_id="'.$id.'"';
		new NonSet($sql);
		
		return parent::Del($id);	
	}
}


//����� � �����, ��� �� ������ ���� � ��������� � ���, ������
class BDDS_AccountValueItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdds_account_value';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	
	//�������� 
	/*public function Add($params){
		$names='';
		$vals='';
		
		
		foreach($params as $key=>$val){
			if($names=='') $names.=$key;
			else $names.=','.$key;
			
			if($vals=='') {
				if($val===self::SET_NULL) $vals.='NULL';
				else  $vals.='"'.$val.'"';
			}else{
				if($val===self::SET_NULL)  $vals.=','.' NULL';
				else  $vals.=','.'"'.$val.'"';
			}
		}
		$query='insert into '.$this->tablename.' ('.$names.') values('.$vals.');';
		$it=new nonSet($query);
		echo $query.'<br><br>';
		
		$code=$it->getResult();
		unset($it);
		return $code;
	}*/
}


//������ ������� ���� - ����� �� ������� � �����
class BDDS_AccountValuesGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bdds_account_value';
		$this->pagename='claim.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	//������ ������� - ��������� ����� ��� ���������� ������
	public function GetItemsByIdArr($id, $version_id, $p_or_m=0, $show_statistics=true, $document=NULL){
		$alls=array();
		
		//��� ��������, �������� ������
		$sql='select des.*,  
			doc.id as d_id, doc.bdr_id, doc.version_id, doc.ds_account_id, doc.cost, doc.year, doc.month
		 
		from  bdds_account as des
		inner join bdds_account_value as doc on doc.ds_account_id=des.id
		
		where 
		 	  des.p_or_m="'.$p_or_m.'" 
			and doc.bdr_id="'.$id.'" and doc.version_id="'.$version_id.'"
		
			order by des.name asc';
		
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
	
}



//���� ������ ����
class BDDS_Block{
	
	protected $data; //������ ������
	protected $short_monthes, $long_monthes, $month_names; //������ �������
	protected $id, $version_id;
	
	//���������� ������ �� ���� ���������
	public function ConstructById(&$balance, &$check){
		$balance=0; $check=0;
		$_data=new BDDS_AccountValueItem;
		
		 
		 
		$alls=$this->data;
		 
		//��������� ������ �� ���������� 0,1
		for($p_or_m=0; $p_or_m<=1; $p_or_m++){
			$subs=array();
			
			//�������� ������ �� ���� �������
			$sql='select * from bdds_account where bdr_id="'.$this->id.'" and version_id="'.$this->version_id.'" and p_or_m="'.$p_or_m.'" order by name, id';
			
			//echo $sql."<br>";
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$account=mysqli_fetch_array($rs);
				foreach($account as $k=>$v) $account[$k]=stripslashes($v);
				
				$data_short=array(); $data_long=array(); $data_formatted=array();
				//�� ������ ������ ����� �� �������� � �������� �����
				//������ ��� � ����� ������
				
				$monthes=array(); $years=array();
				
				foreach($this->long_monthes as $k=>$v){
					$valarr=explode('.', $k);
					$data=$_data->GetItemByFields(array('bdr_id'=>$this->id, 'version_id'=>$this->version_id, 'ds_account_id'=>$account['id'], 'year'=>$valarr[1], 'month'=>$valarr[0] ));
					
					//var_dump($data);
					
					if($data!==false){
						$data_short[]=$data['cost'];
						$data_long[$k]=$data['cost'];
						$data_formatted[]=number_format($data['cost'],2,'.', ' ');
					}else{
						$data_short[]=NULL;
						$data_long[$k]=NULL;
						$data_formatted[]=NULL;	
					}
					$monthes[]=$valarr[0];
					$years[]=$valarr[1];
				}
				
				$subs[]=array(
					'account_id'=>$account['id'],
					'name'=>$account['name'],
					'quantity'=>$account['quantity'],
					'data_short'=>$data_short,
					'data_long'=>$data_long,
					'data_short_formatted'=>$data_formatted,
					'monthes'=>$monthes,
					'years'=>$years
				);
			}
			
			$alls[$p_or_m]['subs']=$subs;
		}
		
		//����� ����� ������ � 0,1
		$alls=$this->CalcSumsByPM($alls);
		
		
		//����� ������ 2
		$alls=$this->CalcSaldo($alls);
		
		
		//����� ������� �/� 3
		$alls=$this->CalcOstDS($alls);
		
		//��������� ��������
		//$alls=$this->CalcCheck($alls);
		
		$check=$this->CalcCheck($alls);
		$balance=$this->CalcBalans($alls); 
		
		return $alls;	
	}
	
	
	//���������� ������ �� ������ ������.
	public function ConstructByAccounts($account_ids, $account_values, &$balance, &$check){
		$balance=0; $check=0;
		$_data=new BDDS_AccountValueItem;
		
		 
		 
		$alls=$this->data;
		
		
		 
		//��������� ������ �� ���������� 0,1
		for($p_or_m=0; $p_or_m<=1; $p_or_m++){
			$subs=array();
			
			
			//���� �� �������
			
			$sql='select * from bdds_account where bdr_id="'.$this->id.'" and version_id="'.$this->version_id.'" and p_or_m="'.$p_or_m.'"  order by name, id';
			
			//echo $sql."<br>";
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$account=mysqli_fetch_array($rs);
				foreach($account as $k=>$v) $account[$k]=stripslashes($v);
				
				$data_short=array(); $data_long=array();  $data_formatted=array();
				//�� ������ ������ ����� �� �������� � �������� �����
				//������ ��� � ����� ������
				
				$monthes=array(); $years=array();
				
				foreach($this->long_monthes as $k=>$v){
					$valarr=explode('.', $k);
					
					//$data=$_data->GetItemByFields(array('bdr_id'=>$this->id, 'version_id'=>$this->version_id, 'ds_account_id'=>$account['id'], 'year'=>$valarr[1], 'month'=>$valarr[0] ));
					
					//var_dump($data);
					
					//�������� ������ �� �������. ����� - id ������, ���, �����
					//%{$recgr.account_group}%_%{$account.account_id}%_%{$account.monthes[$smarty.foreach.fm.index]}%_%{$account.years[$smarty.foreach.fm.index]}%_value
					
					$data=false;
					foreach($account_values as $k2=>$v2){
						$aarr=explode('_', $v2);
						
						if(($aarr[1]==$account['id'])&&($aarr[2]==$valarr[0])&&($aarr[3]==$valarr[1])){
							
							if($aarr[4]!="") $data=array('cost'=>$aarr[4]);
							break;	
						}
					}
					
					
					if($data!==false){
						$data_short[]=$data['cost'];
						$data_long[$k]=$data['cost'];
						$data_formatted[]=number_format($data['cost'],2,'.', ' ');
					}else{
						$data_short[]=NULL;
						$data_long[$k]=NULL;
						$data_formatted[]=NULL;	
					}
					
					$monthes[]=$valarr[0];
					$years[]=$valarr[1];
				}
				
				$subs[]=array(
					'account_id'=>$account['id'],
					'name'=>$account['name'],
					'quantity'=>$account['quantity'],
					'data_short'=>$data_short,
					'data_long'=>$data_long,
					'data_short_formatted'=>$data_formatted,
					'monthes'=>$monthes,
					'years'=>$years
				);
			} 
			
			$alls[$p_or_m]['subs']=$subs;
		}
		
		//����� ����� ������ � 0,1
		$alls=$this->CalcSumsByPM($alls);
		
		
		//����� ������ 2
		$alls=$this->CalcSaldo($alls);
		
		
		//����� ������� �/� 3
		$alls=$this->CalcOstDS($alls);
		
		//��������� ��������
		//$alls=$this->CalcCheck($alls);
		
		$check=$this->CalcCheck($alls);
		$balance=$this->CalcBalans($alls); 
		
		return $alls;	
	}
	
	
	
	
	//����� ���������� ����� ������ � 0,1
	protected function CalcSumsByPM($alls){
		for($p_or_m=0; $p_or_m<=1; $p_or_m++){
			//$alls[$p_or_m]['data_short'], $alls[$p_or_m]['data_long'] 
			
			foreach($alls[$p_or_m]['data_long'] as $pdate=>$value){
				//echo $pdate.'<br>';
				$value=0;
				foreach($alls[$p_or_m]['subs'] as $k=>$account){
					foreach($account['data_long'] as $ac_pdate=>$ac_value){
						if($ac_pdate==$pdate) $value=(float)$ac_value+$value;	
					}
				}
					
				
				$alls[$p_or_m]['data_long'][$pdate]=$value;
			}
			
			foreach($alls[$p_or_m]['data_short'] as $pdate=>$value){
				//echo $pdate.'<br>';
				$value=0;
				foreach($alls[$p_or_m]['subs'] as $k=>$account){
					foreach($account['data_short'] as $ac_pdate=>$ac_value){
						if($ac_pdate==$pdate) $value=(float)$ac_value+$value;	
					}
				}
					
				$alls[$p_or_m]['data_short'][$pdate]=$value;
				$alls[$p_or_m]['data_short_formatted'][$pdate]=number_format($value,2,'.', ' ');
			}
			
		}
		return $alls;	
	}
	
	//����� ������ 2
	protected function CalcSaldo($alls){
		
		foreach($alls[2]['data_long'] as $pdate=>$value){
			 
			$value=	(float)$alls[0]['data_long'][$pdate]- (float)$alls[1]['data_long'][$pdate];
			
			$alls[2]['data_long'][$pdate]=$value;
		}
		
		foreach($alls[2]['data_short'] as $pdate=>$value){
			 
			$value=	(float)$alls[0]['data_short'][$pdate]- (float)$alls[1]['data_short'][$pdate];
			
			$alls[2]['data_short'][$pdate]=$value;
			$alls[2]['data_short_formatted'][$pdate]=number_format($value,2,'.', ' ');
		}
		
		
		return $alls;
	}
		
	//����� ������� �/� 3
	protected function CalcOstDS($alls){
		
		$old_value=0;
		foreach($alls[3]['data_long'] as $pdate=>$value){
			$alls[3]['data_long'][$pdate]=$old_value+(float)$alls[2]['data_long'][$pdate];
			
			$old_value = $alls[3]['data_long'][$pdate];
		}
		
		$old_value=0;
		foreach($alls[3]['data_short'] as $pdate=>$value){
			$alls[3]['data_short'][$pdate]=$old_value+(float)$alls[2]['data_short'][$pdate];
			$alls[3]['data_short_formatted'][$pdate]=number_format($alls[3]['data_short'][$pdate],2,'.', ' ');
			
			
			$old_value = $alls[3]['data_short'][$pdate];
		}
		
		return $alls;
	}
	
	//����� ������ - ������� �� ������� �� ��������� �����
	protected function CalcBalans($alls){
		
		return (float)$alls[3]['data_short'][count($alls[3]['data_short'])-1];
	}
	
	//��������� ��������
	protected function CalcCheck($alls){
		
		//����� �� �� �������, �� ���� ������� ��������� �������
		$_bdr=new BDR_Item;
		$gain=$_bdr->CalcGain($this->id, $this->version_id);
		
		$value=0;
		
		$value=(float)$gain['gain_chp'] - $this->CalcBalans($alls);
		
		return $value;
	}
	
	
	//�������������
	function __construct($id, $version_id=NULL){
		$this->month_names=array('','������','�������','����','������','���','����','����','������','��������','�������','������','�������');
		
		$_bdr=new BDR_Item;
		if($version_id===NULL) $version_id=$_bdr->GetActiveVersionId($id);
		
		$this->id=$id; $this->version_id=$version_id;
		
		$bdr=$_bdr->GetItemById($id,$version_id);
		
		//�������� ������� �������
		for($year=$bdr['beg_year']; $year<=$bdr['end_year']; $year++){
			
			if($year==$bdr['beg_year']) $b_m=$bdr['beg_month']; else $b_m=1;
			if($year==$bdr['end_year']) $e_m=$bdr['end_month']; else $e_m=12;
			
			for($month=$b_m; $month<=$e_m; $month++){
				$this->short_monthes[]=$this->month_names[$month].' '.$year;
				$this->long_monthes[$month.'.'.$year]=$this->month_names[$month].' '.$year;
			}
		}
		
		$this->data=array(); 
		 
		//������ - -1� ������
		$this->data[10]=array(
			'account_group'=>10,
			
			'name'=>'',
			'data_short'=>$this->short_monthes, //�������� ������ � ������� �� �������
			'data_short_formatted'=>$this->short_monthes, //�������� ������ � ������� �� �������
			'data_long'=>$this->long_monthes, //������ ������, ������ ������� ����� ���� ���� ���.���, � � ��� - �������� ������
			'subs'=>array() //������ ������
		);
		
		
		 
		//����������� �� - ������� ������
		$pdata=array(); $pdata_long=array(); $pdata_f=array(); 
		foreach($this->long_monthes as $k=>$v){
			$pdata[]=0;
			$pdata_f[]=0;
			$pdata_long[$k]=0;	
		}
		
		$this->data[0]=array(
			'account_group'=>0,
			
			'name'=>'(+) ����������� ��',
			'data_short'=>$pdata, //�������� ������ � ������� �� �������
			'data_short_formatted'=>$pdata_f, //�������� ������ � ������� �� �������
			'data_long'=>$pdata_long, //������ ������, ������ ������� ����� ���� ���� ���.���, � � ��� - �������� ������
			'subs'=>array() //������ ������
		);
		
		//(-) ������ - ������ ������
		$this->data[1]=array(
			'account_group'=>1,
			 
			'name'=>'(-) ������',
			'data_short'=>$pdata, //�������� ������ � ������� �� �������
			'data_short_formatted'=>$pdata_f, //�������� ������ � ������� �� �������
			'data_long'=>$pdata_long, //������ ������, ������ ������� ����� ���� ���� ���.���, � � ��� - �������� ������
			'subs'=>array() //������ ������
		);
		
		
		//������ 2 ������
		$this->data[2]=array(
			'account_group'=>2,
			 
			'name'=>'������',
			'data_short'=>$pdata, //�������� ������ � ������� �� �������
			'data_short_formatted'=>$pdata_f, //�������� ������ � ������� �� �������
			'data_long'=>$pdata_long, //������ ������, ������ ������� ����� ���� ���� ���.���, � � ��� - �������� ������
			'subs'=>array() //������ ������
		);
		
		//������� �� 3 ������
		$this->data[3]=array(
			'account_group'=>3,
			
			'name'=>'������� �� �� �������',
			'data_short'=>$pdata, //�������� ������ � ������� �� �������
			'data_short_formatted'=>$pdata_f, //�������� ������ � ������� �� �������
			'data_long'=>$pdata_long, //������ ������, ������ ������� ����� ���� ���� ���.���, � � ��� - �������� ������
			'subs'=>array() //������ ������
		);
		 
	}
		
}

?>
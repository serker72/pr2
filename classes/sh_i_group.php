<?
require_once('billgroup.php');
require_once('acc_group.php');
require_once('billitem.php');
require_once('sh_i_item.php');

require_once('authuser.php');
require_once('sh_i_notesgroup.php');
require_once('sh_i_notesitem.php');
require_once('period_checker.php');

require_once('abstract_sh_i_group.php');

// ����������� ������
class ShIGroup extends AbstractShIGroup {
	protected $_auth_result;
	
	
	public $prefix='';
	protected $is_incoming=0;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sh_i';
		$this->pagename='ed_bill.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		$this->_item=new ShIItem;
		$this->_notes_group=new ShINotesGroup;
		
		
		$this->_auth_result=NULL;
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new ShINotesItem;
		 $_itm=new ShIItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			
			//��������� ������� ����. ���������� ���
			
			$sql1='select count(id) from acceptance where sh_i_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			$reason='';
			
			
			if($f['is_confirmed']==0){
				//�� ����������
				//�������� ���� ��������������
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� ������������ �� ��������, ��� ������������ ��������� ����������, �������� �� ���������';
					}
				}else{
					//�������� � ����� ��������	
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� �������� ������������ �� ��������, ��� ������������ ��������� ����������, �������� �� ���������';
					}
				}
				
			}else{
				//����������
				//�������� ���� ��������������
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� ������������ �� ��������, ��� ������������ ��������� ����������';
					}
				}else{
					//�������� � ����� ��������	
					if(($f['confirm_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� ����������� ������������ �� ��������, ��� ������������ ��������� ����������';
					}
				}
			}
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
			
				
				$log->PutEntry(0,'�������������� ������������� ������������ �� ��������',NULL,226,NULL,'� ���������: '.$f['id'].' ���������� ������ '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'�������������� ����������: ������������ �� �������� ���� ������������� ������������, �������: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	//�������������� ������������ ������������ �� ��������
	public function AutoEq($days=21){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		
		
		
		$_stat=new DocStatusItem;
		
		$_ni=new ShINotesItem;
		 $_itm=new ShIItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id="7" order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$can_annul=false;
			
			$checked_time=$now-$days*24*60*60;
			
			//����� ���� (�� ���. ����) ����������. �� ������.
			$set1=new MysqlSet('select * from acceptance where sh_i_id="'.$f['id'].'" and is_confirmed=1 and given_pdate<"'.$checked_time.'" order by given_pdate desc limit 1');
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			if($rc1==0) continue; //���  ����������, �� �������...
			else $can_annul=true;
			
			//$f=mysqli_fetch_array($rs);
			
			//����������� ����, �������� ������������...
			if($can_annul){
					
				//����� ������� ������������, ������������ ������ args, ������� ������������...
				$posset=new mysqlset('select * from sh_i_position where sh_i_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				$args=array();
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					
					//$args=array();
					$args[]=$h['position_id'].';'.$h['pl_position_id'].';'.$h['pl_discount_id'].';'.$h['pl_discount_value'].';'.$h['pl_discount_rub_or_percent'].';'.$h['quantity'].';'.$h['kp_id'];	
					
					//����� ��������� ����������� �� ���� ������� ������������.
					//���� ��� �����������
					
					//$_itm->DoEq($f['id'],$args,$some_output,1,$f,$_result);
				}
				
				//echo $f['id'].'<br>';
				
				//if($f['id']!=51) continue;
				//$zz=$_itm->ScanEq($f['id'],$args,$some_o,$f);
				
				$_itm->DoEq($f['id'],$args,$some_output,1,$f,$_result);
				
			//	echo "$some_o <br />";
			//	print_r($args);
				//print_r($zz);
			}
			
			
		}
		
	}
}
?>
<?
require_once('abstractitem.php');
require_once('lead.class.php');
require_once('actionlog.php');
require_once('docstatusitem.php');
require_once('useritem.php'); 
 
//������ ����� ��� ���-��
class DocOut_HistoryItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_history';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	
	
	//�������� 
	public function Add($params){
		 
		 $code= parent::Add($params);
		 
		 //��������� ������������ ������.
		/* $_ti=new Lead_Item;
		 $ti=$_ti->getitembyid($params['sched_id']);
		 
		 if(($ti['status_id']==2)&&($ti['manager_id']==$params['user_id'])){
				
				$u_params=array();
				$u_params['status_id']=28;
				$_ti->Edit($params['sched_id'],$u_params);
				
				// 
				$log=new ActionLog; $_dsi=new DocStatusItem;
				$status=$_dsi->GetItemById($u_params['status_id']);
				
				$_ui=new UserItem;
				$user=$_ui->GetItemById($params['user_id']);
				
				
				$log->PutEntry($params['user_id'], '����� ������� ����',NULL,950,NULL,'���������� ������ '.$status['name'].' ��� �������� ����������� ������������� ����������� '.$user['name_s'],$params['sched_id']);
				
				
				parent::Add(array(
					'sched_id'=>$params['sched_id'],
					'txt'=>'�������������� �����������: '.'���������� ������ '.$status['name'].' ��� �������� ����������� ������������� ����������� '.$user['name_s'],
					'user_id'=>0,
					'pdate'=>$params['pdate']
				));
		 }
		 */
		 
		 
		 return $code;
	}
	 
}
?>
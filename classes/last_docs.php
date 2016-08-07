<?
require_once('abstractitem.php');
require_once('db_decorator.php');
require_once('kpitem.php');
require_once('docstatusitem.php');
require_once('supplieritem.php');
require_once('opfitem.php');


class LastDocs{
	
	protected $result_data;
	protected $docs_list;
	protected $ct=3;
	
	function __construct(){
		$docs_list=array();	
	}
	
	public function AddDoc($doc){
		$this->docs_list[]=$doc;	
	}
	
	
	
	
	public function GetData($user_id){
		$sql='';
		
		$_sqls=array();
		foreach($this->docs_list as $k=>$v){
			$_sqls[]=' (select distinct affected_object_id, "'.$k.'" as lab, pdate from action_log where user_subj_id='.$user_id.' and object_id in('.implode(', ',$v->object_ids).') '.$v->extra_filters_sql.' and affected_object_id<>0  order by pdate desc  limit '.$this->ct.'  ) ';
		}
		
		$sql='(' .implode(' UNION ALL ', $_sqls).' )  order by 3 desc limit '.$this->ct;
		
		//echo $sql;
		
		$set=new Mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$data=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			$f['name']=$this->docs_list[(int)$f['lab']]->ConstructName($f['affected_object_id']);
			$f['date']=$this->docs_list[(int)$f['lab']]->ConstructDate($f['affected_object_id']);
			
			$f['url']=$this->docs_list[(int)$f['lab']]->ConstructUrl($f['affected_object_id']);
			
			$data[]=$f;
		}
		
		return $data;
	}
	
}

class LastDocs_AbstractDoc{
	public $object_ids;
	public $extra_filters_sql;
	public $pagename;
	public $extra_sting;
	public $id_name;
	
	
	function __construct($object_ids, $extra_filters_sql, $pagename, $extra_sting, $id_name){
		$this->object_ids=$object_ids;
		$this->extra_filters=$extra_filters_sql;
		$this->pagename=$pagename;
		$this->extra_sting=$extra_sting;
		$this->id_name=$id_name;
		
		
		
	}
	
	
	public function ConstructUrl($id){
		return $this->pagename.'?'.$this->id_name.'='.$id.'&'.$this->extra_sting;
	}
	
	public function ConstructName($id){
		
	}
	public function ConstructDate($id){
		
	}
		
}

class LastDocs_KP extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new KpItem; $_stat=new DocStatusItem; $_si=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		$si=$_si->getitembyid($kp['supplier_id']);
		$opf=$_opf->Getitembyid($si['opf_id']);
		
		return 'Коммерческое предложение '.$kp['code'].', контрагент '.$opf['name'].' '.$si['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	

}

?>
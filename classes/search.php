<?
require_once('abstractitem.php');
require_once('db_decorator.php');
require_once('kpitem.php');
require_once('pl_positem.php');
require_once('plan_fact_fact_item.class.php');
require_once('docstatusitem.php');
require_once('tender.class.php');
require_once('lead.class.php');

require_once('spitem.php');
require_once('filefolderitem.php');

require_once('fileitem.php');
require_once('filelitem.php');
require_once('spsitem.php');


require_once('doc_in.class.php');
require_once('doc_vn.class.php');
require_once('doc_out.class.php');

require_once('petitionitem.php');
require_once('petitionkinditem.php');
require_once('memoitem.php');

require_once('sched.class.php');

class Search{
	
	 
	protected $docs_list;
	
	
	function __construct(){
		$docs_list=array();	
	}
	
	public function AddDoc($doc){
		$this->docs_list[]=$doc;	
	}
	
	
	
	
	public function GetData($data, $do_it=false, &$total){
		$sql='';
		
		 
		$_sqls=array();
		foreach($this->docs_list as $k=>$v){
			
			
			
			$strr='( select distinct p.id,  "'.$k.'" as lab  '.$v->base_sql.'  ';
			
			$flt=array();
			foreach($v->fields as $kk=>$vv){
				$flt[]=' '.$vv.' LIKE "%'.$data.'%" ';	
			}
			$strr.= ' WHERE ';
			
			$strr.='('.implode(' OR ',$flt).')';
			
			$db_flt=$v->view_decorator->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$strr.=' and '.$db_flt;
			 
			
			}
			
			
			$strr.=')  ';
			
			$_sqls[]= $strr;
			
		}
		
		$sql='' .implode(' UNION ALL ', $_sqls).'  order by 2 asc, 1 desc ';
		
		
		
		
		 
		$alls=array();
		if($do_it){
			
			//echo $sql;
			$set=new Mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//найдем совпадения в полях
				$f['matched_fields']=$this->docs_list[(int)$f['lab']]->ConstructMatchedFields($f['id'], $data);
				$f['date']=$this->docs_list[(int)$f['lab']]->ConstructDate($f['id']);
				
				$f['name']=$this->docs_list[(int)$f['lab']]->ConstructName($f['id']);
				 
				$f['url']=$this->docs_list[(int)$f['lab']]->ConstructUrl($f['id']);
				
				$f['registry_url']=$this->docs_list[(int)$f['lab']]->ConstructRegistryUrl($f['id']);
				
				$alls[]=$f;
			}
		}
		 
		
		$total=count($alls);
		
		//разбиваем результат по блокам
		$alls1=array();
		foreach($this->docs_list as $k=>$v){
			
			$docs=array();
			
			foreach($alls as $kk=>$vv){
				if($vv['lab']==$k) $docs[]=$vv;	
			}
			
			$alls1[]=array('name'=>$v->block_name,
						   'docs'=>$docs);
		}
		
		return $alls1;
	}
	
}

class Search_AbstractDoc{
	public $block_name;
	 
	public $base_sql;
	public $fields; public $fields_names;
	public $view_decorator;
	
	
	public $pagename;
	public $extra_sting;
	public $id_name;
	
	public $registry_pagename;
	public $registry_extra_sting;
	public $registry_id_name;
	
	
	
	function __construct(
		$block_name, //0
		$base_sql, //1
		$fields, //2
		$fields_names, //3
		$view_decorator, //4
		$pagename, //5
		$extra_sting, //6
		$id_name, //7
		$registry_pagename, //8
		$registry_extra_sting, //9
		$registry_id_name //10
		
		 ){
		$this->block_name=$block_name;
		$this->base_sql=$base_sql;
		$this->fields=$fields;
		$this->fields_names=$fields_names;
		$this->view_decorator=$view_decorator;
		
		$this->pagename=$pagename;
		$this->extra_sting=$extra_sting;
		$this->id_name=$id_name;
		
		$this->registry_pagename=$registry_pagename;
		$this->registry_extra_sting=$registry_extra_sting;
		$this->registry_id_name=$registry_id_name;
		
	}
	
	
	public function ConstructUrl($id){
		return $this->pagename.'?'.$this->id_name.'='.$id.'&'.$this->extra_sting;
	}
	
	public function ConstructRegistryUrl($id){
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$id.'&'.$this->registry_extra_sting;
	}
	
	public function ConstructName($id){
		
	}
	public function ConstructDate($id){
		
	}
	
	public function ConstructMatchedFields($id, $str){
		$sql='select '.implode(', ', $this->fields).' '.$this->base_sql.' where p.id="'.$id.'" ';
		$flt=array();
			foreach($this->fields as $kk=>$vv){
				$flt[]=' '.$vv.' LIKE "%'.$str.'%" ';	
			}
			$sql.= ' and ';
			
			$sql.='('.implode(' OR ',$flt).')';
		
		/*if($id==12468){
			//echo $sql;
		}*/
		
		//
		$set=new Mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$matched=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs,  MYSQLI_NUM);
			
			
			 
			
			foreach($this->fields as $k=>$v){
				/*$v=eregi_replace('^([a-z]+)\.', '', $v);
				if($id==12468){
					echo $v; echo $f[$v];
				}
						
				if(stripos($f[$v], $str)!==false){
					if(!in_array($this->fields_names[$k], $matched)) $matched[]=$this->fields_names[$k];
					if($id==12468) echo "<br> $k $v $str vs $f[$v] <br> ";
				}*/
				
				if(stripos($f[$k], $str)!==false){
					if(!in_array($this->fields_names[$k], $matched)) $matched[]=$this->fields_names[$k];
					//if($id==12468) echo "<br> $k $v $str vs $f[$v] <br> ";
				}
			}
		 
		}
		//echo implode(', ', $matched);
		return implode(', ', $matched);
	}
		
}



class Search_Supplier extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SupplierItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); // $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Контрагент '.$kp['full_name']; //.', контрагент '.$kp['supplier_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		/*$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);*/
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SupplierItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


 

class Search_KP extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new KpItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$_sp=new SupplierItem;
		$sp=$_sp->GetItemById($kp['supplier_id']);
		
		return 'Коммерческое предложение '.$kp['code'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new KpItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


class Search_PlPos extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PlPosItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); //$stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Позиция '.$kp['code'].'  '.$kp['name'];
	}
	
	public function ConstructDate($id){
		/*$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);*/
	}
	
	public function ConstructRegistryUrl($id){
		$_kp=new PlPosItem;  
		
		$f=$_kp->getitembyid($id); $_gi=new PosGroupItem;
		
		$group_id=0;
		
		$gi=$_gi->GetItemById($f['group_id']);
				if($gi['parent_group_id']>0){
					$gi2=$_gi->GetItemById($gi['parent_group_id']);	
					if($gi2['parent_group_id']>0){
						$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
						$group_id=$gi3['id'];
						$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
					}else{
						$group_id=$gi2['id'];
						$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
					}
				}else $group_id=$gi['id'];
		
		
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$id.'&'.$this->registry_extra_sting.'&group_id_1='.$group_id.'&producer_id_1='.$f['producer_id'].'&doShow_1=1';
	}
	

}

class Search_Fact extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PlanFactFactItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Договор № '.$kp['id'].' '.$kp['contract_no'].', контрагент '.$kp['supplier_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	

}

//тендер
class Search_Tender extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new Tender_Item; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$_ts=new Tender_SupplierItem; $ts=$_ts->GetItemByFields(array('sched_id'=>$id));
		
		$_eq=new Tender_EqTypeItem; $_kind=new TenderKindItem;
		$eq=$_eq->GetItemById($kp['eq_type_id']);
		$kind=$_kind->GetItemById($kp['kind_id']);
		 
		$_sp=new SupplierItem;
		$sp=$_sp->GetItemById($ts['supplier_id']);
		
		return 'Тендер '.$kp['code'].', название: "'.$kp['topic'].'", тип оборудования: '.$eq['name'].', вид: '.$kind['name'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new Tender_Item;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new Tender_Item; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

//лид
class Search_Lead extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new Lead_Item; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$_ts=new Lead_SupplierItem; $ts=$_ts->GetItemByFields(array('sched_id'=>$id));
		
		$_eq=new Lead_EqTypeItem; $_kind=new LeadKindItem;
		$eq=$_eq->GetItemById($kp['eq_type_id']);
		$kind=$_kind->GetItemById($kp['kind_id']);
		 
		$_sp=new SupplierItem;
		$sp=$_sp->GetItemById($ts['supplier_id']);
		
		return 'Лид '.$kp['code'].', название: "'.$kp['topic'].'", тип оборудования: '.$eq['name'].', вид: '.$kind['name'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new Lead_Item;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new Lead_Item; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}



//файл справ. информ
class Search_SpravFile extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SpItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new SpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SpItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}



//файл ф и д
class Search_File extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new FilePoItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new FilePoItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new FilePoItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}

//файл письма
class Search_FileL extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new FileLetItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new FileLetItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new FileLetItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}

//файл спец
class Search_Sps extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SpSItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new SpSItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SpSItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}


//З
class Search_Petition extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PetitionItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		//$_ts=new Lead_SupplierItem; $ts=$_ts->GetItemByFields(array('sched_id'=>$id));
		
		//$_eq=new Lead_EqTypeItem; $_kind=new LeadKindItem;
		//$eq=$_eq->GetItemById($kp['eq_type_id']);
		$_kind=new PetitionKindItem;
		
		$kind=$_kind->GetItemById($kp['kind_id']);
		 
		//$_sp=new SupplierItem;
		//$sp=$_sp->GetItemById($ts['supplier_id']);
		
		return 'Заявление '.$kp['code'].', вид: '.$kind['name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new PetitionItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new PetitionItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


//СЗ
class Search_Memo extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new MemoItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		//$_ts=new Lead_SupplierItem; $ts=$_ts->GetItemByFields(array('sched_id'=>$id));
		
	//	$_kind=new PetitionKindItem;
		
		//$kind=$_kind->GetItemById($kp['kind_id']);
		 
		//$_sp=new SupplierItem;
		//$sp=$_sp->GetItemById($ts['supplier_id']);
		
		return 'Служебная записка '.$kp['code'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new MemoItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new MemoItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

//Исх
class Search_DocOut extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new DocOut_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new DocOut_Resolver($kp['kind_id']);
		
		//echo $res->instance->ConstructName($id);;
	 
		
		return $res->instance->ConstructName($id);
	}
	
	public function ConstructDate($id){
		$_kp=new DocOut_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
	//	$res=new DocOut_Resolver($kp['kind_id']);
		 
		return date('d.m.Y',$kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
			$_kp=new DocOut_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


//Вх
class Search_DocIn extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new DocIn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new DocIn_Resolver($kp['kind_id']);
		
	 
		
		return $res->instance->ConstructFullName($id);
	}
	
	public function ConstructDate($id){
		$_kp=new DocIn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		return date('d.m.Y',$kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
			$_kp=new DocIn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

//вн
class Search_DocVn extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new DocVn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new DocVn_Resolver($kp['kind_id']);
		
	 
		
		return $res->instance->ConstructFullName($id);
	}
	
	public function ConstructDate($id){
		$_kp=new DocVn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new DocVn_Resolver($kp['kind_id']);
		
		return date('d.m.Y',$kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
			$_kp=new DocVn_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

//активность планировщика - любая
class Search_Sched extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new Sched_Resolver($kp['kind_id']);
		
	 
		
		return $res->instance->ConstructFullName($id);
	}
	
	public function ConstructDate($id){
		$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new Sched_Resolver($kp['kind_id']);
		
	 
		
		if($kp['kind_id']==5) return date('d.m.Y', $kp['pdate']);
		else return  DateFromYMD($kp['pdate_beg']);
	}
	
	
	public function ConstructRegistryUrl($id){
			$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


?>
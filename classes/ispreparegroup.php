<?
require_once('abstractgroup.php');
require_once('iswf_group.php');
require_once('db_decorator.php');

// абстрактная группа
class IsPrepareGroup extends AbstractGroup {
	
	//установка всех имен
	protected function init(){
		$this->tablename='table';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	public function GetItemsByStorSec($storage_id, $sector_id, $org_id,$is_id=0, DBDecorator $dec=NULL, $_extended_limited_sector=NULL){
		$alls=array();
		
		
		$_iwg=new IswfGroup;
		
		//получим номенклатуру на складе
		if($dec!==NULL){
		  $db_flt=$dec->GenFltSql(' and ');
		  if(strlen($db_flt)>0){
			  $db_flt=' and '.$db_flt;	
		  }
		}
		
		
		//добавить фильтры по с/с, расширенному объекту
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		
		
		//var_dump($_extended_limited_sector);
		
		
		
			
		$sql2='select sum(ap.quantity) as quantity, 
			ap.position_id, ap.pl_position_id, ap.name as position_name, ap.dimension as dim_name, dim.id as dimension_id 
				from acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				 inner join bill as b on b.id=a.bill_id
				 inner join catalog_position as cat on cat.id=ap.position_id
				 inner join pl_position as pl on cat.id=pl.position_id
				 left join catalog_dimension as dim on ap.dimension=dim.name 
				 where 
				 a.is_confirmed=1 
				 and a.org_id="'.$org_id.'"
				
				 '.$db_flt.'
				 group by ap.position_id, ap.pl_position_id 
				 order by position_name asc, ap.pl_position_id asc
				 ';
				 
				 
				 
		$set=new mysqlSet($sql2);		
		//echo $sql2;
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$f['max_quantity']=(float)$f['quantity'];
			//получить суммарное количество по позиции
			
			
			//получим всего списано по данной позиции
			$sql2='select sum(quantity) from interstore_position 
			where position_id="'.$f['position_id'].'" 
			and pl_position_id="'.$f['pl_position_id'].'" 
			and interstore_id in(select id from interstore where is_confirmed=1 and 
			 org_id="'.$org_id.'" and id<>"'.$is_id.'")';
			
			$set1=new mysqlSet($sql2);
			
			//echo 'select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$storage_id.'" and sender_sector_id="'.$sector_id.'" and org_id="'.$org_id.'" and id<>"'.$is_id.'")';
			
			$rs1=$set1->GetResult();
			
			$g=mysqli_fetch_array($rs1);
			
			//вычтем из склада
			$f['max_quantity']-=(float)$g[0];
			
			
			if($f['max_quantity']<0) $f['max_quantity']=0;
			
			
			//нужны поля fact_quantity и max_fact_quantity
			//fact_quantity - это сумма количеств данной позиции по подчиненным распоряжениям
			
			
			$f['fact_quantity']=$_iwg->FactKol($is_id,$f['position_id']);
			
			//echo $f['quantity'].' - '.$g[0].'<br>';
			
			
			//поле min_fact_quantity - это сколько позиции в распоряжениях оп списанию
			$f['min_fact_quantity']=$f['fact_quantity'];
			
			
			if($f['max_quantity']<=0) continue;
			
			$alls[]=$f;
		}
		
		return $alls;	
	}
	
}
?>
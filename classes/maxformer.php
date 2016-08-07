<?
require_once('MysqlSet.php');
require_once('NonSet.php');


class MaxFormer{
	
	//число позиций в заявке - кв
	
	public function MaxInKomplekt($komplekt_id, $position_id){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=$f['quantity_confirmed'];
		}
		
		return round($res,3);
		
	}
	
	
	public function MaxInKomplektInit($komplekt_id, $position_id){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=$f['quantity_initial'];
		}
		
		return round($res,3);
		
	}
	
	
	
	//число позиций в счете
	//public function MaxInBill($bill_id, $position_id,$storage_id=NULL, $sector_id=NULL,$komplekt_ved_id=NULL){
	public function MaxInBill($bill_id, $position_id,  $pl_position_id, $pl_discount_id, $pl_discount_value, $pl_rub_or_percent, $out_bill_id=NULL, $kp_id=NULL){	
		$res=0;
		
		$flt='';
		
		if($out_bill_id!==NULL) $flt.=' and out_bill_id="'.$out_bill_id.'" ';
		if($kp_id!==NULL) $flt.=' and kp_id="'.$kp_id.'" ';
		
		
		//найти число позиций по счету 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" 
		and position_id="'.$position_id.'" 
		and pl_position_id="'.$pl_position_id.'" 
		and pl_discount_id="'.$pl_discount_id.'" 
		and pl_discount_value="'.$pl_discount_value.'" 
		and pl_discount_rub_or_percent="'.$pl_rub_or_percent.'" 
		'.$flt;
		
		//echo $sql;
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	//число позиций в распоряжениях на отгрузку
	public function MaxInShI($bill_id, $position_id, $pl_position_id, $pl_discount_id, $pl_discount_value, $pl_rub_or_percent, $sh_i_id=NULL, $out_bill_id=NULL, $kp_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt='';
		$flt2='';
		if($sh_i_id!==NULL) $flt.=' and id="'.$sh_i_id.'" ';
		if($out_bill_id!==NULL) $flt.=' and out_bill_id="'.$out_bill_id.'" ';
		if($kp_id!==NULL) $flt.=' and kp_id="'.$kp_id.'" ';
		//else $flt='';
	
		
		$set=new mysqlSet('select sum(quantity) from sh_i_position where sh_i_id in(select id from sh_i where bill_id="'.$bill_id.'" '.$flt.' and is_confirmed=1) 
		and position_id="'.$position_id.'" 
		and pl_position_id="'.$pl_position_id.'" 
		and pl_discount_id="'.$pl_discount_id.'" 
		and pl_discount_value="'.$pl_discount_value.'" 
		and pl_discount_rub_or_percent="'.$pl_rub_or_percent.'" 
		
		
		'.$flt2);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	//число позиций в поступления
	public function MaxInAcc($bill_id, $position_id,  $pl_position_id, $pl_discount_id, $pl_discount_value, $pl_rub_or_percent,  $except_id=0, $sh_i_id=NULL, $out_bill_id=NULL, $kp_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		if($sh_i_id!==NULL) $flt.=' and sh_i_id="'.$sh_i_id.'" ';
		if($out_bill_id!==NULL) $flt.=' and out_bill_id="'.$out_bill_id.'" ';
		
		if($kp_id!==NULL) $flt.=' and kp_id="'.$kp_id.'" ';
		
		
		
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance 
		where 
		bill_id="'.$bill_id.'" 
		and position_id="'.$position_id.'" 
		and pl_position_id="'.$pl_position_id.'" 
		and pl_discount_id="'.$pl_discount_id.'" 
		and pl_discount_value="'.$pl_discount_value.'" 
		and pl_discount_rub_or_percent="'.$pl_rub_or_percent.'" 
		and id<>"'.$except_id.'" '.$flt.'  and is_confirmed=1)  '.$flt2;
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	//число позиций в доверенности
	public function MaxInTrust($bill_id, $position_id){
		$res=0;
		
		//найти число позиций по счету 
		$set=new mysqlSet('select sum(quantity) from trust_position where trust_id in(select id from trust where bill_id="'.$bill_id.'"  and is_confirmed=1) and position_id="'.$position_id.'"');
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	//макс доступное количество позиции для формирования доверенности
	public function MaxForTrust($bill_id, $position_id, $pl_position_id, $except_trust_id=NULL){
		$res=0;
		
		//найти 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" and pl_position_id="'.$pl_position_id.'" ';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql);
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			
			$res=(float)$f[0];
			
			//найдем сумму по дов-тям счета
			$flt='';
			if($except_trust_id!==NULL) $flt.=' and trust_id<>"'.$except_trust_id.'"';
			
			$sql='select sum(quantity) from trust_position 
			where 
				position_id="'.$position_id.'" 
				and pl_position_id="'.$pl_position_id.'" 
				and bill_id="'.$bill_id.'" 
				and trust_id in(
						select t.id from trust as t inner join trust_position as tp on t.id=tp.trust_id
						where 
							tp.bill_id="'.$bill_id.'" 
							and tp.position_id="'.$position_id.'" 
							and tp.pl_position_id="'.$pl_position_id.'" 
							and t.is_confirmed=1
				) '.$flt;
			
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			
			
			
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];
			
			
			
			if($res<0) $res=0;
		}
		
		
		return round($res,3);
	}
	
	
	
	//макс доступное количество позиции для формирования счета
	public function MaxForBill($komplekt_id, $position_id, $except_bill_id=NULL, $except_is_id=NULL){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=(float)$f['quantity_confirmed'];
			
			//echo $res;
			
			//найдем сумму по счетам к.в.
			$flt='';
			if($except_bill_id!==NULL) $flt.=' and b.id<>"'.$except_bill_id.'"';
			
			$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
			 where bp.position_id="'.$position_id.'" and bp.komplekt_ved_id="'.$komplekt_id.'" '.$flt.'  and b.is_confirmed_shipping=1 
			 group by bp.position_id
			 ';
			 
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			 
			
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			//echo $sql.(float)$f[0]; echo '<p>';
			
			$res-=(float)$f[0];
			
			
			$flt='';
			if($except_is_id!==NULL) $flt.=' and id<>"'.$except_is_id.'"';
			
			//отнять сумму по связанному не вывез. межскладу
			$sql='select sum(quantity) from interstore_to_komplekt where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" and interstore_id in(select id from interstore where status_id<>3 and is_or_writeoff=0 and is_confirmed=1 and is_confirmed_wf=0 '.$flt.')';
		
			//echo $sql;
			
			$set=new mysqlSet($sql);
			
			$rs=$set->getResult();
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];
			
			
			if($res<0) $res=0;
			
		}
		
		//echo $res; echo '<p>';
		return round($res,3);
	}
	
	
	
	
	
	//макс доступное количество позиции для распоряжения
	public function MaxForShI($bill_id, $position_id, $except_sh_i_id=NULL, $pl_position_id=NULL, $pl_discount_id=NULL, $pl_discount_value=NULL, $pl_discount_rub_or_percent=NULL, $out_bill_id=NULL){
		$res=0;
		
		$flt='';
		if($pl_position_id!==NULL) $flt.=' and pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt.=' and pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt.=' and pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt.=' and pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt.=' and out_bill_id="'.$out_bill_id.'"';
		
		
		//найти число позиций по счету 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt;
		
		//echo $sql;
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
			
			//найдем сумму по распоряжениям счета
			$flt=''; $flt2='';
			if($except_sh_i_id!==NULL) $flt.=' and id<>"'.$except_sh_i_id.'"';
			
			if($pl_position_id!==NULL) $flt2.=' and pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt2.=' and pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt2.=' and pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt2.=' and pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt2.=' and out_bill_id="'.$out_bill_id.'"';
		
			
			$sql='select sum(quantity) from sh_i_position where position_id="'.$position_id.'" and sh_i_id in(select id from sh_i where bill_id="'.$bill_id.'" '.$flt.' and is_confirmed=1) '.$flt2;
			$set=new mysqlSet($sql);
			
						
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];
			
			
			if($res<0) $res=0;
			
		}
		
		
		return round($res,3);
	}
	
	
	
	//макс доступное количество позиции для поступления
	public function MaxForAcc($sh_i_id, $position_id, $except_acc_id=NULL, $pl_position_id=NULL, $pl_discount_id=NULL, $pl_discount_value=NULL, $pl_discount_rub_or_percent=NULL, $out_bill_id=NULL, $kp_id=NULL){
		$res=0;
		$flt_k='';
		if($pl_position_id!==NULL) $flt_k.=' and pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt_k.=' and pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt_k.=' and pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt_k.=' and pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt_k.=' and out_bill_id="'.$out_bill_id.'"';
		if($kp_id!==NULL) $flt_k.=' and kp_id="'.$kp_id.'"';
		
		
		//найти число позиций по распоряжению 
		$sql='select sum(quantity) from sh_i_position where sh_i_id="'.$sh_i_id.'" and position_id="'.$position_id.'" '.$flt_k;
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=$f[0];
			
			//найдем сумму по поступлениям распоряжения
			$flt='';
			if($except_acc_id!==NULL) $flt.=' and id<>"'.$except_acc_id.'"';
			
			$sql='select sum(quantity) from acceptance_position where position_id="'.$position_id.'" '.$flt_k.' and acceptance_id in(select id from acceptance where sh_i_id="'.$sh_i_id.'" '.$flt.' and is_confirmed=1)';
			
			
			$set=new mysqlSet($sql);
			
						
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=$f[0];
			
			
			if($res<0) $res=0;
			
		}
		
		
		return round($res,3);
	}
	
	//подсчет максимального кол-ва по позициям распоряжения на межсклад
	public function MaxForAccIs($is_id, $position_id, $except_p_id=NULL,$except_komplekt_ved_id=NULL){
		$res=0;
		
		$flt_k='';
		$flt_p='';
		
		if($except_komplekt_ved_id!==NULL) $flt_k.=' and komplekt_ved_id<>"'.$except_komplekt_ved_id.'"';
		if($except_p_id!==NULL) $flt_p.=' and id<>"'.$except_p_id.'"';
		
		
		//сколько всего к списанию
		$sql='select sum(quantity) from interstore_wf_position 
			where position_id="'.$position_id.'" and iwf_id in(select id from interstore_wf where interstore_id="'.$is_id.'")';	
		
		$set1=new mysqlSet($sql);
		$rs=$set1->GetResult();
		
		$f=mysqli_fetch_array($rs);
		$res=(float)$f[0];
		
		//echo $res;
		
		//вычтем сумму по тем же позициям этого же поступления, но по другим заявкам.
		$sql='select sum(quantity) from  acceptance_position where position_id="'.$position_id.'" '.$flt_k.' '.$flt_p.' and acceptance_id in(select id from acceptance where interstore_id="'.$is_id.'")';		
		
		//echo " $sql<br />";
		
		$set1=new mysqlSet($sql);
		$rs=$set1->GetResult();
		
		$f=mysqli_fetch_array($rs);
		$res-=(float)$f[0];
		
		//echo " $res <br />";
		
		$res=round($res,3);
		
		return round($res,3);
	}
	
	
	
	
	//число в счетах
	public function InBills($komplekt_id, $position_id){
		$res=0;
		$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
		 where bp.position_id="'.$position_id.'"
		     and b.is_confirmed_shipping=1
			 and bp.komplekt_ved_id="'.$komplekt_id.'"';
			 
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		
		//сюда же - добавить число в связанном невывезенном межскладе
		$sql='select sum(quantity) from interstore_to_komplekt where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" and interstore_id in(select id from interstore where status_id<>3 and is_or_writeoff=0 and is_confirmed=1 and is_confirmed_wf=0)';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
		
		$res+=(float)$f[0];
		
		return round($res,3);	
	}
	
	//число в распоряжениях
	public function InSh($komplekt_id, $position_id){
		$res=0;
		
		//?? проверить потом!
		$set=new mysqlSet('select sum(sp.quantity) from sh_i_position as sp
		inner join sh_i as s on sp.sh_i_id=s.id
		inner join bill as b on b.id=s.bill_id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$komplekt_id.'" 
		and b.is_confirmed_shipping=1');
	
		
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		return round($res,3);	
	}
	
	//число в распоряжениях
	public function InAcc($komplekt_id, $position_id){
		$res=0;
		
		
		//?? проверить позже!
		
		/*$set=new mysqlSet('select sum(sp.quantity) from acceptance_position as sp
		inner join acceptance as s on sp.acceptance_id=s.id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 
		and s.bill_id in(select distinct b.id from bill as b 
						inner join bill_position as bp on b.id=bp.bill_id
						 where bp.komplekt_ved_id="'.$komplekt_id.'" and b.is_confirmed_shipping=1)');*/
						 
		$set=new mysqlSet('select sum(sp.quantity) from acceptance_position as sp
		inner join acceptance as s on sp.acceptance_id=s.id
		inner join bill as b on b.id=s.bill_id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$komplekt_id.'" 
		and b.is_confirmed_shipping=1');				 
		
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		return round($res,3);	
	}
	
	
	//число свободно
	public function InFree($komplekt_id, $position_id, $except_bill_id=NULL){
		$res=0;
		
		
		$res=$this->MaxForBill($komplekt_id, $position_id,$except_bill_id);
		
		
		return round($res,3);	
	}
	
}
?>
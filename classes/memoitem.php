<?
require_once('abstractitem.php');
require_once('memofileitem.php');
require_once('docstatusitem.php');

require_once('authuser.php');
require_once('actionlog.php');
require_once('memohistoryitem.php');
require_once('memofileitem.php');
require_once('sched.class.php');
require_once('user_s_item.php');

//служебная записка
class MemoItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
	 
		
		return 'Служебная записка '.$item['code'].', статус '.$stat['name'];
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		parent::Edit($id,$params);	
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	public function SendStatusMessage($order_id, $status_id){
		
		 
	}
	
	 
	
	
	//удалить
	public function Del($id){
		//удалить все файлы
		$fset=new MysqlSet('select * from memo_file where history_id in (select id from memo_history where memo_id="'.$id.'")');
		$fc=$fset->GetResultNumRows();
		$rfs=$fset->GetResult();
		
		$fi=new MemoFileItem;
		for($i=0; $i<$fc; $i++){
			$f=mysqli_fetch_array($rfs);
			//GetStoragePath()
			@unlink($fi->GetStoragePath().$f['filename']);
		}
		
		
		
		//удалить файлы из бд
		$query = 'delete from memo_file where history_id in (select id from memo_history where memo_id="'.$id.'")';
		$it=new nonSet($query);
		
		//удалить историю
		$query = 'delete from memo_history where memo_id="'.$id.'";';
		$it=new nonSet($query);
		
		parent::Del($id);
	}	
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if(($item['status_id']!=1)&&($item['status_id']!=18)){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_messages=new Memo_Messages;
		
		$setted_status_id=$item['status_id'];
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//смена статуса на 41
				$setted_status_id=41;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				//если пользователь явл-ся рук-лем отдела - то сразу утвердить в роли рук-ля отдела
				$_us=new Sched_UsersSGroup; $_ui=new UserSItem;
				$ui=$_ui->getitembyid($item['user_id']);
				$ruk=$_us->GetRuk($ui);
				if(($ruk!==false)&&($ruk['id']==$_result['id'])){
					$this->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$_result['id'],'ruk_pdate'=>time() ), true, $_result);
				}
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				$setted_status_id=18;
				
				//также снять все прочие утверждения!
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
			 
			
		 }elseif(isset($new_params['is_ruk'])&&isset($old_params['is_ruk'])){
			
			if(($new_params['is_ruk']==1)&&($old_params['is_ruk']!=1)){
				//смена статуса на 43
				$setted_status_id=43;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				$_messages->SendMessageToUser($id, 0, 1);
				
			}elseif(($new_params['is_ruk']==0)&&($old_params['is_ruk']!=0)){
				$setted_status_id=41;
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_dir'=>0,'user_dir_id'=>0, 'dir_pdate'=>time() ));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
			 
			}elseif(($new_params['is_ruk']==2)&&($old_params['is_ruk']!=2)){
				//смена статуса на 52
				$setted_status_id=52;
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_dir'=>0,'user_dir_id'=>0, 'dir_pdate'=>time() ));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				$_messages->SendMessageToUser($id, 0, 2);
				
			}
		 	  
			
		 
			
		 }elseif(isset($new_params['is_dir'])&&isset($old_params['is_dir'])){
			
			if(($new_params['is_dir']==1)&&($old_params['is_dir']!=1)){
				//смена статуса на 2
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				//отправим сообщение об утверждении
				//$_messages->SendMessageOK($id);
				
				$_messages->SendMessageToUser($id, 1, 1);
				
			}elseif(($new_params['is_dir']==0)&&($old_params['is_dir']!=0)){
				$setted_status_id=43;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
			}elseif(($new_params['is_dir']==2)&&($old_params['is_dir']!=2)){
				//смена статуса на 1
				$setted_status_id=1;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса служебной записки',NULL,731,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				$_messages->SendMessageToUser($id, 1, 2);
			
			}
			
		}
		
		//echo $setted_status_id.'<br>';
		
		  
		 
		//die();
	}
	
	
	
	//запрос о возм утв цен
	public function DocCanConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у служебной записки утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			 
			
			 
			
			/*if(($item['kind_id']==7)){
				if(strlen(trim($item['exh_name']))==0){
					$can=$can&&false;
					$reasons[]='не указано название выставки';
				}
			}*/
			
		 	if($item['manager_id']==0){
				$can=$can&&false;
					$reasons[]='не указан сотрудник';
			}
			 
			 
			 
			 $reason.=implode(', ',$reasons);
			
			
		}
		
		return $can;
	}
	
	 //Запрос о возм снятия утв цен
	public function DocCanUnconfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у служебной записки не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //контроль закрытого периода 
		   /* $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата счета контрагента '.$rss23;	
		  }
		   $reason.=implode(', ',$reasons);
		*/
		  	
		  
		}
		
		return $can;
	}
	
	
	
	
	//запрос о возможности  утв рук и возвращение причины, почему нельзя 
	public function DocCanConfirmRuk($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_ruk']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='служебная записка проверена руководителем отдела';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		   /* $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='дата счета контрагента '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 */
			
			/*//проверка по числу позиций 
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
	
	
	//запрос о возможности снятия утв рук и возвращение причины, почему нельзя 
	public function DocCanUnconfirmRuk($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_ruk']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='служебная записка не проверена руководителем отдела';
			$reason.=implode(', ',$reasons);
		}else{
			
			if($item['is_dir']!=0){
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='служебная записка проверена генеральным директором';
				$reason.=implode(', ',$reasons);	
			}
		
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//запрос о возможности  утв рук и возвращение причины, почему нельзя 
	public function DocCanConfirmDir($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_dir']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='служебная записка проверена генеральным директором';
			$reason.=implode(', ',$reasons);
		}else{
			
			
			if($item['is_ruk']==0){
			
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='служебная записка не проверена руководителем отдела';
				$reason.=implode(', ',$reasons);
			}
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв рук и возвращение причины, почему нельзя 
	public function DocCanUnconfirmDir($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_dir']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='служебная записка не проверена генеральным директором';
			$reason.=implode(', ',$reasons);
		}else{
		 
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	//копирование файлов и папок  старого в новый документ
	public function CopyFiles($old_id, $new_id, $user_id=0){
		 $sql='select * from memo_file where bill_id="'.$old_id.'" ';
	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_fi=new MemoFileItem(1);
	
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			$_fi->Add(array(
				'folder_id'=>0,
				'bill_id'=>$new_id,
				'user_id'=>$user_id,
				'filename'=>SecStr($f['filename']),
				'orig_name'=>SecStr($f['orig_name']),
				'pdate'=>time(),
				'txt'=>SecStr($f['txt']),
				'text_contents'=>SecStr($f['text_contents'])
				
			));	
		}
		
	}	
	
}




/**************************************************************************************************/
//класс сообщений
class Memo_Messages{
	
	
	
	//отправка сообщения сотруднику при утв/не утв: рук от или ген дир
	public function SendMessageToUser($id, $ruk_or_dir=0, $value=1){
		$_dem=new MemoItem;
		$doc=$_dem->GetItemById($id);
		 
		
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		$topic="Ваше служебная записка ";
		
		if($value==1) $topic.=" утверждена ";
		else $topic.=" не утверждена ";
		
		if($ruk_or_dir==0) $topic.= " руководителем отдела ";
		else $topic.=" генеральным директором ";
		
		$txt='<div>';
		$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='Уважаемый(ая) '.$signer['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
	 	$txt.='<strong>';
		
		$txt.='Следующая Ваша служебная записка была ';
		
		if($value==1) $txt.=" утверждена ";
		else $txt.=" не утверждена ";
		
		if($ruk_or_dir==0) $txt.= " руководителем отдела ";
		else $txt.=" генеральным директором ";
		
		
		$txt.=': ';
		
		
		$txt.='</strong></div><ul>';
		
		$txt.='<li><a href="memo_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
  
		$txt.='</ul><div></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		/*var_dump($doc);
		
		echo $txt; die();
		*/
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
	
	
	
  
	
}
?>
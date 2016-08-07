<?
if($editing_user['is_confirmed_version']==0){
		  
		  
		  if($editing_user['vid']==$_res->instance->GetActiveVersionId($id)){
				  
			  if($editing_user['is_confirmed']==1){
				 
				  
				  // 
				  if(($au->user_rights->CheckAccess('w',1043)) ){
					  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
						  
						  
						  $_res->instance->Edit($id, NULL, array(), array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
						  
						  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,1043, NULL, NULL,$id);	
						  
					  }
				  } 
				  
			  }else{
				  //есть права
				  if($au->user_rights->CheckAccess('w',1042) ){
					  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
						  
						  $_res->instance->Edit($id,  NULL, array(), array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
						  
						  $log->PutEntry($result['id'],'утвердил заполнение',NULL,1042, NULL, NULL,$id);	
						  
						   
						  //die();
					  }
				  } 
			  }
		  }
		}
		
		
		
		//утверждение фин службой
		if($editing_user['is_confirmed']==1){
		if($editing_user['vid']==$_res->instance->GetActiveVersionId($id))	{
			
		  if($editing_user['is_confirmed_version']==1){
			  
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 	if(!isset($_POST['is_confirmed_version'])&&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
				
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1051)/*||($result['main_department_id']==5)*/);
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id, NULL, array(), array('is_confirmed_version'=>0, 'user_confirm_version_id'=>$result['id'], 'confirm_version_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение фин. службой',NULL,1051, NULL, NULL,$id);	
				}
			}
			 // }
			  
		  }else{
			   
			  //есть права
			  
			   if(isset($_POST['is_confirmed_version'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
				    $can_confirm_shipping=($au->user_rights->CheckAccess('w',1050)/*||($result['main_department_id']==5)*/);
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id, NULL, array(), array('is_confirmed_version'=>1, 'user_confirm_version_id'=>$result['id'], 'confirm_version_pdate'=>time(), 'confirm_version_notes'=>SecStr($_POST['confirm_version_notes'])),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил от имени фин. службы',NULL,1050, NULL, NULL,$id);	
					}
						  
				}
			    
		  }
		}
		}
		
		
		//утверждение ген. дир
		if($editing_user['is_confirmed']==1){
		if($editing_user['vid']==$_res->instance->GetActiveVersionId($id))	{
			
		  if($editing_user['is_confirmed_version1']==1){
			  
			  
			  
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 	if(!isset($_POST['is_confirmed_version1'])&&($_res->instance->DocCanUnconfirmShip1($id, $rss32))){
				
				$can_confirm_shipping=($au->user_rights->CheckAccess('w',1056)||($result['position_id']==8));
				if($can_confirm_shipping){	  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id, NULL, array(), array('is_confirmed_version1'=>0, 'user_confirm_version_id1'=>$result['id'], 'confirm_version_pdate1'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение генерального директора',NULL,1056, NULL, NULL,$id);	
				}
			}
			 
			  
		  }else{
			   
			  //есть права
			  
			  
			   if(isset($_POST['is_confirmed_version1'])&&($_res->instance->DocCanConfirmShip1($id, $rss32))){
				    $can_confirm_shipping=($au->user_rights->CheckAccess('w',1055)||($result['position_id']==8));
					 
					if($can_confirm_shipping){	
					
					 
					 $_res->instance->Edit($id, NULL, array(), array('is_confirmed_version1'=>1, 'user_confirm_version_id1'=>$result['id'], 'confirm_version_pdate1'=>time(), 'confirm_version_notes1'=>SecStr($_POST['confirm_version_notes1'])),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил от имени генерального директора',NULL,1055, NULL, NULL,$id);	
					  
					 
					}
						  
				}
			    
		  }
		}
		}
		
?>
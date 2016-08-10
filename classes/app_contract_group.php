<?

require_once('abstractgroup.php');
require_once('app_contract_view.class.php');

// абстрактная группа
class AppContractGroup extends AbstractGroup {
    //установка всех имен
    protected function init(){
        $this->tablename = 'app_contract';
        $this->pagename = 'app_contract.php';		
        $this->subkeyname = 'user_id';	
        $this->vis_name = 'is_shown';
        $this->_view = NULL;
    }

    // Показать договора
    public function ShowPos($template, DBDecorator $dec, $from=0, $to_page=ITEMS_PER_PAGE, $user_id=0, $can_create_contract, $can_edit_contract, $can_delete_contract, $can_view_all=false){
        $sm = new SmartyAdm;

        $sql = 'select p.*, 
                    u1.login as user_login, u1.name_s as user_name,
                    u2.login as user_confirm_login, u2.name_s as user_confirm_name,
                    sup.full_name as supplier_name,
                    spo.name as opf_name,
                    supc.name as supplier_contact_name, supc.position as supplier_contact_position,
                    st.name as last_status,
                    (select count(id) from app_contract_history where app_contract_id=p.id) as history_message_count
                from ' . $this->tablename . ' as p
                    left join user as u1 on p.' . $this->subkeyname . ' = u1.id	
                    left join user as u2 on p.user_confirm_id = u2.id
                    left join supplier as sup on p.supplier_id = sup.id
                    left join opf as spo on spo.id = sup.opf_id 
                    left join supplier_contact as supc on p.supplier_contact_id = supc.id
                    left join app_contract_status as st on p.status_id = st.id
                ';

        $sql_count = 'select count(*) 
                from ' . $this->tablename . ' as p
                    left join user as u1 on p.' . $this->subkeyname . ' = u1.id	
                    left join user as u2 on p.user_confirm_id = u2.id
                    left join supplier as sup on p.supplier_id = sup.id
                    left join opf as spo on spo.id = sup.opf_id 
                    left join supplier_contact as supc on p.supplier_contact_id = supc.id
                    left join app_contract_status as st on p.status_id = st.id';

        $this->_view = new AppContract_ViewGroup;

        if (!$can_view_all) {
            $sql .= ' where p.' . $this->subkeyname . ' = "'.$user_id.'" ';
            $sql_count .= ' where p.' . $this->subkeyname . ' = "'.$user_id.'" ';
        }



        $db_flt=$dec->GenFltSql(' and ');
        if(strlen($db_flt)>0){
            if($can_view_all && !$user_id) {
                $sql.=' where '.$db_flt;
                $sql_count.=' where '.$db_flt;
            }else{
                $sql.=' and '.$db_flt;
                $sql_count.=' and '.$db_flt;	
            }
        }

        $ord_flt=$dec->GenFltOrd();
        if(strlen($ord_flt)>0){
                $sql.=' order by '.$ord_flt;
        }
        
        // Debug SQL
        //echo '<p>SQL = ' . $sql . '</p>';

        $set=new mysqlSet($sql,$to_page, $from,$sql_count);
        $rs=$set->GetResult();
        $rc=$set->GetResultNumRows();
        $total=$set->GetResultNumRowsUnf();


        //page
        $navig = new PageNavigator($this->pagename, $total, $to_page, $from, 10, '&'.$dec->GenFltUri());
        $navig->SetFirstParamName('from');
        $navig->setDivWrapperName('alblinks');
        $navig->setPageDisplayDivName('alblinks1');			
        $pages= $navig->GetNavigator();

        $alls=array();
        for($i=0; $i<$rc; $i++){
                $f=mysqli_fetch_array($rs);
                foreach($f as $k=>$v) $f[$k]=stripslashes($v);
                $f['pdate']=date("d.m.Y H:i:s",$f['pdate']);

                //print_r($f);	
                $alls[]=$f;
        }

        // Заполним шаблон полями
        $current_status = '';

        $fields = $dec->GetUris();
        foreach($fields as $k => $v){
            if($v->GetName() == 'status_id'){
                $current_status = $v->GetValue();
            }

            $sm->assign($v->GetName(), $v->GetValue());	
        }
        
        // Статусы
        $as=new mysqlSet('select * from app_contract_status order by id asc');
        $rs=$as->GetResult();
        $rc=$as->GetResultNumRows();
        $ids=array(); $names=array(); 
        $ids[]=''; $names[]='';
        for($i=0; $i<$rc; $i++){
                $f=mysqli_fetch_array($rs);
                foreach($f as $k=>$v) $f[$k]=stripslashes($v);

                $ids[]=$f['id'];
                $names[]=$f['name'];
        }
        $sm->assign('status_ids',$ids);
        $sm->assign('status_names',$names);
        $sm->assign('status_id',$current_status);

        $sm->assign('from',$from);
        $sm->assign('to_page',$to_page);
        $sm->assign('pages',$pages);
        $sm->assign('items',$alls);

        //ссылка для кнопок сортировки
        $link=$dec->GenFltUri();
        $link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
        $sm->assign('link',$link);
        
        //показ конфигурации
        $sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
        $sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
        
        $sm->assign('can_create_contract', $can_create_contract);
        $sm->assign('can_edit_contract', $can_edit_contract);
        $sm->assign('can_delete_contract', $can_delete_contract);

        $sss = $sm->fetch($template);
        return $sss;
        //return $sm->fetch($template);
    }
}
?>
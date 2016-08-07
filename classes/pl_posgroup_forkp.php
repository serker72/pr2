<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

require_once('pl_disgroup.php');
require_once('pl_dismaxvalgroup.php');
require_once('pl_posgroup.php');

//  группа прайс-лист для получения позиций в кп
class PlPosGroupForKp extends PlPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		$this->itemsname='pospos';
		
		
	}
	
	
	
	
	public function GainSql(&$sql, &$sql_count){
		$sql='select pl.id as pl_position_id, pl.id as pl_id, pl.position_id, pl.price as price, pl.discount_id as pl_discount_id, pl.discount_value as pl_discount_value, pl.discount_rub_or_percent as pl_discount_rub_or_percent, p.txt_for_kp, p.photo_for_kp,
					dis.name,
					p.dimension_id,
					p.name as position_name,
					d.name as dim_name,
					g.name as group_name
				from '.$this->tablename.' as pl
					inner join catalog_position as p on p.id=pl.'.$this->subkeyname.'
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_discount as dis on dis.id=pl.discount_id
					';
					
					
					
		$sql_count='select count(*)
				from '.$this->tablename.' as pl
					inner join catalog_position as p on p.id=pl.'.$this->subkeyname.'
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_discount as dis on dis.id=pl.discount_id
					';
	}
}
?>
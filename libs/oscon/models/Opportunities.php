<?php
namespace bc\models;

use tip\models\base\BaseModel;
use tip\models\Users;
use tip\models\Accounts;
use tip\core\Util;
use bc\traits\user\TalentTrait;

class Opportunities extends BaseModel
{
    protected $_companies = array();
    function __construct()
    {
    }

    public static function currentUserSavedOpportunities($db){
        $user = Users::current();
        $opps = array();
        if($user){
			$rankings = TalentTrait::tData2A($user,'rankings');
			$savedIds = array();
			foreach($rankings as $cId => $rank){
				$oppRankings = Util::nvlA2A($rank,'opportunities');
				foreach($oppRankings as $oppId => $oppRank){
					$interest = Util::nvlA($oppRank,'interest');
					if($interest >= 45){
						$savedIds[] = $oppId;
					}
				}
			}
            if($savedIds){
				$savedIds = static::toMongoId($savedIds);

                $opps = $db->collection('opportunities')->get(array('_id'=>array('$in'=>$savedIds)));
//                $opps = static::masterById( $app, $savedIds, false, true );
            }

        }
        return static::create($opps,array('set'=>true,'existing'=>true));
    }


    public function company($entity,$db = null){
        $hash = spl_object_hash($entity);
        $id = $this->tData($entity,'/company');
        if(!isset($this->_companies[$hash]))$this->_companies[$hash] = Util::isDocument($db) ? $db : ( $db ? Companies::create($db->collection('companies')->getById($id),array('existing'=>true)) : Companies::byId($id) );
        return $this->_companies[$hash];
    }

	public static function byIdRemote($db,$id,$required=true,$forceSet=false){
		$id = static::toMongoId(Util::toArray($id));

		$opps = $db->collection('opportunities')->get(array('_id'=>array('$in'=>$id)));
		if(!$forceSet && $opps && count($id) == 1){
			return static::create($opps[0],array('existing'=>true));
		}else if($opps){
			return static::create($opps,array('existing'=>true));
		}
		if($required){
			throw new \lithium\core\ConfigException("No Opportunity found with id: ".print_r($id,true));
		}
		return null;
	}
	public static function byCorporateId($id,$db=null,$findOptions=array()){
		if(is_bool($findOptions))$findOptions = array('active'=>$findOptions);
		$conditions = \bc\traits\opportunity\CorporateTrait::conditions($findOptions+array('corporate'=>$id));
		if($db){
			$opps = $db->collection('opportunities')->get($conditions);
			return static::create($opps,array('existing'=>true));
		}else{
			return static::find('all',compact('conditions'));
		}
	}

	public function saveRemote($entity,$db){
		$this->__beforeSave($entity);
		if($this->isNew($entity)){
			$entity->_id = static::newId();
			$data = $entity->to('array');
			$data['_id'] = $entity->_id;
			$result = $db->collection('opportunities')->insert($data);
		}else{
			$data = $entity->to('array');
			unset($data['_id']);
			$update = array('_id'=>$this->id($entity,true));
			$response = $db->collection('opportunities')->update($update,$data);
		}

		$entity->sync();
		return $entity;
	}
}

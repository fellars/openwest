<?php
namespace bc\models;

use tip\models\base\BaseModel;
use tip\models\Users;
use tip\core\Util;
use bc\traits\user_match\CoreTrait;

class UserMatches extends BaseModel
{
    function __construct()
    {
    }

	public static function byUserId($userId, $db=null){
		if(Util::isDocument($userId))$userId = $userId->id();
		$conditions = CoreTrait::conditions(array('user'=>$userId));
		if($db){
			$matched = static::create($db->collection('user_matches')->get($conditions),array('set'=>true,'existing'=>true));
		}else{
			$matched = static::find('all',compact('conditions'));
		}
		return $matched;
	}
	public static function matchedUserCompaniesAndOpportunities($userId,$asMongoIds=true){
		$matched = static::byUserId($userId);
		$matchedResults = array(array(),array());
		foreach($matched as $match){
			$cId = CoreTrait::tData($match,'company');
			if($asMongoIds)$cId = static::toMongoId($cId);
			$matchedResults[0][] = $cId;

			$opps = CoreTrait::tData2A($match,'opportunities');
			$ids = array_keys($opps);
			if($asMongoIds)$ids = static::toMongoId($ids);
			$matchedResults[1] = array_merge($matchedResults[1],$ids);

		}
		return $matchedResults;
	}
	public static function matchedUserOpportunities($userId,$justIds=true,$asMongoIds=true){

		$matched = static::byUserId($userId);
		$matchedOpps = array();
		foreach($matched as $match){
			$opps = CoreTrait::tData2A($match,'opportunities');
			if($justIds){
				$ids = array_keys($opps);
				if($asMongoIds)$ids = static::toMongoId($ids);
				$matchedOpps = array_merge($matchedOpps,$ids);
			}else{
				$matchedOpps += $opps;
			}
		}
		return $matchedOpps;

	}

    public function company($entity,$db = null){

        $id = $this->tData($entity,'/company');
       $company = $this->entityVar($entity,'companies');
        if(!$company)$company = $this->entityVar($entity,'companies', $db ? ( Util::isDocument($db) ? $db : Companies::create($db->collection('companies')->getById($id),array('existing'=>true))) : Companies::byId($id));
		$company->entityVar('match',$this);
        return $company;
    }
    public function opportunities($entity,$db = null){
        $opps = $this->entityVar($entity,'opportunities');
		if(!$opps){
			$ids = static::toMongoId(array_keys($this->tData2A($entity,'/opportunities')));
			$conditions = array('_id'=>array('$in'=>$ids));
			$oppList = $db ? Opportunities::create($db->collection('opportunities')->find($conditions),array('set'=>true,'existing'=>true)) : Opportunities::find('all',compact('conditions'));
			$opps = $this->entityVar($entity,'opportunities',$oppList);
		}
		return $opps;
    }

	public function highestMatchingOpportunity($entity,$ignorePreviousResponse=false,$db=null){
		$ids = $this->tData2A($entity,'/opportunities');
		$maxScore = 0;
		$opp = null;
		foreach($ids as $id=>$oppMatch){
			if(!$ignorePreviousResponse && Util::nvlA($oppMatch,'interest') > 0)continue;
			$score = (int)Util::nvlA($oppMatch,'score');
			if($score> $maxScore){
				$maxScore = $score;
				$opp = $id;
			}
		}
		if($opp){
			$opp = $db ? Opportunities::byIdRemote($db,$opp,false) : Opportunities::byId($opp,false);
		}
		return $opp;

	}

    public static function currentUserMatches($db=null){
        $user = Users::current();
        return static::byUserId($user,$db);
    }

	public static function matchedOpportunityUsers($oppId,$db=null,$find=array(),$fields=array()){
		if(Util::isDocument($oppId))$oppId = $oppId->id();

		$conditions = \bc\traits\user_match\CoreTrait::conditions($find + array(
			"opportunities.$oppId"=>array('$exists'=>true)
		));

		if($db){
			$matches = $db->collection('user_matches')->get($conditions,$fields);
			return static::create($matches,array('set'=>true,'existing'=>true));
		}else{
			return static::find('all',compact('conditions','fields'));
		}
	}

}

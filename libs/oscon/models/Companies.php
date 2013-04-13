<?php
namespace bc\models;

use tip\models\base\BaseModel;
use tip\models\Users;
use tip\models\Accounts;
use tip\core\Util;
use bc\traits\user\TalentTrait;

class Companies extends BaseModel
{
    function __construct()
    {
    }


    public static function currentUserSavedCompanies($db,$interestCutoff=45, $sort = true){
        $user = Users::current();
        $companies = array();
        if($user){
			$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$user->id(),'companyInterest'=>array('$gte'=>$interestCutoff)));
			$rankings = $db->collection('user_matches')->get($find,array('traits.company'=>true),array('sort'=>array('traits.0.companyInterest'=>-1)));
			$savedIds = array();
			foreach($rankings as $cId => $rank){
				$savedIds[] = Util::nvlA($rank,'traits.0.company');
			}

			if($savedIds){
				$savedIds = static::toMongoId($savedIds);

                $companies = $db->collection('companies')->get(array('_id'=>array('$in'=>$savedIds)),array('name'=>true));
				if($sort){
					$new = array();
					foreach($savedIds as $id){
						foreach($companies as $index => $c){
							if($c['_id'] == $id){
								$new[] = $c;
								unset($companies[$index]);
								break;
							}
						}
					}
					$companies = $new;
				}

            }
        }
        return static::create($companies,array('set'=>true,'existing'=>true));
    }

	public static function byIdRemote($db,$id,$required=true,$forceSet=false){
		$id = static::toMongoId(Util::toArray($id));

		$companies = $db->collection('companies')->get(array('_id'=>array('$in'=>$id)));
		if(!$forceSet && $companies && count($id) == 1){
			return static::create($companies[0],array('existing'=>true));
		}else if($companies){
			return static::create($companies,array('existing'=>true));
		}
		if($required){
			throw new \lithium\core\ConfigException("No Companies found with id: ".print_r($id,true));
		}
		return null;
	}



}

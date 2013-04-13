<?php
namespace bc\hitch;
use tip\core\Util;

abstract class OpportunityRanker extends Ranker
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

	public function summarize($job,&$unMatched=array())
	{
		$summary = array('_lastUpdated'=>time());
		$summary['roles'] = $roles = $this->_sumRoles($job,$unMatched['roles']);
		$summary['locations'] = $this->_sumLocations($job,$unMatched['locations']);
		$summary['salary'] = $this->_sumSalary($job);
		$summary['equity'] = $this->_sumEquity($job);
		$summary['skills'] = $this->_sumSkills($job,$roles,$unMatched['skill']);
		return $summary;
	}

    public function matchSkills($skills,$map=null,$roles=nulls,&$unMatchingSkills=null){
        $matches = array();
        $matchingSkills = array();
        $skills = Util::toArray($skills);
        $roles = Util::toArray($roles);
        $skills = array_map('strtolower', $skills);
        if(!$map)$map = $this->map('skills');
        array_walk($map,function($skillMap,$skillKey) use ($skills,&$matches,$roles,&$matchingSkills){
            $search = true;
            if($roles){
                $skillRoles = Util::nvlA($skillMap,'roles');
                $search = array_intersect($roles,$skillRoles);
            }
            if($search){
                $mapSkills = array_merge(Util::nvlA2A($skillMap,'name'),Util::nvlA2A($skillMap,'synonyms'));
                $mapSkills = array_map('strtolower', $mapSkills);
                array_walk($skills,function($skill) use ($mapSkills,&$matches,$skillKey,&$matchingSkills){
//                    echo "$skill<pre>"; print_r($mapSkills ); print_r(array_search( $skill,$mapSkills )); echo "</pre>";
                    if(array_search( $skill,$mapSkills ) !== false){
                        $matches[] = $skillKey;
                        $matchingSkills[] = $skill;
                    }
                });
            }
        });
        $unMatchingSkills = array_diff($skills,$matchingSkills);
        return array_unique($matches);
    }

}

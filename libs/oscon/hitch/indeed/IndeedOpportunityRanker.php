<?php
namespace bc\hitch\indeed;
use bc\hitch\OpportunityRanker;
use tip\core\Util;

class IndeedOpportunityRanker extends OpportunityRanker
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }


    protected function _sumLocations($job,&$unMatched=null){
        $match = AngelList::angelTags($job,'job','location');
        $mappings = AngelList::reverseMappings($this->map('locations'));
        $sum = AngelList::mapMatch($match,$mappings,$unMatched);

        return $sum;
    }
    protected function _sumRoles($job,&$unMatched=null){
        $match = AngelList::angelTags($job,'job','role');
        $mappings = AngelList::reverseMappings($this->map('roles'));
        $sum = AngelList::mapMatch($match,$mappings,$unMatched);

        return $sum;
    }
    protected function _sumSkills($job,$roles=null,&$unMatched=null){
        $skills = AngelList::angelTags($job,'job','skill');
        $sum = array();
        $map = $this->map('skills');
        array_walk($skills,function($skill) use(&$sum){
            $sum[] = Util::nvlA($skill,'name');
        });
        $sumSkills = $this->matchSkills($sum,$map,$roles,$unMatchingSkills);

        return $sumSkills;
    }

    protected function _sumSalary($job){
        $min = Util::nvlA($job,'salary_min',0);
        $max = Util::nvlA($job,'salary_max',0);
        if($max){
            $min /= 1000;
            $max /= 1000;
            return array($min,$max);
        }
        return -1;
    }
    protected function _sumEquity($job){
        $min = Util::nvlA($job,'equity_min',0);
        $max = Util::nvlA($job,'equity_max',0);
        if($max){
            return array($min,$max);
        }
        return -1;
    }
}

<?php
namespace bc\hitch\indeed;

use bc\hitch\CompanyRanker;
use tip\core\Util;

class IndeedCompanyRanker extends CompanyRanker
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }



    protected function _sumLocations($company,&$unMatched=null){
        $data = AngelList::angelTags($company,'company','location');
        $mappings = AngelList::reverseMappings($this->map('locations'));
        $sum = AngelList::mapMatch($data,$mappings,$unMatched);

        return $sum;
    }
    protected function _sumMarkets($company,&$unMatched=null){
        $data = AngelList::angelTags($company,'company','market');
        $mappings = AngelList::reverseMappings($this->map('markets'));
        $sum = AngelList::mapMatch($data,$mappings,$unMatched);
        return $sum;
    }
    protected function _sumCompanySize($company){
        $size = 2;//start at 2, increase based on follower count
        $followers = Util::nvlA($company,'follower_count',0);
        if($followers > 100)$size = 15;
        else if($followers > 50)$size = 10;
        else if($followers > 25)$size = 5;
        return $size;
    }

}

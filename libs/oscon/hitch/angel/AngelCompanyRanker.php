<?php
namespace bc\hitch\angel;

use bc\hitch\CompanyRanker;
use tip\core\Util;

class AngelCompanyRanker extends CompanyRanker
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function sumLocations($compData,&$unMatched,$company){
        $data = AngelList::angelTags($compData,'company','location');
        $mappings = AngelList::reverseMappings($this->map('locations'));
        $sum = AngelList::mapMatch($data,$mappings,$unMatched);

        return $sum;
    }
	public function sumMarkets($compData,&$unMatched,$company){
        $data = AngelList::angelTags($compData,'company','market');
        $mappings = AngelList::reverseMappings($this->map('markets'));
        $sum = AngelList::mapMatch($data,$mappings,$unMatched);
        return $sum;
    }
	public function sumCompanySize($compData,$company){
        $size = 2;//start at 2, increase based on follower count
        $followers = Util::nvlA($compData,'follower_count',0);
        if($followers > 100)$size = 15;
        else if($followers > 50)$size = 10;
        else if($followers > 25)$size = 5;
		return $size;
    }

}

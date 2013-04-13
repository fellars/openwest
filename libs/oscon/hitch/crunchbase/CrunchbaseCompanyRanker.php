<?php
namespace bc\hitch\crunchbase;

use bc\hitch\CompanyRanker;
use tip\core\Util;

class CrunchbaseCompanyRanker extends CompanyRanker
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
        $offices = Crunchbase::offices($compData);
        $mappings =$this->reverseZipMappings();
		$sum = $this->matchZips($offices,"zip_code",$mappings,$unMatched);
        return $sum;
    }
	public function sumMarkets($compData,&$unMatched,$company){
        $category = Crunchbase::category($compData);
        $mappings = Crunchbase::reverseMarkets($this->map('markets'));
		$sum = array();
		if($category){
			if(isset($mappings[$category])){
				$sum = Util::nvlA2A($mappings,$category);
			}else{
				$unMatched[] = $category;
			}
		}
        return $sum;
    }
	public function sumCompanySize($compData,$company){
        $numEmployees = (int)Crunchbase::numEmployees($compData);
		$fundingDate = Crunchbase::foundingDate($compData);

		if(!$numEmployees && $fundingDate){
			$diff = time() - $fundingDate;
			$diff = $diff / ( 60 * 60 * 24 );
			$numEmployees = 1;
			if($diff > (365*5))$numEmployees = 80;
			else if($diff > (365*4))$numEmployees = 40;
			else if($diff > (365*3))$numEmployees = 30;
			else if($diff > (365*2))$numEmployees = 20;
			else if($diff > (365*1))$numEmployees = 10;

		}
        return static::convertFromNumEmployees($numEmployees);
    }

}

<?php
namespace bc\hitch;

use tip\core\Util;

abstract class CompanyRanker extends Ranker
{

	public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }
	public static function convertToNumEmployees($size){
		$size = (int)$size;
		$numEmployees = -1;
		if($size > 0){
			if($size >= 100)$numEmployees = 1000;
			else if($size > 90)$numEmployees = 800;
			else if($size > 85)$numEmployees = 500;
			else if($size > 75)$numEmployees = 100;
		   else if($size > 60)$numEmployees = 50;
		   else if($size >50)$numEmployees = 25;
		   else if($size > 30)$numEmployees = 15;
		   else if($size > 10)$numEmployees = 5;
		   else if($size > 5)$numEmployees = 3;
		   else $numEmployees = 1;

		}
		return $numEmployees;

	}
	public static function convertFromNumEmployees($numEmployees){
		$numEmployees = (int)$numEmployees;
		$size = -1;
		if($numEmployees > 0){
			if($numEmployees >= 1000)$size = 100;
			else if($numEmployees > 800)$size = 90;
			else if($numEmployees > 500)$size = 85;
			else if($numEmployees > 100)$size = 75;
		   else if($numEmployees > 50)$size = 60;
		   else if($numEmployees > 25)$size = 50;
		   else if($numEmployees > 15)$size = 30;
		   else if($numEmployees > 5)$size = 10;
		   else if($numEmployees > 0)$size = 5;

		}
		return $size;
	}

	public function summarize($company,$newData,&$unMatched=array())
    {

		$locations = Util::toArray(\bc\traits\company\CoreTrait::ifHasData($company,'locations'));
		$markets =Util::toArray(\bc\traits\company\CoreTrait::ifHasData($company,'markets'));
		$size = \bc\traits\company\CoreTrait::ifHasData($company,'size');

        $summary = array('_lastUpdated'=>time());
        $summary['locations'] = array_unique(array_merge($locations,Util::toArray($this->sumLocations($newData,$unMatched['locations'],$company))));
        $summary['markets'] = array_unique(array_merge($markets,Util::toArray($this->sumMarkets($newData,$unMatched['markets'],$company))));
        $summary['size'] = $this->sumCompanySize($newData,$size,$company);
		if($size && $size > $summary['size'])$summary['size'] = $size;
        return $summary;
    }

	public abstract function sumLocations($data,&$unmatched,$company);
	public abstract function sumMarkets($data,&$unmatched,$company);
	public abstract function sumCompanySize($data,$company);


	public function reverseZipMappings($mappings=null){
		if(!$mappings)$mappings = $this->map('locations');
		$newMap = array();
		foreach($mappings as $mapKey => $mappings){
			$zipMappings = Util::nvlA2A($mappings,'zipCodes');
			foreach($zipMappings as $map){
				$matches = Util::nvlA2A($newMap,$map);
				$matches[] = $mapKey;
				$newMap[$map] = $matches;
			}
		}
		return $newMap;
	}

	public function matchZips($items,$keyName,$maps,&$unmatched){
		$matches = array();
		foreach($items as $item){
			$zip = Util::nvlA($item,$keyName);
			if($zip){
				if(isset($maps[$zip])){
					$matches = array_merge($matches,Util::nvlA2A($maps,$zip));
				}else{
					$unmatched[] = $zip;
				}
			}
		}
		$matches = array_unique($matches);
		return $matches;
	}
}

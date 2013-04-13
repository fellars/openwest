<?php
namespace bc\hitch\crunchbase;

use tip\core\Util;

class Crunchbase extends \lithium\core\StaticObject{

    public static function offices($company){
		return Util::nvlA2A($company,'offices');
    }

	public static function category($company){
		return Util::nvlA($company,'category_code');
	}
	public static function numEmployees($company){
		return Util::nvlA2A($company,'number_of_employees');
	}
	public static function foundingDate($company){
		$year =  Util::nvlA2A($company,'founded_year');
		$month  = Util::nvlA2A($company,'founded_month',6);
		$date = Util::nvlA2A($company,'founded_day',1);
		if($year){
			return strtotime("$month/$date/$year");
		}
		return false;

	}
    public static function reverseMarkets($mappings){
        $newMap = array();
		foreach($mappings as $mapKey => $mappings){
			$cbMap = Util::nvlA2A($mappings,'crunchbaseMappings');
			foreach($cbMap as $map){
				
				$matches = Util::nvlA2A($newMap,$map);
				$matches[] = $mapKey;
				$newMap[$map] = $matches;
			}
		}
		return $newMap;
    }
//Advertising
//BioTech
//CleanTech
//Consumer Electronics/Devices
//Consumer Web
//eCommerce
//Education
//Enterprise
//Games, Video and Entertainment
//Legal
//Mobile/Wireless
//Network/Hosting
//Consulting
//Communications
//Search
//Security
//Semiconductor
//Software
//Other

//$codes = array(
//);
//$zips = array();
//foreach($codes as $code){
//	list($loc,$zip) = explode('/',$code);
//	if(!isset($zips[$loc]))$zips[$loc] = $zip;
//	else $zips[$loc] .= ",$zip";
//
//}
//$locations = $this->setting("mappings.locations");
//foreach($locations as $loc => $data){
//	if( isset($zips[$loc]) ){
//		$locations[$loc]['zipCodes'] = $zips[$loc];
//
//	}
//}
//$this->setting("mappings.locations",$locations,true);
//echo "<pre>"; print_r($this->setting("mappings.locations") ); echo "</pre>";exit;



}
?>
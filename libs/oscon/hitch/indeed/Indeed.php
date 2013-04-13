<?php
namespace bc\hitch\indeed;

use tip\core\Util;

class Indeed extends \lithium\core\StaticObject{

    public static function angelTags($data,$dataType,$tagType){
        $tags = array();
        if(strpos($tagType,'Tag')===false)$tagType = Util::inflect($tagType,'c').'Tag';
        $dataTagName = $dataType == 'job' ? 'tags' : ($tagType == 'LocationTag' ? 'locations' : ($tagType == 'MarketTag' ? 'markets' : '?'));
        $dataTags = Util::nvlA2A($data,$dataTagName);
        foreach($dataTags as $dataTag){
            if(Util::nvlA($dataTag,'tag_type') == $tagType){
                $tags[Util::nvlA($dataTag,'id')] = $dataTag;
            }
        }
        return $tags;
    }

    public static function mapMatch($data,$mappings,&$unMapped=null){
        $map = array();
        $unMapped = array();
        foreach($data as $tagId => $dataVal){
            if(isset($mappings[$tagId])){
                $map = array_merge($map,$mappings[$tagId]);
            }else{
                $unMapped[$tagId] = $dataVal;
            }
        }
        return array_unique($map);
    }

    public static function reverseMappings($mappings){
        $newMap = array();
        foreach($mappings as $mapKey => $mappings){
            $angelMappings = Util::nvlA2A($mappings,'angelMappings');
            foreach($angelMappings as $map){
                $tagId = Util::nvlA($map,'tagId');
                $matches = Util::nvlA2A($newMap,$tagId);
                $matches[] = $mapKey;
                $newMap[$tagId] = $matches;
            }
        }
        return $newMap;
    }
}
?>
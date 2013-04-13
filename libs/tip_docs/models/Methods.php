<?php
namespace tip_docs\models;

use tip\models\base\BaseModel;
use tip_docs\traits\CoreMethodTrait;
use tip\core\Util;
use tip_docs\services\PhpService;

class Methods extends BaseModel{

    protected static $_class = array();

    public function methodClass($entity, $class=null){
        $hash = spl_object_hash($entity);
        if($class)return static::$_class[$hash] = $class;
        if(isset(static::$_class[$hash]) && static::$_class[$hash]){
            return static::$_class[$hash];
        }
        $classId = $entity->tData('/class');
        return static::$_class[$hash] = Classes::byId($classId);
    }

    public function updateChanges($entity, $save=true, $class=null){
        $class = $this->methodClass($entity,$class);
        $php = PhpService::create();
        $classPath = $class->tData('/class');
        $lib = Util::lib($classPath);
        CoreMethodTrait::addLib($lib);
        $name = $entity->name();
        $info = $php->methodInfo($classPath,$name);
        $content = $php->methodContent($classPath,$name,true);
        $checksum = md5($content);
        $thisChecksum = $entity->tData('/checksum');
        if($checksum !== $thisChecksum){
            //new
            $params = $php->methodParams($classPath,$name);
            unset($info['name']);
            unset($info['file']);
            unset($info['shortName']);
            $info['content'] = $content;
            $info['params'] = $params;
            $info['checksum'] = $checksum;
            $entity->coreData($info);
            return $save ? $entity->save() : true;
        }
        return false;
    }

}

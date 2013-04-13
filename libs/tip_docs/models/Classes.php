<?php
namespace tip_docs\models;

use tip\models\base\BaseModel;
use tip_docs\services\PhpService;
use tip_docs\traits\CoreClassTrait;
use tip_docs\models\Methods;
use tip_docs\traits\CoreMethodTrait;
use tip\core\Util;

class Classes extends BaseModel{

    protected static $_methods = array();

    public static function get($class, $updateChanges=false,$createIfNew=true){
        $className = is_object($class) ? get_class($class) : $class;
        $cEntity = CoreClassTrait::findFirst(array('class'=>$className));
        if(!$cEntity && $createIfNew){
            $cEntity = CoreClassTrait::newEntity(array(),array('class'=>$className),false);
            $updateChanges = true;
        }
        if($updateChanges && $cEntity)$cEntity->updateChanges();
        return $cEntity;
    }

    public function updateChanges($entity, $updateMethods = true,$save=true){
        $php = PhpService::create();
        $class = $entity->tData('/class');
        $lib = Util::lib($class);
        CoreClassTrait::addLib($lib);
        $info = $php->classInfo($class);
        $checksum = md5_file($info['file']);
        $thisChecksum = $entity->tData('/checksum');
        if($checksum !== $thisChecksum){
            //new
            $shortName = $info['shortName'];
            unset($info['name']);
            unset($info['shortName']);
            $info['checksum'] = $checksum;
            $info['library'] = $lib;
            $info['parent'] = get_parent_class($class);
            $entity->name = $shortName;
            $entity->coreData($info);
            $this->updateProperties($entity,false);
            if($updateMethods)$this->updateMethods($entity,false);
            return $save ? $entity->save() : true;
        }
        return false;
    }
    public function updateProperties($entity,$save=true){
        $php = PhpService::create();
        $class = $entity->tData('/class');
        $lib = Util::lib($class);
        CoreClassTrait::addLib($lib);
        $props = $php->classProperties($class);
        $updated = array();
        foreach($props as $prop){
            $info = $php->propertyInfo($class,$name = $prop['name']);
            unset($info['text']);//no text on properties
            $updated[$name] = $info;
        }
        $entity->coreData('properties',$updated);
        if($save)$entity->save();
        return $updated;
    }
    public function updateMethods($entity,$save=true){
        $php = PhpService::create();
        $class = $entity->tData('/class');
        $lib = Util::lib($class);
        CoreClassTrait::addLib($lib);
        $methods = $php->classMethods($class);
        $existing = $this->methods($entity);
        $saveMethods = array();
        foreach($methods as $method){
            $name = $method->name;
            $m = $this->method($entity,$name);
            if(!$m){
                $m = CoreMethodTrait::newEntity(compact('name'),array('class'=>$entity->id()),false);
            }
            $m->updateChanges(true,$entity);
            $saveMethods[$m->name()] = $m->id();
            unset($existing[$name]);
        }
        if($existing){
            //these are no longer active
            foreach($existing as $e){
                $e->status = 'inactive';
                $e->save();
            }
        }
        $entity->tData('/methods',$saveMethods);
        $this->loadMethods($entity);//refresh
        if($save)$entity->save();
    }

    public function loadMethods($entity){
        $hash = spl_object_hash($entity);
        $methods = CoreMethodTrait::find(array('class'=>$entity->id()));
        static::$_methods[$hash] = array();
        foreach($methods as $method){
            static::$_methods[$hash][$method->name()] = $method;
        }
        return static::$_methods[$hash];
    }

    public function methods($entity,$load=true){
        $hash = spl_object_hash($entity);
        if($load && !isset(static::$_methods[$hash])){
            $this->loadMethods($entity);
        }
        if(isset(static::$_methods[$hash]))return static::$_methods[$hash];
        return array();
    }

    public function method($entity,$name){
        $hash = spl_object_hash($entity);
        if(!isset(static::$_methods[$hash])){
            static::$_methods[$hash] = array();
        }
        if(!isset(static::$_methods[$hash][$name])){
            $method = CoreMethodTrait::findFirst(array('class'=>$entity->id(),array(),compact('name')));
            if($method)static::$_methods[$hash][$name] = $method;
        }
        return isset(static::$_methods[$hash][$name]) ? static::$_methods[$hash][$name] : null;
    }
}
